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
});

function newtrip_checkin_settings_page() {
    if (!current_user_can('manage_options')) return;
    $current = get_option('newtrip_checkin_pin', '');
    ?>
    <div class="wrap">
        <h1>Mã PIN nhân viên check-in</h1>
        <form method="post" action="options.php">
            <?php settings_fields('newtrip_checkin_settings'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="newtrip_checkin_pin">Mã PIN</label></th>
                    <td>
                        <input name="newtrip_checkin_pin" id="newtrip_checkin_pin" type="text"
                               value="<?php echo esc_attr($current); ?>" class="regular-text" autocomplete="off" />
                        <p class="description">Đặt mã PIN cho nhân viên dùng tại trang /checkin. Trống = chưa cấu hình, không ai có thể đăng nhập.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Lưu mã PIN'); ?>
        </form>
    </div>
    <?php
}
