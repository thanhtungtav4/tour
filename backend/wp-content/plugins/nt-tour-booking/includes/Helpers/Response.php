<?php
/**
 * REST API Response Helper
 *
 * Standardized API response format matching NT Tour Booking API spec.
 *
 * @since 0.1.0
 */

namespace TourBooking\Helpers;

class Response
{
    /**
     * Success response
     *
     * @param mixed $data Response data
     * @param array $meta Meta/pagination data
     * @param int $status_code HTTP status code
     * @return \WP_REST_Response
     */
    public static function success($data = null, array $meta = [], int $status_code = 200): \WP_REST_Response
    {
        $response = ['success' => true, 'data' => $data];
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }
        return new \WP_REST_Response($response, $status_code);
    }

    /**
     * Error response
     *
     * @param string $code Error code
     * @param string $message Error message
     * @param mixed $data Additional error data
     * @param int $status_code HTTP status code
     * @return \WP_REST_Response
     */
    public static function error(string $code, string $message, $data = null, int $status_code = 400): \WP_REST_Response
    {
        $error = [
            'code' => $code,
            'message' => $message,
        ];
        if ($data !== null) {
            $error['data'] = $data;
        }
        return new \WP_REST_Response(['success' => false, 'error' => $error], $status_code);
    }

    /**
     * Validation error response
     *
     * @param array $field_errors Field-specific errors [field => message]
     * @param string $message General message
     * @return \WP_REST_Response
     */
    public static function validation_error(array $field_errors, string $message = 'Dữ liệu không hợp lệ.'): \WP_REST_Response
    {
        return self::error('validation_failed', $message, ['fields' => $field_errors], 422);
    }

    /**
     * Not found response
     *
     * @param string $code Error code (e.g. tour_not_found)
     * @param string $message Error message
     * @return \WP_REST_Response
     */
    public static function not_found(string $code, string $message): \WP_REST_Response
    {
        return self::error($code, $message, ['status' => 404], 404);
    }

    /**
     * Quick not found for common resources
     *
     * @param string $resource Resource type
     * @return \WP_REST_Response
     */
    public static function resource_not_found(string $resource): \WP_REST_Response
    {
        $codes = [
            'tour' => 'tour_not_found',
            'booking' => 'booking_not_found',
            'departure' => 'departure_not_found',
            'passenger' => 'passenger_not_found',
            'payment' => 'payment_not_found',
            'pickup_point' => 'pickup_point_not_found',
        ];
        $code = $codes[$resource] ?? 'not_found';
        $labels = [
            'tour' => 'Không tìm thấy tour.',
            'booking' => 'Không tìm thấy booking với mã đã cung cấp.',
            'departure' => 'Không tìm thấy lịch khởi hành.',
            'passenger' => 'Không tìm thấy hành khách.',
            'payment' => 'Không tìm thấy payment.',
            'pickup_point' => 'Không tìm thấy điểm đón.',
        ];
        $message = $labels[$resource] ?? "{$resource} not found";
        return self::error($code, $message, ['status' => 404], 404);
    }

    /**
     * Conflict response
     *
     * @param string $code Error code
     * @param string $message Error message
     * @param mixed $data
     * @return \WP_REST_Response
     */
    public static function conflict(string $code, string $message, $data = null): \WP_REST_Response
    {
        return self::error($code, $message, $data, 409);
    }

    /**
     * Gone response (expired)
     *
     * @param string $code Error code
     * @param string $message Error message
     * @return \WP_REST_Response
     */
    public static function gone(string $code, string $message): \WP_REST_Response
    {
        return self::error($code, $message, null, 410);
    }

    /**
     * Unauthorized response
     *
     * @param string $message Error message
     * @return \WP_REST_Response
     */
    public static function unauthorized(string $message = 'Không có quyền truy cập.'): \WP_REST_Response
    {
        return self::error('unauthorized', $message, null, 401);
    }

    /**
     * Forbidden response
     *
     * @param string $message Error message
     * @return \WP_REST_Response
     */
    public static function forbidden(string $message = 'Không có permission.'): \WP_REST_Response
    {
        return self::error('forbidden', $message, null, 403);
    }

    /**
     * Rate limited response
     *
     * @param string $message
     * @return \WP_REST_Response
     */
    public static function rate_limited(string $message = 'Vượt quá rate limit.'): \WP_REST_Response
    {
        return self::error('rate_limited', $message, null, 429);
    }

    /**
     * Paginated success response
     *
     * @param array $items Items for current page
     * @param int $total Total items
     * @param int $page Current page
     * @param int $per_page Items per page
     * @return \WP_REST_Response
     */
    public static function paginated(array $items, int $total, int $page, int $per_page): \WP_REST_Response
    {
        $total_pages = $per_page > 0 ? (int) ceil($total / $per_page) : 0;
        return self::success($items, [
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => $total_pages,
            'has_next' => $page < $total_pages,
            'has_prev' => $page > 1,
        ]);
    }

    /**
     * Created success response
     *
     * @param mixed $data
     * @return \WP_REST_Response
     */
    public static function created($data = null): \WP_REST_Response
    {
        return self::success($data, [], 201);
    }
}