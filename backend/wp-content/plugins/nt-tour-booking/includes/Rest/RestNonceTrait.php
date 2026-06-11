<?php
/**
 * REST nonce verification helper.
 *
 * Shared by REST controllers that need WordPress cookie nonce protection for
 * authenticated write operations. Application Password requests do not carry a
 * WordPress REST nonce, so they are allowed when the user is already
 * authenticated by WordPress and the route capability check passed.
 *
 * @since 0.1.0
 */

namespace TourBooking\Rest;

use WP_REST_Request;

trait RestNonceTrait
{
    /**
     * Verify REST nonce for cookie-authenticated requests.
     *
     * @param WP_REST_Request $request REST request.
     * @return bool
     */
    protected function verify_nonce(WP_REST_Request $request): bool
    {
        if (!is_user_logged_in()) {
            return false;
        }

        $authorization = $request->get_header('authorization');
        if (!empty($authorization)) {
            return true;
        }

        $nonce = $request->get_header('X-WP-Nonce');
        if (empty($nonce)) {
            $nonce = $request->get_param('_wpnonce');
        }

        if (empty($nonce)) {
            return false;
        }

        return (bool) wp_verify_nonce($nonce, 'wp_rest');
    }
}
