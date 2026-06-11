<?php
/**
 * Departure Service
 *
 * Business logic for departures.
 *
 * @since 0.1.0
 */

namespace TourBooking\Services;

use TourBooking\Repositories\DepartureRepository;
use TourBooking\Models\Departure;
use TourBooking\ActivityLogger;

class DepartureService
{
    protected DepartureRepository $repository;

    public function __construct()
    {
        $this->repository = new DepartureRepository();
    }

    /**
     * Get departure by ID
     *
     * @param int $id
     * @return Departure|null
     */
    public function get(int $id): ?Departure
    {
        return $this->repository->find($id);
    }

    /**
     * Get departures by tour
     *
     * @param int $tour_id
     * @param array $args
     * @return Departure[]
     */
    public function get_by_tour(int $tour_id, array $args = []): array
    {
        return $this->repository->get_by_tour($tour_id, $args);
    }

    /**
     * Get upcoming departures
     *
     * @param array $args
     * @return Departure[]
     */
    public function get_upcoming(array $args = []): array
    {
        return $this->repository->get_upcoming($args);
    }

    /**
     * Create a new departure
     *
     * @param array $data
     * @return Departure|false
     */
    public function create(array $data): ?Departure
    {
        // Validate required fields
        if (empty($data['tour_id']) || empty($data['start_date'])) {
            return null;
        }

        // Set default status
        if (empty($data['status'])) {
            $data['status'] = Departure::STATUS_OPEN;
        }

        $id = $this->repository->create($data);

        if (!$id) {
            return null;
        }

        ActivityLogger::log('departure', $id, 'departure_created', null, $data);

        return $this->repository->find($id);
    }

    /**
     * Update a departure
     *
     * @param int $id
     * @param array $data
     * @return Departure|false
     */
    public function update(int $id, array $data): ?Departure
    {
        $departure = $this->repository->find($id);

        if (!$departure) {
            return null;
        }

        $old_data = $departure->to_array();

        $success = $this->repository->update($id, $data);

        if (!$success) {
            return null;
        }

        ActivityLogger::log('departure', $id, 'departure_updated', $old_data, $data);

        return $this->repository->find($id);
    }

    /**
     * Delete a departure
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $departure = $this->repository->find($id);

        if (!$departure) {
            return false;
        }

        // Check if there are bookings
        global $wpdb;
        $bookings_table = $wpdb->prefix . 'nt_bookings';

        $has_bookings = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$bookings_table} WHERE tour_departure_id = %d AND booking_status NOT IN ('cancelled')",
                $id
            )
        );

        if ($has_bookings > 0) {
            return false; // Cannot delete departure with active bookings
        }

        ActivityLogger::log('departure', $id, 'departure_deleted', $departure->to_array(), null);

        return $this->repository->delete($id);
    }

    /**
     * Update departure status
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function update_status(int $id, string $status): bool
    {
        $departure = $this->repository->find($id);

        if (!$departure) {
            return false;
        }

        $old_status = $departure->status;

        $success = $this->repository->update_status($id, $status);

        if ($success) {
            ActivityLogger::log('departure', $id, 'departure_status_changed', $old_status, $status);
        }

        return $success;
    }

    /**
     * Open a departure for booking
     *
     * @param int $id
     * @return bool
     */
    public function open(int $id): bool
    {
        return $this->update_status($id, Departure::STATUS_OPEN);
    }

    /**
     * Close a departure
     *
     * @param int $id
     * @return bool
     */
    public function close(int $id): bool
    {
        return $this->update_status($id, Departure::STATUS_CLOSED);
    }

    /**
     * Cancel a departure
     *
     * @param int $id
     * @return bool
     */
    public function cancel(int $id): bool
    {
        return $this->update_status($id, Departure::STATUS_CANCELLED);
    }

    /**
     * Mark departure as departed
     *
     * @param int $id
     * @return bool
     */
    public function mark_departed(int $id): bool
    {
        return $this->update_status($id, Departure::STATUS_DEPARTED);
    }

    /**
     * Mark departure as completed
     *
     * @param int $id
     * @return bool
     */
    public function mark_completed(int $id): bool
    {
        return $this->update_status($id, Departure::STATUS_COMPLETED);
    }

    /**
     * Get departures for today
     *
     * @return Departure[]
     */
    public function get_today(): array
    {
        return $this->repository->get_today();
    }

    /**
     * Get departures by date range
     *
     * @param string $start_date
     * @param string $end_date
     * @param array $args
     * @return Departure[]
     */
    public function get_by_date_range(string $start_date, string $end_date, array $args = []): array
    {
        return $this->repository->get_by_date_range($start_date, $end_date, $args);
    }

    /**
     * Count departures
     *
     * @param string|null $status
     * @return int
     */
    public function count(?string $status = null): int
    {
        return $this->repository->count($status);
    }
}
