<?php
/**
 * Dashboard Page
 *
 * Admin dashboard with statistics and quick actions.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin\Pages;

class DashboardPage extends BasePage
{
    /**
     * Initialize dashboard
     */
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'update_menu']);
    }

    /**
     * Update dashboard menu
     */
    public static function update_menu(): void
    {
        // Remove old dashboard and add new one
        remove_submenu_page('nt-tour-booking', 'nt-tour-booking');
        add_submenu_page(
            'nt-tour-booking',
            __('Dashboard', 'nt-tour-booking'),
            __('Dashboard', 'nt-tour-booking'),
            'nt_view_dashboard',
            'nt-tour-dashboard',
            [self::class, 'render']
        );
    }

    /**
     * Render dashboard page
     */
    public static function render(): void
    {
        $instance = new self();
        $instance->enqueue_assets();

        $stats = $instance->get_stats();
        $recent_bookings = $instance->get_recent_bookings();
        $today_departures = $instance->get_today_departures();
        $pending_payments = $instance->get_pending_payments();

        ?>
        <div class="wrap nt-tour-wrap">
            <?php
            $instance->render_header(
                __('Tour Booking Dashboard', 'nt-tour-booking'),
                __('Tổng quan hoạt động đặt tour', 'nt-tour-booking'),
                [
                    [
                        'label' => __('Tạo Booking', 'nt-tour-booking'),
                        'url' => admin_url('admin.php?page=nt-tour-bookings&action=new'),
                        'icon' => 'plus',
                        'class' => 'nt-btn-primary',
                    ],
                    [
                        'label' => __('Xem Bookings', 'nt-tour-booking'),
                        'url' => admin_url('admin.php?page=nt-tour-bookings'),
                        'icon' => 'list',
                        'class' => 'nt-btn-secondary',
                    ],
                ]
            );
            ?>

            <!-- Stats Cards -->
            <div class="nt-stats-grid">
                <?php
                $instance->render_stat_card(
                    __('Booking hôm nay', 'nt-tour-booking'),
                    number_format($stats['bookings_today']),
                    'calendar-check',
                    'blue'
                );
                $instance->render_stat_card(
                    __('Booking tuần này', 'nt-tour-booking'),
                    number_format($stats['bookings_week']),
                    'calendar',
                    'indigo'
                );
                $instance->render_stat_card(
                    __('Doanh thu tháng', 'nt-tour-booking'),
                    $instance->format_currency($stats['revenue_month']),
                    'trending-up',
                    'green'
                );
                $instance->render_stat_card(
                    __('Chờ thanh toán', 'nt-tour-booking'),
                    number_format($stats['pending_payments']),
                    'clock',
                    'yellow'
                );
                ?>
            </div>

            <!-- Main Content Grid -->
            <div class="nt-dashboard-grid mt-6">
                <!-- Recent Bookings -->
                <div class="nt-card">
                    <div class="nt-card__header">
                        <h3 class="nt-card__title">
                            <i data-lucide="file-text" class="w-5 h-5 mr-2"></i>
                            <?php _e('Booking gần đây', 'nt-tour-booking'); ?>
                        </h3>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=nt-tour-bookings')); ?>" class="nt-card__link">
                            <?php _e('Xem tất cả', 'nt-tour-booking'); ?>
                        </a>
                    </div>
                    <div class="nt-card__body">
                        <?php if (empty($recent_bookings)): ?>
                            <p class="text-gray-500 text-center py-4"><?php _e('Chưa có booking nào', 'nt-tour-booking'); ?></p>
                        <?php else: ?>
                            <div class="nt-list">
                                <?php foreach ($recent_bookings as $booking): ?>
                                    <div class="nt-list__item">
                                        <div class="nt-list__content">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <span class="nt-list__title"><?php echo esc_html($booking['code']); ?></span>
                                                    <span class="nt-list__subtitle"><?php echo esc_html($booking['customer_name']); ?></span>
                                                </div>
                                                <div class="text-right">
                                                    <span class="nt-list__value"><?php echo $instance->format_currency($booking['total_amount']); ?></span>
                                                    <span class="nt-badge <?php echo esc_attr($instance->get_status_class($booking['booking_status'])); ?>">
                                                        <?php echo esc_html($booking['booking_status_label']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Today's Departures -->
                <div class="nt-card">
                    <div class="nt-card__header">
                        <h3 class="nt-card__title">
                            <i data-lucide="bus" class="w-5 h-5 mr-2"></i>
                            <?php _e('Khởi hành hôm nay', 'nt-tour-booking'); ?>
                        </h3>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=nt-tour-departures')); ?>" class="nt-card__link">
                            <?php _e('Quản lý', 'nt-tour-booking'); ?>
                        </a>
                    </div>
                    <div class="nt-card__body">
                        <?php if (empty($today_departures)): ?>
                            <p class="text-gray-500 text-center py-4"><?php _e('Không có lịch khởi hành hôm nay', 'nt-tour-booking'); ?></p>
                        <?php else: ?>
                            <div class="nt-list">
                                <?php foreach ($today_departures as $departure): ?>
                                    <div class="nt-list__item">
                                        <div class="nt-list__content">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <span class="nt-list__title"><?php echo esc_html($departure['tour_name']); ?></span>
                                                    <span class="nt-list__subtitle">
                                                        <?php echo esc_html($departure['start_date']); ?>
                                                        <?php if ($departure['departure_time']): ?>
                                                            lúc <?php echo esc_html($departure['departure_time']); ?>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                                <div class="text-right">
                                                    <span class="nt-list__value"><?php echo esc_html($departure['booked_count']); ?>/<?php echo esc_html($departure['capacity']); ?></span>
                                                    <span class="text-sm text-gray-500"><?php _e('khách', 'nt-tour-booking'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Payments -->
                <div class="nt-card">
                    <div class="nt-card__header">
                        <h3 class="nt-card__title">
                            <i data-lucide="credit-card" class="w-5 h-5 mr-2"></i>
                            <?php _e('Chờ thanh toán', 'nt-tour-booking'); ?>
                        </h3>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=nt-tour-payments')); ?>" class="nt-card__link">
                            <?php _e('Xem tất cả', 'nt-tour-booking'); ?>
                        </a>
                    </div>
                    <div class="nt-card__body">
                        <?php if (empty($pending_payments)): ?>
                            <p class="text-gray-500 text-center py-4"><?php _e('Không có booking chờ thanh toán', 'nt-tour-booking'); ?></p>
                        <?php else: ?>
                            <div class="nt-list">
                                <?php foreach ($pending_payments as $booking): ?>
                                    <div class="nt-list__item">
                                        <div class="nt-list__content">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <span class="nt-list__title"><?php echo esc_html($booking['code']); ?></span>
                                                    <span class="nt-list__subtitle"><?php echo esc_html($booking['customer_name']); ?></span>
                                                </div>
                                                <div class="text-right">
                                                    <span class="nt-list__value"><?php echo $instance->format_currency($booking['total_amount']); ?></span>
                                                    <a href="<?php echo esc_url(admin_url('admin.php?page=nt-tour-bookings&action=view&id=' . $booking['id'])); ?>"
                                                       class="nt-btn nt-btn-sm nt-btn-primary">
                                                        <?php _e('Xác nhận', 'nt-tour-booking'); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="nt-card">
                    <div class="nt-card__header">
                        <h3 class="nt-card__title">
                            <i data-lucide="zap" class="w-5 h-5 mr-2"></i>
                            <?php _e('Thao tác nhanh', 'nt-tour-booking'); ?>
                        </h3>
                    </div>
                    <div class="nt-card__body">
                        <div class="nt-quick-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=nt-tour-bookings&action=new')); ?>" class="nt-quick-action">
                                <i data-lucide="plus-circle" class="w-8 h-8 text-blue-500"></i>
                                <span><?php _e('Tạo Booking', 'nt-tour-booking'); ?></span>
                            </a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=nt-tour-departures&action=new')); ?>" class="nt-quick-action">
                                <i data-lucide="calendar-plus" class="w-8 h-8 text-green-500"></i>
                                <span><?php _e('Tạo Departure', 'nt-tour-booking'); ?></span>
                            </a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=nt-tour-checkin')); ?>" class="nt-quick-action">
                                <i data-lucide="scan" class="w-8 h-8 text-purple-500"></i>
                                <span><?php _e('Check-in', 'nt-tour-booking'); ?></span>
                            </a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=nt-tour-api-docs')); ?>" class="nt-quick-action">
                                <i data-lucide="code" class="w-8 h-8 text-indigo-500"></i>
                                <span><?php _e('API Docs', 'nt-tour-booking'); ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            <?php if ($stats['expired_holds'] > 0 || $stats['incomplete_passengers'] > 0): ?>
                <div class="nt-alert mt-6">
                    <div class="nt-alert__icon">
                        <i data-lucide="alert-triangle"></i>
                    </div>
                    <div class="nt-alert__content">
                        <h4 class="nt-alert__title"><?php _e('Thông báo', 'nt-tour-booking'); ?></h4>
                        <ul class="nt-alert__list">
                            <?php if ($stats['expired_holds'] > 0): ?>
                                <li><?php printf(__('%d ghế đã hết hạn giữ chỗ', 'nt-tour-booking'), $stats['expired_holds']); ?></li>
                            <?php endif; ?>
                            <?php if ($stats['incomplete_passengers'] > 0): ?>
                                <li><?php printf(__('%d booking thiếu thông tin khách', 'nt-tour-booking'), $stats['incomplete_passengers']); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <script>
            jQuery(document).ready(function($) {
                // Initialize Lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });
        </script>
        <?php
    }

    /**
     * Get dashboard statistics
     */
    private function get_stats(): array
    {
        global $wpdb;

        $today = date('Y-m-d');
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $month_start = date('Y-m-01');

        // Bookings today
        $bookings_today = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}nt_bookings WHERE DATE(created_at) = %s",
            $today
        ));

        // Bookings this week
        $bookings_week = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}nt_bookings WHERE DATE(created_at) >= %s",
            $week_start
        ));

        // Revenue this month
        $revenue_month = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(total_amount), 0) FROM {$wpdb->prefix}nt_bookings WHERE DATE(created_at) >= %s AND payment_status = 'paid'",
            $month_start
        ));

        // Pending payments
        $pending_payments = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}nt_bookings WHERE payment_status = 'unpaid' AND booking_status = 'pending_payment'"
        );

        // Expired holds
        $expired_holds = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}nt_departure_seats WHERE status = 'holding' AND hold_expires_at < %s",
            current_time('mysql')
        ));

        // Incomplete passenger info
        $incomplete_passengers = (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT booking_id) FROM {$wpdb->prefix}nt_bookings WHERE passenger_info_status != 'completed' AND booking_status IN ('pending_payment', 'confirmed')"
        );

        return [
            'bookings_today' => $bookings_today,
            'bookings_week' => $bookings_week,
            'revenue_month' => $revenue_month,
            'pending_payments' => $pending_payments,
            'expired_holds' => $expired_holds,
            'incomplete_passengers' => $incomplete_passengers,
        ];
    }

    /**
     * Get recent bookings
     */
    private function get_recent_bookings(int $limit = 5): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT b.*,
                    CASE b.booking_status
                        WHEN 'pending_payment' THEN 'Chờ thanh toán'
                        WHEN 'confirmed' THEN 'Đã xác nhận'
                        WHEN 'cancelled' THEN 'Đã hủy'
                        WHEN 'completed' THEN 'Hoàn thành'
                        ELSE b.booking_status
                    END as booking_status_label
             FROM {$wpdb->prefix}nt_bookings b
             ORDER BY b.created_at DESC
             LIMIT {$limit}",
            ARRAY_A
        );

        return $results ?: [];
    }

    /**
     * Get today's departures
     */
    private function get_today_departures(): array
    {
        global $wpdb;

        $today = date('Y-m-d');

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT d.*,
                    p.post_title as tour_name,
                    (SELECT COUNT(*) FROM {$wpdb->prefix}nt_bookings b WHERE b.tour_departure_id = d.id AND b.booking_status IN ('pending_payment', 'confirmed')) as booked_count
             FROM {$wpdb->prefix}nt_tour_departures d
             LEFT JOIN {$wpdb->posts} p ON d.tour_id = p.ID
             WHERE d.start_date = %s AND d.status = 'open'
             ORDER BY d.departure_time ASC
             LIMIT 5",
            $today
        ), ARRAY_A);

        return $results ?: [];
    }

    /**
     * Get pending payments
     */
    private function get_pending_payments(int $limit = 5): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}nt_bookings
             WHERE payment_status = 'unpaid' AND booking_status = 'pending_payment'
             ORDER BY created_at DESC
             LIMIT {$limit}",
            ARRAY_A
        );

        return $results ?: [];
    }
}