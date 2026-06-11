<?php
/**
 * API Management REST Controller
 *
 * Admin endpoints for API clients and logs.
 *
 * @since 0.1.0
 */

namespace TourBooking\Rest;

use TourBooking\Helpers\Response;
use TourBooking\Services\ApiKeyService;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class ApiManagementController extends WP_REST_Controller
{
    use RestNonceTrait;

    protected string $namespace = 'nt-tour/v1';

    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/admin/api/clients', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'list_clients'],
            'permission_callback' => [$this, 'permission_check'],
        ]);

        register_rest_route($this->namespace, '/admin/api/clients', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'create_client'],
            'permission_callback' => [$this, 'write_permission_check'],
        ]);

        register_rest_route($this->namespace, '/admin/api/clients/(?P<id>\d+)/revoke', [
            'methods' => 'POST',
            'callback' => [$this, 'revoke_client'],
            'permission_callback' => [$this, 'write_permission_check'],
        ]);

        register_rest_route($this->namespace, '/admin/api/logs', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'list_logs'],
            'permission_callback' => [$this, 'permission_check'],
        ]);

        register_rest_route($this->namespace, '/admin/api/webhook-logs', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'list_webhook_logs'],
            'permission_callback' => [$this, 'permission_check'],
        ]);
    }

    public function permission_check(): bool
    {
        return current_user_can('nt_manage_api') || current_user_can('nt_manage_settings');
    }

    public function write_permission_check(WP_REST_Request $request): bool
    {
        return $this->permission_check() && $this->verify_nonce($request);
    }

    public function list_clients(WP_REST_Request $request): WP_REST_Response
    {
        return Response::success((new ApiKeyService())->list_clients());
    }

    public function create_client(WP_REST_Request $request): WP_REST_Response
    {
        $name = sanitize_text_field($request->get_param('name') ?: 'API Client');
        $scopes = $request->get_param('scopes') ?: ['tours', 'booking'];

        $result = (new ApiKeyService())->create_client([
            'name' => $name,
            'scopes' => $scopes,
            'allowed_ips' => $request->get_param('allowed_ips') ?: '',
            'allowed_origins' => $request->get_param('allowed_origins') ?: '',
            'expires_at' => $request->get_param('expires_at') ?: null,
        ]);

        return Response::created($result);
    }

    public function revoke_client(WP_REST_Request $request): WP_REST_Response
    {
        $ok = (new ApiKeyService())->revoke_client((int) $request->get_param('id'));
        return $ok ? Response::success(['status' => 'revoked']) : Response::error('revoke_failed', 'Unable to revoke API client.');
    }

    public function list_logs(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;
        $limit = min(max((int) ($request->get_param('per_page') ?: 50), 1), 200);
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}nt_api_request_logs ORDER BY id DESC LIMIT %d", $limit), ARRAY_A) ?: [];
        return Response::success($rows);
    }

    public function list_webhook_logs(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;
        $limit = min(max((int) ($request->get_param('per_page') ?: 50), 1), 200);
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}nt_webhook_logs ORDER BY id DESC LIMIT %d", $limit), ARRAY_A) ?: [];
        return Response::success($rows);
    }
}
