<?php
/**
 * Settings Page - Check-in PIN
 */

// Add settings page under Settings menu
add_action('admin_menu', function () {
    add_options_page(
        'Check-in Nhân viên',
        'Check-in Nhân viên',
        'manage_options',
        'newtrip-checkin-settings',
        'newtrip_checkin_settings_page'
    );
});

add_action('admin_init', function () {
    register_setting('newtrip_checkin_settings', 'newtrip_checkin_pin', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);
    register_setting('newtrip_checkin_settings', 'newtrip_payment_webhook_url', [
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default' => '',
    ]);
    register_setting('newtrip_checkin_settings', 'newtrip_payment_webhook_token', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);
    register_setting('newtrip_checkin_settings', 'newtrip_bank_gateway_lookup_url', [
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default' => '',
    ]);
    register_setting('newtrip_checkin_settings', 'newtrip_bank_gateway_token', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);
});

function newtrip_checkin_settings_page() {
    if (!current_user_can('manage_options')) return;
    $current_pin = get_option('newtrip_checkin_pin', '');
    $webhook_url = get_option('newtrip_payment_webhook_url', '');
    $webhook_token = get_option('newtrip_payment_webhook_token', '');
    $gateway_url = get_option('newtrip_bank_gateway_lookup_url', '');
    $gateway_token = get_option('newtrip_bank_gateway_token', '');
    if (empty($webhook_token)) {
        $webhook_token = wp_generate_password(24, false);
        update_option('newtrip_payment_webhook_token', $webhook_token);
    }
    ?>
    <div class="wrap">
        <h1>Cấu hình hệ thống NewTrip</h1>
        <form method="post" action="options.php">
            <?php settings_fields('newtrip_checkin_settings'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="newtrip_checkin_pin">Mã PIN check-in</label></th>
                    <td>
                        <input name="newtrip_checkin_pin" id="newtrip_checkin_pin" type="text"
                               value="<?php echo esc_attr($current_pin); ?>" class="regular-text" autocomplete="off" />
                        <p class="description">Đặt mã PIN cho nhân viên dùng tại trang /checkin. Trống = chưa cấu hình, không ai có thể đăng nhập.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="newtrip_payment_webhook_url">Webhook báo có (Outgoing)</label></th>
                    <td>
                        <input name="newtrip_payment_webhook_url" id="newtrip_payment_webhook_url" type="url"
                               value="<?php echo esc_url($webhook_url); ?>" class="large-text" placeholder="https://example.com/webhook" style="width: 25em;" />
                        <p class="description">URL Webhook của bạn để hệ thống nhận thông tin khi khách nhấn nút "Báo đã thanh toán".</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="newtrip_payment_webhook_token">Token bảo mật Webhook (Incoming)</label></th>
                    <td>
                        <input name="newtrip_payment_webhook_token" id="newtrip_payment_webhook_token" type="text"
                               value="<?php echo esc_attr($webhook_token); ?>" class="regular-text" style="width: 25em;" />
                        <p class="description">Token để xác thực các yêu cầu báo có từ hệ thống bên ngoài gửi tới NewTrip. Hãy đính kèm dưới dạng header <code>X-Webhook-Token</code> hoặc tham số <code>?token=...</code> trong URL gọi API.</p>
                        <p class="description">Đường dẫn nhận báo có: <code><?php echo esc_url(home_url('/wp-json/newtrip/v1/payment/webhook')); ?></code></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="newtrip_bank_gateway_lookup_url">Bank Gateway Lookup URL (Laravel API)</label></th>
                    <td>
                        <input name="newtrip_bank_gateway_lookup_url" id="newtrip_bank_gateway_lookup_url" type="url"
                               value="<?php echo esc_url($gateway_url); ?>" class="large-text" placeholder="https://notification.nttung.dev/api/transactions/lookup" style="width: 25em;" />
                        <p class="description">Đường dẫn API tra soát giao dịch trên Bank Notification Gateway.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="newtrip_bank_gateway_token">Bank Gateway Token (Sanctum)</label></th>
                    <td>
                        <input name="newtrip_bank_gateway_token" id="newtrip_bank_gateway_token" type="text"
                               value="<?php echo esc_attr($gateway_token); ?>" class="large-text" style="width: 25em;" />
                        <p class="description">Token Sanctum dùng để gọi API tra soát giao dịch.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Lưu cấu hình'); ?>
        </form>
    </div>
    <?php
}
