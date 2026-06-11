<?php
/**
 * Seat REST Controller
 *
 * API endpoints for seats with atomic operations.
 *
 * @since 0.1.0
 */

namespace TourBooking\Rest;

use TourBooking\Helpers\Response;
use TourBooking\Services\SeatGeneratorService;
use TourBooking\Repositories\SeatRepository;
use TourBooking\Admin\SettingsPage;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class SeatController extends WP_REST_Controller
{
    protected string $namespace = 'nt-tour/v1';

    /**
     * Register routes
     *
     * @return void
     */
    public function register_routes(): void
    {
        // Public: Get seats for a departure (safe data only)
        register_rest_route($this->namespace, '/departures/(?P<departure_id>\d+)/seats', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_departure_seats'],
            'permission_callback' => '__return_true',
        ]);

        // Public: Get seats for a specific vehicle
        register_rest_route($this->namespace, '/departures/(?P<departure_id>\d+)/vehicles/(?P<vehicle_id>\d+)/seats', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_vehicle_seats'],
            'permission_callback' => '__return_true',
        ]);

        // Admin: Generate seats for departure vehicle
        register_rest_route($this->namespace, '/admin/departures/(?P<departure_id>\d+)/vehicles/(?P<vehicle_id>\d+)/seats/generate', [
            'methods' => 'POST',
            'callback' => [$this, 'generate_seats'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Block seat
        register_rest_route($this->namespace, '/admin/departures/(?P<departure_id>\d+)/vehicles/(?P<vehicle_id>\d+)/seats/(?P<seat_code>[^/]+)/block', [
            'methods' => 'POST',
            'callback' => [$this, 'block_seat'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Unblock seat
        register_rest_route($this->namespace, '/admin/departures/(?P<departure_id>\d+)/vehicles/(?P<vehicle_id>\d+)/seats/(?P<seat_code>[^/]+)/unblock', [
            'methods' => 'POST',
            'callback' => [$this, 'unblock_seat'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Admin: Get seat statistics
        register_rest_route($this->namespace, '/admin/departures/(?P<departure_id>\d+)/seats/stats', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_seat_stats'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);
    }

    /**
     * Admin permission check
     *
     * @return bool
     */
    public function admin_permission_check(): bool
    {
        return current_user_can('nt_manage_seats');
    }

    /**
     * Get seats for a departure (public safe data)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_departure_seats(WP_REST_Request $request): WP_REST_Response
    {
        $departure_id = (int) $request->get_param('departure_id');
        $vehicle_id = $request->get_param('vehicle_id');

        $service = new SeatGeneratorService();

        if ($vehicle_id) {
            $seats = $service->get_seats_by_vehicle($departure_id, (int) $vehicle_id);
        } else {
            $seats = $service->get_seats($departure_id);
        }

        // Return safe data only (no booking_id, passenger_id, etc.)
        $safe_seats = array_map(fn($seat) => $seat->to_safe_array(), $seats);

        return Response::success($safe_seats);
    }

    /**
     * Get seats for a specific vehicle
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_vehicle_seats(WP_REST_Request $request): WP_REST_Response
    {
        $departure_id = (int) $request->get_param('departure_id');
        $vehicle_id = (int) $request->get_param('vehicle_id');

        $service = new SeatGeneratorService();
        $seats = $service->get_seats_by_vehicle($departure_id, $vehicle_id);

        $safe_seats = array_map(fn($seat) => $seat->to_safe_array(), $seats);

        return Response::success($safe_seats);
    }

    /**
     * Generate seats for a departure vehicle
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function generate_seats(WP_REST_Request $request): WP_REST_Response
    {
        $departure_id = (int) $request->get_param('departure_id');
        $vehicle_id = (int) $request->get_param('vehicle_id');
        $layout_id = $request->get_param('layout_id');

        if (!$layout_id) {
            return Response::error('missing_layout_id', 'layout_id is required');
        }

        $service = new SeatGeneratorService();
        $result = $service->generate_seats($departure_id, $vehicle_id, (int) $layout_id);

        if (!$result['success']) {
            return Response::error('seat_generation_failed', $result['message']);
        }

        return Response::created(['seats_created' => $result['seats_created']]);
    }

    /**
     * Block a seat
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function block_seat(WP_REST_Request $request): WP_REST_Response
    {
        $departure_id = (int) $request->get_param('departure_id');
        $vehicle_id = (int) $request->get_param('vehicle_id');
        $seat_code = $request->get_param('seat_code');

        $service = new SeatGeneratorService();
        $success = $service->block_seat($departure_id, $vehicle_id, $seat_code);

        if (!$success) {
            return Response::error('seat_block_failed', 'Failed to block seat');
        }

        return Response::success(null);
    }

    /**
     * Unblock a seat
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function unblock_seat(WP_REST_Request $request): WP_REST_Response
    {
        $departure_id = (int) $request->get_param('departure_id');
        $vehicle_id = (int) $request->get_param('vehicle_id');
        $seat_code = $request->get_param('seat_code');

        $service = new SeatGeneratorService();
        $success = $service->unblock_seat($departure_id, $vehicle_id, $seat_code);

        if (!$success) {
            return Response::error('seat_unblock_failed', 'Failed to unblock seat');
        }

        return Response::success(null);
    }

    /**
     * Get seat statistics
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_seat_stats(WP_REST_Request $request): WP_REST_Response
    {
        $departure_id = (int) $request->get_param('departure_id');

        $service = new SeatGeneratorService();
        $stats = $service->get_stats($departure_id);

        return Response::success($stats);
    }
}
