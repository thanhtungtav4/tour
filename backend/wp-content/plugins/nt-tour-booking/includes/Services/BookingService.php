<?php
namespace TourBooking\Services;

use TourBooking\Repositories\BookingRepository;
use TourBooking\Repositories\PassengerRepository;
use TourBooking\Repositories\AccessTokenRepository;
use TourBooking\Helpers\TokenGenerator;
use TourBooking\ActivityLogger;
use TourBooking\Models\Booking;
use TourBooking\Models\Passenger;
use TourBooking\Models\Tour;

class BookingService
{
    protected BookingRepository $booking_repo;
    protected PassengerRepository $passenger_repo;
    protected AccessTokenRepository $token_repo;
    protected SeatHoldService $seat_hold_service;

    public function __construct()
    {
        $this->booking_repo = new BookingRepository();
        $this->passenger_repo = new PassengerRepository();
        $this->token_repo = new AccessTokenRepository();
        $this->seat_hold_service = new SeatHoldService();
    }

    /**
     * Create booking from spec-compliant request data
     */
    public function create_booking(array $data): array
    {
        global $wpdb;

        $tour = Tour::find_by_slug($data['tour_slug'] ?? '');
        if (!$tour) {
            return ['success' => false, 'error_code' => 'tour_not_found', 'message' => 'Không tìm thấy tour.'];
        }

        $departure = $this->get_departure_by_date($tour->get_id(), $data['departure_date']);
        if (!$departure) {
            return ['success' => false, 'error_code' => 'departure_not_found', 'message' => 'Không tìm thấy lịch khởi hành.'];
        }

        if (strtotime($departure['start_date']) < strtotime(date('Y-m-d'))) {
            return ['success' => false, 'error_code' => 'departure_past', 'message' => 'Lịch khởi hành đã qua.'];
        }

        $total_people = (int) ($data['participants'] ?? 1);
        $available = (int) $departure['capacity'] - (int) $departure['booked_count'];
        if ($total_people > $available) {
            return ['success' => false, 'error_code' => 'departure_full', 'message' => "Chỉ còn {$available} chỗ."];
        }

        $main_contact = $data['main_contact'] ?? [];
        $booking_data = [
            'tour_departure_id' => (int) $departure['id'],
            'customer_name' => $main_contact['full_name'] ?? '',
            'customer_phone' => $main_contact['phone'] ?? '',
            'customer_email' => $main_contact['email'] ?? null,
            'adult_count' => $total_people,
            'child_count' => 0,
            'infant_count' => 0,
            'total_people' => $total_people,
            'expected_people' => $total_people,
            'pickup_point_id' => $data['pickup_point_id'] ?? null,
            'booking_status' => Booking::STATUS_PENDING_PAYMENT,
            'payment_status' => Booking::PAYMENT_UNPAID,
            'passenger_info_status' => Booking::PASSENGER_MISSING,
            'note' => $data['notes'] ?? null,
            'source' => 'website',
        ];

        $tour_price = (float) $departure['adult_price'] * $total_people;
        $services_total = $this->calculate_services_total($data['services'] ?? [], $tour);
        $rental_total = $this->calculate_rental_total($data['rental_items'] ?? []);
        $total_amount = $tour_price + $services_total + $rental_total;

        $booking_data['total_amount'] = $total_amount;
        $booking_data['deposit_amount'] = 0;

        $wpdb->query('START TRANSACTION');

        $booking_id = $this->booking_repo->create($booking_data);
        if (!$booking_id) {
            $wpdb->query('ROLLBACK');
            return ['success' => false, 'error_code' => 'booking_failed', 'message' => 'Không thể tạo booking.'];
        }

        $booking = $this->booking_repo->find($booking_id);

        $passenger_ids = [];
        $passengers_data = $data['passengers'] ?? [];
        for ($i = 0; $i < $total_people; $i++) {
            $pdata = $passengers_data[$i] ?? [];
            $pid = $this->passenger_repo->create([
                'booking_id' => $booking_id,
                'tour_departure_id' => $booking_data['tour_departure_id'],
                'name' => sanitize_text_field($pdata['full_name'] ?? ''),
                'phone' => sanitize_text_field($pdata['phone'] ?? ''),
                'email' => !empty($pdata['email']) ? sanitize_email($pdata['email']) : null,
                'passenger_type' => Passenger::TYPE_ADULT,
                'pickup_point_id' => $pdata['pickup_point_id'] ?? $data['pickup_point_id'] ?? null,
                'is_placeholder' => empty($pdata['full_name']),
                'profile_status' => empty($pdata['full_name']) ? Passenger::PROFILE_MISSING : Passenger::PROFILE_COMPLETED,
            ]);

            if (!$pid) {
                $wpdb->query('ROLLBACK');
                return ['success' => false, 'error_code' => 'passenger_create_failed', 'message' => 'Không thể tạo thông tin khách.'];
            }

            $passenger_ids[] = $pid;
        }

        if (!empty($data['selected_seats'])) {
            if (empty($departure['vehicle_id'])) {
                $wpdb->query('ROLLBACK');
                return ['success' => false, 'error_code' => 'vehicle_not_found', 'message' => 'Không tìm thấy xe cho lịch khởi hành.'];
            }

            if (count($data['selected_seats']) !== $total_people) {
                $wpdb->query('ROLLBACK');
                return ['success' => false, 'error_code' => 'seat_count_mismatch', 'message' => 'Số ghế chọn không khớp số lượng khách.'];
            }

            $seat_result = $this->seat_hold_service->hold_seats(
                $booking_data['tour_departure_id'],
                (int) $departure['vehicle_id'],
                $data['selected_seats'],
                $booking_id,
                $passenger_ids
            );

            if (!$seat_result['success']) {
                $wpdb->query('ROLLBACK');
                return [
                    'success' => false,
                    'error_code' => 'seat_not_available',
                    'message' => $seat_result['message'] ?? 'Ghế đã được giữ hoặc đặt bởi người khác.',
                    'failed_seats' => $seat_result['failed_seats'] ?? [],
                ];
            }

            $this->booking_repo->update($booking_id, ['hold_expires_at' => $seat_result['hold_expires_at'] ?? null]);
            foreach ($passenger_ids as $idx => $pid) {
                if (isset($data['selected_seats'][$idx])) {
                    $this->passenger_repo->update($pid, ['seat_code' => sanitize_text_field($data['selected_seats'][$idx])]);
                }
            }
        }

        $this->save_rental_items($booking_id, $data['rental_items'] ?? []);

        $payment_method = $data['payment_method'] ?? 'transfer';
        ActivityLogger::log_booking_created($booking_id, $booking_data);

        $wpdb->query('COMMIT');

        $passengers = $this->passenger_repo->get_by_booking($booking_id);
        $passenger_list = [];
        foreach ($passengers as $idx => $p) {
            $passenger_list[] = [
                'id' => $p->id,
                'full_name' => $p->name,
                'seat' => $p->seat_code,
                'pickup_point' => $this->get_pickup_point_name($p->pickup_point_id),
                'qr_code_url' => $p->qr_token_hash ? TokenGenerator::generate_qr_url($p->qr_token_hash) : null,
            ];
        }

        $hold_expires = null;
        if (!empty($booking->hold_expires_at)) {
            $hold_expires = date('c', strtotime($booking->hold_expires_at));
        }

        return [
            'success' => true,
            'data' => [
                'booking_id' => $booking->code,
                'status' => 'pending',
                'hold_expires_at' => $hold_expires,
                'total_amount' => $total_amount,
                'total_amount_formatted' => number_format($total_amount, 0, '.', ',') . 'đ',
                'breakdown' => [
                    'tour_price' => $tour_price,
                    'services_total' => $services_total,
                    'rental_total' => $rental_total,
                    'rental_items' => $this->get_rental_items_breakdown($data['rental_items'] ?? []),
                ],
                'deposit_amount' => 0,
                'remaining_amount' => $total_amount,
                'payment_method' => $payment_method,
                'payment_status' => 'unpaid',
                'passengers' => $passenger_list,
                'next_steps' => [
                    'Kiểm tra email để nhận thông tin chi tiết',
                    'Thanh toán trước ngày khởi hành ít nhất 3 ngày',
                    'Nhận QR code để check-in',
                ],
            ],
        ];
    }

    private function get_departure_by_date(int $tour_id, string $date): ?array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nt_tour_departures';
        $b_table = $wpdb->prefix . 'nt_bookings';
        $dv_table = $wpdb->prefix . 'nt_departure_vehicles';

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT d.*, (SELECT COUNT(*) FROM {$b_table} b WHERE b.tour_departure_id = d.id AND b.booking_status IN ('pending_payment', 'confirmed', 'completed')) as booked_count,
             (SELECT vehicle_id FROM {$dv_table} dv WHERE dv.tour_departure_id = d.id AND dv.status = 'active' LIMIT 1) as vehicle_id
             FROM {$table} d WHERE d.tour_id = %d AND d.start_date = %s AND d.status = 'open'",
            $tour_id, $date
        ), ARRAY_A);

        return $row ?: null;
    }

    private function calculate_services_total(array $services, Tour $tour): float
    {
        $total = 0;
        $available = $tour->get_services();
        foreach ($services as $service_id) {
            foreach ($available as $svc) {
                if (($svc['id'] ?? '') === $service_id) {
                    $total += (float) ($svc['price'] ?? 0);
                }
            }
        }
        return $total;
    }

    private function calculate_rental_total(array $rental_items): float
    {
        $total = 0;
        foreach ($rental_items as $item_id => $qty) {
            $item = $this->get_rental_item($item_id);
            if ($item) {
                $total += (float) $item['price'] * max(0, (int) $qty);
            }
        }
        return $total;
    }

    private function save_rental_items(int $booking_id, array $rental_items): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nt_booking_rental_items';
        foreach ($rental_items as $item_id => $qty) {
            $qty = max(0, (int) $qty);
            if ($qty <= 0) continue;
            $item = $this->get_rental_item($item_id);
            if (!$item) continue;
            $subtotal = (float) $item['price'] * $qty;
            $wpdb->insert($table, [
                'booking_id' => $booking_id,
                'rental_item_id' => $item_id,
                'quantity' => $qty,
                'unit_price' => (float) $item['price'],
                'subtotal' => $subtotal,
                'created_at' => current_time('mysql'),
            ], ['%d', '%s', '%d', '%f', '%f', '%s']);
        }
    }

    private function get_rental_item(string $item_id): ?array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nt_rental_items';
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %s AND is_active = 1", $item_id
        ), ARRAY_A);
    }

    private function get_rental_items_breakdown(array $rental_items): array
    {
        $result = [];
        foreach ($rental_items as $item_id => $qty) {
            $item = $this->get_rental_item($item_id);
            if ($item) {
                $result[] = [
                    'id' => $item_id,
                    'name' => $item['name'],
                    'qty' => (int) $qty,
                    'subtotal' => (float) $item['price'] * (int) $qty,
                ];
            }
        }
        return $result;
    }

    private function get_pickup_point_name(?int $pickup_point_id): string
    {
        if (!$pickup_point_id) return '';
        global $wpdb;
        $table = $wpdb->prefix . 'nt_pickup_points';
        return $wpdb->get_var($wpdb->prepare("SELECT name FROM {$table} WHERE id = %d", $pickup_point_id)) ?: '';
    }

    public function hold_seats_only(int $departure_id, array $seats): array
    {
        global $wpdb;
        $dv_table = $wpdb->prefix . 'nt_departure_vehicles';
        $vehicle = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$dv_table} WHERE tour_departure_id = %d AND status = 'active' LIMIT 1", $departure_id
        ), ARRAY_A);

        if (!$vehicle) {
            return ['success' => false, 'error_code' => 'departure_not_found', 'message' => 'Không tìm thấy xe cho depart.'];
        }

        $result = $this->seat_hold_service->hold_seats($departure_id, (int) $vehicle['id'], $seats, 0, []);
        if (!$result['success']) {
            return ['success' => false, 'error_code' => 'seat_not_available', 'message' => $result['message'], 'failed_seats' => $result['failed_seats']];
        }

        return [
            'success' => true,
            'data' => [
                'hold_id' => 'hold_' . wp_generate_password(12, false),
                'seats' => $result['held_seats'],
                'expires_at' => date('c', strtotime($result['hold_expires_at'])),
                'expires_in_minutes' => (int) (strtotime($result['hold_expires_at']) - time()) / 60,
            ],
        ];
    }

    public function get_booking_public(string $booking_code): ?array
    {
        $booking = $this->booking_repo->find_by_code($booking_code);
        if (!$booking) return null;

        return $this->build_public_response($booking);
    }

    public function lookup_bookings(?string $booking_id, ?string $email, ?string $phone): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nt_bookings';
        $d_table = $wpdb->prefix . 'nt_tour_departures';
        $p_table = $wpdb->posts;

        $where = [];
        $values = [];

        if ($booking_id) {
            $where[] = 'b.code = %s';
            $values[] = $booking_id;
        }
        if ($email) {
            $where[] = 'b.customer_email = %s';
            $values[] = sanitize_email($email);
        }
        if ($phone) {
            $where[] = 'b.customer_phone = %s';
            $values[] = sanitize_text_field($phone);
        }

        if (empty($where)) return [];

        $sql = "SELECT b.*, t.post_title as tour_name, d.start_date as departure_date
                FROM {$table} b
                JOIN {$d_table} d ON b.tour_departure_id = d.id
                JOIN {$p_table} t ON d.tour_id = t.ID
                WHERE " . implode(' OR ', $where) . " ORDER BY b.created_at DESC LIMIT 20";

        $results = $wpdb->get_results($wpdb->prepare($sql, ...$values), ARRAY_A);

        return array_map(fn($r) => [
            'booking_id' => $r['code'],
            'tour_name' => $r['tour_name'],
            'departure_date' => $r['departure_date'],
            'status' => $r['booking_status'],
            'passengers_count' => (int) $r['total_people'],
            'payment_method' => $r['payment_status'],
            'total_amount' => (float) $r['total_amount'],
            'payment_status' => $r['payment_status'],
        ], $results ?: []);
    }

    public function get_booking_admin(string $booking_code): ?array
    {
        $booking = $this->booking_repo->find_by_code($booking_code);
        if (!$booking) return null;

        $data = $booking->to_array();
        $data['passengers'] = array_map(fn($p) => $p->to_array(), $booking->get_passengers());
        $data['booked_seats'] = $booking->get_booked_seats(true);
        $data['rental_items'] = $this->get_booking_rental_items($booking->id);
        $data['payments'] = $this->get_booking_payments($booking->id);
        return $data;
    }

    public function update_booking(string $booking_code, array $data): array
    {
        $booking = $this->booking_repo->find_by_code($booking_code);
        if (!$booking) {
            return ['success' => false, 'error_code' => 'booking_not_found', 'message' => 'Không tìm thấy booking.'];
        }

        $update = [];

        if (isset($data['status'])) {
            $update['booking_status'] = $data['status'];
            if ($data['status'] === 'confirmed') {
                $update['confirmed_at'] = current_time('mysql');
            }
        }
        if (isset($data['notes'])) {
            $update['note'] = $data['notes'];
        }

        if (!empty($update)) {
            $this->booking_repo->update($booking->id, $update);
        }

        if (!empty($data['passengers'])) {
            foreach ($data['passengers'] as $pdata) {
                if (!empty($pdata['id'])) {
                    $pupdate = [];
                    if (isset($pdata['seat'])) $pupdate['seat_code'] = $pdata['seat'];
                    if (isset($pdata['pickup_point_id'])) $pupdate['pickup_point_id'] = (int) $pdata['pickup_point_id'];
                    if (!empty($pupdate)) {
                        $this->passenger_repo->update((int) $pdata['id'], $pupdate);
                    }
                }
            }
        }

        if (isset($data['rental_items'])) {
            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'nt_booking_rental_items', ['booking_id' => $booking->id], ['%d']);
            $this->save_rental_items($booking->id, $data['rental_items']);
        }

        return ['success' => true, 'data' => $this->get_booking_admin($booking_code)];
    }

    /**
     * Cancel booking by code (spec format)
     */
    public function cancel_by_code(string $booking_code, string $reason = '', $refund_amount = null, bool $notify = true): array
    {
        $booking = $this->booking_repo->find_by_code($booking_code);
        if (!$booking) {
            return ['success' => false, 'error_code' => 'booking_not_found', 'message' => 'Không tìm thấy booking.'];
        }

        return $this->cancel((int) $booking->id, $reason);
    }

    /**
     * Cancel booking by ID
     */
    public function cancel(int $booking_id, string $reason = ''): array
    {
        $booking = $this->booking_repo->find($booking_id);
        if (!$booking) {
            return ['success' => false, 'error_code' => 'booking_not_found', 'message' => 'Không tìm thấy booking.'];
        }

        $seat_data = $booking->get_booked_seats(true);
        if (!empty($seat_data)) {
            foreach ($seat_data as $seat) {
                $this->seat_hold_service->release_seats(
                    $booking->tour_departure_id,
                    (int) $seat['departure_vehicle_id'],
                    [$seat['seat_code']]
                );
            }
        }

        $this->booking_repo->update_status($booking_id, Booking::STATUS_CANCELLED);
        ActivityLogger::log_booking_cancelled($booking_id, $reason);

        return [
            'success' => true,
            'data' => [
                'booking_id' => $booking->code,
                'status' => 'cancelled',
                'cancelled_at' => current_time('mysql'),
                'reason' => $reason,
            ],
        ];
    }

    public function confirm_payment(int $booking_id, string $payment_status = 'paid'): array
    {
        $booking = $this->booking_repo->find($booking_id);
        if (!$booking) {
            return ['success' => false, 'error_code' => 'booking_not_found', 'message' => 'Không tìm thấy booking.'];
        }

        $update_data = ['payment_status' => $payment_status];
        if ($payment_status === 'paid') {
            $update_data['booking_status'] = Booking::STATUS_CONFIRMED;
            $update_data['confirmed_at'] = current_time('mysql');
        }

        $this->booking_repo->update($booking_id, $update_data);

        if ($booking->seat_selection_mode === Booking::SEAT_CUSTOMER_SELECT || true) {
            $seat_data = $booking->get_booked_seats(true);
            foreach ($seat_data as $seat) {
                $this->seat_hold_service->book_seats(
                    $booking->tour_departure_id,
                    (int) $seat['departure_vehicle_id'],
                    [$seat['seat_code']]
                );
            }
        }

        ActivityLogger::log_payment_confirmed($booking_id, $booking->total_amount, $payment_status);

        return [
            'success' => true,
            'data' => [
                'booking_id' => $booking->code,
                'payment_status' => $payment_status,
                'confirmed_at' => current_time('mysql'),
            ],
        ];
    }

    public function send_magic_link(int $booking_id): array
    {
        $booking = $this->booking_repo->find($booking_id);
        if (!$booking) {
            return ['success' => false, 'error_code' => 'booking_not_found', 'message' => 'Không tìm thấy booking.'];
        }

        $link = $this->get_magic_link($booking_id);
        if (empty($link)) {
            return ['success' => false, 'error_code' => 'send_failed', 'message' => 'Không thể tạo magic link.'];
        }

        return [
            'success' => true,
            'data' => [
                'magic_link' => $link,
                'sent_at' => current_time('mysql'),
            ],
        ];
    }

    public function get_magic_link(int $booking_id): string
    {
        $booking = $this->booking_repo->find($booking_id);
        if (!$booking) return '';

        $existing = $this->token_repo->find_by_booking_purpose($booking_id, 'complete_passengers');
        if ($existing) {
            $this->token_repo->delete($existing['id']);
        }

        $raw_token = TokenGenerator::generate();
        $token_hash = TokenGenerator::hash($raw_token);
        $departure = $booking->get_departure();
        $expiry = AccessTokenRepository::calculate_expiry(
            $departure ? $departure->start_date : date('Y-m-d'),
            'departure_plus_1_day'
        );

        $this->token_repo->create([
            'booking_id' => $booking_id,
            'token_hash' => $token_hash,
            'purpose' => 'complete_passengers',
            'expires_at' => $expiry,
        ]);

        return TokenGenerator::generate_magic_link_url($raw_token);
    }

    public function get(int $id): ?Booking
    {
        return $this->booking_repo->find($id);
    }

    public function get_by_code(string $code): ?Booking
    {
        return $this->booking_repo->find_by_code($code);
    }

    public function get_bookings(array $args = []): array
    {
        return $this->booking_repo->get($args);
    }

    public function update_passenger(int $passenger_id, array $data): array
    {
        $passenger = $this->passenger_repo->find($passenger_id);
        if (!$passenger) {
            return ['success' => false, 'message' => 'Passenger not found'];
        }

        $allowed = ['name', 'phone', 'email', 'gender', 'date_of_birth', 'id_number', 'id_issue_date', 'id_issue_place', 'id_front_attachment_id', 'id_back_attachment_id', 'address', 'emergency_contact', 'health_notes', 'dietary_requirements', 'note', 'pickup_point_id'];
        $update_data = [];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $update_data[$field] = $data[$field];
            }
        }

        if (empty($update_data)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }

        $this->passenger_repo->update($passenger_id, $update_data);
        $this->update_booking_passenger_status((int) $passenger->booking_id);
        ActivityLogger::log_passenger_updated($passenger_id, $update_data);

        return ['success' => true, 'message' => 'Passenger updated successfully'];
    }

    protected function update_booking_passenger_status(int $booking_id): void
    {
        $passengers = $this->passenger_repo->get_by_booking($booking_id);
        $total = count($passengers);
        $completed = 0;
        foreach ($passengers as $p) {
            if ($p->is_profile_complete()) $completed++;
        }
        if ($completed === 0) {
            $status = Booking::PASSENGER_MISSING;
        } elseif ($completed === $total) {
            $status = Booking::PASSENGER_COMPLETED;
        } else {
            $status = Booking::PASSENGER_PARTIAL;
        }
        $this->booking_repo->update_passenger_status($booking_id, $status);
    }

    protected function create_passenger_placeholders(int $booking_id, int $departure_id, array $data): void
    {
        $total = ($data['adult_count'] ?? 0) + ($data['child_count'] ?? 0) + ($data['infant_count'] ?? 0);
        for ($i = 0; $i < $total; $i++) {
            $this->passenger_repo->create([
                'booking_id' => $booking_id,
                'tour_departure_id' => $departure_id,
                'passenger_type' => Passenger::TYPE_ADULT,
                'is_placeholder' => true,
                'profile_status' => Passenger::PROFILE_MISSING,
            ]);
        }
    }

    private function build_public_response(Booking $booking): array
    {
        $departure = $booking->get_departure();
        $tour = Tour::find($departure ? $departure->tour_id : 0);

        $passengers = array_map(function ($p) {
            return [
                'id' => $p->id,
                'full_name' => $p->name,
                'email' => $p->email ? substr($p->email, 0, 2) . '***@' . explode('@', $p->email)[1] : null,
                'seat' => $p->seat_code,
                'pickup_point' => $this->get_pickup_point_name($p->pickup_point_id),
                'checked_in' => $p->is_checked_in(),
            ];
        }, $booking->get_passengers());

        $rental = $this->get_booking_rental_items($booking->id);

        $bank_info = null;
        $settings = get_option('nt_tour_bank_info', []);
        if (!empty($settings['bank_account'])) {
            $amount = (int) $booking->total_amount;
            $content = $booking->code;
            $acct = $settings['bank_account'];
            $bank = $settings['bank_name'] ?? 'MB Bank';
            $name = $settings['account_name'] ?? '';
            $bank_info = [
                'bank_name' => $bank,
                'account_no' => $acct,
                'account_name' => $name,
                'amount' => $amount,
                'content' => $content,
                'qr_url' => "https://img.vietqr.io/image/{$bank}-{$acct}-compact2.png?amount={$amount}&addInfo={$content}&accountName=" . urlencode($name),
            ];
        }

        return [
            'booking_id' => $booking->code,
            'status' => $booking->booking_status === 'pending_payment' ? 'pending' : $booking->booking_status,
            'created_at' => date('c', strtotime($booking->created_at)),
            'tour' => [
                'name' => $tour ? $tour->get_title() : '',
                'slug' => $tour ? $tour->get_slug() : '',
            ],
            'departure' => [
                'date' => $departure ? $departure->start_date : '',
                'departure_time' => $departure ? $departure->departure_time : '',
            ],
            'main_contact' => [
                'full_name' => $booking->customer_name,
                'phone' => substr($booking->customer_phone, 0, 4) . '****' . substr($booking->customer_phone, -3),
                'email' => $booking->customer_email ? substr($booking->customer_email, 0, 2) . '***@' . explode('@', $booking->customer_email)[1] : null,
            ],
            'passengers' => $passengers,
            'rental_items' => $rental,
            'payment' => [
                'method' => 'transfer',
                'total' => (float) $booking->total_amount,
                'paid' => 0,
                'remaining' => (float) $booking->total_amount,
                'status' => $booking->payment_status,
                'bank_info' => $bank_info,
            ],
        ];
    }

    private function get_booking_rental_items(int $booking_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nt_booking_rental_items';
        $r_table = $wpdb->prefix . 'nt_rental_items';
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT bri.*, ri.name, ri.icon FROM {$table} bri LEFT JOIN {$r_table} ri ON bri.rental_item_id = ri.id WHERE bri.booking_id = %d", $booking_id
        ), ARRAY_A);

        return array_map(fn($r) => [
            'id' => $r['rental_item_id'],
            'name' => $r['name'] ?? '',
            'qty' => (int) $r['quantity'],
            'subtotal' => (float) $r['subtotal'],
        ], $results ?: []);
    }

    private function get_booking_payments(int $booking_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nt_payments';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE booking_id = %d ORDER BY created_at DESC", $booking_id
        ), ARRAY_A) ?: [];
    }
}