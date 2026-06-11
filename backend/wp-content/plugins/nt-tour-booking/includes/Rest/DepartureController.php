<?php
/**
 * Departure REST Controller
 *
 * API endpoints for departures.
 *
 * @since 0.1.0
 */

namespace TourBooking\Rest;

use TourBooking\Helpers\Response;
use TourBooking\Services\DepartureService;
use TourBooking\Services\PickupPointService;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class DepartureController extends WP_REST_Controller
{
    protected string $namespace = 'nt-tour/v1';

    /**
     * Register routes
     *
     * @return void
     */
    public function register_routes(): void
    {
        // Get departures (filter by tour_id)
        register_rest_route($this->namespace, '/departures', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_departures'],
            'permission_callback' => '__return_true',
        ]);

        // Get single departure
        register_rest_route($this->namespace, '/departures/(?P<id>\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_departure'],
            'permission_callback' => '__return_true',
        ]);

        // Get departure pickup points
        register_rest_route($this->namespace, '/departures/(?P<id>\d+)/pickup-points', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_departure_pickup_points'],
            'permission_callback' => '__return_true',
        ]);

        // Get departure seats (public - safe data only)
        register_rest_route($this->namespace, '/departures/(?P<id>\d+)/seats', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_departure_seats'],
            'permission_callback' => '__return_true',
        ]);

        // Admin: List departures with pagination
        register_rest_route($this->namespace, '/admin/departures', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'list_departures'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Create departure
        register_rest_route($this->namespace, '/admin/departures', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'create_departure'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Update departure
        register_rest_route($this->namespace, '/admin/departures/(?P<id>\d+)', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [$this, 'update_departure'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Delete departure
        register_rest_route($this->namespace, '/admin/departures/(?P<id>\d+)', [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => [$this, 'delete_departure'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Assign pickup points
        register_rest_route($this->namespace, '/admin/departures/(?P<id>\d+)/pickup-points', [
            'methods' => 'POST',
            'callback' => [$this, 'assign_pickup_points'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);
    }

    /**
     * List departures with pagination (admin)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function list_departures(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;

        $table = $wpdb->prefix . 'nt_tour_departures';
        $posts_table = $wpdb->posts;

        $per_page = min((int) $request->get_param('length') ?: 20, 100);
        $page = (int) ($request->get_param('start') ?: 0) / $per_page + 1;
        $offset = ($page - 1) * $per_page;

        $where = ['1=1'];
        $values = [];

        // Filters
        if ($tour_id = $request->get_param('tour_id')) {
            $where[] = 'd.tour_id = %d';
            $values[] = $tour_id;
        }

        if ($date_from = $request->get_param('date_from')) {
            $where[] = 'd.start_date >= %s';
            $values[] = $date_from;
        }

        if ($date_to = $request->get_param('date_to')) {
            $where[] = 'd.start_date <= %s';
            $values[] = $date_to;
        }

        if ($status = $request->get_param('status')) {
            $where[] = 'd.status = %s';
            $values[] = $status;
        }

        // Search
        if ($search = $request->get_param('search')) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where[] = '(p.post_title LIKE %s OR d.departure_code LIKE %s)';
            $values[] = $search_term;
            $values[] = $search_term;
        }

        // Order
        $orderby = $request->get_param('orderby') ?: 'd.start_date';
        $order = $request->get_param('order') ?: 'DESC';
        $allowed_orderby = ['id', 'start_date', 'departure_time', 'adult_price', 'capacity', 'status'];
        $allowed_order = ['ASC', 'DESC'];

        if (!in_array($orderby, $allowed_orderby)) {
            $orderby = 'd.start_date';
        }
        if (!in_array(strtoupper($order), $allowed_order)) {
            $order = 'DESC';
        }

        $where_sql = implode(' AND ', $where);

        // Count total
        $count_sql = "SELECT COUNT(*) FROM {$table} d LEFT JOIN {$posts_table} p ON d.tour_id = p.ID WHERE {$where_sql}";
        $total = (int) $wpdb->get_var(empty($values) ? $count_sql : $wpdb->prepare($count_sql, ...$values));

        // Get filtered count
        $filtered = $total;

        // Get data
        $sql = "SELECT d.*, p.post_title as tour_name,
                (SELECT COUNT(*) FROM {$wpdb->prefix}nt_bookings b WHERE b.tour_departure_id = d.id AND b.booking_status IN ('pending_payment', 'confirmed')) as booked_count
                FROM {$table} d
                LEFT JOIN {$posts_table} p ON d.tour_id = p.ID
                WHERE {$where_sql}
                ORDER BY {$orderby} {$order}
                LIMIT %d OFFSET %d";

        $values[] = $per_page;
        $values[] = $offset;

        $results = $wpdb->get_results($wpdb->prepare($sql, ...$values), ARRAY_A);

        // Format for DataTables
        $data = array_map(function($row) {
            $booked = (int) $row['booked_count'];
            $capacity = (int) $row['capacity'];
            $capacity_info = $booked . '/' . $capacity;

            $status_labels = [
                'open' => 'Mở bán',
                'closed' => 'Đóng bán',
                'full' => 'Đã đầy',
                'cancelled' => 'Đã hủy',
            ];

            return [
                'id' => (int) $row['id'],
                'tour_id' => (int) $row['tour_id'],
                'tour_name' => $row['tour_name'] ?: 'N/A',
                'start_date' => $row['start_date'],
                'start_date_formatted' => date('d/m/Y', strtotime($row['start_date'])),
                'departure_time' => $row['departure_time'] ?: '-',
                'adult_price' => (float) $row['adult_price'],
                'adult_price_formatted' => number_format((float) $row['adult_price'], 0, ',', '.') . 'đ',
                'child_price' => (float) $row['child_price'],
                'child_price_formatted' => number_format((float) $row['child_price'], 0, ',', '.') . 'đ',
                'infant_price' => (float) $row['infant_price'],
                'capacity' => $capacity,
                'capacity_info' => $capacity_info,
                'booked_count' => $booked,
                'status' => $row['status'],
                'status_badge' => '<span class="nt-badge ' . $this->get_status_class($row['status']) . '">' . ($status_labels[$row['status']] ?? $row['status']) . '</span>',
                'actions' => $this->get_departure_actions((int) $row['id']),
            ];
        }, $results ?: []);

        return new WP_REST_Response([
            'draw' => (int) $request->get_param('draw') ?: 1,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page),
        ], 200);
    }

    /**
     * Get status class for badge
     */
    private function get_status_class(string $status): string
    {
        $classes = [
            'open' => 'bg-green-100 text-green-800',
            'closed' => 'bg-gray-100 text-gray-800',
            'full' => 'bg-blue-100 text-blue-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];
        return $classes[$status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get action buttons HTML
     */
    private function get_departure_actions(int $id): string
    {
        ob_start();
        ?>
        <div class="flex gap-2">
            <button type="button" class="nt-btn nt-btn-sm nt-btn-ghost" onclick="openDepartureModal(<?php echo $id; ?>)">
                <i data-lucide="edit-2" class="w-4 h-4"></i>
            </button>
            <button type="button" class="nt-btn nt-btn-sm nt-btn-ghost text-red-600" onclick="deleteDeparture(<?php echo $id; ?>)">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Admin permission check
     *
     * @return bool
     */
    public function admin_permission_check(): bool
    {
        return current_user_can('nt_manage_departures');
    }

    /**
     * Get departures
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_departures(WP_REST_Request $request): WP_REST_Response
    {
        $service = new DepartureService();

        $args = [
            'orderby' => $request->get_param('orderby') ?: 'start_date',
            'order' => $request->get_param('order') ?: 'ASC',
        ];

        // Filter by tour_id
        if ($tour_id = $request->get_param('tour_id')) {
            $departures = $service->get_by_tour((int) $tour_id, $args);
            $departures_array = array_map(fn($d) => $d->to_array(), $departures);
            return Response::success($departures_array);
        }

        // Get upcoming departures
        $departures = $service->get_upcoming(['limit' => $request->get_param('per_page') ?: 20]);
        $departures_array = array_map(fn($d) => $d->to_array(), $departures);

        return Response::success($departures_array);
    }

    /**
     * Get single departure
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_departure(WP_REST_Request $request): WP_REST_Response
    {
        $service = new DepartureService();
        $departure = $service->get((int) $request->get_param('id'));

        if (!$departure) {
            return Response::resource_not_found('departure');
        }

        return Response::success($departure->to_array());
    }

    /**
     * Get departure pickup points
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_departure_pickup_points(WP_REST_Request $request): WP_REST_Response
    {
        $service = new PickupPointService();
        $pickup_points = $service->get_for_departure((int) $request->get_param('id'));

        return Response::success($pickup_points);
    }

    /**
     * Get departure seats (public safe data only)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_departure_seats(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;

        $departure_id = (int) $request->get_param('id');
        $table_name = $wpdb->prefix . 'nt_departure_seats';

        // Get seats with safe data only
        $seats = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT seat_code, status FROM {$table_name} WHERE tour_departure_id = %d ORDER BY seat_code",
                $departure_id
            ),
            ARRAY_A
        );

        // Transform to safe format
        $safe_seats = array_map(function ($seat) {
            return [
                'seat_code' => $seat['seat_code'],
                'status' => $seat['status'],
            ];
        }, $seats ?: []);

        return Response::success($safe_seats);
    }

    /**
     * Create departure (admin)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function create_departure(WP_REST_Request $request): WP_REST_Response
    {
        $data = [
            'tour_id' => $request->get_param('tour_id'),
            'start_date' => $request->get_param('start_date'),
            'end_date' => $request->get_param('end_date'),
            'departure_time' => $request->get_param('departure_time'),
            'adult_price' => $request->get_param('adult_price'),
            'child_price' => $request->get_param('child_price'),
            'infant_price' => $request->get_param('infant_price'),
            'capacity' => $request->get_param('capacity'),
            'status' => $request->get_param('status') ?: 'open',
        ];

        $service = new DepartureService();
        $departure = $service->create($data);

        if (!$departure) {
            return Response::error('departure_creation_failed', 'Failed to create departure');
        }

        return Response::created($departure->to_array());
    }

    /**
     * Update departure (admin)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_departure(WP_REST_Request $request): WP_REST_Response
    {
        $id = (int) $request->get_param('id');

        $data = [];

        $params = ['start_date', 'end_date', 'departure_time', 'adult_price', 'child_price', 'infant_price', 'capacity', 'status'];

        foreach ($params as $param) {
            if (($value = $request->get_param($param)) !== null) {
                $data[$param] = $value;
            }
        }

        $service = new DepartureService();
        $departure = $service->update($id, $data);

        if (!$departure) {
            return Response::error('departure_update_failed', 'Failed to update departure');
        }

        return Response::success($departure->to_array());
    }

    /**
     * Delete departure (admin)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function delete_departure(WP_REST_Request $request): WP_REST_Response
    {
        $service = new DepartureService();
        $success = $service->delete((int) $request->get_param('id'));

        if (!$success) {
            return Response::error('departure_delete_failed', 'Cannot delete departure. It may have active bookings.');
        }

        return Response::success(null);
    }

    /**
     * Assign pickup points to departure (admin)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function assign_pickup_points(WP_REST_Request $request): WP_REST_Response
    {
        $departure_id = (int) $request->get_param('id');
        $pickup_point_ids = $request->get_param('pickup_point_ids');

        if (!is_array($pickup_point_ids)) {
            return Response::error('invalid_pickup_point_ids', 'pickup_point_ids must be an array');
        }

        $service = new PickupPointService();
        $success = $service->assign_to_departure($departure_id, $pickup_point_ids);

        if (!$success) {
            return Response::error('pickup_assignment_failed', 'Failed to assign pickup points');
        }

        $pickup_points = $service->get_for_departure($departure_id);

        return Response::success($pickup_points);
    }
}
