<?php
/**
 * Plugin Activator
 *
 * Handles plugin activation tasks.
 *
 * @since 0.1.0
 */

namespace TourBooking;

use TourBooking\Database\Migrations;
use TourBooking\Security\Capabilities;

class Activator
{
    /**
     * Run activation tasks
     *
     * @return void
     */
    public static function activate(): void
    {
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '6.0', '<')) {
            deactivate_plugins(NT_TOUR_BOOKING_PLUGIN_BASENAME);
            wp_die(
                esc_html__('NT Tour Booking requires WordPress 6.0 or higher.', 'nt-tour-booking'),
                'Plugin Activation Error',
                ['back_link' => true]
            );
        }

        // Check PHP version
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            deactivate_plugins(NT_TOUR_BOOKING_PLUGIN_BASENAME);
            wp_die(
                esc_html__('NT Tour Booking requires PHP 8.0 or higher.', 'nt-tour-booking'),
                'Plugin Activation Error',
                ['back_link' => true]
            );
        }

        // Run database migrations
        Migrations::run();

        // Register roles and capabilities
        Capabilities::register();

        // Install sample data on first activation
        if (!Installer::is_installed()) {
            Installer::run();
        }

        // Set plugin version
        update_option('nt_tour_version', NT_TOUR_BOOKING_VERSION);

        // Clear any cached data
        wp_cache_flush();

        // Flush rewrite rules for CPT
        flush_rewrite_rules();
    }
}
