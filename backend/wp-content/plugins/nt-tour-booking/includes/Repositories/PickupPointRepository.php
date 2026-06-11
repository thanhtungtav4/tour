<?php
/**
 * PickupPoint Repository
 *
 * Data access layer for pickup points.
 *
 * @since 0.1.0
 */

namespace TourBooking\Repositories;

use TourBooking\Models\PickupPoint;

class PickupPointRepository
{
    /**
     * @var string Table name
     */
    protected string $table;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'nt_pickup_points';
    }

    /**
     * Find pickup point by ID
     *
     * @param int $id
     * @return PickupPoint|null
     */
    public function find(int $id): ?PickupPoint
    {
        global $wpdb;

        $data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$data) {
            return null;
        }

        return new PickupPoint($data);
    }

    /**
     * Get all active pickup points
     *
     * @return PickupPoint[]
     */
    public function get_all_active(): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY sort_order ASC, name ASC",
            ARRAY_A
        );

        return array_map(fn($data) => new PickupPoint($data), $results ?: []);
    }

    /**
     * Get all pickup points
     *
     * @param array $args
     * @return PickupPoint[]
     */
    public function get_all(array $args = []): array
    {
        global $wpdb;

        $defaults = [
            'status' => null,
            'orderby' => 'sort_order',
            'order' => 'ASC',
        ];

        $args = wp_parse_args($args, $defaults);

        $where = '';
        $values = [];

        if ($args['status']) {
            $where = 'WHERE status = %s';
            $values[] = $args['status'];
        }

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} {$where} ORDER BY {$args['orderby']} {$args['order']}",
                ...$values
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new PickupPoint($data), $results ?: []);
    }

    /**
     * Create a new pickup point
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        global $wpdb;

        $data['created_at'] = current_time('mysql');

        $result = $wpdb->insert($this->table, $data, [
            '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s',
        ]);

        if ($result === false) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update a pickup point
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
     * Delete a pickup point
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
     * Update status
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function update_status(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Toggle active status
     *
     * @param int $id
     * @return bool
     */
    public function toggle_status(int $id): bool
    {
        $pickup_point = $this->find($id);

        if (!$pickup_point) {
            return false;
        }

        $new_status = $pickup_point->is_active() ? PickupPoint::STATUS_INACTIVE : PickupPoint::STATUS_ACTIVE;

        return $this->update_status($id, $new_status);
    }

    /**
     * Update sort order
     *
     * @param int $id
     * @param int $sort_order
     * @return bool
     */
    public function update_sort_order(int $id, int $sort_order): bool
    {
        return $this->update($id, ['sort_order' => $sort_order]);
    }

    /**
     * Reorder pickup points
     *
     * @param array $order Array of [id => sort_order]
     * @return bool
     */
    public function reorder(array $order): bool
    {
        global $wpdb;

        foreach ($order as $id => $sort_order) {
            $wpdb->update(
                $this->table,
                ['sort_order' => (int) $sort_order],
                ['id' => (int) $id],
                ['%d'],
                ['%d']
            );
        }

        return true;
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
        global $wpdb;

        $table_name = $wpdb->prefix . 'nt_departure_pickup_points';

        // Remove existing assignments
        $wpdb->delete($table_name, ['tour_departure_id' => $departure_id], ['%d']);

        // Add new assignments
        foreach ($pickup_point_ids as $sort_order => $pickup_point_id) {
            $wpdb->insert(
                $table_name,
                [
                    'tour_departure_id' => $departure_id,
                    'pickup_point_id' => $pickup_point_id,
                    'sort_order' => $sort_order,
                    'status' => 'active',
                    'created_at' => current_time('mysql'),
                ],
                ['%d', '%d', '%d', '%s', '%s']
            );
        }

        return true;
    }

    /**
     * Get pickup points assigned to a departure
     *
     * @param int $departure_id
     * @return array
     */
    public function get_for_departure(int $departure_id): array
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nt_departure_pickup_points';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT pp.*, dpp.pickup_time, dpp.note as departure_note, dpp.sort_order as departure_sort_order
                FROM {$table_name} dpp
                JOIN {$this->table} pp ON dpp.pickup_point_id = pp.id
                WHERE dpp.tour_departure_id = %d AND dpp.status = 'active'
                ORDER BY dpp.sort_order ASC",
                $departure_id
            ),
            ARRAY_A
        );

        return $results ?: [];
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
        global $wpdb;

        $table_name = $wpdb->prefix . 'nt_departure_pickup_points';

        $result = $wpdb->update(
            $table_name,
            ['pickup_time' => $pickup_time],
            [
                'tour_departure_id' => $departure_id,
                'pickup_point_id' => $pickup_point_id,
            ],
            ['%s'],
            ['%d', '%d']
        );

        return $result !== false;
    }

    /**
     * Search pickup points
     *
     * @param string $query
     * @return PickupPoint[]
     */
    public function search(string $query): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE status = 'active' AND name LIKE %s ORDER BY sort_order ASC",
                '%' . $wpdb->esc_like($query) . '%'
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new PickupPoint($data), $results ?: []);
    }
}
