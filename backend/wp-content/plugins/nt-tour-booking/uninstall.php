<?php
/**
 * Uninstall NT Tour Booking Plugin
 *
 * This file runs when the plugin is deleted (not just deactivated).
 * It removes all plugin data: tables, options, roles, and capabilities.
 *
 * @since 0.1.0
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Plugin options to delete
$options = [
    'nt_tour_company_name',
    'nt_tour_hotline',
    'nt_tour_support_email',
    'nt_tour_bank_name',
    'nt_tour_bank_account_name',
    'nt_tour_bank_account_number',
    'nt_tour_bank_branch',
    'nt_tour_transfer_content_template',
    'nt_tour_seat_hold_minutes',
    'nt_tour_allow_customer_seat_selection',
    'nt_tour_allow_admin_assign_seat',
    'nt_tour_checkin_allow_incomplete_profile',
    'nt_tour_webhook_secret',
    'nt_tour_email_from_name',
    'nt_tour_email_from_address',
    'nt_tour_version',
    'nt_tour_db_version',
];

// Delete all plugin options
foreach ($options as $option) {
    delete_option($option);
}

// Delete custom tables
$tables = [
    'nt_tour_departures',
    'nt_pickup_points',
    'nt_departure_pickup_points',
    'nt_vehicle_layouts',
    'nt_vehicles',
    'nt_departure_vehicles',
    'nt_departure_seats',
    'nt_bookings',
    'nt_booking_passengers',
    'nt_booking_access_tokens',
    'nt_payments',
    'nt_departure_guides',
    'nt_checkin_logs',
    'nt_activity_logs',
    'nt_rental_items',
    'nt_booking_rental_items',
    'nt_api_clients',
    'nt_api_request_logs',
    'nt_webhook_logs',
];

foreach ($tables as $table) {
    $table_name = $wpdb->prefix . $table;
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
}

// Remove custom roles
remove_role('nt_admin');
remove_role('nt_operator');
remove_role('nt_sale');
remove_role('nt_accountant');
remove_role('nt_guide');
remove_role('nt_content');

// Clear scheduled cron jobs
wp_clear_scheduled_hook('nt_release_expired_seats');
wp_clear_scheduled_hook('nt_passenger_info_reminder');
wp_clear_scheduled_hook('nt_cleanup_expired_tokens');

// Clear any transients
delete_transient('nt_tour_dashboard_cache');
