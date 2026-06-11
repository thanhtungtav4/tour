<?php
/**
 * Magic passenger form frontend template.
 */
$token = isset($_GET['token']) ? sanitize_text_field(wp_unslash($_GET['token'])) : '';
?>
<div class="nt-tour-magic-passenger-form" data-token="<?php echo esc_attr($token); ?>">
    <h3><?php echo esc_html__('Bổ sung thông tin khách', 'nt-tour-booking'); ?></h3>
    <p><?php echo esc_html__('Vui lòng nhập thông tin khách, bao gồm số điện thoại, email và CCCD nếu được yêu cầu. Ảnh CCCD chỉ hỗ trợ JPG, PNG hoặc WEBP, tối đa 5MB.', 'nt-tour-booking'); ?></p>
    <div class="nt-tour-magic-passenger-form__content" aria-live="polite"></div>
</div>
