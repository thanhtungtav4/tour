<?php
/**
 * Departure Repository
 *
 * Data access layer for departures.
 *
 * @since 0.1.0
 */

namespace TourBooking\Repositories;

use TourBooking\Models\Departure;

class DepartureRepository
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
        $this->table = $wpdb->prefix . 'nt_tour_departures';
    }

    /**
     * Find departure by ID
     *
     * @param int $id
     * @return Departure|null
     */
    public function find(int $id): ?Departure
    {
        global $wpdb;

        $data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$data) {
            return null;
        }

        return new Departure($data);
    }

    /**
     * Find departure by code
     *
     * @param string $code
     * @return Departure|null
     */
    public function find_by_code(string $code): ?Departure
    {
        global $wpdb;

        $data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE departure_code = %s", $code),
            ARRAY_A
        );

        if (!$data) {
            return null;
        }

        return new Departure($data);
    }

    /**
     * Get departures by tour ID
     *
     * @param int $tour_id
     * @param array $args Additional arguments
     * @return Departure[]
     */
    public function get_by_tour(int $tour_id, array $args = []): array
    {
        global $wpdb;

        $defaults = [
            'orderby' => 'start_date',
            'order' => 'ASC',
            'status' => null,
        ];

        $args = wp_parse_args($args, $defaults);

        $where = ['tour_id = %d'];
        $values = [$tour_id];

        if ($args['status']) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        $where_clause = 'WHERE ' . implode(' AND ', $where);

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} {$where_clause} ORDER BY {$args['orderby']} {$args['order']}",
                ...$values
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Departure($data), $results ?: []);
    }

    /**
     * Get upcoming departures
     *
     * @param array $args
     * @return Departure[]
     */
    public function get_upcoming(array $args = []): array
    {
        global $wpdb;

        $defaults = [
            'limit' => 10,
            'tour_id' => null,
        ];

        $args = wp_parse_args($args, $defaults);

        $where = ['start_date >= %s', 'status = %s'];
        $values = [current_time('Y-m-d'), 'open'];

        if ($args['tour_id']) {
            $where[] = 'tour_id = %d';
            $values[] = $args['tour_id'];
        }

        $where_clause = 'WHERE ' . implode(' AND ', $where);

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} {$where_clause} ORDER BY start_date ASC LIMIT %d",
                ...$values
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Departure($data), $results ?: []);
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
        global $wpdb;

        $defaults = [
            'status' => null,
        ];

        $args = wp_parse_args($args, $defaults);

        $where = ['start_date >= %s', 'start_date <= %s'];
        $values = [$start_date, $end_date];

        if ($args['status']) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        $where_clause = 'WHERE ' . implode(' AND ', $where);

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} {$where_clause} ORDER BY start_date ASC",
                ...$values
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Departure($data), $results ?: []);
    }

    /**
     * Create a new departure
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        global $wpdb;

        $data['created_at'] = current_time('mysql');

        // Generate departure code if not provided
        if (empty($data['departure_code'])) {
            $data['departure_code'] = $this->generate_departure_code($data['tour_id'] ?? 0, $data['start_date'] ?? '');
        }

        $result = $wpdb->insert($this->table, $data, [
            '%d', '%s', '%s', '%s', '%s', '%s',
            '%f', '%f', '%f', '%d', '%s', '%s', '%s',
        ]);

        if ($result === false) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update a departure
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
     * Delete a departure
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
     * Update departure status
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
     * Generate unique departure code
     *
     * @param int $tour_id
     * @param string $start_date
     * @return string
     */
    protected function generate_departure_code(int $tour_id, string $start_date): string
    {
        $tour_code = get_post_meta($tour_id, 'tour_code', true) ?: 'T' . $tour_id;
        $date_part = date('ymd', strtotime($start_date));
        $random_part = strtoupper(substr(wp_generate_password(4, false), 0, 4));

        return "{$tour_code}-{$date_part}-{$random_part}";
    }

    /**
     * Count departures by status
     *
     * @param string|null $status
     * @return int
     */
    public function count(?string $status = null): int
    {
        global $wpdb;

        if ($status) {
            return (int) $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM {$this->table} WHERE status = %s", $status)
            );
        }

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");
    }

    /**
     * Get departures for today
     *
     * @return Departure[]
     */
    public function get_today(): array
    {
        global $wpdb;

        $today = current_time('Y-m-d');

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE start_date = %s AND status IN ('open', 'departed')",
                $today
            ),
            ARRAY_A
        );

        return array_map(fn($data) => new Departure($data), $results ?: []);
    }
}
