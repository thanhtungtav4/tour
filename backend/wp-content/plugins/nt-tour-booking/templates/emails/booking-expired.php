<?php
/**
 * Booking Expired Email Template
 *
 * Sent when a booking's seat hold has expired.
 *
 * @var array $booking
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
    <title>Thông báo hết hạn giữ chỗ - <?php echo esc_html($booking['code'] ?? ''); ?></title>
</head>
<body style="margin: 0; padding: 20px; font-family: Arial, Helvetica, sans-serif; background-color: #f5f5f5;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div style="background-color: #dc2626; color: #ffffff; padding: 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;"><?php echo esc_html($company_name ?: 'Tour Booking'); ?></h1>
            <p style="margin: 10px 0 0; opacity: 0.9;">Thông báo hết hạn giữ chỗ</p>
        </div>

        <!-- Content -->
        <div style="padding: 30px;">
            <p>Xin chào <strong><?php echo esc_html($booking['customer_name'] ?? ''); ?></strong>,</p>

            <p>Rất tiếc, thời gian giữ chỗ của bạn đã hết. Các ghế đã được mở lại cho khách hàng khác.</p>

            <!-- Booking Info -->
            <div style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <h3 style="margin: 0 0 10px; color: #991b1b;">Thông tin Booking đã hết hạn</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #991b1b;">Mã booking:</td>
                        <td style="padding: 8px 0; font-weight: bold; color: #991b1b;"><?php echo esc_html($booking['code'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #991b1b;">Ngày khởi hành:</td>
                        <td style="padding: 8px 0; color: #991b1b;"><?php echo esc_html($booking['departure_date'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #991b1b;">Số khách:</td>
                        <td style="padding: 8px 0; color: #991b1b;">
                            <?php echo esc_html($booking['adult_count'] ?? 0); ?> người lớn
                            <?php if (!empty($booking['child_count'])): ?>
                                / <?php echo esc_html($booking['child_count']); ?> trẻ em
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Re-book CTA -->
            <div style="text-align: center; margin: 30px 0;">
                <p>Bạn vẫn có thể đặt lại tour nếu còn ghế trống:</p>
                <a href="<?php echo esc_url(home_url()); ?>" style="display: inline-block; background-color: #dc2626; color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                    Đặt lại tour
                </a>
            </div>

            <p>Nếu bạn đã thanh toán, vui lòng liên hệ hotline để được hỗ trợ: <strong><?php echo esc_html($hotline ?: ''); ?></strong></p>

            <p>Xin cảm ơn,<br><?php echo esc_html($company_name ?: 'Tour Booking'); ?></p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f1f5f9; padding: 20px; text-align: center; color: #64748b; font-size: 12px;">
            <p style="margin: 0;">Email này được gửi tự động từ <?php echo esc_html($company_name ?: 'Tour Booking'); ?></p>
        </div>
    </div>
</body>
</html>