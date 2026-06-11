<?php
/**
 * Booking Repository
 *
 * Data access layer for bookings.
 *
 * @since 0.1.0
 */

namespace TourBooking\Repositories;

use TourBooking\Models\Booking;

class BookingRepository
{
    protected string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'nt_bookings';
    }

    /**
     * Find booking by ID
     *
     * @param int $id
     * @return Booking|null
     */
    public function find(int $id): ?Booking
    {
        global $wpdb;

        $data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        if (!$data) {
            return null;
        }

        return new Booking($data);
    }

    /**
     * Find booking by code
     *
     * @param string $code
     * @return Booking|null
     */
    public function find_by_code(string $code): ?Booking
    {
        global $wpdb;

        $data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE code = %s",
                $code
            ),
            ARRAY_A
        );

        if (!$data) {
            return null;
        }

        return new Booking($data);
    }

    /**
     * Get bookings with filters
     *
     * @param array $args
     * @return array
     */
    public function get(array $args = []): array
    {
        global $wpdb;

        $defaults = [
            'departure_id' => null,
            'status' => null,
            'payment_status' => null,
            'passenger_status' => null,
            'source' => null,
            'created_by' => null,
            'date_from' => null,
            'date_to' => null,
            'per_page' => 20,
            'page' => 1,
            'orderby' => 'created_at',
            'order' => 'DESC',
        ];

        $args = wp_parse_args($args, $defaults);
        $where = ['1=1'];
        $values = [];

        if (!empty($args['departure_id'])) {
            $where[] = 'tour_departure_id = %d';
            $values[] = (int) $args['departure_id'];
        }

        if (!empty($args['status'])) {
            $where[] = 'booking_status = %s';
            $values[] = sanitize_text_field($args['status']);
        }

        if (!empty($args['payment_status'])) {
            $where[] = 'payment_status = %s';
            $values[] = sanitize_text_field($args['payment_status']);
        }

        if (!empty($args['passenger_status'])) {
            $where[] = 'passenger_info_status = %s';
            $values[] = sanitize_text_field($args['passenger_status']);
        }

        if (!empty($args['source'])) {
            $where[] = 'source = %s';
            $values[] = sanitize_text_field($args['source']);
        }

        if (!empty($args['created_by'])) {
            $where[] = 'created_by = %d';
            $values[] = (int) $args['created_by'];
        }

        if (!empty($args['date_from'])) {
            $where[] = 'DATE(created_at) >= %s';
            $values[] = sanitize_text_field($args['date_from']);
        }

        if (!empty($args['date_to'])) {
            $where[] = 'DATE(created_at) <= %s';
            $values[] = sanitize_text_field($args['date_to']);
        }

        $where_clause = 'WHERE ' . implode(' AND ', $where);
        $offset = ((int) $args['page'] - 1) * (int) $args['per_page'];

        $allowed_orderby = ['id', 'code', 'customer_name', 'customer_phone', 'total_amount', 'booking_status', 'payment_status', 'created_at', 'updated_at'];
        $orderby = in_array($args['orderby'], $allowed_orderby, true) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        // Build count query
        $count_sql = "SELECT COUNT(*) FROM {$this->table} {$where_clause}";
        if (!empty($values)) {
            $total = (int) $wpdb->get_var(
                $wpdb->prepare($count_sql, ...$values)
            );
        } else {
            $total = (int) $wpdb->get_var($count_sql);
        }

        // Build main query
        $sql = "SELECT * FROM {$this->table} {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $values[] = (int) $args['per_page'];
        $values[] = $offset;

        $results = $wpdb->get_results(
            $wpdb->prepare($sql, ...$values),
            ARRAY_A
        );

        return [
            'items' => array_map(fn($data) => new Booking($data), $results ?: []),
            'total' => $total,
            'per_page' => (int) $args['per_page'],
            'page' => (int) $args['page'],
        ];
    }

    /**
     * Create a new booking
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        global $wpdb;

        $data['created_at'] = current_time('mysql');

        // Generate unique booking code
        if (empty($data['code'])) {
            $data['code'] = $this->generate_booking_code();
        }

        $format = [
            '%s', '%s', '%d', '%s', '%s', '%s',
            '%d', '%d', '%d', '%d', '%d', '%f', '%f',
            '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s',
        ];

        $result = $wpdb->insert($this->table, $data, $format);

        if ($result === false) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update a booking
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        global $wpdb;

        $data['updated_at'] = current_time('mysql');

        $result = $wpdb->update(
            $this->table,
            $data,
            ['id' => $id],
            null,
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Update booking status
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function update_status(int $id, string $status): bool
    {
        $data = ['booking_status' => $status];

        if ($status === Booking::STATUS_CANCELLED) {
            $data['cancelled_at'] = current_time('mysql');
        }

        if ($status === Booking::STATUS_CONFIRMED) {
            $data['confirmed_at'] = current_time('mysql');
        }

        return $this->update($id, $data);
    }

    /**
     * Update payment status
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function update_payment_status(int $id, string $status): bool
    {
        return $this->update($id, ['payment_status' => $status]);
    }

    /**
     * Update passenger info status
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function update_passenger_status(int $id, string $status): bool
    {
        return $this->update($id, ['passenger_info_status' => $status]);
    }

    /**
     * Get bookings pending payment
     *
     * @return Booking[]
     */
    public function get_pending_payment(): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE booking_status = %s AND payment_status IN ('unpaid', 'underpaid') ORDER BY created_at DESC",
                Booking::STATUS_PENDING_PAYMENT
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Booking($data), $results ?: []);
    }

    /**
     * Get bookings with expiring holds
     *
     * @return Booking[]
     */
    public function get_expiring_holds(): array
    {
        global $wpdb;

        $threshold = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE booking_status = %s AND payment_status = 'unpaid' AND hold_expires_at IS NOT NULL AND hold_expires_at < %s ORDER BY hold_expires_at ASC",
                Booking::STATUS_PENDING_PAYMENT,
                $threshold
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Booking($data), $results ?: []);
    }

    /**
     * Generate unique booking code
     *
     * @return string
     */
    protected function generate_booking_code(): string
    {
        $prefix = 'NTB';
        $date = date('ymd');
        $random = strtoupper(substr(wp_generate_password(6, false), 0, 6));
        return "{$prefix}{$date}{$random}";
    }

    /**
     * Count by status
     *
     * @param string|null $status
     * @return int
     */
    public function count(?string $status = null): int
    {
        global $wpdb;

        if ($status) {
            return (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->table} WHERE booking_status = %s",
                    $status
                )
            );
        }

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");
    }

    /**
     * Get today's new bookings
     *
     * @return Booking[]
     */
    public function get_today_new(): array
    {
        global $wpdb;

        $today = date('Y-m-d');

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE DATE(created_at) = %s ORDER BY created_at DESC",
                $today
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Booking($data), $results ?: []);
    }
}
