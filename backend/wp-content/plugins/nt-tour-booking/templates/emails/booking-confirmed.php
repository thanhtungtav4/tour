<?php
/**
 * Booking Confirmed Email Template
 *
 * Sent when a booking payment is confirmed.
 *
 * @var array $booking
 * @var array $departure
 * @var array $qr_codes
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
    <title>Xác nhận đặt tour thành công - <?php echo esc_html($booking['code']); ?></title>
</head>
<body style="margin: 0; padding: 20px; font-family: Arial, Helvetica, sans-serif; background-color: #f5f5f5;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div style="background-color: #10b981; color: #ffffff; padding: 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;"><?php echo esc_html($company_name ?: 'Tour Booking'); ?></h1>
            <p style="margin: 10px 0 0; opacity: 0.9;">Xác nhận đặt tour thành công!</p>
        </div>

        <!-- Content -->
        <div style="padding: 30px;">
            <p>Xin chào <strong><?php echo esc_html($booking['customer_name']); ?></strong>,</p>

            <p>Chúc mừng bạn! Booking của bạn đã được xác nhận thành công.</p>

            <!-- Booking Info -->
            <div style="background-color: #f8fafc; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h3 style="margin: 0 0 15px; color: #1e293b;">Thông tin booking</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #64748b;">Mã booking:</td>
                        <td style="padding: 8px 0; font-weight: bold; color: #1e293b;"><?php echo esc_html($booking['code']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #64748b;">Tour:</td>
                        <td style="padding: 8px 0; color: #1e293b;"><?php echo esc_html($departure['title'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #64748b;">Ngày khởi hành:</td>
                        <td style="padding: 8px 0; color: #1e293b;">
                            <?php echo esc_html($departure['start_date_formatted'] ?? ''); ?>
                            <?php if (!empty($departure['departure_time'])): ?>
                                lúc <?php echo esc_html($departure['departure_time_formatted'] ?? ''); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #64748b;">Số khách:</td>
                        <td style="padding: 8px 0; color: #1e293b;">
                            <?php echo esc_html($booking['total_people']); ?> người
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #64748b;">Đã thanh toán:</td>
                        <td style="padding: 8px 0; font-weight: bold; color: #10b981;">
                            <?php echo number_format($booking['deposit_amount'] ?: $booking['total_amount'], 0, ',', '.'); ?> VNĐ
                        </td>
                    </tr>
                </table>
            </div>

            <!-- QR Codes -->
            <?php if (!empty($qr_codes)): ?>
                <div style="margin: 30px 0;">
                    <h3 style="color: #1e293b;">Mã QR Check-in</h3>
                    <p>Vui lòng quét QR khi lên xe để check-in:</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                        <?php foreach ($qr_codes as $qr): ?>
                            <div style="text-align: center; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; min-width: 120px;">
                                <p style="margin: 0 0 10px; font-weight: bold;"><?php echo esc_html($qr['seat_code']); ?></p>
                                <img src="<?php echo esc_url($qr['qr_url']); ?>" alt="QR Code" style="width: 100px; height: 100px;">
                                <p style="margin: 10px 0 0; font-size: 12px; color: #64748b;"><?php echo esc_html($qr['passenger_name']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Reminder -->
            <?php if ($booking['passenger_info_status'] !== 'completed'): ?>
                <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
                    <p style="margin: 0; color: #92400e;">
                        <strong>Lưu ý:</strong> Booking của bạn vẫn còn thiếu thông tin khách.
                        Vui lòng bổ sung để việc check-in được thuận lợi.
                    </p>
                </div>
            <?php endif; ?>

            <p>Nếu bạn có thắc mắc, vui lòng liên hệ hotline: <strong><?php echo esc_html($hotline); ?></strong></p>

            <p>Chúc bạn có một chuyến đi vui vẻ!<br>Trân trọng,<br><?php echo esc_html($company_name ?: 'Tour Booking'); ?></p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f1f5f9; padding: 20px; text-align: center; color: #64748b; font-size: 12px;">
            <p style="margin: 0;">Email này được gửi tự động từ <?php echo esc_html($company_name ?: 'Tour Booking'); ?></p>
        </div>
    </div>
</body>
</html>
