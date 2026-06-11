<?php
/**
 * Seat Repository
 *
 * Data access layer for seats with atomic operations.
 *
 * @since 0.1.0
 */

namespace TourBooking\Repositories;

use TourBooking\Models\Seat;

class SeatRepository
{
    protected string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'nt_departure_seats';
    }

    /**
     * Find seat by ID
     *
     * @param int $id
     * @return Seat|null
     */
    public function find(int $id): ?Seat
    {
        global $wpdb;

        $data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$data) {
            return null;
        }

        return new Seat($data);
    }

    /**
     * Find seat by departure, vehicle, and code
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @param string $seat_code
     * @return Seat|null
     */
    public function find_by_code(int $departure_id, int $vehicle_id, string $seat_code): ?Seat
    {
        global $wpdb;

        $data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE tour_departure_id = %d AND departure_vehicle_id = %d AND seat_code = %s",
                $departure_id,
                $vehicle_id,
                $seat_code
            ),
            ARRAY_A
        );

        if (!$data) {
            return null;
        }

        return new Seat($data);
    }

    /**
     * Get seats for a departure vehicle
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @return Seat[]
     */
    public function get_by_departure_vehicle(int $departure_id, int $vehicle_id): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE tour_departure_id = %d AND departure_vehicle_id = %d ORDER BY seat_code",
                $departure_id,
                $vehicle_id
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Seat($data), $results ?: []);
    }

    /**
     * Get seats by departure (all vehicles)
     *
     * @param int $departure_id
     * @return Seat[]
     */
    public function get_by_departure(int $departure_id): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE tour_departure_id = %d ORDER BY departure_vehicle_id, seat_code",
                $departure_id
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Seat($data), $results ?: []);
    }

    /**
     * Get available seats for a departure vehicle
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @return Seat[]
     */
    public function get_available(int $departure_id, int $vehicle_id): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE tour_departure_id = %d AND departure_vehicle_id = %d AND status = 'available' ORDER BY seat_code",
                $departure_id,
                $vehicle_id
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Seat($data), $results ?: []);
    }

    /**
     * Get seats by booking
     *
     * @param int $booking_id
     * @return Seat[]
     */
    public function get_by_booking(int $booking_id): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE booking_id = %d ORDER BY seat_code",
                $booking_id
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Seat($data), $results ?: []);
    }

    /**
     * Get seats by passenger
     *
     * @param int $passenger_id
     * @return Seat|null
     */
    public function get_by_passenger(int $passenger_id): ?Seat
    {
        global $wpdb;

        $data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE passenger_id = %d",
                $passenger_id
            ),
            ARRAY_A
        );

        if (!$data) {
            return null;
        }

        return new Seat($data);
    }

    /**
     * HOLD SEAT - Atomic operation to prevent race conditions
     *
     * Uses UPDATE with WHERE conditions to atomically hold a seat.
     * Returns true only if the seat was successfully held.
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @param string $seat_code
     * @param int $booking_id
     * @param int $passenger_id
     * @param string $hold_expires_at
     * @return bool True if seat was held, false if already taken
     */
    public function hold_seat(
        int $departure_id,
        int $vehicle_id,
        string $seat_code,
        int $booking_id,
        int $passenger_id,
        string $hold_expires_at
    ): bool {
        global $wpdb;

        $result = $wpdb->update(
            $this->table,
            [
                'status' => Seat::STATUS_HOLDING,
                'booking_id' => $booking_id,
                'passenger_id' => $passenger_id,
                'hold_expires_at' => $hold_expires_at,
                'updated_at' => current_time('mysql'),
            ],
            [
                'tour_departure_id' => $departure_id,
                'departure_vehicle_id' => $vehicle_id,
                'seat_code' => $seat_code,
                'status' => Seat::STATUS_AVAILABLE, // Only hold if available
            ],
            ['%s', '%d', '%d', '%s', '%s'],
            ['%d', '%d', '%s', '%s']
        );

        // $result = 1 means the row was updated (seat was available and is now held)
        // $result = 0 means no rows matched (seat was not available)
        // $result = false means an error occurred
        return $result === 1;
    }

    /**
     * Release a held seat
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @param string $seat_code
     * @return bool
     */
    public function release_seat(int $departure_id, int $vehicle_id, string $seat_code): bool
    {
        global $wpdb;

        $result = $wpdb->update(
            $this->table,
            [
                'status' => Seat::STATUS_AVAILABLE,
                'booking_id' => null,
                'passenger_id' => null,
                'hold_expires_at' => null,
                'updated_at' => current_time('mysql'),
            ],
            [
                'tour_departure_id' => $departure_id,
                'departure_vehicle_id' => $vehicle_id,
                'seat_code' => $seat_code,
            ],
            ['%s', 'null', 'null', 'null', '%s'],
            ['%d', '%d', '%s']
        );

        return $result !== false;
    }

    /**
     * Book a held seat (confirm booking)
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @param string $seat_code
     * @return bool
     */
    public function book_seat(int $departure_id, int $vehicle_id, string $seat_code): bool
    {
        global $wpdb;

        $result = $wpdb->update(
            $this->table,
            [
                'status' => Seat::STATUS_BOOKED,
                'hold_expires_at' => null,
                'booked_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ],
            [
                'tour_departure_id' => $departure_id,
                'departure_vehicle_id' => $vehicle_id,
                'seat_code' => $seat_code,
                'status' => Seat::STATUS_HOLDING, // Only book if holding
            ],
            ['%s', 'null', '%s', '%s'],
            ['%d', '%d', '%s', '%s']
        );

        return $result === 1;
    }

    /**
     * Check-in a seat
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @param string $seat_code
     * @return bool
     */
    public function checkin_seat(int $departure_id, int $vehicle_id, string $seat_code): bool
    {
        global $wpdb;

        $result = $wpdb->update(
            $this->table,
            [
                'status' => Seat::STATUS_CHECKED_IN,
                'checked_in_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ],
            [
                'tour_departure_id' => $departure_id,
                'departure_vehicle_id' => $vehicle_id,
                'seat_code' => $seat_code,
            ],
            ['%s', '%s', '%s'],
            ['%d', '%d', '%s']
        );

        return $result !== false;
    }

    /**
     * Block a seat
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @param string $seat_code
     * @return bool
     */
    public function block_seat(int $departure_id, int $vehicle_id, string $seat_code): bool
    {
        global $wpdb;

        $result = $wpdb->update(
            $this->table,
            [
                'status' => Seat::STATUS_BLOCKED,
                'updated_at' => current_time('mysql'),
            ],
            [
                'tour_departure_id' => $departure_id,
                'departure_vehicle_id' => $vehicle_id,
                'seat_code' => $seat_code,
            ],
            ['%s', '%s'],
            ['%d', '%d', '%s']
        );

        return $result !== false;
    }

    /**
     * Unblock a seat
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @param string $seat_code
     * @return bool
     */
    public function unblock_seat(int $departure_id, int $vehicle_id, string $seat_code): bool
    {
        global $wpdb;

        $result = $wpdb->update(
            $this->table,
            [
                'status' => Seat::STATUS_AVAILABLE,
                'updated_at' => current_time('mysql'),
            ],
            [
                'tour_departure_id' => $departure_id,
                'departure_vehicle_id' => $vehicle_id,
                'seat_code' => $seat_code,
            ],
            ['%s', '%s'],
            ['%d', '%d', '%s']
        );

        return $result !== false;
    }

    /**
     * Get expired holding seats
     *
     * @return Seat[]
     */
    public function get_expired_holds(): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE status = %s AND hold_expires_at < %s",
                Seat::STATUS_HOLDING,
                current_time('mysql')
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Seat($data), $results ?: []);
    }

    /**
     * Release all expired holds
     *
     * @return int Number of seats released
     */
    public function release_expired_holds(): int
    {
        global $wpdb;

        $result = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$this->table} SET status = %s, booking_id = NULL, passenger_id = NULL, hold_expires_at = NULL, updated_at = %s WHERE status = %s AND hold_expires_at < %s",
                Seat::STATUS_AVAILABLE,
                current_time('mysql'),
                Seat::STATUS_HOLDING,
                current_time('mysql')
            )
        );

        return $result !== false ? $result : 0;
    }

    /**
     * Create seats from layout
     *
     * @param int $departure_id
     * @param int $departure_vehicle_id
     * @param array $seat_codes
     * @return bool
     */
    public function create_seats(int $departure_id, int $departure_vehicle_id, array $seat_codes): bool
    {
        global $wpdb;

        $now = current_time('mysql');
        $values = [];
        $placeholders = [];

        foreach ($seat_codes as $code) {
            $placeholders[] = '(%d, %d, %s, %s, %s)';
            $values[] = $departure_id;
            $values[] = $departure_vehicle_id;
            $values[] = $code;
            $values[] = Seat::STATUS_AVAILABLE;
            $values[] = $now;
        }

        if (empty($placeholders)) {
            return true;
        }

        $sql = "INSERT INTO {$this->table} (tour_departure_id, departure_vehicle_id, seat_code, status, created_at) VALUES " . implode(', ', $placeholders);

        $result = $wpdb->query($wpdb->prepare($sql, ...$values));

        return $result !== false;
    }

    /**
     * Get seat statistics for a departure
     *
     * @param int $departure_id
     * @return array
     */
    public function get_stats(int $departure_id): array
    {
        global $wpdb;

        $stats = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN status = 'holding' THEN 1 ELSE 0 END) as holding,
                    SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked,
                    SUM(CASE WHEN status = 'checked_in' THEN 1 ELSE 0 END) as checked_in,
                    SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked
                FROM {$this->table} WHERE tour_departure_id = %d",
                $departure_id
            ),
            ARRAY_A
        );

        return [
            'total' => (int) ($stats['total'] ?? 0),
            'available' => (int) ($stats['available'] ?? 0),
            'holding' => (int) ($stats['holding'] ?? 0),
            'booked' => (int) ($stats['booked'] ?? 0),
            'checked_in' => (int) ($stats['checked_in'] ?? 0),
            'blocked' => (int) ($stats['blocked'] ?? 0),
        ];
    }
}
