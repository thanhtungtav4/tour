<?php
/**
 * Passenger Repository
 *
 * Data access layer for passengers.
 *
 * @since 0.1.0
 */

namespace TourBooking\Repositories;

use TourBooking\Models\Passenger;

class PassengerRepository
{
    protected string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'nt_booking_passengers';
    }

    /**
     * Find passenger by ID
     *
     * @param int $id
     * @return Passenger|null
     */
    public function find(int $id): ?Passenger
    {
        global $wpdb;

        $data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$data) {
            return null;
        }

        return new Passenger($data);
    }

    /**
     * Get passengers by booking
     *
     * @param int $booking_id
     * @return Passenger[]
     */
    public function get_by_booking(int $booking_id): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE booking_id = %d ORDER BY id",
                $booking_id
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Passenger($data), $results ?: []);
    }

    /**
     * Get passengers by departure
     *
     * @param int $departure_id
     * @return Passenger[]
     */
    public function get_by_departure(int $departure_id): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE tour_departure_id = %d ORDER BY seat_code",
                $departure_id
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Passenger($data), $results ?: []);
    }

    /**
     * Create passenger
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        global $wpdb;

        $data['created_at'] = current_time('mysql');

        $formats = [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['booking_id', 'tour_departure_id', 'pickup_point_id', 'is_placeholder', 'checked_in_by', 'id_front_attachment_id', 'id_back_attachment_id'], true)) {
                $formats[] = '%d';
            } else {
                $formats[] = '%s';
            }
        }

        $result = $wpdb->insert($this->table, $data, $formats);

        if ($result === false) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update passenger
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        global $wpdb;

        $data['updated_at'] = current_time('mysql');

        // Auto-calculate profile status
        if (isset($data['name']) && !empty($data['name'])) {
            $data['is_placeholder'] = 0;
            $data['profile_status'] = Passenger::PROFILE_COMPLETED;
        }

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
     * Delete passenger
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        global $wpdb;

        $result = $wpdb->delete($this->table, ['id' => $id], ['%d']);

        return $result !== false;
    }

    /**
     * Delete all passengers for a booking
     *
     * @param int $booking_id
     * @return bool
     */
    public function delete_by_booking(int $booking_id): bool
    {
        global $wpdb;

        $result = $wpdb->delete($this->table, ['booking_id' => $booking_id], ['%d']);

        return $result !== false;
    }

    /**
     * Update profile status
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function update_profile_status(int $id, string $status): bool
    {
        return $this->update($id, ['profile_status' => $status]);
    }

    /**
     * Update seat code
     *
     * @param int $id
     * @param string|null $seat_code
     * @return bool
     */
    public function update_seat(int $id, ?string $seat_code): bool
    {
        return $this->update($id, ['seat_code' => $seat_code]);
    }

    /**
     * Check-in passenger
     *
     * @param int $id
     * @param int|null $checked_in_by
     * @return bool
     */
    public function checkin(int $id, ?int $checked_in_by = null): bool
    {
        $data = [
            'checkin_status' => Passenger::CHECKIN_CHECKED_IN,
            'checked_in_at' => current_time('mysql'),
        ];

        if ($checked_in_by) {
            $data['checked_in_by'] = $checked_in_by;
        }

        return $this->update($id, $data);
    }

    /**
     * Undo check-in
     *
     * @param int $id
     * @return bool
     */
    public function undo_checkin(int $id): bool
    {
        return $this->update($id, [
            'checkin_status' => Passenger::CHECKIN_NOT_CHECKED_IN,
            'checked_in_at' => null,
            'checked_in_by' => null,
        ]);
    }

    /**
     * Mark as no-show
     *
     * @param int $id
     * @return bool
     */
    public function mark_no_show(int $id): bool
    {
        return $this->update($id, [
            'checkin_status' => Passenger::CHECKIN_NO_SHOW,
        ]);
    }

    /**
     * Update QR token
     *
     * @param int $id
     * @param string $token_hash
     * @return bool
     */
    public function set_qr_token(int $id, string $token_hash): bool
    {
        return $this->update($id, [
            'qr_token_hash' => $token_hash,
            'qr_generated_at' => current_time('mysql'),
        ]);
    }

    /**
     * Get passengers with missing info
     *
     * @param int $departure_id
     * @return Passenger[]
     */
    public function get_missing_info(int $departure_id): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE tour_departure_id = %d AND profile_status IN ('missing', 'partial') ORDER BY id",
                $departure_id
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Passenger($data), $results ?: []);
    }

    /**
     * Get passengers not checked in
     *
     * @param int $departure_id
     * @return Passenger[]
     */
    public function get_not_checked_in(int $departure_id): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE tour_departure_id = %d AND checkin_status = 'not_checked_in' ORDER BY seat_code",
                $departure_id
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Passenger($data), $results ?: []);
    }

    /**
     * Count by profile status for a booking
     *
     * @param int $booking_id
     * @param string $status
     * @return int
     */
    public function count_by_status(int $booking_id, string $status): int
    {
        global $wpdb;

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE booking_id = %d AND profile_status = %s",
                $booking_id,
                $status
            )
        );
    }
}
