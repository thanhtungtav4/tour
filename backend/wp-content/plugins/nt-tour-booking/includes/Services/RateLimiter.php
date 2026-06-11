<?php
/**
 * Rate Limiter Service
 *
 * Lightweight transient-based rate limiting for public REST endpoints.
 *
 * @since 0.1.0
 */

namespace TourBooking\Services;

class RateLimiter
{
    public static function hit(string $bucket, int $limit, int $window_seconds): bool
    {
        $key = self::key($bucket);
        $data = get_transient($key);

        if (!is_array($data)) {
            set_transient($key, ['count' => 1, 'reset' => time() + $window_seconds], $window_seconds);
            return true;
        }

        if ((int) ($data['count'] ?? 0) >= $limit) {
            return false;
        }

        $data['count'] = (int) $data['count'] + 1;
        $ttl = max(1, (int) ($data['reset'] ?? time() + $window_seconds) - time());
        set_transient($key, $data, $ttl);
        return true;
    }

    private static function key(string $bucket): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return 'nt_rate_' . md5($bucket . '|' . $ip);
    }
}
