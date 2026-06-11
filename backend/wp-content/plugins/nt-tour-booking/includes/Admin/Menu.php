<?php
/**
 * Admin Menu
 *
 * Sets up the WordPress admin menu and submenu items.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin;

use TourBooking\Admin\Pages\DashboardPage;
use TourBooking\Admin\Pages\DeparturesPage;
use TourBooking\Admin\Pages\PickupPointsPage;
use TourBooking\Admin\Pages\VehiclesPage;
use TourBooking\Admin\Pages\SeatLayoutsPage;
use TourBooking\Admin\Pages\BookingsPage;
use TourBooking\Admin\Pages\PassengersPage;
use TourBooking\Admin\Pages\PaymentsPage;
use TourBooking\Admin\Pages\CheckinPage;
use TourBooking\Admin\Pages\ReportsPage;
use TourBooking\Admin\Pages\ApiDocsPage;
use TourBooking\Admin\Pages\ToolsPage;

class Menu
{
    /**
     * Menu slug
     */
    const MENU_SLUG = 'nt-tour-booking';

    /**
     * Initialize menu
     *
     * @return void
     */
    public static function init(): void
    {
        // Initialize page classes
        DashboardPage::init();
        DeparturesPage::init();
        PickupPointsPage::init();
        VehiclesPage::init();
        SeatLayoutsPage::init();
        BookingsPage::init();
        PassengersPage::init();
        PaymentsPage::init();
        CheckinPage::init();
        ReportsPage::init();
        ApiDocsPage::init();
        ToolsPage::init();

        // Continue with other menu items via old methods for now
        add_action('admin_menu', [self::class, 'add_menus']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
    }

    /**
     * Add admin menus
     *
     * @return void
     */
    public static function add_menus(): void
    {
        // Main menu - points directly to DashboardPage (no redirect)
        add_menu_page(
            __('Tour Booking', 'nt-tour-booking'),
            __('Tour Booking', 'nt-tour-booking'),
            'nt_view_dashboard',
            self::MENU_SLUG,
            [DashboardPage::class, 'render'],
            'dashicons-calendar-alt',
            30
        );

        // Tours (CPT) - link to WordPress native post type
        add_submenu_page(
            self::MENU_SLUG,
            __('Tours', 'nt-tour-booking'),
            __('Tours', 'nt-tour-booking'),
            'nt_manage_tours',
            'edit.php?post_type=nt_tour',
            null
        );

        // Seat Map - redirect to checkin page
        add_submenu_page(
            self::MENU_SLUG,
            __('Seat Map', 'nt-tour-booking'),
            __('Seat Map', 'nt-tour-booking'),
            'nt_manage_seats',
            'nt-tour-seat-map',
            [self::class, 'render_redirect_seat_map']
        );

        // Settings - inline render
        add_submenu_page(
            self::MENU_SLUG,
            __('Settings', 'nt-tour-booking'),
            __('Settings', 'nt-tour-booking'),
            'nt_manage_settings',
            'nt-tour-settings',
            [self::class, 'render_settings']
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page
     * @return void
     */
    public static function enqueue_assets(string $hook): void
    {
        // Only load on our plugin pages
        if (strpos($hook, 'nt-tour') === false && strpos($hook, 'nt_tour') === false) {
            return;
        }

        wp_enqueue_style(
            'nt-tour-admin',
            NT_TOUR_BOOKING_PLUGIN_URL . 'assets/admin/css/nt-tour-admin.css',
            [],
            NT_TOUR_BOOKING_VERSION
        );
    }

    /**
     * Render seat map redirect
     */
    public static function render_redirect_seat_map(): void
    {
        wp_redirect(admin_url('admin.php?page=nt-tour-checkin'));
        exit;
    }

    /**
     * Render settings page
     */
    public static function render_settings(): void
    {
        echo '<div class="wrap nt-tour-wrap">';
        echo '<h1>' . esc_html__('Settings', 'nt-tour-booking') . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('nt_tour_settings');
        do_settings_sections('nt-tour-settings');
        submit_button();
        echo '</form>';
        echo '</div>';
    }
}
