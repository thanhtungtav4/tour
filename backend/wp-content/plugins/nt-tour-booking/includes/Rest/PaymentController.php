<?php
namespace TourBooking\Rest;

use TourBooking\Helpers\Response;
use TourBooking\Security\WebhookSignature;
use TourBooking\Services\RateLimiter;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class PaymentController extends WP_REST_Controller
{
    protected string $namespace = 'nt-tour/v1';

    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/payments/(?P<booking_id>[A-Za-z0-9]+)/info', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'payment_info'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route($this->namespace, '/payments/(?P<booking_id>[A-Za-z0-9]+)/upload-proof', [
            'methods' => 'POST',
            'callback' => [$this, 'upload_proof'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route($this->namespace, '/payments/webhook', [
            'methods' => 'POST',
            'callback' => [$this, 'webhook'],
            'permission_callback' => [$this, 'webhook_permission_check'],
        ]);
    }

    public function webhook_permission_check(WP_REST_Request $request): bool
    {
        return WebhookSignature::verify($request);
    }

    public function payment_info(WP_REST_Request $request): WP_REST_Response
    {
        $booking_id = $request->get_param('booking_id');
        $service = new \TourBooking\Services\BookingService();
        $booking = $service->get_by_code($booking_id);

        if (!$booking) {
            return Response::resource_not_found('booking');
        }

        $settings = get_option('nt_tour_bank_info', []);
        $amount = (int) $booking->total_amount;
        $content = $booking->code;
        $acct = $settings['bank_account'] ?? '';
        $bank = $settings['bank_name'] ?? '';
        $bank_bin = $settings['bank_bin'] ?? '';
        $name = $settings['account_name'] ?? '';

        return Response::success([
            'booking_id' => $booking->code,
            'payment_method' => 'transfer',
            'total_amount' => $amount,
            'paid_amount' => 0,
            'remaining_amount' => $amount,
            'payment_status' => $booking->payment_status,
            'breakdown' => [
                'tour_price' => $amount,
                'services_total' => 0,
                'rental_total' => 0,
            ],
            'bank_transfer' => [
                'bank_name' => $bank,
                'bank_bin' => $bank_bin,
                'account_no' => $acct,
                'account_name' => $name,
                'amount' => $amount,
                'content' => $content,
                'qr_url' => "https://img.vietqr.io/image/{$bank}-{$acct}-compact2.png?amount={$amount}&addInfo={$content}&accountName=" . urlencode($name),
            ],
        ]);
    }

    public function upload_proof(WP_REST_Request $request): WP_REST_Response
    {
        if (!RateLimiter::hit('payment_upload_proof', 10, 60)) {
            return Response::rate_limited();
        }

        $booking_id = sanitize_text_field($request->get_param('booking_id'));
        $token = $request->get_header('X-NT-Token') ?: $request->get_param('token');
        if (empty($token)) {
            return Response::unauthorized('Cần magic token để tải bằng chứng thanh toán.');
        }

        $service = new \TourBooking\Services\BookingService();
        $booking = $service->get_by_code($booking_id);

        if (!$booking) {
            return Response::resource_not_found('booking');
        }

        $token_hash = \TourBooking\Helpers\TokenGenerator::hash($token);
        $token_repo = new \TourBooking\Repositories\AccessTokenRepository();
        $token_data = $token_repo->find_by_hash($token_hash);
        if (!$token_data || (int) $token_data['booking_id'] !== (int) $booking->id || ($token_data['expires_at'] && strtotime($token_data['expires_at']) < time())) {
            return Response::forbidden('Magic token không hợp lệ cho booking này.');
        }

        $transaction_ref = sanitize_text_field($request->get_param('transaction_ref') ?: '');
        $note = sanitize_textarea_field($request->get_param('note') ?: '');
        $proof_attachment_id = $this->handle_payment_proof_upload();
        if (is_wp_error($proof_attachment_id)) {
            return Response::error('upload_failed', $proof_attachment_id->get_error_message(), null, 422);
        }

        global $wpdb;
        $result = $wpdb->insert($wpdb->prefix . 'nt_payments', [
            'booking_id' => $booking->id,
            'amount' => $booking->total_amount,
            'method' => 'bank_transfer',
            'transaction_code' => $transaction_ref,
            'transfer_content' => $note,
            'status' => 'pending_review',
            'raw_payload' => wp_json_encode(['proof_attachment_id' => $proof_attachment_id]),
            'created_at' => current_time('mysql'),
        ], ['%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s']);

        if (!$result) {
            return Response::error('upload_failed', 'Không thể lưu bằng chứng thanh toán.');
        }

        $payment_id = $wpdb->insert_id;

        return Response::created([
            'payment_id' => $payment_id,
            'proof_attachment_id' => $proof_attachment_id,
            'status' => 'pending_review',
            'message' => 'Đã nhận bằng chứng thanh toán. Sẽ xác nhận trong 24 giờ.',
        ]);
    }

    private function handle_payment_proof_upload()
    {
        $files = $_FILES;
        if (empty($files['proof_image']) || empty($files['proof_image']['tmp_name'])) {
            return new \WP_Error('missing_file', 'Vui lòng tải ảnh bằng chứng chuyển khoản.');
        }

        $file = $files['proof_image'];
        if (!empty($file['error'])) {
            return new \WP_Error('upload_error', 'Tải ảnh bằng chứng không thành công.');
        }

        $type = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        if (empty($type['type']) || !in_array($type['type'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return new \WP_Error('invalid_file_type', 'Ảnh thanh toán chỉ hỗ trợ JPG, PNG hoặc WEBP.');
        }

        if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
            return new \WP_Error('file_too_large', 'Ảnh thanh toán không được vượt quá 5MB.');
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        return media_handle_upload('proof_image', 0);
    }

    public function webhook(WP_REST_Request $request): WP_REST_Response
    {
        $payload = $request->get_params();

        $description = sanitize_text_field($payload['description'] ?? '');
        $amount = (float) ($payload['amount'] ?? 0);

        if (empty($description) || $amount <= 0) {
            return Response::error('webhook_invalid', 'Webhook không hợp lệ.');
        }

        global $wpdb;
        $booking_code = $this->extract_booking_code($description);
        $this->log_webhook($payload, true, $booking_code, $amount, 'received', null);

        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}nt_bookings WHERE code = %s",
            $booking_code
        ), ARRAY_A);

        if (!$booking) {
            $this->log_webhook($payload, true, $booking_code, $amount, 'manual_review', 'Không tìm thấy booking để match.');
            return Response::error('webhook_invalid', 'Không tìm thấy booking để match.');
        }

        if ((float) $booking['total_amount'] > $amount) {
            $this->log_webhook($payload, true, $booking_code, $amount, 'manual_review', 'Số tiền chuyển khoản thấp hơn tổng booking.');
            return Response::error('payment_underpaid', 'Số tiền chuyển khoản thấp hơn tổng booking.', null, 422);
        }

        $service = new \TourBooking\Services\BookingService();
        $service->confirm_payment((int) $booking['id'], 'paid');

        return Response::success([
            'booking_id' => $booking['code'],
            'payment_status' => 'paid',
            'message' => 'Payment confirmed.',
        ]);
    }

    private function extract_booking_code(string $description): string
    {
        if (preg_match('/NTB[0-9A-Z]{8,20}/', strtoupper($description), $matches)) {
            return $matches[0];
        }
        return substr($description, 0, 20);
    }

    private function log_webhook(array $payload, bool $signature_valid, string $booking_code, float $amount, string $status, ?string $error): void
    {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'nt_webhook_logs', [
            'provider' => sanitize_text_field($payload['provider'] ?? 'bank'),
            'event_type' => sanitize_text_field($payload['event'] ?? 'bank_credit'),
            'signature_valid' => $signature_valid ? 1 : 0,
            'booking_code' => $booking_code,
            'amount' => $amount,
            'status' => $status,
            'error_message' => $error,
            'raw_payload' => wp_json_encode($payload),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at' => current_time('mysql'),
        ], ['%s', '%s', '%d', '%s', '%f', '%s', '%s', '%s', '%s', '%s']);
    }
}