<?php
/**
 * Vehicle Service
 *
 * Business logic for vehicles.
 *
 * @since 0.1.0
 */

namespace TourBooking\Services;

use TourBooking\Repositories\VehicleRepository;
use TourBooking\Models\Vehicle;
use TourBooking\ActivityLogger;

class VehicleService
{
    protected VehicleRepository $repository;

    public function __construct()
    {
        $this->repository = new VehicleRepository();
    }

    /**
     * Get vehicle by ID
     *
     * @param int $id
     * @return Vehicle|null
     */
    public function get(int $id): ?Vehicle
    {
        return $this->repository->find($id);
    }

    /**
     * Get all active vehicles
     *
     * @return Vehicle[]
     */
    public function get_all_active(): array
    {
        return $this->repository->get_all_active();
    }

    /**
     * Get all vehicles
     *
     * @param array $args
     * @return Vehicle[]
     */
    public function get_all(array $args = []): array
    {
        return $this->repository->get_all($args);
    }

    /**
     * Create a new vehicle
     *
     * @param array $data
     * @return Vehicle|false
     */
    public function create(array $data): ?Vehicle
    {
        if (empty($data['name'])) {
            return null;
        }

        if (empty($data['status'])) {
            $data['status'] = Vehicle::STATUS_ACTIVE;
        }

        $id = $this->repository->create($data);

        if (!$id) {
            return null;
        }

        ActivityLogger::log('vehicle', $id, 'vehicle_created', null, $data);

        return $this->repository->find($id);
    }

    /**
     * Update a vehicle
     *
     * @param int $id
     * @param array $data
     * @return Vehicle|false
     */
    public function update(int $id, array $data): ?Vehicle
    {
        $vehicle = $this->repository->find($id);

        if (!$vehicle) {
            return null;
        }

        $old_data = $vehicle->to_array();

        $success = $this->repository->update($id, $data);

        if (!$success) {
            return null;
        }

        ActivityLogger::log('vehicle', $id, 'vehicle_updated', $old_data, $data);

        return $this->repository->find($id);
    }

    /**
     * Delete a vehicle
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $vehicle = $this->repository->find($id);

        if (!$vehicle) {
            return false;
        }

        ActivityLogger::log('vehicle', $id, 'vehicle_deleted', $vehicle->to_array(), null);

        return $this->repository->delete($id);
    }

    /**
     * Toggle vehicle status
     *
     * @param int $id
     * @return bool
     */
    public function toggle_status(int $id): bool
    {
        $vehicle = $this->repository->find($id);

        if (!$vehicle) {
            return false;
        }

        $old_status = $vehicle->status;
        $success = $this->repository->toggle_status($id);

        if ($success) {
            $new_status = $vehicle->is_active() ? 'inactive' : 'active';
            ActivityLogger::log('vehicle', $id, 'vehicle_status_changed', $old_status, $new_status);
        }

        return $success;
    }
}
