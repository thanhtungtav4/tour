<?php
/**
 * Vehicle Repository
 *
 * Data access layer for vehicles.
 *
 * @since 0.1.0
 */

namespace TourBooking\Repositories;

use TourBooking\Models\Vehicle;

class VehicleRepository
{
    protected string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'nt_vehicles';
    }

    /**
     * Find vehicle by ID
     *
     * @param int $id
     * @return Vehicle|null
     */
    public function find(int $id): ?Vehicle
    {
        global $wpdb;

        $data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$data) {
            return null;
        }

        return new Vehicle($data);
    }

    /**
     * Get all active vehicles
     *
     * @return Vehicle[]
     */
    public function get_all_active(): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name ASC",
            ARRAY_A
        );

        return array_map(fn($data) => new Vehicle($data), $results ?: []);
    }

    /**
     * Get all vehicles
     *
     * @param array $args
     * @return Vehicle[]
     */
    public function get_all(array $args = []): array
    {
        global $wpdb;

        $defaults = [
            'status' => null,
            'vehicle_type' => null,
            'orderby' => 'name',
            'order' => 'ASC',
        ];

        $args = wp_parse_args($args, $defaults);

        $where = [];
        $values = [];

        if ($args['status']) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if ($args['vehicle_type']) {
            $where[] = 'vehicle_type = %s';
            $values[] = $args['vehicle_type'];
        }

        $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $values[] = $args['orderby'];
        $values[] = $args['order'];

        $sql = "SELECT * FROM {$this->table} {$where_clause} ORDER BY %s %s";

        $results = $wpdb->get_results(
            $wpdb->prepare($sql, ...$values),
            ARRAY_A
        );

        return array_map(fn($data) => new Vehicle($data), $results ?: []);
    }

    /**
     * Create a new vehicle
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        global $wpdb;

        $data['created_at'] = current_time('mysql');

        $result = $wpdb->insert($this->table, $data, [
            '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s',
        ]);

        if ($result === false) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update a vehicle
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
     * Delete a vehicle
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
     * Toggle status
     *
     * @param int $id
     * @return bool
     */
    public function toggle_status(int $id): bool
    {
        $vehicle = $this->find($id);

        if (!$vehicle) {
            return false;
        }

        $new_status = $vehicle->is_active() ? Vehicle::STATUS_INACTIVE : Vehicle::STATUS_ACTIVE;

        return $this->update($id, ['status' => $new_status]);
    }

    /**
     * Get vehicles by type
     *
     * @param string $type
     * @return Vehicle[]
     */
    public function get_by_type(string $type): array
    {
        return $this->get_all(['vehicle_type' => $type, 'status' => 'active']);
    }
}
