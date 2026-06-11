<?php
/**
 * PickupPoint REST Controller
 *
 * API endpoints for pickup points.
 *
 * @since 0.1.0
 */

namespace TourBooking\Rest;

use TourBooking\Helpers\Response;
use TourBooking\Services\PickupPointService;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class PickupPointController extends WP_REST_Controller
{
    protected string $namespace = 'nt-tour/v1';

    /**
     * Register routes
     *
     * @return void
     */
    public function register_routes(): void
    {
        // Get all pickup points (public)
        register_rest_route($this->namespace, '/pickup-points', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_pickup_points'],
            'permission_callback' => '__return_true',
        ]);

        // Admin: List pickup points with pagination
        register_rest_route($this->namespace, '/admin/pickup-points', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'list_pickup_points'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Create pickup point
        register_rest_route($this->namespace, '/admin/pickup-points', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'create_pickup_point'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Update pickup point
        register_rest_route($this->namespace, '/admin/pickup-points/(?P<id>\d+)', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [$this, 'update_pickup_point'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Delete pickup point
        register_rest_route($this->namespace, '/admin/pickup-points/(?P<id>\d+)', [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => [$this, 'delete_pickup_point'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Toggle status
        register_rest_route($this->namespace, '/admin/pickup-points/(?P<id>\d+)/toggle-status', [
            'methods' => 'POST',
            'callback' => [$this, 'toggle_status'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Reorder pickup points
        register_rest_route($this->namespace, '/admin/pickup-points/reorder', [
            'methods' => 'POST',
            'callback' => [$this, 'reorder_pickup_points'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);
    }

    /**
     * List pickup points with pagination (admin)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function list_pickup_points(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;

        $table = $wpdb->prefix . 'nt_pickup_points';

        $per_page = min((int) $request->get_param('length') ?: 20, 100);
        $page = (int) ($request->get_param('start') ?: 0) / $per_page + 1;
        $offset = ($page - 1) * $per_page;

        $where = ['1=1'];
        $values = [];

        // Search
        if ($search = $request->get_param('search')) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where[] = '(name LIKE %s OR address LIKE %s)';
            $values[] = $search_term;
            $values[] = $search_term;
        }

        if ($status = $request->get_param('status')) {
            $where[] = 'status = %s';
            $values[] = $status;
        }

        $orderby = $request->get_param('orderby') ?: 'sort_order';
        $order = $request->get_param('order') ?: 'ASC';

        $where_sql = implode(' AND ', $where);

        // Count total
        $count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
        $total = (int) $wpdb->get_var(empty($values) ? $count_sql : $wpdb->prepare($count_sql, ...$values));

        $filtered = $total;

        // Get data
        $sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $values[] = $per_page;
        $values[] = $offset;

        $results = $wpdb->get_results($wpdb->prepare($sql, ...$values), ARRAY_A);

        // Format for DataTables
        $data = array_map(function($row) {
            $map_link = '';
            if (!empty($row['map_url'])) {
                $map_link = '<a href="' . esc_url($row['map_url']) . '" target="_blank" class="text-blue-600 hover:underline">
                    <i data-lucide="map" class="w-4 h-4"></i>
                </a>';
            }

            return [
                'id' => (int) $row['id'],
                'name' => esc_html($row['name']),
                'address' => esc_html($row['address']) ?: '-',
                'map_url' => $row['map_url'] ?: '',
                'map_link' => $map_link,
                'note' => esc_html($row['note']) ?: '',
                'sort_order' => (int) $row['sort_order'],
                'status' => $row['status'],
                'status_badge' => '<span class="nt-badge ' . $this->get_status_class($row['status']) . '">' . ($row['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động') . '</span>',
                'actions' => $this->get_pickup_point_actions((int) $row['id']),
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
        return $status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
    }

    /**
     * Get action buttons HTML
     */
    private function get_pickup_point_actions(int $id): string
    {
        ob_start();
        ?>
        <div class="flex gap-2">
            <button type="button" class="nt-btn nt-btn-sm nt-btn-ghost" onclick="openPickupPointModal(<?php echo $id; ?>)">
                <i data-lucide="edit-2" class="w-4 h-4"></i>
            </button>
            <button type="button" class="nt-btn nt-btn-sm nt-btn-ghost text-red-600" onclick="deletePickupPoint(<?php echo $id; ?>)">
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
        return current_user_can('nt_manage_pickup_points');
    }

    /**
     * Get all pickup points
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_pickup_points(WP_REST_Request $request): WP_REST_Response
    {
        $service = new PickupPointService();

        $include_inactive = $request->get_param('include_inactive');

        if ($include_inactive) {
            $pickup_points = $service->get_all();
        } else {
            $pickup_points = $service->get_all_active();
        }

        $data = array_map(fn($pp) => $pp->to_array(), $pickup_points);

        return Response::success($data);
    }

    /**
     * Create pickup point (admin)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function create_pickup_point(WP_REST_Request $request): WP_REST_Response
    {
        $data = [
            'name' => $request->get_param('name'),
            'address' => $request->get_param('address'),
            'map_url' => $request->get_param('map_url'),
            'note' => $request->get_param('note'),
            'sort_order' => $request->get_param('sort_order') ?: 0,
        ];

        $service = new PickupPointService();
        $pickup_point = $service->create($data);

        if (!$pickup_point) {
            return Response::error('pickup_creation_failed', 'Failed to create pickup point. Name is required.');
        }

        return Response::created($pickup_point->to_array());
    }

    /**
     * Update pickup point (admin)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_pickup_point(WP_REST_Request $request): WP_REST_Response
    {
        $id = (int) $request->get_param('id');

        $data = [];

        $params = ['name', 'address', 'map_url', 'note', 'sort_order'];

        foreach ($params as $param) {
            if (($value = $request->get_param($param)) !== null) {
                $data[$param] = $value;
            }
        }

        $service = new PickupPointService();
        $pickup_point = $service->update($id, $data);

        if (!$pickup_point) {
            return Response::error('pickup_update_failed', 'Failed to update pickup point');
        }

        return Response::success($pickup_point->to_array());
    }

    /**
     * Delete pickup point (admin)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function delete_pickup_point(WP_REST_Request $request): WP_REST_Response
    {
        $service = new PickupPointService();
        $success = $service->delete((int) $request->get_param('id'));

        if (!$success) {
            return Response::error('pickup_delete_failed', 'Failed to delete pickup point');
        }

        return Response::success(null);
    }

    /**
     * Toggle pickup point status (admin)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function toggle_status(WP_REST_Request $request): WP_REST_Response
    {
        $service = new PickupPointService();
        $success = $service->toggle_status((int) $request->get_param('id'));

        if (!$success) {
            return Response::error('pickup_toggle_failed', 'Failed to toggle status');
        }

        $pickup_point = $service->get((int) $request->get_param('id'));

        return Response::success($pickup_point->to_array());
    }

    /**
     * Reorder pickup points (admin)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function reorder_pickup_points(WP_REST_Request $request): WP_REST_Response
    {
        $order = $request->get_param('order');

        if (!is_array($order)) {
            return Response::error('invalid_order_format', 'order must be an array of id => sort_order');
        }

        $service = new PickupPointService();
        $success = $service->reorder($order);

        if (!$success) {
            return Response::error('pickup_reorder_failed', 'Failed to reorder pickup points');
        }

        $pickup_points = $service->get_all_active();

        return Response::success(
            array_map(fn($pp) => $pp->to_array(), $pickup_points)
        );
    }
}
