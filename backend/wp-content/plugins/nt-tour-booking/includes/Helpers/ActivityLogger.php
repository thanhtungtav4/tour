<?php
/**
 * Activity Logger
 *
 * Logs important system actions.
 *
 * @since 0.1.0
 */

namespace TourBooking;

class ActivityLogger
{
    /**
     * Log an activity
     *
     * @param string $object_type Object type (booking, seat, payment, etc.)
     * @param int $object_id Object ID
     * @param string $action Action performed
     * @param mixed $old_value Old value (optional)
     * @param mixed $new_value New value (optional)
     * @param int|null $user_id User ID (optional)
     * @return int|false Inserted log ID or false on failure
     */
    public static function log(
        string $object_type,
        int $object_id,
        string $action,
        $old_value = null,
        $new_value = null,
        ?int $user_id = null
    ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nt_activity_logs';

        // Get current user ID if not provided
        if ($user_id === null && function_exists('get_current_user_id')) {
            $user_id = get_current_user_id();
        }

        // Get IP address
        $ip_address = self::get_client_ip();

        // Get user agent
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';

        // Serialize complex values
        $old_value_serialized = self::needs_serialize($old_value) ? maybe_serialize($old_value) : null;
        $new_value_serialized = self::needs_serialize($new_value) ? maybe_serialize($new_value) : null;

        $result = $wpdb->insert(
            $table_name,
            [
                'object_type' => $object_type,
                'object_id' => $object_id,
                'action' => $action,
                'old_value' => $old_value_serialized,
                'new_value' => $new_value_serialized,
                'user_id' => $user_id ?: null,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    private static function get_client_ip(): string
    {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Log booking created
     *
     * @param int $booking_id
     * @param array $booking_data
     * @return int|false
     */
    public static function log_booking_created(int $booking_id, array $booking_data)
    {
        return self::log('booking', $booking_id, 'booking_created', null, $booking_data);
    }

    /**
     * Log seats held
     *
     * @param int $booking_id
     * @param array $seat_codes
     * @return int|false
     */
    public static function log_seats_held(int $booking_id, array $seat_codes)
    {
        return self::log('booking', $booking_id, 'seats_held', null, ['seats' => $seat_codes]);
    }

    /**
     * Log seats released
     *
     * @param int $booking_id
     * @param array $seat_codes
     * @param string $reason
     * @return int|false
     */
    public static function log_seats_released(int $booking_id, array $seat_codes, string $reason = 'timeout')
    {
        return self::log('booking', $booking_id, 'seats_released', null, ['seats' => $seat_codes, 'reason' => $reason]);
    }

    /**
     * Log payment confirmed
     *
     * @param int $booking_id
     * @param float $amount
     * @param string $status
     * @return int|false
     */
    public static function log_payment_confirmed(int $booking_id, float $amount, string $status)
    {
        return self::log('booking', $booking_id, 'payment_confirmed', null, ['amount' => $amount, 'status' => $status]);
    }

    /**
     * Log booking confirmed
     *
     * @param int $booking_id
     * @return int|false
     */
    public static function log_booking_confirmed(int $booking_id)
    {
        return self::log('booking', $booking_id, 'booking_confirmed');
    }

    /**
     * Log booking cancelled
     *
     * @param int $booking_id
     * @param string $reason
     * @return int|false
     */
    public static function log_booking_cancelled(int $booking_id, string $reason = '')
    {
        return self::log('booking', $booking_id, 'booking_cancelled', null, ['reason' => $reason]);
    }

    /**
     * Log passenger updated
     *
     * @param int $passenger_id
     * @param array $changes
     * @return int|false
     */
    public static function log_passenger_updated(int $passenger_id, array $changes)
    {
        return self::log('passenger', $passenger_id, 'passenger_updated', null, $changes);
    }

    /**
     * Log magic link accessed
     *
     * @param int $booking_id
     * @return int|false
     */
    public static function log_magic_link_accessed(int $booking_id)
    {
        return self::log('booking', $booking_id, 'magic_link_accessed');
    }

    /**
     * Log seat changed
     *
     * @param int $passenger_id
     * @param string $old_seat
     * @param string $new_seat
     * @return int|false
     */
    public static function log_seat_changed(int $passenger_id, string $old_seat, string $new_seat)
    {
        return self::log('passenger', $passenger_id, 'seat_changed', $old_seat, $new_seat);
    }

    /**
     * Log check-in
     *
     * @param int $passenger_id
     * @param string $status
     * @return int|false
     */
    public static function log_checkin(int $passenger_id, string $status)
    {
        return self::log('passenger', $passenger_id, 'checked_in', 'not_checked_in', $status);
    }

    /**
     * Log no-show
     *
     * @param int $passenger_id
     * @return int|false
     */
    public static function log_no_show(int $passenger_id)
    {
        return self::log('passenger', $passenger_id, 'no_show');
    }

    /**
     * Log QR generated
     *
     * @param int $passenger_id
     * @return int|false
     */
    public static function log_qr_generated(int $passenger_id)
    {
        return self::log('passenger', $passenger_id, 'qr_generated');
    }

    /**
     * Check if value needs serialization
     *
     * @param mixed $value
     * @return bool
     */
    private static function needs_serialize($value): bool
    {
        return is_array($value) || is_object($value);
    }
}

/**
 * Check if value is serializable
 *
 * @param mixed $value
 * @return bool
 * @deprecated Use ActivityLogger::needs_serialize() instead
 */
function is_serializable($value): bool
{
    return is_array($value) || is_object($value);
}
