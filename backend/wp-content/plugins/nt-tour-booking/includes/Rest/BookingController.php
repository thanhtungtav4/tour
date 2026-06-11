<?php
/**
 * Booking REST Controller
 *
 * API endpoints for bookings matching NT Tour Booking spec.
 *
 * @since 0.1.0
 */

namespace TourBooking\Rest;

use TourBooking\Helpers\Response;
use TourBooking\Helpers\TokenGenerator;
use TourBooking\Repositories\AccessTokenRepository;
use TourBooking\Repositories\PassengerRepository;
use TourBooking\Services\BookingService;
use TourBooking\Models\Tour;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class BookingController extends WP_REST_Controller
{
    use RestNonceTrait;

    protected string $namespace = 'nt-tour/v1';

    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/bookings', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'create_booking'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route($this->namespace, '/bookings/hold-seats', [
            'methods' => 'POST',
            'callback' => [$this, 'hold_seats'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route($this->namespace, '/bookings/lookup', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'lookup_booking'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route($this->namespace, '/bookings/(?P<booking_id>[A-Za-z0-9]+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_booking'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route($this->namespace, '/admin/bookings', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'list_bookings'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        register_rest_route($this->namespace, '/admin/bookings/(?P<booking_id>[A-Za-z0-9]+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_admin_booking'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        register_rest_route($this->namespace, '/admin/bookings/(?P<booking_id>[A-Za-z0-9]+)', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [$this, 'update_booking'],
            'permission_callback' => [$this, 'admin_write_permission_check'],
        ]);

        register_rest_route($this->namespace, '/admin/bookings/(?P<booking_id>[A-Za-z0-9]+)/cancel', [
            'methods' => 'POST',
            'callback' => [$this, 'cancel_booking'],
            'permission_callback' => [$this, 'admin_write_permission_check'],
        ]);

        register_rest_route($this->namespace, '/admin/bookings/(?P<id>\d+)/confirm-payment', [
            'methods' => 'POST',
            'callback' => [$this, 'confirm_payment'],
            'permission_callback' => [$this, 'admin_write_permission_check'],
        ]);

        register_rest_route($this->namespace, '/admin/bookings/(?P<id>\d+)/send-magic-link', [
            'methods' => 'POST',
            'callback' => [$this, 'send_magic_link'],
            'permission_callback' => [$this, 'admin_write_permission_check'],
        ]);

        // Magic link: validate token
        register_rest_route($this->namespace, '/magic/validate', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'validate_magic_token'],
            'permission_callback' => '__return_true',
        ]);

        // Magic link: update passenger
        register_rest_route($this->namespace, '/magic/update-passenger', [
            'methods' => 'POST',
            'callback' => [$this, 'update_magic_passenger'],
            'permission_callback' => '__return_true',
        ]);

        // Magic link: resend
        register_rest_route($this->namespace, '/magic/resend', [
            'methods' => 'POST',
            'callback' => [$this, 'resend_magic_link'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function admin_permission_check(): bool
    {
        return current_user_can('nt_manage_bookings');
    }

    public function admin_write_permission_check(WP_REST_Request $request): bool
    {
        return current_user_can('nt_manage_bookings') && $this->verify_nonce($request);
    }

    /**
     * Create booking (public)
     */
    public function create_booking(WP_REST_Request $request): WP_REST_Response
    {
        $data = $request->get_params();

        $errors = [];

        if (empty($data['tour_slug'])) {
            $errors['tour_slug'] = 'Tour slug là bắt buộc.';
        }
        if (empty($data['departure_date'])) {
            $errors['departure_date'] = 'Ngày khởi hành là bắt buộc.';
        }
        if (empty($data['participants']) || (int) $data['participants'] < 1) {
            $errors['participants'] = 'Số lượng khách phải >= 1.';
        }
        if (empty($data['main_contact']['full_name'])) {
            $errors['main_contact.full_name'] = 'Họ tên không được để trống.';
        }
        if (empty($data['main_contact']['phone'])) {
            $errors['main_contact.phone'] = 'Số điện thoại không được để trống.';
        }
        if (empty($data['main_contact']['email'])) {
            $errors['main_contact.email'] = 'Email không được để trống.';
        }
        if (empty($data['agree_terms'])) {
            $errors['agree_terms'] = 'Bạn phải đồng ý điều khoản.';
        }
        if (empty($data['payment_method']) || !in_array($data['payment_method'], ['cash', 'transfer'])) {
            $errors['payment_method'] = 'Phương thức thanh toán không hợp lệ.';
        }

        if (!empty($errors)) {
            return Response::validation_error($errors);
        }

        $service = new BookingService();
        $result = $service->create_booking($data);

        if (!$result['success']) {
            return Response::error($result['error_code'] ?? 'booking_failed', $result['message']);
        }

        return Response::created($result['data']);
    }

    /**
     * Hold seats (public)
     */
    public function hold_seats(WP_REST_Request $request): WP_REST_Response
    {
        $departure_id = (int) $request->get_param('departure_id');
        $seats = $request->get_param('seats') ?: [];

        if (empty($departure_id) || empty($seats)) {
            return Response::error('validation_failed', 'departure_id và seats là bắt buộc.');
        }

        $service = new BookingService();
        $result = $service->hold_seats_only($departure_id, $seats);

        if (!$result['success']) {
            return Response::conflict($result['error_code'] ?? 'seat_not_available', $result['message'], $result['failed_seats'] ?? []);
        }

        return Response::success($result['data']);
    }

    /**
     * Get booking by code (public)
     */
    public function get_booking(WP_REST_Request $request): WP_REST_Response
    {
        $booking_id = $request->get_param('booking_id');
        $service = new BookingService();
        $data = $service->get_booking_public($booking_id);

        if (!$data) {
            return Response::resource_not_found('booking');
        }

        return Response::success($data);
    }

    /**
     * Lookup booking by email/phone/booking_id (public)
     */
    public function lookup_booking(WP_REST_Request $request): WP_REST_Response
    {
        $booking_id = $request->get_param('booking_id');
        $email = $request->get_param('email');
        $phone = $request->get_param('phone');

        if (empty($booking_id) && empty($email) && empty($phone)) {
            return Response::error('validation_failed', 'Cần ít nhất 1 trong booking_id, email, phone.');
        }

        $service = new BookingService();
        $results = $service->lookup_bookings($booking_id ?: null, $email ?: null, $phone ?: null);

        return Response::success($results);
    }

    /**
     * List bookings (admin) - DataTables format
     */
    public function list_bookings(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;

        $table = $wpdb->prefix . 'nt_bookings';
        $d_table = $wpdb->prefix . 'nt_tour_departures';
        $p_table = $wpdb->posts;

        $per_page = min((int) $request->get_param('length') ?: 20, 100);
        $page = (int) ($request->get_param('start') ?: 0) / $per_page + 1;
        $offset = ($page - 1) * $per_page;
        $draw = (int) $request->get_param('draw') ?: 1;

        $where = ['1=1'];
        $values = [];

        // Filters
        if ($status = $request->get_param('status')) {
            $where[] = 'b.booking_status = %s';
            $values[] = $status;
        }

        if ($payment_status = $request->get_param('payment_status')) {
            $where[] = 'b.payment_status = %s';
            $values[] = $payment_status;
        }

        if ($date_from = $request->get_param('date_from')) {
            $where[] = 'DATE(b.created_at) >= %s';
            $values[] = $date_from;
        }

        if ($date_to = $request->get_param('date_to')) {
            $where[] = 'DATE(b.created_at) <= %s';
            $values[] = $date_to;
        }

        // Search
        if ($search = $request->get_param('search')) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where[] = '(b.code LIKE %s OR b.customer_name LIKE %s OR b.customer_phone LIKE %s OR b.customer_email LIKE %s)';
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
        }

        // Order
        $orderby = $request->get_param('orderby') ?: 'b.created_at';
        $order = $request->get_param('order') ?: 'DESC';

        $where_sql = implode(' AND ', $where);

        // Count total
        $count_sql = "SELECT COUNT(*) FROM {$table} b WHERE {$where_sql}";
        $total = (int) $wpdb->get_var(empty($values) ? $count_sql : $wpdb->prepare($count_sql, ...$values));
        $filtered = $total;

        // Get data
        $sql = "SELECT b.*, d.start_date as departure_date, p.post_title as tour_name
                FROM {$table} b
                LEFT JOIN {$d_table} d ON b.tour_departure_id = d.id
                LEFT JOIN {$p_table} p ON d.tour_id = p.ID
                WHERE {$where_sql}
                ORDER BY {$orderby} {$order}
                LIMIT %d OFFSET %d";

        $values[] = $per_page;
        $values[] = $offset;

        $results = $wpdb->get_results($wpdb->prepare($sql, ...$values), ARRAY_A);

        // Format for DataTables
        $booking_status_labels = [
            'pending_payment' => 'Chờ thanh toán',
            'confirmed' => 'Đã xác nhận',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'expired_hold' => 'Hết hạn',
            'no_show' => 'No-show',
        ];

        $payment_status_labels = [
            'unpaid' => 'Chưa thanh toán',
            'deposit_paid' => 'Đặt cọc',
            'paid' => 'Đã thanh toán',
            'underpaid' => 'Thiếu tiền',
            'overpaid' => 'Thừa tiền',
            'refunded' => 'Đã hoàn',
        ];

        $data = array_map(function($row) use ($booking_status_labels, $payment_status_labels) {
            $booking_status = $row['booking_status'];
            $payment_status = $row['payment_status'];

            $status_class = [
                'pending_payment' => 'bg-yellow-100 text-yellow-800',
                'confirmed' => 'bg-green-100 text-green-800',
                'completed' => 'bg-blue-100 text-blue-800',
                'cancelled' => 'bg-red-100 text-red-800',
                'expired_hold' => 'bg-gray-100 text-gray-800',
                'no_show' => 'bg-purple-100 text-purple-800',
            ];

            $payment_class = [
                'unpaid' => 'bg-yellow-100 text-yellow-800',
                'deposit_paid' => 'bg-blue-100 text-blue-800',
                'paid' => 'bg-green-100 text-green-800',
                'underpaid' => 'bg-orange-100 text-orange-800',
                'overpaid' => 'bg-purple-100 text-purple-800',
                'refunded' => 'bg-gray-100 text-gray-800',
            ];

            return [
                'id' => (int) $row['id'],
                'code' => '<a href="#" onclick="openBookingModal(\'' . esc_attr($row['code']) . '\'); return false;" class="text-blue-600 hover:underline font-mono">' . esc_html($row['code']) . '</a>',
                'tour_name' => esc_html($row['tour_name'] ?: 'N/A'),
                'departure_date' => $row['departure_date'] ? date('d/m/Y', strtotime($row['departure_date'])) : '-',
                'customer' => '<div><strong>' . esc_html($row['customer_name']) . '</strong><br><span class="text-gray-500 text-sm">' . esc_html($row['customer_phone']) . '</span></div>',
                'total_people' => (int) $row['total_people'],
                'total_amount' => (float) $row['total_amount'],
                'total_amount_formatted' => number_format((float) $row['total_amount'], 0, ',', '.') . 'đ',
                'booking_status' => $booking_status,
                'booking_status_badge' => '<span class="nt-badge ' . ($status_class[$booking_status] ?? 'bg-gray-100 text-gray-800') . '">' . ($booking_status_labels[$booking_status] ?? $booking_status) . '</span>',
                'payment_status' => $payment_status,
                'payment_status_badge' => '<span class="nt-badge ' . ($payment_class[$payment_status] ?? 'bg-gray-100 text-gray-800') . '">' . ($payment_status_labels[$payment_status] ?? $payment_status) . '</span>',
                'created_at' => $row['created_at'],
                'actions' => '<button type="button" class="nt-btn nt-btn-sm nt-btn-ghost" onclick="openBookingModal(\'' . esc_attr($row['code']) . '\')"><i data-lucide="eye" class="w-4 h-4"></i></button>',
            ];
        }, $results ?: []);

        return new WP_REST_Response([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
            'page' => $page,
            'per_page' => $per_page,
        ], 200);
    }

    /**
     * Get booking detail (admin)
     */
    public function get_admin_booking(WP_REST_Request $request): WP_REST_Response
    {
        $booking_id = $request->get_param('booking_id');
        $service = new BookingService();
        $data = $service->get_booking_admin($booking_id);

        if (!$data) {
            return Response::resource_not_found('booking');
        }

        return Response::success($data);
    }

    /**
     * Update booking (admin)
     */
    public function update_booking(WP_REST_Request $request): WP_REST_Response
    {
        $booking_id = $request->get_param('booking_id');
        $data = $request->get_params();
        unset($data['booking_id']);

        $service = new BookingService();
        $result = $service->update_booking($booking_id, $data);

        if (!$result['success']) {
            return Response::error($result['error_code'] ?? 'update_failed', $result['message']);
        }

        return Response::success($result['data']);
    }

    /**
     * Cancel booking (admin)
     */
    public function cancel_booking(WP_REST_Request $request): WP_REST_Response
    {
        $booking_id = $request->get_param('booking_id');
        $reason = $request->get_param('reason') ?: '';
        $refund_amount = $request->get_param('refund_amount');
        $notify = $request->get_param('notify_customer', true);

        $service = new BookingService();
        $result = $service->cancel_by_code($booking_id, $reason, $refund_amount, $notify);

        if (!$result['success']) {
            return Response::error($result['error_code'] ?? 'cancel_failed', $result['message']);
        }

        return Response::success($result['data']);
    }

    /**
     * Confirm payment (admin)
     */
    public function confirm_payment(WP_REST_Request $request): WP_REST_Response
    {
        $id = (int) $request->get_param('id');
        $payment_status = $request->get_param('payment_status') ?: 'paid';

        $service = new BookingService();
        $result = $service->confirm_payment($id, $payment_status);

        if (!$result['success']) {
            return Response::error($result['error_code'] ?? 'confirm_failed', $result['message']);
        }

        return Response::success($result['data']);
    }

    /**
     * Send magic link (admin)
     */
    public function send_magic_link(WP_REST_Request $request): WP_REST_Response
    {
        $id = (int) $request->get_param('id');
        $service = new BookingService();
        $result = $service->send_magic_link($id);

        if (!$result['success']) {
            return Response::error($result['error_code'] ?? 'send_failed', $result['message']);
        }

        return Response::success($result['data']);
    }

    /**
     * Validate magic token (public)
     */
    public function validate_magic_token(WP_REST_Request $request): WP_REST_Response
    {
        $token = $request->get_param('token');
        if (empty($token)) {
            return Response::error('invalid_token', 'Token không hợp lệ.');
        }

        $token_hash = TokenGenerator::hash($token);
        $token_repo = new AccessTokenRepository();
        $token_data = $token_repo->find_by_hash($token_hash);

        if (!$token_data) {
            return Response::error('invalid_token', 'Token không hợp lệ.');
        }

        if ($token_data['expires_at'] && strtotime($token_data['expires_at']) < time()) {
            return Response::gone('token_expired', 'Token đã hết hạn.');
        }

        $service = new BookingService();
        $booking = $service->get($token_data['booking_id']);

        if (!$booking) {
            return Response::resource_not_found('booking');
        }

        $departure = $booking->get_departure();
        $tour = \TourBooking\Models\Tour::find($departure ? $departure->tour_id : 0);

        return Response::success([
            'valid' => true,
            'booking_id' => $booking->code,
            'passenger_id' => $token_data['id'],
            'passenger_name' => $booking->customer_name,
            'departure_date' => $departure ? $departure->start_date : '',
            'tour_name' => $tour ? $tour->get_title() : '',
            'expires_at' => $token_data['expires_at'],
            'permissions' => [
                'update_info' => true,
                'change_seat' => false,
                'change_date' => false,
                'cancel' => false,
            ],
        ]);
    }

    /**
     * Update passenger via magic link (public)
     */
    public function update_magic_passenger(WP_REST_Request $request): WP_REST_Response
    {
        $token = $request->get_header('X-NT-Token') ?: $request->get_param('token');
        if (empty($token)) {
            return Response::error('invalid_token', 'Token không hợp lệ.');
        }

        $token_hash = TokenGenerator::hash($token);
        $token_repo = new AccessTokenRepository();
        $token_data = $token_repo->find_by_hash($token_hash);

        if (!$token_data || ($token_data['expires_at'] && strtotime($token_data['expires_at']) < time())) {
            return Response::error('token_expired', 'Token không hợp lệ hoặc đã hết hạn.');
        }

        $passenger_id = $request->get_param('passenger_id');
        if (!$passenger_id) {
            return Response::error('validation_failed', 'passenger_id là bắt buộc.');
        }

        $passenger_repo = new PassengerRepository();
        $passenger = $passenger_repo->find((int) $passenger_id);
        if (!$passenger) {
            return Response::resource_not_found('passenger');
        }

        if ((int) $passenger->booking_id !== (int) $token_data['booking_id']) {
            return Response::forbidden('Passenger không thuộc booking của magic link này.');
        }

        $allowed = [
            'full_name',
            'phone',
            'email',
            'gender',
            'date_of_birth',
            'id_number',
            'id_issue_date',
            'id_issue_place',
            'address',
            'emergency_contact',
            'health_notes',
            'dietary_requirements',
            'pickup_point_id',
            'note',
        ];
        $data = [];
        foreach ($allowed as $field) {
            $value = $request->get_param($field);
            if ($value !== null) {
                $data[$field] = $this->sanitize_magic_passenger_field($field, $value);
            }
        }

        if (isset($data['full_name'])) {
            $data['name'] = $data['full_name'];
            unset($data['full_name']);
        }

        $upload_result = $this->handle_magic_identity_uploads($request);
        if (!$upload_result['success']) {
            return Response::error($upload_result['code'], $upload_result['message'], null, 422);
        }
        $data = array_merge($data, $upload_result['data']);

        $service = new BookingService();
        $result = $service->update_passenger((int) $passenger_id, $data);

        if (!$result['success']) {
            return Response::error('update_failed', $result['message']);
        }

        return Response::success([
            'passenger_id' => (int) $passenger_id,
            'updated_at' => date('c'),
            'message' => 'Đã cập nhật thông tin thành công.',
        ]);
    }

    private function sanitize_magic_passenger_field(string $field, $value)
    {
        switch ($field) {
            case 'email':
                return sanitize_email((string) $value);
            case 'phone':
            case 'id_number':
                return preg_replace('/[^0-9+]/', '', (string) $value);
            case 'pickup_point_id':
                return (int) $value;
            case 'emergency_contact':
                return is_array($value) ? wp_json_encode(array_map('sanitize_text_field', $value)) : sanitize_textarea_field((string) $value);
            case 'address':
            case 'health_notes':
            case 'dietary_requirements':
            case 'note':
                return sanitize_textarea_field((string) $value);
            default:
                return sanitize_text_field((string) $value);
        }
    }

    private function handle_magic_identity_uploads(WP_REST_Request $request): array
    {
        $files = $request->get_file_params();
        $map = [
            'id_front_image' => 'id_front_attachment_id',
            'id_back_image' => 'id_back_attachment_id',
        ];
        $data = [];

        foreach ($map as $field => $column) {
            if (empty($files[$field]) || empty($files[$field]['tmp_name'])) {
                continue;
            }

            $file = $files[$field];
            if (!empty($file['error'])) {
                return ['success' => false, 'code' => 'upload_failed', 'message' => 'Tải ảnh CCCD không thành công.', 'data' => []];
            }

            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $type = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
            if (empty($type['type']) || !in_array($type['type'], $allowed, true)) {
                return ['success' => false, 'code' => 'invalid_file_type', 'message' => 'Ảnh CCCD chỉ hỗ trợ JPG, PNG hoặc WEBP.', 'data' => []];
            }

            if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
                return ['success' => false, 'code' => 'file_too_large', 'message' => 'Ảnh CCCD không được vượt quá 5MB.', 'data' => []];
            }

            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $attachment_id = media_handle_upload($field, 0);
            if (is_wp_error($attachment_id)) {
                return ['success' => false, 'code' => 'upload_failed', 'message' => $attachment_id->get_error_message(), 'data' => []];
            }

            $data[$column] = (int) $attachment_id;
        }

        return ['success' => true, 'data' => $data];
    }

    /**
     * Resend magic link (public)
     */
    public function resend_magic_link(WP_REST_Request $request): WP_REST_Response
    {
        $booking_id = $request->get_param('booking_id');
        $email = $request->get_param('email');

        if (empty($booking_id) || empty($email)) {
            return Response::error('validation_failed', 'booking_id và email là bắt buộc.');
        }

        $service = new BookingService();
        $booking = $service->get_by_code($booking_id);

        if (!$booking || $booking->customer_email !== $email) {
            return Response::error('booking_not_found', 'Không tìm thấy booking.');
        }

        $result = $service->send_magic_link($booking->id);

        if (!$result['success']) {
            return Response::error('send_failed', $result['message']);
        }

        return Response::success([
            'message' => 'Đã gửi lại link qua email.',
            'sent_at' => date('c'),
        ]);
    }
}