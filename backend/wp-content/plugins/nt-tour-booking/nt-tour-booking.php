<?php
/**
 * Plugin Name: NT Tour Booking
 * Plugin URI: https://example.com/nt-tour-booking
 * Description: WordPress plugin for tour booking management with seat selection, magic links, QR check-in, and payment handling.
 * Version: 0.1.0
 * Author: NTTour
 * Author URI: https://example.com
 * Text Domain: nt-tour-booking
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace TourBooking;

use TourBooking\Database\Migrations;
use TourBooking\Security\Capabilities;
use TourBooking\Admin\Menu;
use TourBooking\Admin\SettingsPage;
use TourBooking\Cron\Scheduler;
use TourBooking\CPT\TourCPT;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Composer autoload
require_once __DIR__ . '/vendor/autoload.php';

// Plugin constants
define('NT_TOUR_BOOKING_VERSION', '0.1.0');
define('NT_TOUR_BOOKING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NT_TOUR_BOOKING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NT_TOUR_BOOKING_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 *
 * @since 0.1.0
 */
final class Plugin
{
    /**
     * Plugin instance
     *
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;

    /**
     * Get plugin instance
     *
     * @return Plugin
     */
    public static function instance(): Plugin
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files
     *
     * @return void
     */
    private function load_dependencies(): void
    {
        // All classes are autoloaded via Composer PSR-4.
        // No manual require_once needed.
    }

    /**
     * Initialize hooks
     *
     * @return void
     */
    private function init_hooks(): void
    {
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, [Activator::class, 'activate']);
        register_deactivation_hook(__FILE__, [Deactivator::class, 'deactivate']);

        // Initialize plugin after all plugins loaded
        add_action('plugins_loaded', [$this, 'init_plugin']);
    }

    /**
     * Initialize plugin components
     *
     * @return void
     */
    public function init_plugin(): void
    {
        // Load text domain
        load_plugin_textdomain(
            'nt-tour-booking',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );

        // Run database migrations
        Migrations::run();

        // Register roles and capabilities
        Capabilities::register();

        // Register CPT, taxonomies, and tour metadata during normal WordPress init.
        TourCPT::init();

        // Initialize admin menu
        Menu::init();

        // Initialize settings
        SettingsPage::init();

        // Register cron jobs
        Scheduler::register();

        // Register frontend shortcodes during the normal WordPress lifecycle.
        \TourBooking\Frontend\Shortcodes::init();

        // Initialize REST API
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    /**
     * Register REST API routes
     *
     * @return void
     */
    public function register_rest_routes(): void
    {
        // Register REST controllers
        $tour_controller = new \TourBooking\Rest\TourController();
        $tour_controller->register_routes();

        $departure_controller = new \TourBooking\Rest\DepartureController();
        $departure_controller->register_routes();

        $pickup_point_controller = new \TourBooking\Rest\PickupPointController();
        $pickup_point_controller->register_routes();

        $vehicle_controller = new \TourBooking\Rest\VehicleController();
        $vehicle_controller->register_routes();

        $booking_controller = new \TourBooking\Rest\BookingController();
        $booking_controller->register_routes();

        $seat_controller = new \TourBooking\Rest\SeatController();
        $seat_controller->register_routes();

        $checkin_controller = new \TourBooking\Rest\CheckinController();
        $checkin_controller->register_routes();

        $payment_controller = new \TourBooking\Rest\PaymentController();
        $payment_controller->register_routes();

        $api_management_controller = new \TourBooking\Rest\ApiManagementController();
        $api_management_controller->register_routes();

    }

    /**
     * Prevent cloning
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserializing
     *
     * @return void
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}

/**
 * Initialize plugin
 *
 * @return Plugin
 */
function nt_tour_booking(): Plugin
{
    return Plugin::instance();
}

// Start the plugin
nt_tour_booking();
