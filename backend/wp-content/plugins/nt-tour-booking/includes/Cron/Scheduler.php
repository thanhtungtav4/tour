<?php
/**
 * Cron Scheduler
 *
 * Registers and manages scheduled cron jobs.
 *
 * @since 0.1.0
 */

namespace TourBooking\Cron;

class Scheduler
{
    /**
     * Cron hook names
     */
    const HOOK_RELEASE_SEATS = 'nt_release_expired_seats';
    const HOOK_PASSENGER_REMINDER = 'nt_passenger_info_reminder';
    const HOOK_CLEANUP_TOKENS = 'nt_cleanup_expired_tokens';

    /**
     * Register cron jobs
     *
     * @return void
     */
    public static function register(): void
    {
        // Add custom schedule intervals
        add_filter('cron_schedules', [self::class, 'add_custom_schedules']);

        // Register cron job callbacks
        add_action(self::HOOK_RELEASE_SEATS, [self::class, 'release_expired_seats']);
        add_action(self::HOOK_PASSENGER_REMINDER, [self::class, 'send_passenger_reminders']);
        add_action(self::HOOK_CLEANUP_TOKENS, [self::class, 'cleanup_expired_tokens']);

        // Schedule cron jobs on init
        add_action('init', [self::class, 'schedule_crons']);
    }

    /**
     * Add custom cron schedules
     *
     * @param array $schedules Existing schedules
     * @return array Modified schedules
     */
    public static function add_custom_schedules(array $schedules): array
    {
        // Every 5 minutes
        $schedules['nt_five_minutes'] = [
            'interval' => 5 * MINUTE_IN_SECONDS,
            'display' => __('Every 5 Minutes', 'nt-tour-booking'),
        ];

        return $schedules;
    }

    /**
     * Schedule all cron jobs
     *
     * @return void
     */
    public static function schedule_crons(): void
    {
        // Release expired seats - every 5 minutes
        if (!wp_next_scheduled(self::HOOK_RELEASE_SEATS)) {
            wp_schedule_event(time(), 'nt_five_minutes', self::HOOK_RELEASE_SEATS);
        }

        // Passenger reminder - daily at 9 AM
        if (!wp_next_scheduled(self::HOOK_PASSENGER_REMINDER)) {
            wp_schedule_event(strtotime('today 9:00 AM'), 'daily', self::HOOK_PASSENGER_REMINDER);
        }

        // Cleanup expired tokens - daily at midnight
        if (!wp_next_scheduled(self::HOOK_CLEANUP_TOKENS)) {
            wp_schedule_event(strtotime('today midnight'), 'daily', self::HOOK_CLEANUP_TOKENS);
        }
    }

    /**
     * Release expired seat holds
     *
     * @return void
     */
    public static function release_expired_seats(): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nt_departure_seats';
        $bookings_table = $wpdb->prefix . 'nt_bookings';

        // Find expired holds
        $expired_seats = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT s.*, b.code as booking_code 
                FROM {$table_name} s 
                LEFT JOIN {$bookings_table} b ON s.booking_id = b.id 
                WHERE s.status = 'holding' 
                AND s.hold_expires_at< %s 
                AND b.payment_status IN ('unpaid', 'underpaid')",
                current_time('mysql')
            ),
            ARRAY_A
        );

        if (empty($expired_seats)) {
            return;
        }

        $released_count = 0;

        foreach ($expired_seats as $seat) {
            // Release the seat
            $wpdb->update(
                $table_name,
                [
                    'status' => 'available',
                    'booking_id' => null,
                    'passenger_id' => null,
                    'hold_expires_at' => null,
                    'updated_at' => current_time('mysql'),
                ],
                ['id' => $seat['id']],
                ['%s', 'null', 'null', 'null', '%s'],
                ['%d']
            );

            $released_count++;

            // Log the release
            if ($seat['booking_id']) {
                \TourBooking\ActivityLogger::log_seats_released(
                    (int) $seat['booking_id'],
                    [$seat['seat_code']],
                    'expired_hold'
                );
            }
        }

        // Log overall action
        \TourBooking\ActivityLogger::log(
            'system',
            0,
            'expired_seats_released',
            null,
            ['count' => $released_count]
        );
    }

    /**
     * Send passenger info reminders
     *
     * @return void
     */
    public static function send_passenger_reminders(): void
    {
        // Will be implemented in Phase 5 when EmailService is ready
        // For now, just log that it ran
        \TourBooking\ActivityLogger::log(
            'system',
            0,
            'passenger_reminder_job_ran',
            null,
            ['timestamp' => current_time('mysql')]
        );
    }

    /**
     * Cleanup expired access tokens
     *
     * @return void
     */
    public static function cleanup_expired_tokens(): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nt_booking_access_tokens';

        // Mark expired tokens (don't delete for audit)
        $result = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table_name} SET expires_at = NULL WHERE expires_at < %s AND expires_at IS NOT NULL",
                current_time('mysql')
            )
        );

        // Log cleanup
        \TourBooking\ActivityLogger::log(
            'system',
            0,
            'tokens_cleanup',
            null,
            ['affected_rows' => $result]
        );
    }

    /**
     * Unschedule all cron jobs
     *
     * @return void
     */
    public static function unschedule_all(): void
    {
        wp_clear_scheduled_hook(self::HOOK_RELEASE_SEATS);
        wp_clear_scheduled_hook(self::HOOK_PASSENGER_REMINDER);
        wp_clear_scheduled_hook(self::HOOK_CLEANUP_TOKENS);
    }
}
