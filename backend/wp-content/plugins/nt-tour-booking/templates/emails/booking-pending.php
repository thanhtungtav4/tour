<?php
/**
 * Booking Pending Email Template
 *
 * Sent when a booking is created and seats are held.
 *
 * @var array $booking
 * @var array $departure
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
    <title>Xác nhận giữ chỗ - <?php echo esc_html($booking['code']); ?></title>
</head>
<body style="margin: 0; padding: 20px; font-family: Arial, Helvetica, sans-serif; background-color: #f5f5f5;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div style="background-color: #2563eb; color: #ffffff; padding: 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;"><?php echo esc_html($company_name ?: 'Tour Booking'); ?></h1>
            <p style="margin: 10px 0 0; opacity: 0.9;">Xác nhận giữ chỗ</p>
        </div>

        <!-- Content -->
        <div style="padding: 30px;">
            <p>Xin chào <strong><?php echo esc_html($booking['customer_name']); ?></strong>,</p>

            <p>Cảm ơn bạn đã đặt tour! Chúng tôi đã tiếp nhận yêu cầu và đang giữ chỗ cho bạn.</p>

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
                        <td style="padding: 8px 0; color: #1e293b;"><?php echo esc_html($departure['start_date_formatted'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #64748b;">Số khách:</td>
                        <td style="padding: 8px 0; color: #1e293b;">
                            <?php echo esc_html($booking['adult_count']); ?> người lớn
                            <?php if ($booking['child_count'] > 0): ?> / <?php echo esc_html($booking['child_count']); ?> trẻ em<?php endif; ?>
                            <?php if ($booking['infant_count'] > 0): ?> / <?php echo esc_html($booking['infant_count']); ?> em bé<?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #64748b;">Tổng tiền:</td>
                        <td style="padding: 8px 0; font-weight: bold; color: #dc2626;">
                            <?php echo number_format($booking['total_amount'], 0, ',', '.'); ?> VNĐ
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Seat Hold Warning -->
            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0; color: #92400e;">
                    <strong>Lưu ý:</strong> Ghế của bạn đang được giữ tạm trong <strong>2 giờ</strong>.
                    Vui lòng thanh toán trước khi hết thời gian giữ chỗ để xác nhận booking.
                </p>
            </div>

            <!-- Payment Info -->
            <div style="background-color: #ecfdf5; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <h3 style="margin: 0 0 10px; color: #065f46;">Thông tin chuyển khoản</h3>
                <p style="margin: 0; color: #065f46;">
                    Ngân hàng: <strong><?php echo esc_html(SettingsPage::get('nt_tour_bank_name')); ?></strong><br>
                    Tên tài khoản: <strong><?php echo esc_html(SettingsPage::get('nt_tour_bank_account_name')); ?></strong><br>
                    Số tài khoản: <strong><?php echo esc_html(SettingsPage::get('nt_tour_bank_account_number')); ?></strong><br>
                    Nội dung: <strong style="color: #dc2626;"><?php echo esc_html(str_replace('{booking_code}', $booking['code'], SettingsPage::get('nt_tour_transfer_content_template', 'NT{booking_code}'))); ?></strong>
                </p>
            </div>

            <!-- Complete Passenger Info -->
            <div style="text-align: center; margin: 30px 0;">
                <p>Vui lòng bổ sung thông tin chi tiết của từng khách để hoàn tất booking:</p>
                <a href="<?php echo esc_url($magic_link); ?>" style="display: inline-block; background-color: #2563eb; color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                    Bổ sung thông tin khách
                </a>
            </div>

            <p>Nếu bạn có thắc mắc, vui lòng liên hệ hotline: <strong><?php echo esc_html($hotline); ?></strong></p>

            <p>Trân trọng,<br><?php echo esc_html($company_name ?: 'Tour Booking'); ?></p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f1f5f9; padding: 20px; text-align: center; color: #64748b; font-size: 12px;">
            <p style="margin: 0;">Email này được gửi tự động từ <?php echo esc_html($company_name ?: 'Tour Booking'); ?></p>
        </div>
    </div>
</body>
</html>
