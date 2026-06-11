<?php
/**
 * Database Migrations
 *
 * Handles database table creation and upgrades.
 *
 * @since 0.1.0
 */

namespace TourBooking\Database;

use TourBooking\ActivityLogger;

class Migrations
{
    /**
     * Current database version
     */
    public const DB_VERSION = '0.2.0';

    /**
     * Run migrations
     *
     * @return void
     */
    public static function run(): void
    {
        $installed_version = get_option('nt_tour_db_version');

        if ($installed_version !== self::DB_VERSION) {
            self::create_tables();
            update_option('nt_tour_db_version', self::DB_VERSION);
        }
    }

    /**
     * Create all custom tables
     *
     * @return void
     */
    public static function create_tables(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $tables = Schema::get_tables();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        foreach ($tables as $table_name => $sql) {
            // Replace placeholders
            $sql = str_replace(
                ['{prefix}', '{charset_collate}'],
                [$wpdb->prefix, $charset_collate],
                $sql
            );

            // Create or update table
            dbDelta($sql);
        }

        // Log migration
        ActivityLogger::log(
            'system',
            0,
            'tables_created',
            null,
            ['version' => self::DB_VERSION]
        );
    }

    /**
     * Drop all custom tables (for testing/reset)
     *
     * @return void
     */
    public static function drop_tables(): void
    {
        global $wpdb;

        $tables = Schema::get_tables();

        foreach ($tables as $table_name => $sql) {
            $full_table_name = $wpdb->prefix . $table_name;
            $wpdb->query("DROP TABLE IF EXISTS {$full_table_name}");
        }

        delete_option('nt_tour_db_version');
    }

    /**
     * Check if tables exist
     *
     * @return bool
     */
    public static function tables_exist(): bool
    {
        global $wpdb;

        $sample_table = $wpdb->prefix . 'nt_tour_departures';

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
                DB_NAME,
                $sample_table
            )
        );

        return (int) $result > 0;
    }
}
