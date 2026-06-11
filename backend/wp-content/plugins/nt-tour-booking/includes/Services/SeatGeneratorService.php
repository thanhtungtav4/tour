<?php
/**
 * Seat Generator Service
 *
 * Service for generating and managing seats.
 *
 * @since 0.1.0
 */

namespace TourBooking\Services;

use TourBooking\Repositories\SeatRepository;
use TourBooking\Repositories\VehicleLayoutRepository;
use TourBooking\Models\Seat;
use TourBooking\ActivityLogger;

class SeatGeneratorService
{
    protected SeatRepository $seat_repository;
    protected VehicleLayoutRepository $layout_repository;

    public function __construct()
    {
        $this->seat_repository = new SeatRepository();
        $this->layout_repository = new VehicleLayoutRepository();
    }

    /**
     * Generate seats for a departure vehicle from layout
     *
     * @param int $departure_id
     * @param int $departure_vehicle_id
     * @param int $layout_id
     * @return array ['success' => bool, 'seats_created' => int, 'message' => string]
     */
    public function generate_seats(int $departure_id, int $departure_vehicle_id, int $layout_id): array
    {
        // Get layout
        $layout = $this->layout_repository->find($layout_id);

        if (!$layout) {
            return [
                'success' => false,
                'seats_created' => 0,
                'message' => 'Layout not found',
            ];
        }

        // Get seat codes from layout
        $seat_codes = $layout->get_seat_codes();

        if (empty($seat_codes)) {
            return [
                'success' => false,
                'seats_created' => 0,
                'message' => 'No seats found in layout',
            ];
        }

        // Check if seats already exist
        $existing_seats = $this->seat_repository->get_by_departure_vehicle($departure_id, $departure_vehicle_id);

        if (!empty($existing_seats)) {
            return [
                'success' => false,
                'seats_created' => 0,
                'message' => 'Seats already exist for this departure vehicle',
            ];
        }

        // Create seats
        $success = $this->seat_repository->create_seats($departure_id, $departure_vehicle_id, $seat_codes);

        if (!$success) {
            return [
                'success' => false,
                'seats_created' => 0,
                'message' => 'Failed to create seats',
            ];
        }

        ActivityLogger::log(
            'departure',
            $departure_id,
            'seats_generated',
            null,
            [
                'departure_vehicle_id' => $departure_vehicle_id,
                'layout_id' => $layout_id,
                'seats_count' => count($seat_codes),
            ]
        );

        return [
            'success' => true,
            'seats_created' => count($seat_codes),
            'message' => 'Seats generated successfully',
        ];
    }

    /**
     * Get seats for a departure (all vehicles)
     *
     * @param int $departure_id
     * @return Seat[]
     */
    public function get_seats(int $departure_id): array
    {
        return $this->seat_repository->get_by_departure($departure_id);
    }

    /**
     * Get seats for a specific departure vehicle
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @return Seat[]
     */
    public function get_seats_by_vehicle(int $departure_id, int $vehicle_id): array
    {
        return $this->seat_repository->get_by_departure_vehicle($departure_id, $vehicle_id);
    }

    /**
     * Get available seats
     *
     * @param int $departure_id
     * @param int $vehicle_id
     * @return Seat[]
     */
    public function get_available_seats(int $departure_id, int $vehicle_id): array
    {
        return $this->seat_repository->get_available($departure_id, $vehicle_id);
    }

    /**
     * Get seat statistics
     *
     * @param int $departure_id
     * @return array
     */
    public function get_stats(int $departure_id): array
    {
        return $this->seat_repository->get_stats($departure_id);
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
        $success = $this->seat_repository->block_seat($departure_id, $vehicle_id, $seat_code);

        if ($success) {
            ActivityLogger::log('seat', 0, 'seat_blocked', null, [
                'departure_id' => $departure_id,
                'vehicle_id' => $vehicle_id,
                'seat_code' => $seat_code,
            ]);
        }

        return $success;
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
        $success = $this->seat_repository->unblock_seat($departure_id, $vehicle_id, $seat_code);

        if ($success) {
            ActivityLogger::log('seat', 0, 'seat_unblocked', null, [
                'departure_id' => $departure_id,
                'vehicle_id' => $vehicle_id,
                'seat_code' => $seat_code,
            ]);
        }

        return $success;
    }

    /**
     * Release expired seats (called by cron)
     *
     * @return int Number of seats released
     */
    public function release_expired_seats(): int
    {
        return $this->seat_repository->release_expired_holds();
    }
}
