<?php
/**
 * Token Generator Helper
 *
 * Generates secure tokens for magic links and QR codes.
 *
 * @since 0.1.0
 */

namespace TourBooking\Helpers;

class TokenGenerator
{
    /**
     * Generate a secure random token
     *
     * @param int $length
     * @return string
     */
    public static function generate(int $length = 64): string
    {
        return wp_generate_password($length, true, true);
    }

    /**
     * Generate a short token for QR codes
     *
     * @return string
     */
    public static function generate_short(): string
    {
        return wp_generate_password(16, false, false);
    }

    /**
     * Hash a token for storage
     *
     * @param string $token
     * @return string
     */
    public static function hash(string $token): string
    {
        return hash_hmac('sha256', $token, self::get_secret_key());
    }

    /**
     * Verify a token against its hash
     *
     * @param string $token
     * @param string $hash
     * @return bool
     */
    public static function verify(string $token, string $hash): bool
    {
        return hash_equals($hash, self::hash($token));
    }

    /**
     * Get secret key for hashing
     *
     * @return string
     */
    protected static function get_secret_key(): string
    {
        $key = get_option('nt_tour_webhook_secret', '');
        if (empty($key)) {
            $key = AUTH_KEY ?: 'nt-tour-booking-secret-key';
        }
        return $key;
    }

    /**
     * Generate booking code
     *
     * @return string
     */
    public static function generate_booking_code(): string
    {
        $prefix = 'NTB';
        $date = date('ymd');
        $random = strtoupper(substr(wp_generate_password(6, false), 0, 6));
        return "{$prefix}{$date}{$random}";
    }

    /**
     * Generate departure code
     *
     * @param int $tour_id
     * @param string $start_date
     * @return string
     */
    public static function generate_departure_code(int $tour_id, string $start_date): string
    {
        $tour_code = get_post_meta($tour_id, 'tour_code', true) ?: 'T' . $tour_id;
        $date_part = date('ymd', strtotime($start_date));
        $random_part = strtoupper(substr(wp_generate_password(4, false), 0, 4));
        return "{$tour_code}-{$date_part}-{$random_part}";
    }

    /**
     * Generate magic link URL
     *
     * @param string $token
     * @return string
     */
    public static function generate_magic_link_url(string $token): string
    {
        return home_url('/booking/passengers/' . $token);
    }

    /**
     * Generate QR check-in URL
     *
     * @param string $token
     * @return string
     */
    public static function generate_qr_url(string $token): string
    {
        return home_url('/nt-checkin/scan/' . $token);
    }
}
