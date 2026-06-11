<?php
/**
 * PickupPoint Service
 *
 * Business logic for pickup points.
 *
 * @since 0.1.0
 */

namespace TourBooking\Services;

use TourBooking\Repositories\PickupPointRepository;
use TourBooking\Models\PickupPoint;
use TourBooking\ActivityLogger;

class PickupPointService
{
    protected PickupPointRepository $repository;

    public function __construct()
    {
        $this->repository = new PickupPointRepository();
    }

    /**
     * Get pickup point by ID
     *
     * @param int $id
     * @return PickupPoint|null
     */
    public function get(int $id): ?PickupPoint
    {
        return $this->repository->find($id);
    }

    /**
     * Get all active pickup points
     *
     * @return PickupPoint[]
     */
    public function get_all_active(): array
    {
        return $this->repository->get_all_active();
    }

    /**
     * Get all pickup points
     *
     * @param array $args
     * @return PickupPoint[]
     */
    public function get_all(array $args = []): array
    {
        return $this->repository->get_all($args);
    }

    /**
     * Create a new pickup point
     *
     * @param array $data
     * @return PickupPoint|false
     */
    public function create(array $data): ?PickupPoint
    {
        // Validate required fields
        if (empty($data['name'])) {
            return null;
        }

        // Set default status
        if (empty($data['status'])) {
            $data['status'] = PickupPoint::STATUS_ACTIVE;
        }

        $id = $this->repository->create($data);

        if (!$id) {
            return null;
        }

        ActivityLogger::log('pickup_point', $id, 'pickup_point_created', null, $data);

        return $this->repository->find($id);
    }

    /**
     * Update a pickup point
     *
     * @param int $id
     * @param array $data
     * @return PickupPoint|false
     */
    public function update(int $id, array $data): ?PickupPoint
    {
        $pickup_point = $this->repository->find($id);

        if (!$pickup_point) {
            return null;
        }

        $old_data = $pickup_point->to_array();

        $success = $this->repository->update($id, $data);

        if (!$success) {
            return null;
        }

        ActivityLogger::log('pickup_point', $id, 'pickup_point_updated', $old_data, $data);

        return $this->repository->find($id);
    }

    /**
     * Delete a pickup point
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $pickup_point = $this->repository->find($id);

        if (!$pickup_point) {
            return false;
        }

        ActivityLogger::log('pickup_point', $id, 'pickup_point_deleted', $pickup_point->to_array(), null);

        return $this->repository->delete($id);
    }

    /**
     * Toggle active status
     *
     * @param int $id
     * @return bool
     */
    public function toggle_status(int $id): bool
    {
        $pickup_point = $this->repository->find($id);

        if (!$pickup_point) {
            return false;
        }

        $old_status = $pickup_point->status;
        $success = $this->repository->toggle_status($id);

        if ($success) {
            $new_status = $pickup_point->is_active() ? 'inactive' : 'active';
            ActivityLogger::log('pickup_point', $id, 'pickup_point_status_changed', $old_status, $new_status);
        }

        return $success;
    }

    /**
     * Reorder pickup points
     *
     * @param array $order
     * @return bool
     */
    public function reorder(array $order): bool
    {
        return $this->repository->reorder($order);
    }

    /**
     * Assign pickup points to a departure
     *
     * @param int $departure_id
     * @param array $pickup_point_ids
     * @return bool
     */
    public function assign_to_departure(int $departure_id, array $pickup_point_ids): bool
    {
        $success = $this->repository->assign_to_departure($departure_id, $pickup_point_ids);

        if ($success) {
            ActivityLogger::log('departure', $departure_id, 'pickup_points_assigned', null, [
                'departure_id' => $departure_id,
                'pickup_point_ids' => $pickup_point_ids,
            ]);
        }

        return $success;
    }

    /**
     * Get pickup points for a departure
     *
     * @param int $departure_id
     * @return array
     */
    public function get_for_departure(int $departure_id): array
    {
        return $this->repository->get_for_departure($departure_id);
    }

    /**
     * Update pickup time for departure
     *
     * @param int $departure_id
     * @param int $pickup_point_id
     * @param string|null $pickup_time
     * @return bool
     */
    public function update_departure_pickup_time(int $departure_id, int $pickup_point_id, ?string $pickup_time): bool
    {
        return $this->repository->update_departure_pickup_time($departure_id, $pickup_point_id, $pickup_time);
    }

    /**
     * Search pickup points
     *
     * @param string $query
     * @return PickupPoint[]
     */
    public function search(string $query): array
    {
        return $this->repository->search($query);
    }
}
