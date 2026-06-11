<?php
/**
 * Frontend Shortcodes
 *
 * Registers frontend shortcodes.
 *
 * @since 0.1.0
 */

namespace TourBooking\Frontend;

class Shortcodes
{
    /**
     * Initialize
     */
    public static function init(): void
    {
        add_shortcode('nt_booking_form', [self::class, 'booking_form']);
        add_shortcode('nt_seat_map', [self::class, 'seat_map']);
        add_shortcode('nt_magic_passenger_form', [self::class, 'magic_passenger_form']);
        add_shortcode('nt_booking_lookup', [self::class, 'booking_lookup']);
    }

    /**
     * Booking form shortcode
     *
     * @param array $atts
     * @return string
     */
    public static function booking_form(array $atts = []): string
    {
        $atts = shortcode_atts([
            'departure_id' => 0,
            'tour_id' => 0,
        ], $atts);

        ob_start();
        $departure_id = intval($atts['departure_id']);
        $tour_id = intval($atts['tour_id']);

        // Enqueue assets
        wp_enqueue_style('nt-tour-frontend', NT_TOUR_BOOKING_PLUGIN_URL . 'assets/frontend/css/nt-tour-frontend.css', [], NT_TOUR_BOOKING_VERSION);
        wp_enqueue_script('nt-tour-frontend', NT_TOUR_BOOKING_PLUGIN_URL . 'assets/frontend/js/nt-tour-frontend.js', ['jquery'], NT_TOUR_BOOKING_VERSION, true);

        // Pass data to JS
        wp_localize_script('nt-tour-frontend', 'ntTour', [
            'apiUrl' => rest_url('nt-tour/v1'),
            'departureId' => $departure_id,
            'tourId' => $tour_id,
            'nonce' => wp_create_nonce('wp_rest'),
        ]);

        include NT_TOUR_BOOKING_PLUGIN_DIR . 'templates/frontend/booking-form.php';

        return ob_get_clean();
    }

    /**
     * Seat map shortcode
     *
     * @param array $atts
     * @return string
     */
    public static function seat_map(array $atts = []): string
    {
        $atts = shortcode_atts([
            'departure_id' => 0,
        ], $atts);

        ob_start();
        $departure_id = intval($atts['departure_id']);

        wp_enqueue_style('nt-tour-frontend', NT_TOUR_BOOKING_PLUGIN_URL . 'assets/frontend/css/nt-tour-frontend.css', [], NT_TOUR_BOOKING_VERSION);

        include NT_TOUR_BOOKING_PLUGIN_DIR . 'templates/frontend/seat-map.php';

        return ob_get_clean();
    }

    /**
     * Magic passenger form shortcode
     *
     * @param array $atts
     * @return string
     */
    public static function magic_passenger_form(array $atts = []): string
    {
        ob_start();

        wp_enqueue_style('nt-tour-frontend', NT_TOUR_BOOKING_PLUGIN_URL . 'assets/frontend/css/nt-tour-frontend.css', [], NT_TOUR_BOOKING_VERSION);
        wp_enqueue_script('nt-tour-frontend', NT_TOUR_BOOKING_PLUGIN_URL . 'assets/frontend/js/nt-tour-frontend.js', ['jquery'], NT_TOUR_BOOKING_VERSION, true);

        wp_localize_script('nt-tour-frontend', 'ntTour', [
            'apiUrl' => rest_url('nt-tour/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);

        include NT_TOUR_BOOKING_PLUGIN_DIR . 'templates/frontend/magic-passenger-form.php';

        return ob_get_clean();
    }

    /**
     * Booking lookup shortcode
     *
     * @param array $atts
     * @return string
     */
    public static function booking_lookup(array $atts = []): string
    {
        ob_start();

        wp_enqueue_style('nt-tour-frontend', NT_TOUR_BOOKING_PLUGIN_URL . 'assets/frontend/css/nt-tour-frontend.css', [], NT_TOUR_BOOKING_VERSION);

        include NT_TOUR_BOOKING_PLUGIN_DIR . 'templates/frontend/booking-lookup.php';

        return ob_get_clean();
    }
}
