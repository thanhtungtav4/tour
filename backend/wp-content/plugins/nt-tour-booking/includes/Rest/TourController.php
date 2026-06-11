<?php
/**
 * Tour REST Controller
 *
 * Public API endpoints for tours.
 *
 * @since 0.1.0
 */

namespace TourBooking\Rest;

use TourBooking\Helpers\Response;
use TourBooking\Models\Tour;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class TourController extends WP_REST_Controller
{
    protected string $namespace = 'nt-tour/v1';

    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/tours', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_tours'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route($this->namespace, '/tours/(?P<slug>[a-z0-9-]+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_tour'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route($this->namespace, '/tours/(?P<slug>[a-z0-9-]+)/departures', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_tour_departures'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function get_tours(WP_REST_Request $request): WP_REST_Response
    {
        $per_page = min((int) ($request->get_param('per_page') ?: 20), 50);
        $page = (int) ($request->get_param('page') ?: 1);
        $sort = $request->get_param('sort') ?: 'date';

        $args = [
            'post_type' => 'nt_tour',
            'post_status' => $request->get_param('status') ?: 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
        ];

        if ($search = $request->get_param('search')) {
            $args['s'] = sanitize_text_field($search);
        }

        $tax_query = [];

        if ($destination = $request->get_param('destination')) {
            $tax_query[] = ['taxonomy' => 'nt_destination', 'field' => 'slug', 'terms' => $destination];
        }

        if ($type = $request->get_param('type')) {
            $tax_query[] = ['taxonomy' => 'nt_tour_type', 'field' => 'slug', 'terms' => $type];
        }

        if (!empty($tax_query)) {
            $tax_query['relation'] = 'AND';
            $args['tax_query'] = $tax_query;
        }

        $meta_query = [];

        if ($difficulty = $request->get_param('difficulty')) {
            $tax_query[] = ['taxonomy' => 'nt_difficulty', 'field' => 'slug', 'terms' => $difficulty];
            $args['tax_query'] = $tax_query;
        }

        if ($duration = $request->get_param('duration')) {
            if ($duration === '1day') {
                $meta_query[] = ['key' => 'duration_days', 'value' => 1, 'type' => 'NUMERIC', 'compare' => '<='];
            } elseif ($duration === 'multi') {
                $meta_query[] = ['key' => 'duration_days', 'value' => 1, 'type' => 'NUMERIC', 'compare' => '>'];
            }
        }

        if ($price_min = $request->get_param('price_min')) {
            $meta_query[] = ['key' => 'base_price', 'value' => (int) $price_min, 'type' => 'NUMERIC', 'compare' => '>='];
        }

        if ($price_max = $request->get_param('price_max')) {
            $meta_query[] = ['key' => 'base_price', 'value' => (int) $price_max, 'type' => 'NUMERIC', 'compare' => '<='];
        }

        if ($departure_time = $request->get_param('departure_time')) {
            $meta_query[] = ['key' => 'departure_times', 'value' => $departure_time, 'compare' => 'LIKE'];
        }

        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        switch ($sort) {
            case 'price_asc':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'base_price';
                $args['order'] = 'ASC';
                break;
            case 'price_desc':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'base_price';
                $args['order'] = 'DESC';
                break;
            case 'popular':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'booking_count';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
        }

        $query = new \WP_Query($args);
        $tours = [];

        foreach ($query->posts as $post) {
            $tour = new Tour($post);
            $tours[] = $tour->to_list_array();
        }

        return Response::paginated($tours, $query->found_posts, $page, $per_page);
    }

    public function get_tour(WP_REST_Request $request): WP_REST_Response
    {
        $slug = $request->get_param('slug');
        $tour = Tour::find_by_slug($slug);

        if (!$tour) {
            return Response::resource_not_found('tour');
        }

        return Response::success($tour->to_array());
    }

    public function get_tour_departures(WP_REST_Request $request): WP_REST_Response
    {
        $slug = $request->get_param('slug');
        $tour = Tour::find_by_slug($slug);

        if (!$tour) {
            return Response::resource_not_found('tour');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nt_tour_departures';
        $bookings_table = $wpdb->prefix . 'nt_bookings';

        $month = $request->get_param('month');
        $status = $request->get_param('status');

        $where = ['d.tour_id = %d', "d.status IN ('open', 'departed')"];
        $values = [$tour->get_id()];

        if ($month) {
            $where[] = "DATE_FORMAT(d.start_date, '%Y-%m') = %s";
            $values[] = $month;
        }

        $where_clause = 'WHERE ' . implode(' AND ', $where);

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT d.*, t.post_title as tour_name, t.post_name as tour_slug,
             (SELECT COUNT(*) FROM {$bookings_table} b WHERE b.tour_departure_id = d.id AND b.booking_status IN ('pending_payment', 'confirmed', 'completed')) as booked_count
             FROM {$table} d
             JOIN {$wpdb->posts} t ON d.tour_id = t.ID
             {$where_clause}
             ORDER BY d.start_date ASC",
            ...$values
        ), ARRAY_A);

        $departures = [];
        foreach ($results as $row) {
            $available = max(0, (int) $row['capacity'] - (int) $row['booked_count']);
            $departures[] = [
                'id' => (int) $row['id'],
                'tour_id' => (int) $row['tour_id'],
                'tour_slug' => $row['tour_slug'],
                'date' => $row['start_date'],
                'date_formatted' => date_i18n('l, d/m/Y', strtotime($row['start_date'])),
                'departure_time' => $row['departure_time'] ?: 'Sáng',
                'total_spots' => (int) $row['capacity'],
                'available_spots' => $available,
                'booked_spots' => (int) $row['booked_count'],
                'status' => $row['status'] === 'open' ? 'available' : $row['status'],
                'price' => (float) $row['adult_price'],
                'pickup_points' => $this->get_departure_pickup_points((int) $row['id']),
                'vehicles' => $this->get_departure_vehicles((int) $row['id']),
            ];
        }

        return Response::success($departures);
    }

    private function get_departure_pickup_points(int $departure_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nt_departure_pickup_points';
        $pp_table = $wpdb->prefix . 'nt_pickup_points';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT pp.id, pp.name, pp.address, dpp.pickup_time as time
             FROM {$table} dpp
             JOIN {$pp_table} pp ON dpp.pickup_point_id = pp.id
             WHERE dpp.tour_departure_id = %d AND dpp.status = 'active'
             ORDER BY dpp.sort_order ASC",
            $departure_id
        ), ARRAY_A);

        return $results ?: [];
    }

    private function get_departure_vehicles(int $departure_id): array
    {
        global $wpdb;
        $dv_table = $wpdb->prefix . 'nt_departure_vehicles';
        $v_table = $wpdb->prefix . 'nt_vehicles';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT v.id, v.name, v.plate_number
             FROM {$dv_table} dv
             JOIN {$v_table} v ON dv.vehicle_id = v.id
             WHERE dv.tour_departure_id = %d AND dv.status = 'active'",
            $departure_id
        ), ARRAY_A);

        return $results ?: [];
    }
}