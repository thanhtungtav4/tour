<?php
/**
 * Plugin Deactivator
 *
 * Handles plugin deactivation tasks.
 *
 * @since 0.1.0
 */

namespace TourBooking;

class Deactivator
{
    /**
     * Run deactivation tasks
     *
     * @return void
     */
    public static function deactivate(): void
    {
        // Clear scheduled cron jobs
        wp_clear_scheduled_hook('nt_release_expired_seats');
        wp_clear_scheduled_hook('nt_passenger_info_reminder');
        wp_clear_scheduled_hook('nt_cleanup_expired_tokens');

        // Clear transients
        delete_transient('nt_tour_dashboard_cache');

        // Flush rewrite rules
        flush_rewrite_rules();

        // Note: We don't remove roles/capabilities or tables on deactivation
        // They are only removed on uninstall (see uninstall.php)
    }
}
