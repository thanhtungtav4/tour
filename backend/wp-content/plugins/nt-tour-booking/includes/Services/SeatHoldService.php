<?php
/**
 * Seat Hold Service
 *
 * Handles atomic seat holding with race condition protection.
 *
 * @since 0.1.0
 */

namespace TourBooking\Services;

use TourBooking\Repositories\SeatRepository;
use TourBooking\Models\Seat;
use TourBooking\Admin\SettingsPage;

class SeatHoldService
{
    protected SeatRepository $seat_repo;

    public function __construct()
    {
        $this->seat_repo = new SeatRepository();
    }

    /**
     * Hold seats atomically for a booking
     *
     * Uses atomic UPDATE with WHERE conditions to prevent race conditions.
     * If any seat fails to hold, all previously held seats are released (rollback).
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @param array $seat_codes Array of seat codes to hold
     * @param int $booking_id
     * @param array $passenger_ids Array of passenger IDs (same order as seat_codes)
     * @return array ['success' => bool, 'held_seats' => array, 'failed_seats' => array]
     */
    public function hold_seats(int $departure_id, int $vehicle_id, array $seat_codes, int $booking_id, array $passenger_ids): array
    {
        global $wpdb;

        $hold_minutes = (int) SettingsPage::get('nt_tour_seat_hold_minutes', 120);
        $hold_expires_at = date('Y-m-d H:i:s', time() + ($hold_minutes * 60));

        $failed_seats = [];
        $held_seats = [];

        // Use transaction to ensure atomicity
        $wpdb->query('START TRANSACTION');

        foreach ($seat_codes as $index => $seat_code) {
            $passenger_id = $passenger_ids[$index] ?? 0;

            $success = $this->seat_repo->hold_seat(
                $departure_id,
                $vehicle_id,
                $seat_code,
                $booking_id,
                $passenger_id,
                $hold_expires_at
            );

            if ($success) {
                $held_seats[] = $seat_code;
            } else {
                $failed_seats[] = $seat_code;
            }
        }

        // If any seat failed, rollback entire transaction
        if (!empty($failed_seats)) {
            $wpdb->query('ROLLBACK');
            return [
                'success' => false,
                'held_seats' => [],
                'failed_seats' => $failed_seats,
                'message' => sprintf(
                    __('Seat(s) %s are no longer available', 'nt-tour-booking'),
                    implode(', ', $failed_seats)
                ),
            ];
        }

        $wpdb->query('COMMIT');

        return [
            'success' => true,
            'held_seats' => $held_seats,
            'failed_seats' => [],
            'hold_expires_at' => $hold_expires_at,
            'message' => sprintf(
                __('Successfully held %d seat(s). Expires at %s', 'nt-tour-booking'),
                count($held_seats),
                date_i18n('H:i d/m/Y', strtotime($hold_expires_at))
            ),
        ];
    }

    /**
     * Release seats
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @param array $seat_codes
     * @return bool
     */
    public function release_seats(int $departure_id, int $vehicle_id, array $seat_codes): bool
    {
        foreach ($seat_codes as $seat_code) {
            $this->seat_repo->release_seat($departure_id, $vehicle_id, $seat_code);
        }

        return true;
    }

    /**
     * Book held seats (confirm booking)
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @param array $seat_codes
     * @return bool
     */
    public function book_seats(int $departure_id, int $vehicle_id, array $seat_codes): bool
    {
        foreach ($seat_codes as $seat_code) {
            $this->seat_repo->book_seat($departure_id, $vehicle_id, $seat_code);
        }

        return true;
    }

    /**
     * Release all expired holds
     *
     * @return int Number of seats released
     */
    public function release_expired_holds(): int
    {
        return $this->seat_repo->release_expired_holds();
    }

    /**
     * Get seat status
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @param string $seat_code
     * @return Seat|null
     */
    public function get_seat(int $departure_id, int $vehicle_id, string $seat_code): ?Seat
    {
        return $this->seat_repo->find_by_code($departure_id, $vehicle_id, $seat_code);
    }

    /**
     * Check if seat is available
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @param string $seat_code
     * @return bool
     */
    public function is_seat_available(int $departure_id, int $vehicle_id, string $seat_code): bool
    {
        $seat = $this->get_seat($departure_id, $vehicle_id, $seat_code);

        if (!$seat) {
            return false;
        }

        return $seat->is_available();
    }

    /**
     * Get available seats count
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @return int
     */
    public function get_available_count(int $departure_id, int $vehicle_id): int
    {
        $seats = $this->seat_repo->get_available($departure_id, $vehicle_id);

        return count($seats);
    }

    /**
     * Reserve seats for a booking (admin assign)
     *
     * Similar to hold_seats but without time limit - admin direct assignment
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @param array $seat_codes
     * @param int $booking_id
     * @param array $passenger_ids
     * @return array
     */
    public function assign_seats(int $departure_id, int $vehicle_id, array $seat_codes, int $booking_id, array $passenger_ids): array
    {
        // Admin assignment directly books seats (no hold timeout)
        foreach ($seat_codes as $index => $seat_code) {
            $passenger_id = $passenger_ids[$index] ?? 0;

            // Use atomic hold then immediately book
            $held = $this->seat_repo->hold_seat(
                $departure_id,
                $vehicle_id,
                $seat_code,
                $booking_id,
                $passenger_id,
                date('Y-m-d H:i:s', time() + 60) // 1 minute grace period
            );

            if ($held) {
                $this->seat_repo->book_seat($departure_id, $vehicle_id, $seat_code);
            }
        }

        return [
            'success' => true,
            'assigned_seats' => $seat_codes,
            'message' => sprintf(__('Assigned %d seat(s)', 'nt-tour-booking'), count($seat_codes)),
        ];
    }

    /**
     * Change seat for a passenger
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @param string $old_seat_code
     * @param string $new_seat_code
     * @param int $passenger_id
     * @return array
     */
    public function change_seat(int $departure_id, int $vehicle_id, string $old_seat_code, string $new_seat_code, int $passenger_id): array
    {
        global $wpdb;

        // Check if new seat is available
        $new_seat = $this->get_seat($departure_id, $vehicle_id, $new_seat_code);

        if (!$new_seat || !$new_seat->is_available()) {
            return [
                'success' => false,
                'message' => __('New seat is not available', 'nt-tour-booking'),
            ];
        }

        // Get the seat table
        $table_name = $wpdb->prefix . 'nt_departure_seats';

        // Release old seat
        $this->seat_repo->release_seat($departure_id, $vehicle_id, $old_seat_code);

        // Hold and book new seat atomically
        $held = $this->seat_repo->hold_seat(
            $departure_id,
            $vehicle_id,
            $new_seat_code,
            $new_seat->booking_id ?? 0,
            $passenger_id,
            date('Y-m-d H:i:s', time() + 60)
        );

        if ($held) {
            $this->seat_repo->book_seat($departure_id, $vehicle_id, $new_seat_code);

            // Update passenger's seat
            $passengers_table = $wpdb->prefix . 'nt_booking_passengers';
            $wpdb->update(
                $passengers_table,
                ['seat_code' => $new_seat_code, 'updated_at' => current_time('mysql')],
                ['id' => $passenger_id],
                ['%s', '%s'],
                ['%d']
            );

            return [
                'success' => true,
                'old_seat' => $old_seat_code,
                'new_seat' => $new_seat_code,
                'message' => sprintf(__('Changed seat from %s to %s', 'nt-tour-booking'), $old_seat_code, $new_seat_code),
            ];
        }

        // Rollback - re-hold old seat
        $this->seat_repo->hold_seat(
            $departure_id,
            $vehicle_id,
            $old_seat_code,
            $new_seat->booking_id ?? 0,
            $passenger_id,
            date('Y-m-d H:i:s', time() + 60)
        );

        return [
            'success' => false,
            'message' => __('Failed to change seat. New seat may have been taken.', 'nt-tour-booking'),
        ];
    }
}
