<?php
/**
 * Webhook Signature Verifier
 *
 * Verifies bank webhook HMAC signatures.
 *
 * @since 0.1.0
 */

namespace TourBooking\Security;

use WP_REST_Request;

class WebhookSignature
{
    public static function verify(WP_REST_Request $request): bool
    {
        $secret = get_option('nt_tour_webhook_secret', '');
        if ($secret === '') {
            return false;
        }

        $signature = $request->get_header('X-NT-Signature') ?: $request->get_header('X-Webhook-Signature');
        $timestamp = $request->get_header('X-NT-Timestamp') ?: $request->get_header('X-Webhook-Timestamp');

        if ($signature === '' || $timestamp === '') {
            return false;
        }

        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $body = $request->get_body();
        $expected = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $body, $secret);

        if (strpos($signature, 'sha256=') !== 0) {
            $signature = 'sha256=' . $signature;
        }

        return hash_equals($expected, $signature);
    }
}
