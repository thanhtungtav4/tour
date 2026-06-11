<?php
/**
 * Vehicle REST Controller
 *
 * API endpoints for vehicles and layouts.
 *
 * @since 0.1.0
 */

namespace TourBooking\Rest;

use TourBooking\Helpers\Response;
use TourBooking\Services\VehicleService;
use TourBooking\Services\LayoutService;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class VehicleController extends WP_REST_Controller
{
    protected string $namespace = 'nt-tour/v1';

    /**
     * Register routes
     *
     * @return void
     */
    public function register_routes(): void
    {
        // Public: Get all vehicles
        register_rest_route($this->namespace, '/vehicles', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_vehicles'],
            'permission_callback' => '__return_true',
        ]);

        // Public: Get all layouts
        register_rest_route($this->namespace, '/layouts', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_layouts'],
            'permission_callback' => '__return_true',
        ]);

        // Admin: List vehicles with pagination
        register_rest_route($this->namespace, '/admin/vehicles', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'list_vehicles'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Create vehicle
        register_rest_route($this->namespace, '/admin/vehicles', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'create_vehicle'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Update vehicle
        register_rest_route($this->namespace, '/admin/vehicles/(?P<id>\d+)', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [$this, 'update_vehicle'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Delete vehicle
        register_rest_route($this->namespace, '/admin/vehicles/(?P<id>\d+)', [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => [$this, 'delete_vehicle'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Toggle vehicle status
        register_rest_route($this->namespace, '/admin/vehicles/(?P<id>\d+)/toggle-status', [
            'methods' => 'POST',
            'callback' => [$this, 'toggle_vehicle_status'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: List layouts with pagination
        register_rest_route($this->namespace, '/admin/vehicle-layouts', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'list_layouts'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Create layout
        register_rest_route($this->namespace, '/admin/vehicle-layouts', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'create_layout'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Update layout
        register_rest_route($this->namespace, '/admin/vehicle-layouts/(?P<id>\d+)', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [$this, 'update_layout'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Delete layout
        register_rest_route($this->namespace, '/admin/vehicle-layouts/(?P<id>\d+)', [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => [$this, 'delete_layout'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Create layout from template
        register_rest_route($this->namespace, '/admin/layouts/from-template', [
            'methods' => 'POST',
            'callback' => [$this, 'create_layout_from_template'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);
    }

    /**
     * List vehicles with pagination (admin)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function list_vehicles(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;

        $table = $wpdb->prefix . 'nt_vehicles';
        $layouts_table = $wpdb->prefix . 'nt_vehicle_layouts';

        $per_page = min((int) $request->get_param('length') ?: 20, 100);
        $page = (int) ($request->get_param('start') ?: 0) / $per_page + 1;
        $offset = ($page - 1) * $per_page;

        $where = ['1=1'];
        $values = [];

        if ($search = $request->get_param('search')) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where[] = '(name LIKE %s OR plate_number LIKE %s)';
            $values[] = $search_term;
            $values[] = $search_term;
        }

        if ($status = $request->get_param('status')) {
            $where[] = 'v.status = %s';
            $values[] = $status;
        }

        if ($vehicle_type = $request->get_param('vehicle_type')) {
            $where[] = 'v.vehicle_type = %s';
            $values[] = $vehicle_type;
        }

        $orderby = $request->get_param('orderby') ?: 'v.name';
        $order = $request->get_param('order') ?: 'ASC';

        $where_sql = implode(' AND ', $where);

        $count_sql = "SELECT COUNT(*) FROM {$table} v WHERE {$where_sql}";
        $total = (int) $wpdb->get_var(empty($values) ? $count_sql : $wpdb->prepare($count_sql, ...$values));
        $filtered = $total;

        $sql = "SELECT v.*, l.name as layout_name
                FROM {$table} v
                LEFT JOIN {$layouts_table} l ON v.layout_id = l.id
                WHERE {$where_sql}
                ORDER BY {$orderby} {$order}
                LIMIT %d OFFSET %d";
        $values[] = $per_page;
        $values[] = $offset;

        $results = $wpdb->get_results($wpdb->prepare($sql, ...$values), ARRAY_A);

        $type_labels = [
            'bus_29' => 'Xe 29 chỗ',
            'bus_45' => 'Xe 45 chỗ',
            'limousine' => 'Limousine',
            'other' => 'Khác',
        ];

        $data = array_map(function($row) {
            return [
                'id' => (int) $row['id'],
                'name' => esc_html($row['name']),
                'plate_number' => esc_html($row['plate_number']) ?: '-',
                'vehicle_type' => $row['vehicle_type'],
                'type_label' => $GLOBALS['this']->type_labels[$row['vehicle_type']] ?? $row['vehicle_type'],
                'total_seats' => (int) $row['total_seats'],
                'layout_id' => (int) $row['layout_id'] ?: null,
                'layout_name' => $row['layout_name'] ?: '-',
                'status' => $row['status'],
                'status_badge' => '<span class="nt-badge ' . ($row['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') . '">' . ($row['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động') . '</span>',
                'actions' => $this->get_vehicle_actions((int) $row['id']),
            ];
        }, $results ?: []);

        // Fix type_label reference
        foreach ($data as &$row) {
            $row['type_label'] = $type_labels[$row['vehicle_type']] ?? $row['vehicle_type'];
        }

        return new WP_REST_Response([
            'draw' => (int) $request->get_param('draw') ?: 1,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
            'page' => $page,
            'per_page' => $per_page,
        ], 200);
    }

    /**
     * List layouts with pagination (admin)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function list_layouts(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;

        $table = $wpdb->prefix . 'nt_vehicle_layouts';

        $per_page = min((int) $request->get_param('length') ?: 20, 100);
        $page = (int) ($request->get_param('start') ?: 0) / $per_page + 1;
        $offset = ($page - 1) * $per_page;

        $where = ['1=1'];
        $values = [];

        if ($search = $request->get_param('search')) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where[] = 'name LIKE %s';
            $values[] = $search_term;
        }

        if ($vehicle_type = $request->get_param('vehicle_type')) {
            $where[] = 'vehicle_type = %s';
            $values[] = $vehicle_type;
        }

        $orderby = $request->get_param('orderby') ?: 'name';
        $order = $request->get_param('order') ?: 'ASC';

        $where_sql = implode(' AND ', $where);

        $count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
        $total = (int) $wpdb->get_var(empty($values) ? $count_sql : $wpdb->prepare($count_sql, ...$values));
        $filtered = $total;

        $sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $values[] = $per_page;
        $values[] = $offset;

        $results = $wpdb->get_results($wpdb->prepare($sql, ...$values), ARRAY_A);

        $type_labels = [
            'bus_29' => 'Xe 29 chỗ',
            'bus_45' => 'Xe 45 chỗ',
            'limousine' => 'Limousine',
            'other' => 'Khác',
        ];

        $data = array_map(function($row) {
            // Generate preview from layout_json
            $preview = '';
            $layout_json = json_decode($row['layout_json'], true);
            if ($layout_json) {
                $preview = '<div class="flex gap-1 flex-wrap max-w-[100px]">';
                $seat_count = 0;
                foreach ($layout_json as $r) {
                    foreach ($r as $c) {
                        if (isset($c['type']) && $c['type'] === 'seat' && $seat_count < 6) {
                            $preview .= '<span class="w-5 h-5 bg-blue-100 rounded text-xs flex items-center justify-center text-xs">' . ($c['label'] ?? '') . '</span>';
                            $seat_count++;
                        }
                    }
                }
                if (count($layout_json) > 0 && count($layout_json[0]) > 6) {
                    $preview .= '<span class="text-xs text-gray-400">...</span>';
                }
                $preview .= '</div>';
            }

            return [
                'id' => (int) $row['id'],
                'name' => esc_html($row['name']),
                'vehicle_type' => $row['vehicle_type'],
                'type_label' => $type_labels[$row['vehicle_type']] ?? $row['vehicle_type'],
                'total_seats' => (int) $row['total_seats'],
                'layout_json' => $row['layout_json'],
                'preview' => $preview,
                'actions' => $this->get_layout_actions((int) $row['id']),
            ];
        }, $results ?: []);

        return new WP_REST_Response([
            'draw' => (int) $request->get_param('draw') ?: 1,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
            'page' => $page,
            'per_page' => $per_page,
        ], 200);
    }

    /**
     * Get action buttons for vehicle
     */
    private function get_vehicle_actions(int $id): string
    {
        ob_start();
        ?>
        <div class="flex gap-2">
            <button type="button" class="nt-btn nt-btn-sm nt-btn-ghost" onclick="openVehicleModal(<?php echo $id; ?>)">
                <i data-lucide="edit-2" class="w-4 h-4"></i>
            </button>
            <button type="button" class="nt-btn nt-btn-sm nt-btn-ghost text-red-600" onclick="deleteVehicle(<?php echo $id; ?>)">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get action buttons for layout
     */
    private function get_layout_actions(int $id): string
    {
        ob_start();
        ?>
        <div class="flex gap-2">
            <button type="button" class="nt-btn nt-btn-sm nt-btn-ghost" onclick="openLayoutModal(<?php echo $id; ?>)">
                <i data-lucide="edit-2" class="w-4 h-4"></i>
            </button>
            <button type="button" class="nt-btn nt-btn-sm nt-btn-ghost text-red-600" onclick="deleteLayout(<?php echo $id; ?>)">
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
        return current_user_can('nt_manage_vehicles');
    }

    /**
     * Get all vehicles
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_vehicles(WP_REST_Request $request): WP_REST_Response
    {
        $service = new VehicleService();
        $vehicles = $service->get_all_active();

        return Response::success(
            array_map(fn($v) => $v->to_array(), $vehicles)
        );
    }

    /**
     * Get all layouts
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_layouts(WP_REST_Request $request): WP_REST_Response
    {
        $service = new LayoutService();
        $layouts = $service->get_all();

        return Response::success(
            array_map(fn($l) => $l->to_array(), $layouts)
        );
    }

    /**
     * Create vehicle
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function create_vehicle(WP_REST_Request $request): WP_REST_Response
    {
        $data = [
            'name' => $request->get_param('name'),
            'plate_number' => $request->get_param('plate_number'),
            'vehicle_type' => $request->get_param('vehicle_type') ?: 'bus',
            'total_seats' => $request->get_param('total_seats'),
            'layout_id' => $request->get_param('layout_id'),
        ];

        $service = new VehicleService();
        $vehicle = $service->create($data);

        if (!$vehicle) {
            return Response::error('vehicle_creation_failed', 'Failed to create vehicle. Name is required.');
        }

        return Response::created($vehicle->to_array());
    }

    /**
     * Update vehicle
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_vehicle(WP_REST_Request $request): WP_REST_Response
    {
        $id = (int) $request->get_param('id');

        $data = [];
        $params = ['name', 'plate_number', 'vehicle_type', 'total_seats', 'layout_id'];

        foreach ($params as $param) {
            if (($value = $request->get_param($param)) !== null) {
                $data[$param] = $value;
            }
        }

        $service = new VehicleService();
        $vehicle = $service->update($id, $data);

        if (!$vehicle) {
            return Response::error('vehicle_update_failed', 'Failed to update vehicle');
        }

        return Response::success($vehicle->to_array());
    }

    /**
     * Delete vehicle
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function delete_vehicle(WP_REST_Request $request): WP_REST_Response
    {
        $service = new VehicleService();
        $success = $service->delete((int) $request->get_param('id'));

        if (!$success) {
            return Response::error('vehicle_delete_failed', 'Failed to delete vehicle');
        }

        return Response::success(null);
    }

    /**
     * Toggle vehicle status
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function toggle_vehicle_status(WP_REST_Request $request): WP_REST_Response
    {
        $service = new VehicleService();
        $success = $service->toggle_status((int) $request->get_param('id'));

        if (!$success) {
            return Response::error('vehicle_toggle_failed', 'Failed to toggle status');
        }

        $vehicle = $service->get((int) $request->get_param('id'));

        return Response::success($vehicle->to_array());
    }

    /**
     * Create layout
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function create_layout(WP_REST_Request $request): WP_REST_Response
    {
        $data = [
            'name' => $request->get_param('name'),
            'vehicle_type' => $request->get_param('vehicle_type') ?: 'bus',
            'total_seats' => $request->get_param('total_seats'),
            'layout_json' => $request->get_param('layout_json'),
        ];

        $service = new LayoutService();
        $layout = $service->create($data);

        if (!$layout) {
            return Response::error('layout_creation_failed', 'Failed to create layout. Name and layout_json are required.');
        }

        return Response::created($layout->to_array());
    }

    /**
     * Update layout
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_layout(WP_REST_Request $request): WP_REST_Response
    {
        $id = (int) $request->get_param('id');

        $data = [];
        $params = ['name', 'vehicle_type', 'total_seats', 'layout_json'];

        foreach ($params as $param) {
            if (($value = $request->get_param($param)) !== null) {
                $data[$param] = $value;
            }
        }

        $service = new LayoutService();
        $layout = $service->update($id, $data);

        if (!$layout) {
            return Response::error('layout_update_failed', 'Failed to update layout');
        }

        return Response::success($layout->to_array());
    }

    /**
     * Delete layout
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function delete_layout(WP_REST_Request $request): WP_REST_Response
    {
        $service = new LayoutService();
        $success = $service->delete((int) $request->get_param('id'));

        if (!$success) {
            return Response::error('layout_delete_failed', 'Failed to delete layout. It may be in use by a vehicle.');
        }

        return Response::success(null);
    }

    /**
     * Create layout from template
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function create_layout_from_template(WP_REST_Request $request): WP_REST_Response
    {
        $name = $request->get_param('name');
        $vehicle_type = $request->get_param('vehicle_type') ?: 'bus';
        $template = $request->get_param('template');

        if (!$name || !$template) {
            return Response::error('missing_template_params', 'Name and template are required');
        }

        $service = new LayoutService();
        $layout = $service->create_from_template($name, $vehicle_type, $template);

        if (!$layout) {
            return Response::error('layout_template_failed', 'Failed to create layout from template. Invalid template.');
        }

        return Response::created($layout->to_array());
    }
}
