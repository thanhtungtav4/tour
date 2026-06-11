<?php
/**
 * Email Service
 *
 * Handles email sending with templates.
 *
 * @since 0.1.0
 */

namespace TourBooking\Services;

use TourBooking\Admin\SettingsPage;

class EmailService
{
    /**
     * Send email
     *
     * @param string $to Recipient email
     * @param string $subject Subject
     * @param string $message Message body
     * @param array $headers Additional headers
     * @return bool
     */
    public function send(string $to, string $subject, string $message, array $headers = []): bool
    {
        $from_name = SettingsPage::get('nt_tour_email_from_name', '');
        $from_email = SettingsPage::get('nt_tour_email_from_address', '');

        if ($from_name && $from_email) {
            $headers[] = sprintf('From: %s <%s>', $from_name, $from_email);
        }

        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        return wp_mail($to, $subject, $message, $headers);
    }

    /**
     * Send booking pending email
     *
     * @param array $booking
     * @param string $magic_link
     * @return bool
     */
    public function send_booking_pending(array $booking, string $magic_link): bool
    {
        $to = $booking['customer_email'] ?? '';
        if (empty($to)) {
            return false;
        }

        $departure = $booking['departure'] ?? [];
        $subject = sprintf('[%s] Xác nhận giữ chỗ tour %s', $booking['code'], $departure['title'] ?? '');

        $message = $this->render_template('booking-pending', [
            'booking' => $booking,
            'departure' => $departure,
            'magic_link' => $magic_link,
            'company_name' => SettingsPage::get('nt_tour_company_name'),
            'hotline' => SettingsPage::get('nt_tour_hotline'),
        ]);

        return $this->send($to, $subject, $message);
    }

    /**
     * Send booking confirmed email
     *
     * @param array $booking
     * @param array $qr_codes
     * @return bool
     */
    public function send_booking_confirmed(array $booking, array $qr_codes = []): bool
    {
        $to = $booking['customer_email'] ?? '';
        if (empty($to)) {
            return false;
        }

        $departure = $booking['departure'] ?? [];
        $subject = sprintf('[%s] Xác nhận đặt tour thành công', $booking['code']);

        $message = $this->render_template('booking-confirmed', [
            'booking' => $booking,
            'departure' => $departure,
            'qr_codes' => $qr_codes,
            'company_name' => SettingsPage::get('nt_tour_company_name'),
            'hotline' => SettingsPage::get('nt_tour_hotline'),
        ]);

        return $this->send($to, $subject, $message);
    }

    /**
     * Send booking expired email
     *
     * @param array $booking
     * @return bool
     */
    public function send_booking_expired(array $booking): bool
    {
        $to = $booking['customer_email'] ?? '';
        if (empty($to)) {
            return false;
        }

        $subject = sprintf('[%s] Thông báo hết hạn giữ chỗ', $booking['code']);

        $message = $this->render_template('booking-expired', [
            'booking' => $booking,
            'company_name' => SettingsPage::get('nt_tour_company_name'),
            'hotline' => SettingsPage::get('nt_tour_hotline'),
        ]);

        return $this->send($to, $subject, $message);
    }

    /**
     * Send passenger info reminder
     *
     * @param array $booking
     * @param string $magic_link
     * @return bool
     */
    public function send_passenger_reminder(array $booking, string $magic_link): bool
    {
        $to = $booking['customer_email'] ?? '';
        if (empty($to)) {
            return false;
        }

        $subject = sprintf('[%s] Nhắc nhở: Cần bổ sung thông tin khách', $booking['code']);

        $message = $this->render_template('passenger-reminder', [
            'booking' => $booking,
            'magic_link' => $magic_link,
            'company_name' => SettingsPage::get('nt_tour_company_name'),
            'hotline' => SettingsPage::get('nt_tour_hotline'),
        ]);

        return $this->send($to, $subject, $message);
    }

    /**
     * Render email template
     *
     * @param string $template Template name
     * @param array $data Template data
     * @return string
     */
    protected function render_template(string $template, array $data): string
    {
        $template_path = NT_TOUR_BOOKING_PLUGIN_DIR . 'templates/emails/' . $template . '.php';

        if (file_exists($template_path)) {
            ob_start();
            extract($data);
            include $template_path;
            return ob_get_clean();
        }

        // Fallback: simple text email
        return $this->render_simple_template($template, $data);
    }

    /**
     * Render simple text template (fallback)
     *
     * @param string $template
     * @param array $data
     * @return string
     */
    protected function render_simple_template(string $template, array $data): string
    {
        $booking = $data['booking'] ?? [];
        $departure = $data['departure'] ?? [];

        $html = '<html><body style="font-family: Arial, sans-serif;">';
        $html .= '<h2>' . esc_html($data['company_name'] ?? 'Tour Booking') . '</h2>';

        switch ($template) {
            case 'booking-pending':
                $html .= '<p>Xin chào ' . esc_html($booking['customer_name'] ?? '') . ',</p>';
                $html .= '<p>Cảm ơn bạn đã đặt tour! Chúng tôi đã giữ chỗ cho bạn.</p>';
                $html .= '<p><strong>Mã booking:</strong> ' . esc_html($booking['code'] ?? '') . '</p>';
                $html .= '<p><strong>Tour:</strong> ' . esc_html($departure['title'] ?? '') . '</p>';
                $html .= '<p><strong>Ngày khởi hành:</strong> ' . esc_html($departure['start_date'] ?? '') . '</p>';
                $html .= '<p>Vui lòng bổ sung thông tin khách trong vòng 2 giờ để giữ chỗ.</p>';
                $html .= '<p><a href="' . esc_url($data['magic_link'] ?? '') . '">Bổ sung thông tin khách</a></p>';
                break;

            case 'booking-confirmed':
                $html .= '<p>Xin chào ' . esc_html($booking['customer_name'] ?? '') . ',</p>';
                $html .= '<p>Booking của bạn đã được xác nhận!</p>';
                $html .= '<p><strong>Mã booking:</strong> ' . esc_html($booking['code'] ?? '') . '</p>';
                $html .= '<p><strong>Tour:</strong> ' . esc_html($departure['title'] ?? '') . '</p>';
                $html .= '<p><strong>Ngày khởi hành:</strong> ' . esc_html($departure['start_date'] ?? '') . '</p>';
                break;

            case 'booking-expired':
                $html .= '<p>Xin chào ' . esc_html($booking['customer_name'] ?? '') . ',</p>';
                $html .= '<p>Rất tiếc, thời gian giữ chỗ của bạn đã hết.</p>';
                $html .= '<p>Ghế đã được mở lại cho khách khác.</p>';
                $html .= '<p>Nếu bạn vẫn muốn đặt tour, vui lòng đặt lại.</p>';
                break;

            case 'passenger-reminder':
                $html .= '<p>Xin chào ' . esc_html($booking['customer_name'] ?? '') . ',</p>';
                $html .= '<p>Booking của bạn sắp khởi hành nhưng vẫn còn thiếu thông tin khách.</p>';
                $html .= '<p>Vui lòng bổ sung thông tin ngay.</p>';
                $html .= '<p><a href="' . esc_url($data['magic_link'] ?? '') . '">Bổ sung thông tin</a></p>';
                break;
        }

        $html .= '<p>Hotline: ' . esc_html($data['hotline'] ?? '') . '</p>';
        $html .= '</body></html>';

        return $html;
    }
}
