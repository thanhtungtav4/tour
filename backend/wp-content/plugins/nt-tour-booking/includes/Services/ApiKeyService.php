<?php
/**
 * API Key Service
 *
 * Creates and validates scoped API clients for external integrations.
 *
 * @since 0.1.0
 */

namespace TourBooking\Services;

use TourBooking\Helpers\TokenGenerator;

class ApiKeyService
{
    public function create_client(array $data): array
    {
        global $wpdb;

        $public_key = 'nt_' . wp_generate_password(32, false, false);
        $secret = TokenGenerator::generate(64);
        $secret_hash = TokenGenerator::hash($secret);

        $scopes = $data['scopes'] ?? [];
        if (!is_array($scopes)) {
            $scopes = array_filter(array_map('trim', explode(',', (string) $scopes)));
        }

        $wpdb->insert(
            $wpdb->prefix . 'nt_api_clients',
            [
                'name' => sanitize_text_field($data['name'] ?? 'API Client'),
                'public_key' => $public_key,
                'secret_hash' => $secret_hash,
                'scopes' => wp_json_encode(array_values($scopes)),
                'allowed_ips' => sanitize_text_field($data['allowed_ips'] ?? ''),
                'allowed_origins' => sanitize_text_field($data['allowed_origins'] ?? ''),
                'status' => 'active',
                'expires_at' => !empty($data['expires_at']) ? sanitize_text_field($data['expires_at']) : null,
                'created_by' => get_current_user_id() ?: null,
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s']
        );

        return [
            'id' => (int) $wpdb->insert_id,
            'public_key' => $public_key,
            'secret' => $secret,
            'scopes' => array_values($scopes),
        ];
    }

    public function validate_request(string $scope = ''): ?array
    {
        $public_key = isset($_SERVER['HTTP_X_NT_API_KEY']) ? sanitize_text_field($_SERVER['HTTP_X_NT_API_KEY']) : '';
        $signature = isset($_SERVER['HTTP_X_NT_SIGNATURE']) ? sanitize_text_field($_SERVER['HTTP_X_NT_SIGNATURE']) : '';
        $timestamp = isset($_SERVER['HTTP_X_NT_TIMESTAMP']) ? sanitize_text_field($_SERVER['HTTP_X_NT_TIMESTAMP']) : '';

        if ($public_key === '' || $signature === '' || $timestamp === '') {
            return null;
        }

        if (abs(time() - (int) $timestamp) > 300) {
            return null;
        }

        global $wpdb;
        $client = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}nt_api_clients WHERE public_key = %s AND status = 'active'",
                $public_key
            ),
            ARRAY_A
        );

        if (!$client) {
            return null;
        }

        if (!empty($client['expires_at']) && strtotime($client['expires_at']) < time()) {
            return null;
        }

        $scopes = json_decode($client['scopes'] ?: '[]', true) ?: [];
        if ($scope !== '' && !in_array($scope, $scopes, true) && !in_array('*', $scopes, true)) {
            return null;
        }

        $body = file_get_contents('php://input') ?: '';
        $expected = hash_hmac('sha256', $timestamp . '.' . $body, $client['secret_hash']);
        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $wpdb->update(
            $wpdb->prefix . 'nt_api_clients',
            ['last_used_at' => current_time('mysql')],
            ['id' => (int) $client['id']],
            ['%s'],
            ['%d']
        );

        return $client;
    }

    public function list_clients(): array
    {
        global $wpdb;
        return $wpdb->get_results("SELECT id, name, public_key, scopes, allowed_ips, allowed_origins, status, expires_at, last_used_at, created_at FROM {$wpdb->prefix}nt_api_clients ORDER BY id DESC", ARRAY_A) ?: [];
    }

    public function revoke_client(int $id): bool
    {
        global $wpdb;
        return $wpdb->update($wpdb->prefix . 'nt_api_clients', ['status' => 'revoked'], ['id' => $id], ['%s'], ['%d']) !== false;
    }
}
