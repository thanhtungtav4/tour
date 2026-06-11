<?php
/**
 * Passenger Reminder Email Template
 *
 * Reminds customers to complete passenger information.
 *
 * @var array $booking
 * @var array $passengers
 * @var string $magic_link
 * @var string $company_name
 * @var string $hotline
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhắc nhở bổ sung thông tin khách - <?php echo esc_html($booking['code'] ?? ''); ?></title>
</head>
<body style="margin: 0; padding: 20px; font-family: Arial, Helvetica, sans-serif; background-color: #f5f5f5;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div style="background-color: #f59e0b; color: #ffffff; padding: 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;"><?php echo esc_html($company_name ?: 'Tour Booking'); ?></h1>
            <p style="margin: 10px 0 0; opacity: 0.9;">Nhắc nhở bổ sung thông tin hành khách</p>
        </div>

        <!-- Content -->
        <div style="padding: 30px;">
            <p>Xin chào <strong><?php echo esc_html($booking['customer_name'] ?? ''); ?></strong>,</p>

            <p>Booking <strong><?php echo esc_html($booking['code'] ?? ''); ?></strong> của bạn sắp khởi hành nhưng vẫn còn thiếu thông tin hành khách. Vui lòng bổ sung đầy đủ thông tin để chúng tôi có thể phục vụ tốt nhất.</p>

            <!-- Booking Info -->
            <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <h3 style="margin: 0 0 10px; color: #92400e;">Thông tin booking</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; color: #92400e;">Tour:</td>
                        <td style="padding: 6px 0; font-weight: bold; color: #92400e;"><?php echo esc_html($booking['tour_name'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #92400e;">Ngày KH:</td>
                        <td style="padding: 6px 0; font-weight: bold; color: #92400e;"><?php echo esc_html($booking['departure_date'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #92400e;">Tổng khách:</td>
                        <td style="padding: 6px 0; font-weight: bold; color: #92400e;"><?php echo esc_html($booking['total_people'] ?? 0); ?> người</td>
                    </tr>
                </table>
            </div>

            <!-- Missing Passengers -->
            <?php if (!empty($passengers) && is_array($passengers)): ?>
            <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <h3 style="margin: 0 0 10px; color: #991b1b;">Thông tin cần bổ sung</h3>
                <ul style="margin: 0; padding-left: 20px; color: #991b1b;">
                    <?php foreach ($passengers as $passenger): ?>
                        <?php if (!empty($passenger['missing_fields'])): ?>
                        <li style="margin-bottom: 8px;">
                            <strong><?php echo esc_html($passenger['name'] ?? 'Khách ' . ($passenger['index'] ?? '')); ?></strong> -
                            <?php echo esc_html(implode(', ', $passenger['missing_fields'])); ?>
                        </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- CTA -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="<?php echo esc_url($magic_link ?? '#'); ?>" style="display: inline-block; background-color: #f59e0b; color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                    Bổ sung thông tin ngay
                </a>
            </div>

            <p style="color: #f59e0b; font-size: 14px;">
                <strong>Lưu ý:</strong> Nếu thông tin không được bổ sung đầy đủ trước ngày khởi hành, booking có thể bị hủy hoặc hành khách không được lên xe.
            </p>

            <p>Nếu bạn cần hỗ trợ, vui lòng liên hệ hotline: <strong><?php echo esc_html($hotline ?: ''); ?></strong></p>

            <p>Trân trọng,<br><?php echo esc_html($company_name ?: 'Tour Booking'); ?></p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f1f5f9; padding: 20px; text-align: center; color: #64748b; font-size: 12px;">
            <p style="margin: 0;">Email này được gửi tự động từ <?php echo esc_html($company_name ?: 'Tour Booking'); ?></p>
            <p style="margin: 5px 0 0;">Vui lòng không reply email này.</p>
        </div>
    </div>
</body>
</html>