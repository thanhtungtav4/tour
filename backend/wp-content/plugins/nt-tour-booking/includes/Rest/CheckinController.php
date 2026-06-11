<?php
namespace TourBooking\Rest;

use TourBooking\Helpers\Response;
use TourBooking\Repositories\PassengerRepository;
use TourBooking\Repositories\BookingRepository;
use TourBooking\Repositories\SeatRepository;
use TourBooking\Security\Capabilities;
use TourBooking\Services\QRService;
use TourBooking\ActivityLogger;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class CheckinController extends WP_REST_Controller
{
    use RestNonceTrait;

    protected string $namespace = 'nt-tour/v1';

    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/checkin/scan', [
            'methods' => 'POST',
            'callback' => [$this, 'scan_qr'],
            'permission_callback' => [$this, 'guide_permission_check'],
        ]);

        register_rest_route($this->namespace, '/checkin/manual', [
            'methods' => 'POST',
            'callback' => [$this, 'manual_checkin'],
            'permission_callback' => [$this, 'guide_write_permission_check'],
        ]);

        register_rest_route($this->namespace, '/checkin/(?P<passenger_id>\d+)/undo', [
            'methods' => 'POST',
            'callback' => [$this, 'undo_checkin'],
            'permission_callback' => [$this, 'guide_write_permission_check'],
        ]);

        register_rest_route($this->namespace, '/checkin/(?P<passenger_id>\d+)/no-show', [
            'methods' => 'POST',
            'callback' => [$this, 'mark_no_show'],
            'permission_callback' => [$this, 'guide_write_permission_check'],
        ]);

        register_rest_route($this->namespace, '/guide/departures/today', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_guide_today_departures'],
            'permission_callback' => [$this, 'guide_permission_check'],
        ]);

        register_rest_route($this->namespace, '/guide/departures/(?P<id>\d+)/passengers', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_departure_passengers'],
            'permission_callback' => [$this, 'guide_permission_check'],
        ]);

        register_rest_route($this->namespace, '/guide/passengers/(?P<id>\d+)/checkin', [
            'methods' => 'POST',
            'callback' => [$this, 'checkin_passenger'],
            'permission_callback' => [$this, 'guide_write_permission_check'],
        ]);

        register_rest_route($this->namespace, '/guide/passengers/(?P<id>\d+)/undo', [
            'methods' => 'POST',
            'callback' => [$this, 'undo_checkin_guide'],
            'permission_callback' => [$this, 'guide_write_permission_check'],
        ]);

        register_rest_route($this->namespace, '/guide/stats', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_guide_stats'],
            'permission_callback' => [$this, 'guide_permission_check'],
        ]);
    }

    public function guide_permission_check(): bool
    {
        if (!is_user_logged_in()) return false;
        return current_user_can('nt_checkin_passengers');
    }

    public function guide_write_permission_check(WP_REST_Request $request): bool
    {
        return $this->guide_permission_check() && $this->verify_nonce($request);
    }

    /**
     * Check-in bằng QR
     */
    public function scan_qr(WP_REST_Request $request): WP_REST_Response
    {
        $qr_data = $request->get_param('qr_data');
        if (empty($qr_data)) {
            return Response::error('validation_failed', 'qr_data là bắt buộc.');
        }

        $qr_service = new QRService();
        $passenger_data = $qr_service->validate_token((string) $qr_data);

        if (!$passenger_data) {
            return Response::error('invalid_qr', 'QR code không hợp lệ.');
        }

        $passenger_id = (int) $passenger_data['id'];
        $repo = new PassengerRepository();
        $passenger = $repo->find($passenger_id);

        if (!$passenger) {
            return Response::resource_not_found('passenger');
        }

        if (!$this->can_access_departure((int) $passenger->tour_departure_id)) {
            return Response::forbidden('Bạn không được phân công vào lịch khởi hành này.');
        }

        if ($passenger->is_checked_in()) {
            return Response::conflict('already_checked_in', 'Khách đã check-in trước đó.', [
                'passenger_name' => $passenger->name,
                'checked_in_at' => $passenger->checked_in_at,
            ]);
        }

        $this->perform_checkin($passenger, 'checked_in');

        return Response::success([
            'passenger_id' => $passenger_id,
            'full_name' => $passenger->name,
            'seat' => $passenger->seat_code,
            'checked_in_at' => date('c'),
            'message' => 'Check-in thành công!',
        ]);
    }

    /**
     * Check-in thủ công
     */
    public function manual_checkin(WP_REST_Request $request): WP_REST_Response
    {
        $passenger_id = (int) $request->get_param('passenger_id');
        $repo = new PassengerRepository();
        $passenger = $repo->find($passenger_id);

        if (!$passenger) {
            return Response::resource_not_found('passenger');
        }

        if (!$this->can_access_departure((int) $passenger->tour_departure_id)) {
            return Response::forbidden('Bạn không được phân công vào lịch khởi hành này.');
        }

        if ($passenger->is_checked_in()) {
            return Response::conflict('already_checked_in', 'Khách đã check-in trước đó.');
        }

        $this->perform_checkin($passenger, 'checked_in');

        return Response::success([
            'passenger_id' => $passenger_id,
            'full_name' => $passenger->name,
            'seat' => $passenger->seat_code,
            'checked_in_at' => date('c'),
        ]);
    }

    /**
     * Hoàn tác check-in
     */
    public function undo_checkin(WP_REST_Request $request): WP_REST_Response
    {
        $passenger_id = (int) $request->get_param('passenger_id');
        $reason = $request->get_param('reason') ?: '';

        $repo = new PassengerRepository();
        $passenger = $repo->find($passenger_id);

        if (!$passenger) {
            return Response::resource_not_found('passenger');
        }

        if (!$this->can_access_departure((int) $passenger->tour_departure_id)) {
            return Response::forbidden('Bạn không được phân công vào lịch khởi hành này.');
        }

        if (!$passenger->is_checked_in()) {
            return Response::error('not_checked_in', 'Khách chưa check-in.', [], 400);
        }

        $repo->undo_checkin($passenger_id);

        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'nt_checkin_logs', [
            'tour_departure_id' => $passenger->tour_departure_id,
            'booking_id' => $passenger->booking_id,
            'passenger_id' => $passenger_id,
            'action' => 'undo_checkin',
            'old_status' => 'checked_in',
            'new_status' => 'not_checked_in',
            'user_id' => get_current_user_id(),
            'created_at' => current_time('mysql'),
        ], ['%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s']);

        return Response::success([
            'passenger_id' => $passenger_id,
            'checkin_status' => 'not_checked_in',
            'reason' => $reason,
        ]);
    }

    /**
     * Đánh dấu no-show
     */
    public function mark_no_show(WP_REST_Request $request): WP_REST_Response
    {
        $passenger_id = (int) $request->get_param('passenger_id');
        $repo = new PassengerRepository();
        $passenger = $repo->find($passenger_id);

        if (!$passenger) {
            return Response::resource_not_found('passenger');
        }

        if (!$this->can_access_departure((int) $passenger->tour_departure_id)) {
            return Response::forbidden('Bạn không được phân công vào lịch khởi hành này.');
        }

        $repo->mark_no_show($passenger_id);
        ActivityLogger::log_no_show($passenger_id);

        return Response::success([
            'passenger_id' => $passenger_id,
            'checkin_status' => 'no_show',
        ]);
    }

    public function checkin_passenger(WP_REST_Request $request): WP_REST_Response
    {
        return $this->manual_checkin($request);
    }

    public function undo_checkin_guide(WP_REST_Request $request): WP_REST_Response
    {
        $passenger_id = (int) $request->get_param('id');
        $request->set_param('passenger_id', $passenger_id);
        return $this->undo_checkin($request);
    }

    public function get_guide_today_departures(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $today = date('Y-m-d');

        if (current_user_can('nt_manage_bookings')) {
            $query = $wpdb->prepare(
                "SELECT d.*, t.post_title as tour_name
                FROM {$wpdb->prefix}nt_tour_departures d
                JOIN {$wpdb->posts} t ON d.tour_id = t.ID
                WHERE d.start_date = %s AND d.status IN ('open', 'departed')
                ORDER BY d.departure_time ASC", $today
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT d.*, t.post_title as tour_name
                FROM {$wpdb->prefix}nt_tour_departures d
                JOIN {$wpdb->posts} t ON d.tour_id = t.ID
                JOIN {$wpdb->prefix}nt_departure_guides g ON d.id = g.tour_departure_id
                WHERE d.start_date = %s AND g.user_id = %d AND d.status IN ('open', 'departed')
                ORDER BY d.departure_time ASC", $today, $user_id
            );
        }

        $departures = $wpdb->get_results($query, ARRAY_A);

        foreach ($departures as &$dep) {
            $stats = $wpdb->get_row($wpdb->prepare(
                "SELECT COUNT(*) as total,
                    SUM(CASE WHEN checkin_status = 'checked_in' THEN 1 ELSE 0 END) as checked_in,
                    SUM(CASE WHEN checkin_status = 'not_checked_in' THEN 1 ELSE 0 END) as not_checked_in,
                    SUM(CASE WHEN checkin_status = 'no_show' THEN 1 ELSE 0 END) as no_show
                FROM {$wpdb->prefix}nt_booking_passengers WHERE tour_departure_id = %d", $dep['id']
            ), ARRAY_A);
            $dep['stats'] = $stats;
        }

        return Response::success($departures);
    }

    public function get_departure_passengers(WP_REST_Request $request): WP_REST_Response
    {
        $departure_id = (int) $request->get_param('id');
        if (!$this->can_access_departure($departure_id)) {
            return Response::forbidden('Bạn không được phân công vào lịch khởi hành này.');
        }

        $repo = new PassengerRepository();
        $passengers = $repo->get_by_departure($departure_id);

        $data = array_map(function ($p) {
            return [
                'id' => $p->id,
                'full_name' => $p->name,
                'phone' => $p->phone ? substr($p->phone, 0, 4) . '****' . substr($p->phone, -3) : null,
                'seat' => $p->seat_code,
                'pickup_point' => $p->pickup_point_id,
                'checked_in' => $p->is_checked_in(),
                'checked_in_at' => $p->checked_in_at,
                'health_notes' => null,
                'qr_available' => !empty($p->qr_token_hash),
            ];
        }, $passengers);

        return Response::success($data);
    }

    public function get_guide_stats(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;
        $user_id = get_current_user_id();

        $departure = $wpdb->get_row($wpdb->prepare(
            "SELECT d.* FROM {$wpdb->prefix}nt_tour_departures d
             JOIN {$wpdb->prefix}nt_departure_guides g ON d.id = g.tour_departure_id
             WHERE g.user_id = %d AND d.start_date = %s LIMIT 1", $user_id, date('Y-m-d')
        ), ARRAY_A);

        if (!$departure) {
            return Response::success([
                'total_passengers' => 0, 'checked_in' => 0, 'not_checked_in' => 0,
                'no_show' => 0, 'check_in_rate' => 0,
            ]);
        }

        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(*) as total,
                SUM(CASE WHEN checkin_status = 'checked_in' THEN 1 ELSE 0 END) as checked_in,
                SUM(CASE WHEN checkin_status = 'not_checked_in' THEN 1 ELSE 0 END) as not_checked_in,
                SUM(CASE WHEN checkin_status = 'no_show' THEN 1 ELSE 0 END) as no_show
            FROM {$wpdb->prefix}nt_booking_passengers WHERE tour_departure_id = %d", $departure['id']
        ), ARRAY_A);

        $total = (int) ($stats['total'] ?? 0);
        $checked = (int) ($stats['checked_in'] ?? 0);
        $rate = $total > 0 ? round($checked / $total * 100) : 0;

        return Response::success([
            'total_passengers' => $total,
            'checked_in' => $checked,
            'not_checked_in' => (int) ($stats['not_checked_in'] ?? 0),
            'no_show' => (int) ($stats['no_show'] ?? 0),
            'check_in_rate' => $rate,
        ]);
    }

    private function can_access_departure(int $departure_id): bool
    {
        if (current_user_can('nt_manage_bookings') || current_user_can('nt_manage_departures')) {
            return true;
        }

        return Capabilities::is_guide_for_departure(get_current_user_id(), $departure_id);
    }

    private function perform_checkin(\TourBooking\Models\Passenger $passenger, string $action): void
    {
        global $wpdb;

        $passenger_id = (int) $passenger->id;
        $user_id = get_current_user_id();
        $repo = new PassengerRepository();
        $seat_repo = new SeatRepository();

        $wpdb->query('START TRANSACTION');
        $repo->checkin($passenger_id, $user_id);

        $seat = $seat_repo->get_by_passenger($passenger_id);
        if ($seat) {
            $seat_repo->checkin_seat((int) $seat->tour_departure_id, (int) $seat->departure_vehicle_id, (string) $seat->seat_code);
        }

        $wpdb->insert($wpdb->prefix . 'nt_checkin_logs', [
            'tour_departure_id' => $passenger->tour_departure_id,
            'booking_id' => $passenger->booking_id,
            'passenger_id' => $passenger_id,
            'action' => $action,
            'old_status' => $passenger->checkin_status,
            'new_status' => 'checked_in',
            'user_id' => $user_id,
            'created_at' => current_time('mysql'),
        ], ['%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s']);

        $wpdb->query('COMMIT');
        ActivityLogger::log_checkin($passenger_id, 'checked_in');
    }
}