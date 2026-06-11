<?php
/**
 * Layout Service
 *
 * Business logic for vehicle layouts.
 *
 * @since 0.1.0
 */

namespace TourBooking\Services;

use TourBooking\Repositories\VehicleLayoutRepository;
use TourBooking\Models\VehicleLayout;
use TourBooking\ActivityLogger;

class LayoutService
{
    protected VehicleLayoutRepository $repository;

    public function __construct()
    {
        $this->repository = new VehicleLayoutRepository();
    }

    /**
     * Get layout by ID
     *
     * @param int $id
     * @return VehicleLayout|null
     */
    public function get(int $id): ?VehicleLayout
    {
        return $this->repository->find($id);
    }

    /**
     * Get all layouts
     *
     * @param array $args
     * @return VehicleLayout[]
     */
    public function get_all(array $args = []): array
    {
        return $this->repository->get_all($args);
    }

    /**
     * Create a new layout
     *
     * @param array $data
     * @return VehicleLayout|false
     */
    public function create(array $data): ?VehicleLayout
    {
        if (empty($data['name']) || empty($data['layout_json'])) {
            return null;
        }

        // Validate layout JSON
        if (!VehicleLayout::validate_layout_json($data['layout_json'])) {
            return null;
        }

        // Calculate total seats if not provided
        if (empty($data['total_seats'])) {
            $temp_layout = new VehicleLayout($data);
            $data['total_seats'] = count($temp_layout->get_seat_codes());
        }

        $id = $this->repository->create($data);

        if (!$id) {
            return null;
        }

        ActivityLogger::log('layout', $id, 'layout_created', null, $data);

        return $this->repository->find($id);
    }

    /**
     * Update a layout
     *
     * @param int $id
     * @param array $data
     * @return VehicleLayout|false
     */
    public function update(int $id, array $data): ?VehicleLayout
    {
        $layout = $this->repository->find($id);

        if (!$layout) {
            return null;
        }

        // Validate layout JSON if provided
        if (isset($data['layout_json']) && !VehicleLayout::validate_layout_json($data['layout_json'])) {
            return null;
        }

        $old_data = $layout->to_array();

        $success = $this->repository->update($id, $data);

        if (!$success) {
            return null;
        }

        ActivityLogger::log('layout', $id, 'layout_updated', $old_data, $data);

        return $this->repository->find($id);
    }

    /**
     * Delete a layout
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $layout = $this->repository->find($id);

        if (!$layout) {
            return false;
        }

        ActivityLogger::log('layout', $id, 'layout_deleted', $layout->to_array(), null);

        return $this->repository->delete($id);
    }

    /**
     * Create a layout with default template
     *
     * @param string $name
     * @param string $vehicle_type
     * @param string $template '45_seat' or '29_seat'
     * @return VehicleLayout|false
     */
    public function create_from_template(string $name, string $vehicle_type, string $template): ?VehicleLayout
    {
        $layout_json = match ($template) {
            '45_seat' => VehicleLayout::default_45_seat_layout(),
            '29_seat' => VehicleLayout::default_29_seat_layout(),
            default => null,
        };

        if (!$layout_json) {
            return null;
        }

        $temp_layout = new VehicleLayout(['layout_json' => $layout_json]);
        $total_seats = count($temp_layout->get_seat_codes());

        return $this->create([
            'name' => $name,
            'vehicle_type' => $vehicle_type,
            'total_seats' => $total_seats,
            'layout_json' => $layout_json,
        ]);
    }
}
