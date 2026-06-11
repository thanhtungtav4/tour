<?php
/**
 * VehicleLayout Repository
 *
 * Data access layer for vehicle layouts.
 *
 * @since 0.1.0
 */

namespace TourBooking\Repositories;

use TourBooking\Models\VehicleLayout;

class VehicleLayoutRepository
{
    protected string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'nt_vehicle_layouts';
    }

    /**
     * Find layout by ID
     *
     * @param int $id
     * @return VehicleLayout|null
     */
    public function find(int $id): ?VehicleLayout
    {
        global $wpdb;

        $data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$data) {
            return null;
        }

        return new VehicleLayout($data);
    }

    /**
     * Get all layouts
     *
     * @param array $args
     * @return VehicleLayout[]
     */
    public function get_all(array $args = []): array
    {
        global $wpdb;

        $defaults = [
            'vehicle_type' => null,
            'orderby' => 'name',
            'order' => 'ASC',
        ];

        $args = wp_parse_args($args, $defaults);

        $where = [];
        $values = [];

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

        return array_map(fn($data) => new VehicleLayout($data), $results ?: []);
    }

    /**
     * Create a new layout
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        global $wpdb;

        $data['created_at'] = current_time('mysql');

        $result = $wpdb->insert($this->table, $data, [
            '%s', '%s', '%d', '%s', '%s', '%s',
        ]);

        if ($result === false) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update a layout
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
     * Delete a layout
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        global $wpdb;

        // Check if any vehicles use this layout
        $vehicles_table = $wpdb->prefix . 'nt_vehicles';
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$vehicles_table} WHERE layout_id = %d",
                $id
            )
        );

        if ($count > 0) {
            return false; // Cannot delete layout in use
        }

        $result = $wpdb->delete($this->table, ['id' => $id], ['%d']);

        return $result !== false;
    }

    /**
     * Get layouts by vehicle type
     *
     * @param string $type
     * @return VehicleLayout[]
     */
    public function get_by_type(string $type): array
    {
        return $this->get_all(['vehicle_type' => $type]);
    }
}
