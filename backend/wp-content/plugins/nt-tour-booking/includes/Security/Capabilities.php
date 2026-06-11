<?php
/**
 * Roles and Capabilities
 *
 * Defines 6 roles with 14 capabilities for the NT Tour Booking plugin.
 *
 * @since 0.1.0
 */

namespace TourBooking\Security;

class Capabilities
{
    /**
     * Role definitions
     *
     * @var array<string, array>
     */
    const ROLES = [
        'nt_admin' => [
            'name' => 'NT Tour Admin',
            'description' => 'Full access to all NT Tour Booking features',
        ],
        'nt_operator' => [
            'name' => 'NT Tour Operator',
            'description' => 'Manage departures, vehicles, bookings, and check-in',
        ],
        'nt_sale' => [
            'name' => 'NT Tour Sale',
            'description' => 'Create bookings and edit passenger information',
        ],
        'nt_accountant' => [
            'name' => 'NT Tour Accountant',
            'description' => 'View bookings and confirm payments',
        ],
        'nt_guide' => [
            'name' => 'NT Tour Guide',
            'description' => 'Check-in passengers and manage assigned departures',
        ],
        'nt_content' => [
            'name' => 'NT Tour Content',
            'description' => 'Manage tour content only',
        ],
    ];

    /**
     * Capability definitions
     *
     * @var array<string, string>
     */
    const CAPABILITIES = [
        'nt_view_dashboard' => 'View dashboard',
        'nt_manage_tours' => 'Manage tour content',
        'nt_manage_departures' => 'Manage departures',
        'nt_manage_pickup_points' => 'Manage pickup points',
        'nt_manage_vehicles' => 'Manage vehicles',
        'nt_manage_bookings' => 'Manage bookings',
        'nt_manage_passengers' => 'Manage passengers',
        'nt_manage_seats' => 'Manage seats',
        'nt_confirm_payments' => 'Confirm payments',
        'nt_checkin_passengers' => 'Check-in passengers',
        'nt_change_passenger_seats' => 'Change passenger seats',
        'nt_fill_passenger_info' => 'Fill passenger information',
        'nt_view_reports' => 'View reports',
        'nt_manage_settings' => 'Manage settings',
        'nt_manage_api' => 'Manage API clients and logs',
    ];

    /**
     * Role to capabilities mapping
     *
     * @var array<string, array>
     */
    const ROLE_CAPABILITIES = [
        'nt_admin' => [
            'nt_view_dashboard',
            'nt_manage_tours',
            'nt_manage_departures',
            'nt_manage_pickup_points',
            'nt_manage_vehicles',
            'nt_manage_bookings',
            'nt_manage_passengers',
            'nt_manage_seats',
            'nt_confirm_payments',
            'nt_checkin_passengers',
            'nt_change_passenger_seats',
            'nt_fill_passenger_info',
            'nt_view_reports',
            'nt_manage_settings',
            'nt_manage_api',
        ],
        'nt_operator' => [
            'nt_view_dashboard',
            'nt_manage_departures',
            'nt_manage_pickup_points',
            'nt_manage_vehicles',
            'nt_manage_bookings',
            'nt_manage_passengers',
            'nt_manage_seats',
            'nt_checkin_passengers',
            'nt_change_passenger_seats',
            'nt_fill_passenger_info',
            'nt_view_reports',
        ],
        'nt_sale' => [
            'nt_view_dashboard',
            'nt_manage_bookings',
            'nt_manage_passengers',
            'nt_fill_passenger_info',
        ],
        'nt_accountant' => [
            'nt_view_dashboard',
            'nt_manage_bookings',
            'nt_confirm_payments',
            'nt_view_reports',
        ],
        'nt_guide' => [
            'nt_checkin_passengers',
            'nt_change_passenger_seats',
            'nt_fill_passenger_info',
        ],
        'nt_content' => [
            'nt_manage_tours',
        ],
    ];

    /**
     * Register roles and capabilities
     *
     * @return void
     */
    public static function register(): void
    {
        add_action('init', [self::class, 'register_roles']);
        add_action('admin_init', [self::class, 'map_capabilities_to_admins']);
    }

    /**
     * Register custom roles
     *
     * @return void
     */
    public static function register_roles(): void
    {
        foreach (self::ROLE_CAPABILITIES as $role_key => $capabilities) {
            $role_info = self::ROLES[$role_key] ?? ['name' => ucfirst(str_replace('_', ' ', $role_key))];

            add_role(
                $role_key,
                $role_info['name'],
                array_fill_keys($capabilities, true)
            );
        }

        // Add capabilities to WordPress admin
        self::map_capabilities_to_admins();
    }

    /**
     * Map capabilities to WordPress admin role
     *
     * @return void
     */
    public static function map_capabilities_to_admins(): void
    {
        $admin_role = get_role('administrator');

        if ($admin_role) {
            foreach (self::CAPABILITIES as $cap => $label) {
                $admin_role->add_cap($cap);
            }
        }
    }

    /**
     * Check if user has capability
     *
     * @param int|null $user_id User ID (null for current user)
     * @param string $capability Capability name
     * @return bool
     */
    public static function has_cap(?int $user_id, string $capability): bool
    {
        $user_id = $user_id ?? get_current_user_id();

        if (!$user_id) {
            return false;
        }

        $user = get_userdata($user_id);

        if (!$user) {
            return false;
        }

        return $user->has_cap($capability);
    }

    /**
     * Check if user is assigned as guide for a departure
     *
     * @param int $user_id
     * @param int $departure_id
     * @return bool
     */
    public static function is_guide_for_departure(int $user_id, int $departure_id): bool
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nt_departure_guides';

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d AND tour_departure_id = %d",
                $user_id,
                $departure_id
            )
        );

        return (int) $count > 0;
    }

    /**
     * Remove all custom roles (used in uninstall)
     *
     * @return void
     */
    public static function remove_roles(): void
    {
        foreach (array_keys(self::ROLES) as $role_key) {
            remove_role($role_key);
        }
    }
}
