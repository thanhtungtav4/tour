<?php
/**
 * Tour Custom Post Type
 *
 * Registers the nt_tour CPT and related taxonomies.
 *
 * @since 0.1.0
 */

namespace TourBooking\CPT;

class TourCPT
{
    /**
     * Post type slug
     */
    const POST_TYPE = 'nt_tour';

    /**
     * Initialize CPT
     *
     * @return void
     */
    public static function init(): void
    {
        add_action('init', [self::class, 'register_post_type']);
        add_action('init', [self::class, 'register_taxonomies']);
        add_action('init', [self::class, 'register_meta']);
        add_filter('rest_api_init', [self::class, 'register_rest_field']);
    }

    /**
     * Register the Tour CPT
     *
     * @return void
     */
    public static function register_post_type(): void
    {
        $labels = [
            'name' => __('Tours', 'nt-tour-booking'),
            'singular_name' => __('Tour', 'nt-tour-booking'),
            'menu_name' => __('Tours', 'nt-tour-booking'),
            'name_admin_bar' => __('Tour', 'nt-tour-booking'),
            'add_new' => __('Add New', 'nt-tour-booking'),
            'add_new_item' => __('Add New Tour', 'nt-tour-booking'),
            'edit_item' => __('Edit Tour', 'nt-tour-booking'),
            'new_item' => __('New Tour', 'nt-tour-booking'),
            'view_item' => __('View Tour', 'nt-tour-booking'),
            'search_items' => __('Search Tours', 'nt-tour-booking'),
            'not_found' => __('No tours found', 'nt-tour-booking'),
            'not_found_in_trash' => __('No tours found in trash', 'nt-tour-booking'),
        ];

        $args = [
            'labels' => $labels,
            'description' => __('Tour packages', 'nt-tour-booking'),
            'public' => true,
            'show_in_menu' => false, // We'll add under our plugin menu
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'has_archive' => true,
            'rewrite' => ['slug' => 'tours'],
            'menu_icon' => 'dashicons-calendar-alt',
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ];

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Register taxonomies
     *
     * @return void
     */
    public static function register_taxonomies(): void
    {
        // Destination taxonomy
        register_taxonomy('nt_destination', self::POST_TYPE, [
            'labels' => [
                'name' => __('Destinations', 'nt-tour-booking'),
                'singular_name' => __('Destination', 'nt-tour-booking'),
                'search_items' => __('Search Destinations', 'nt-tour-booking'),
                'all_items' => __('All Destinations', 'nt-tour-booking'),
                'edit_item' => __('Edit Destination', 'nt-tour-booking'),
                'update_item' => __('Update Destination', 'nt-tour-booking'),
                'add_new_item' => __('Add New Destination', 'nt-tour-booking'),
                'new_item_name' => __('New Destination Name', 'nt-tour-booking'),
                'menu_name' => __('Destinations', 'nt-tour-booking'),
            ],
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'destination'],
        ]);

        // Tour type taxonomy
        register_taxonomy('nt_tour_type', self::POST_TYPE, [
            'labels' => [
                'name' => __('Tour Types', 'nt-tour-booking'),
                'singular_name' => __('Tour Type', 'nt-tour-booking'),
                'search_items' => __('Search Tour Types', 'nt-tour-booking'),
                'all_items' => __('All Tour Types', 'nt-tour-booking'),
                'edit_item' => __('Edit Tour Type', 'nt-tour-booking'),
                'update_item' => __('Update Tour Type', 'nt-tour-booking'),
                'add_new_item' => __('Add New Tour Type', 'nt-tour-booking'),
                'new_item_name' => __('New Tour Type Name', 'nt-tour-booking'),
                'menu_name' => __('Tour Types', 'nt-tour-booking'),
            ],
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'tour-type'],
        ]);

        // Departure location taxonomy
        register_taxonomy('nt_departure_location', self::POST_TYPE, [
            'labels' => [
                'name' => __('Departure Locations', 'nt-tour-booking'),
                'singular_name' => __('Departure Location', 'nt-tour-booking'),
                'search_items' => __('Search Departure Locations', 'nt-tour-booking'),
                'all_items' => __('All Departure Locations', 'nt-tour-booking'),
                'edit_item' => __('Edit Departure Location', 'nt-tour-booking'),
                'update_item' => __('Update Departure Location', 'nt-tour-booking'),
                'add_new_item' => __('Add New Departure Location', 'nt-tour-booking'),
                'new_item_name' => __('New Departure Location Name', 'nt-tour-booking'),
                'menu_name' => __('Departure Locations', 'nt-tour-booking'),
            ],
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'departure-location'],
        ]);
    }

    /**
     * Register post meta for REST API
     *
     * @return void
     */
    public static function register_meta(): void
    {
        $meta_fields = [
            'tour_code' => ['type' => 'string'],
            'destination' => ['type' => 'string'],
            'duration_days' => ['type' => 'integer'],
            'duration_nights' => ['type' => 'integer'],
            'departure_location' => ['type' => 'string'],
            'included' => ['type' => 'string'],
            'excluded' => ['type' => 'string'],
            'policy' => ['type' => 'string'],
            'itinerary_json' => ['type' => 'string'],
            'gallery' => ['type' => 'string'],
            'seo_title' => ['type' => 'string'],
            'seo_description' => ['type' => 'string'],
        ];

        foreach ($meta_fields as $key => $args) {
            register_post_meta(self::POST_TYPE, $key, [
                'type' => $args['type'],
                'single' => true,
                'show_in_rest' => true,
                'auth_callback' => function () {
                    return current_user_can('edit_posts');
                },
            ]);
        }
    }

    /**
     * Register REST API field for meta
     *
     * @return void
     */
    public static function register_rest_field(): void
    {
        register_rest_field(self::POST_TYPE, 'tour_meta', [
            'get_callback' => [self::class, 'get_tour_meta'],
            'update_callback' => [self::class, 'update_tour_meta'],
            'schema' => [
                'type' => 'object',
                'description' => 'Tour metadata',
            ],
        ]);
    }

    /**
     * Get tour meta for REST API
     *
     * @param array $post Post object
     * @return array
     */
    public static function get_tour_meta(array $post): array
    {
        return [
            'tour_code' => get_post_meta($post['id'], 'tour_code', true),
            'destination' => get_post_meta($post['id'], 'destination', true),
            'duration_days' => (int) get_post_meta($post['id'], 'duration_days', true),
            'duration_nights' => (int) get_post_meta($post['id'], 'duration_nights', true),
            'departure_location' => get_post_meta($post['id'], 'departure_location', true),
            'included' => get_post_meta($post['id'], 'included', true),
            'excluded' => get_post_meta($post['id'], 'excluded', true),
            'policy' => get_post_meta($post['id'], 'policy', true),
            'itinerary_json' => get_post_meta($post['id'], 'itinerary_json', true),
            'gallery' => get_post_meta($post['id'], 'gallery', true),
            'seo_title' => get_post_meta($post['id'], 'seo_title', true),
            'seo_description' => get_post_meta($post['id'], 'seo_description', true),
        ];
    }

    /**
     * Update tour meta from REST API
     *
     * @param mixed $meta_value Meta value
     * @param \WP_Post $post Post object
     * @return bool
     */
    public static function update_tour_meta($meta_value, \WP_Post $post): bool
    {
        if (!current_user_can('edit_post', $post->ID)) {
            return false;
        }

        if (!is_array($meta_value)) {
            return false;
        }

        $allowed_fields = ['tour_code', 'destination', 'duration_days', 'duration_nights', 'departure_location', 'included', 'excluded', 'policy', 'itinerary_json', 'gallery', 'seo_title', 'seo_description'];

        foreach ($allowed_fields as $key) {
            if (isset($meta_value[$key])) {
                update_post_meta($post->ID, $key, $meta_value[$key]);
            }
        }

        return true;
    }
}
