<?php
/**
 * Booking Model
 *
 * Represents a tour booking.
 *
 * @since 0.1.0
 */

namespace TourBooking\Models;

class Booking
{
    public int $id;
    public string $code;
    public string $booking_type;
    public int $tour_departure_id;
    public string $customer_name;
    public string $customer_phone;
    public ?string $customer_email;
    public ?int $pickup_point_id;
    public int $expected_people;
    public int $total_people;
    public int $adult_count;
    public int $child_count;
    public int $infant_count;
    public float $total_amount;
    public float $deposit_amount;
    public string $booking_status;
    public string $payment_status;
    public string $passenger_info_status;
    public string $seat_selection_mode;
    public ?string $hold_expires_at;
    public ?string $magic_link_sent_at;
    public ?string $confirmed_at;
    public ?string $cancelled_at;
    public string $source;
    public ?string $note;
    public ?int $created_by;
    public string $created_at;
    public ?string $updated_at;

    /**
     * Booking type constants
     */
    const TYPE_RETAIL_GROUP = 'retail_group';
    const TYPE_PRIVATE_GROUP = 'private_group';

    /**
     * Booking status constants
     */
    const STATUS_PENDING_PAYMENT = 'pending_payment';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_EXPIRED_HOLD = 'expired_hold';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_NO_SHOW = 'no_show';

    /**
     * Payment status constants
     */
    const PAYMENT_UNPAID = 'unpaid';
    const PAYMENT_DEPOSIT_PAID = 'deposit_paid';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_UNDERPAID = 'underpaid';
    const PAYMENT_OVERPAID = 'overpaid';
    const PAYMENT_REFUNDED = 'refunded';

    /**
     * Passenger info status constants
     */
    const PASSENGER_MISSING = 'missing';
    const PASSENGER_PARTIAL = 'partial';
    const PASSENGER_COMPLETED = 'completed';

    /**
     * Seat selection mode constants
     */
    const SEAT_CUSTOMER_SELECT = 'customer_select';
    const SEAT_ADMIN_ASSIGN = 'admin_assign';

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->code = $data['code'] ?? '';
        $this->booking_type = $data['booking_type'] ?? self::TYPE_RETAIL_GROUP;
        $this->tour_departure_id = (int) ($data['tour_departure_id'] ?? 0);
        $this->customer_name = $data['customer_name'] ?? '';
        $this->customer_phone = $data['customer_phone'] ?? '';
        $this->customer_email = $data['customer_email'] ?? null;
        $this->pickup_point_id = isset($data['pickup_point_id']) ? (int) $data['pickup_point_id'] : null;
        $this->expected_people = (int) ($data['expected_people'] ?? 0);
        $this->total_people = (int) ($data['total_people'] ?? 0);
        $this->adult_count = (int) ($data['adult_count'] ?? 0);
        $this->child_count = (int) ($data['child_count'] ?? 0);
        $this->infant_count = (int) ($data['infant_count'] ?? 0);
        $this->total_amount = (float) ($data['total_amount'] ?? 0);
        $this->deposit_amount = (float) ($data['deposit_amount'] ?? 0);
        $this->booking_status = $data['booking_status'] ?? self::STATUS_PENDING_PAYMENT;
        $this->payment_status = $data['payment_status'] ?? self::PAYMENT_UNPAID;
        $this->passenger_info_status = $data['passenger_info_status'] ?? self::PASSENGER_MISSING;
        $this->seat_selection_mode = $data['seat_selection_mode'] ?? self::SEAT_ADMIN_ASSIGN;
        $this->hold_expires_at = $data['hold_expires_at'] ?? null;
        $this->magic_link_sent_at = $data['magic_link_sent_at'] ?? null;
        $this->confirmed_at = $data['confirmed_at'] ?? null;
        $this->cancelled_at = $data['cancelled_at'] ?? null;
        $this->source = $data['source'] ?? 'website';
        $this->note = $data['note'] ?? null;
        $this->created_by = isset($data['created_by']) ? (int) $data['created_by'] : null;
        $this->created_at = $data['created_at'] ?? current_time('mysql');
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Check if pending payment
     *
     * @return bool
     */
    public function is_pending_payment(): bool
    {
        return $this->booking_status === self::STATUS_PENDING_PAYMENT;
    }

    /**
     * Check if confirmed
     *
     * @return bool
     */
    public function is_confirmed(): bool
    {
        return $this->booking_status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if expired hold
     *
     * @return bool
     */
    public function is_expired_hold(): bool
    {
        return $this->booking_status === self::STATUS_EXPIRED_HOLD;
    }

    /**
     * Check if cancelled
     *
     * @return bool
     */
    public function is_cancelled(): bool
    {
        return $this->booking_status === self::STATUS_CANCELLED;
    }

    /**
     * Check if hold is expired
     *
     * @return bool
     */
    public function is_hold_expired(): bool
    {
        if (!$this->hold_expires_at) {
            return false;
        }

        return strtotime($this->hold_expires_at) < time();
    }

    /**
     * Get hold time remaining in seconds
     *
     * @return int
     */
    public function get_hold_time_remaining(): int
    {
        if (!$this->hold_expires_at) {
            return 0;
        }

        $expires = strtotime($this->hold_expires_at);
        $remaining = $expires - time();

        return max(0, $remaining);
    }

    /**
     * Check if all passengers are completed
     *
     * @return bool
     */
    public function is_passenger_info_complete(): bool
    {
        return $this->passenger_info_status === self::PASSENGER_COMPLETED;
    }

    /**
     * Get departure object
     *
     * @return Departure|null
     */
    public function get_departure(): ?Departure
    {
        return (new DepartureRepository())->find($this->tour_departure_id);
    }

    /**
     * Get passengers
     *
     * @return Passenger[]
     */
    public function get_passengers(): array
    {
        $repo = new PassengerRepository();
        return $repo->get_by_booking($this->id);
    }

    /**
     * Get booked seats
     *
     * @param bool $with_ids Include departure_vehicle_id
     * @return array
     */
    public function get_booked_seats(bool $with_ids = false): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nt_departure_seats';

        $seats = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT seat_code, departure_vehicle_id, status FROM {$table} WHERE booking_id = %d ORDER BY seat_code",
                $this->id
            ),
            ARRAY_A
        );

        if ($with_ids) {
            return $seats;
        }

        return array_column($seats, 'seat_code');
    }

    /**
     * Get booking status label
     *
     * @return string
     */
    public function get_booking_status_label(): string
    {
        $labels = [
            self::STATUS_PENDING_PAYMENT => __('Chờ thanh toán', 'nt-tour-booking'),
            self::STATUS_CONFIRMED => __('Đã xác nhận', 'nt-tour-booking'),
            self::STATUS_EXPIRED_HOLD => __('Hết hạn giữ chỗ', 'nt-tour-booking'),
            self::STATUS_CANCELLED => __('Đã hủy', 'nt-tour-booking'),
            self::STATUS_COMPLETED => __('Hoàn thành', 'nt-tour-booking'),
            self::STATUS_NO_SHOW => __('No-show', 'nt-tour-booking'),
        ];

        return $labels[$this->booking_status] ?? $this->booking_status;
    }

    /**
     * Get payment status label
     *
     * @return string
     */
    public function get_payment_status_label(): string
    {
        $labels = [
            self::PAYMENT_UNPAID => __('Chưa thanh toán', 'nt-tour-booking'),
            self::PAYMENT_DEPOSIT_PAID => __('Đặt cọc', 'nt-tour-booking'),
            self::PAYMENT_PAID => __('Đã thanh toán', 'nt-tour-booking'),
            self::PAYMENT_UNDERPAID => __('Thiếu tiền', 'nt-tour-booking'),
            self::PAYMENT_OVERPAID => __('Thừa tiền', 'nt-tour-booking'),
            self::PAYMENT_REFUNDED => __('Đã hoàn tiền', 'nt-tour-booking'),
        ];

        return $labels[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Get passenger info status label
     *
     * @return string
     */
    public function get_passenger_status_label(): string
    {
        $labels = [
            self::PASSENGER_MISSING => __('Thiếu thông tin', 'nt-tour-booking'),
            self::PASSENGER_PARTIAL => __('Thông tin chưa đủ', 'nt-tour-booking'),
            self::PASSENGER_COMPLETED => __('Đã đủ thông tin', 'nt-tour-booking'),
        ];

        return $labels[$this->passenger_info_status] ?? $this->passenger_info_status;
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function to_array(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'booking_type' => $this->booking_type,
            'tour_departure_id' => $this->tour_departure_id,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'pickup_point_id' => $this->pickup_point_id,
            'expected_people' => $this->expected_people,
            'total_people' => $this->total_people,
            'adult_count' => $this->adult_count,
            'child_count' => $this->child_count,
            'infant_count' => $this->infant_count,
            'total_amount' => $this->total_amount,
            'deposit_amount' => $this->deposit_amount,
            'booking_status' => $this->booking_status,
            'booking_status_label' => $this->get_booking_status_label(),
            'payment_status' => $this->payment_status,
            'payment_status_label' => $this->get_payment_status_label(),
            'passenger_info_status' => $this->passenger_info_status,
            'passenger_info_status_label' => $this->get_passenger_status_label(),
            'seat_selection_mode' => $this->seat_selection_mode,
            'hold_expires_at' => $this->hold_expires_at,
            'hold_time_remaining' => $this->get_hold_time_remaining(),
            'is_hold_expired' => $this->is_hold_expired(),
            'magic_link_sent_at' => $this->magic_link_sent_at,
            'confirmed_at' => $this->confirmed_at,
            'cancelled_at' => $this->cancelled_at,
            'source' => $this->source,
            'note' => $this->note,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function to_list_array(): array
    {
        $departure = $this->get_departure();
        return [
            'booking_id' => $this->code,
            'tour_name' => $departure ? ($departure->get_tour() ? $departure->get_tour()->get_title() : '') : '',
            'departure_date' => $departure ? $departure->start_date : '',
            'passengers' => $this->total_people,
            'total' => $this->total_amount,
            'payment_method' => 'transfer',
            'status' => $this->booking_status,
            'payment_status' => $this->payment_status,
            'created_at' => $this->created_at,
        ];
    }
}
