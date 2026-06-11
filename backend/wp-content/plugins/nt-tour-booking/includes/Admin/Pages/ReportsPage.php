<?php
/**
 * Reports Page
 *
 * Revenue and booking reports.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin\Pages;

class ReportsPage extends BasePage
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'update_menu']);
    }

    public static function update_menu(): void
    {
        remove_submenu_page('nt-tour-booking', 'nt-tour-reports');
        add_submenu_page(
            'nt-tour-booking',
            __('Reports', 'nt-tour-booking'),
            __('Reports', 'nt-tour-booking'),
            'nt_view_reports',
            'nt-tour-reports',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        $instance = new self();
        $instance->enqueue_assets();

        // Get default date range (last 30 days)
        $date_from = date('Y-m-01');
        $date_to = date('Y-m-d');

        $stats = $instance->get_report_stats($date_from, $date_to);
        $top_tours = $instance->get_top_tours($date_from, $date_to);
        $recent_bookings = $instance->get_recent_bookings_report($date_from, $date_to);

        ?>
        <div class="wrap nt-tour-wrap">
            <?php
            $instance->render_header(
                __('Báo cáo', 'nt-tour-booking'),
                __('Thống kê doanh thu và bookings', 'nt-tour-booking'),
                [
                    [
                        'label' => __('Export Excel', 'nt-tour-booking'),
                        'url' => '#',
                        'icon' => 'download',
                        'class' => 'nt-btn-secondary',
                        'onclick' => 'exportReport()',
                    ],
                ]
            );
            ?>

            <!-- Date Range Filter -->
            <div class="nt-filters mb-6">
                <div class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Từ ngày', 'nt-tour-booking'); ?></label>
                        <input type="date" id="date-from" class="nt-input nt-input-sm" value="<?php echo esc_attr($date_from); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Đến ngày', 'nt-tour-booking'); ?></label>
                        <input type="date" id="date-to" class="nt-input nt-input-sm" value="<?php echo esc_attr($date_to); ?>">
                    </div>
                    <div>
                        <select id="preset-range" class="nt-input nt-input-sm">
                            <option value="">Tùy chỉnh</option>
                            <option value="today">Hôm nay</option>
                            <option value="yesterday">Hôm qua</option>
                            <option value="this_week">Tuần này</option>
                            <option value="this_month" selected>Tháng này</option>
                            <option value="last_month">Tháng trước</option>
                            <option value="this_quarter">Quý này</option>
                        </select>
                    </div>
                    <button type="button" id="btn-apply" class="nt-btn nt-btn-primary nt-btn-sm">
                        <i data-lucide="refresh" class="w-4 h-4 mr-1"></i><?php _e('Áp dụng', 'nt-tour-booking'); ?>
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="nt-stats-grid mb-6">
                <?php
                $instance->render_stat_card('Tổng Bookings', number_format($stats['total_bookings']), 'calendar-check', 'blue');
                $instance->render_stat_card('Doanh thu', $instance->format_currency($stats['total_revenue']), 'trending-up', 'green');
                $instance->render_stat_card('Khách', number_format($stats['total_passengers']), 'users', 'purple');
                $instance->render_stat_card('TB/Booking', number_format($stats['avg_booking_value'], 0, ',', '.'), 'calculator', 'indigo');
                ?>
            </div>

            <!-- Charts Placeholder -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="nt-card">
                    <div class="nt-card__header">
                        <h3 class="nt-card__title">
                            <i data-lucide="bar-chart-2" class="w-5 h-5 mr-2"></i>
                            <?php _e('Doanh thu theo ngày', 'nt-tour-booking'); ?>
                        </h3>
                    </div>
                    <div class="nt-card__body">
                        <div id="revenue-chart" class="h-64 flex items-center justify-center text-gray-400">
                            <p>Biểu đồ doanh thu sẽ hiển thị ở đây</p>
                        </div>
                    </div>
                </div>
                <div class="nt-card">
                    <div class="nt-card__header">
                        <h3 class="nt-card__title">
                            <i data-lucide="pie-chart" class="w-5 h-5 mr-2"></i>
                            <?php _e('Booking theo trạng thái', 'nt-tour-booking'); ?>
                        </h3>
                    </div>
                    <div class="nt-card__body">
                        <div class="space-y-3">
                            <?php foreach ($stats['bookings_by_status'] as $status): ?>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full" style="background-color: <?php echo esc_attr($status['color']); ?>"></div>
                                        <span class="text-sm"><?php echo esc_html($status['label']); ?></span>
                                    </div>
                                    <span class="font-medium"><?php echo esc_html($status['count']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Tours -->
                <div class="nt-card">
                    <div class="nt-card__header">
                        <h3 class="nt-card__title">
                            <i data-lucide="award" class="w-5 h-5 mr-2"></i>
                            <?php _e('Top Tours', 'nt-tour-booking'); ?>
                        </h3>
                    </div>
                    <div class="nt-card__body">
                        <?php if (empty($top_tours)): ?>
                            <p class="text-gray-500 text-center py-4"><?php _e('Chưa có dữ liệu', 'nt-tour-booking'); ?></p>
                        <?php else: ?>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500 border-b">
                                        <th class="pb-2">#</th>
                                        <th class="pb-2">Tour</th>
                                        <th class="pb-2 text-right">Bookings</th>
                                        <th class="pb-2 text-right">Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_tours as $i => $tour): ?>
                                        <tr class="border-b">
                                            <td class="py-2"><?php echo $i + 1; ?></td>
                                            <td class="py-2"><?php echo esc_html($tour['tour_name']); ?></td>
                                            <td class="py-2 text-right"><?php echo esc_html($tour['booking_count']); ?></td>
                                            <td class="py-2 text-right font-medium"><?php echo $instance->format_currency($tour['revenue']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="nt-card">
                    <div class="nt-card__header">
                        <h3 class="nt-card__title">
                            <i data-lucide="clock" class="w-5 h-5 mr-2"></i>
                            <?php _e('Bookings gần đây', 'nt-tour-booking'); ?>
                        </h3>
                    </div>
                    <div class="nt-card__body">
                        <?php if (empty($recent_bookings)): ?>
                            <p class="text-gray-500 text-center py-4"><?php _e('Chưa có dữ liệu', 'nt-tour-booking'); ?></p>
                        <?php else: ?>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500 border-b">
                                        <th class="pb-2">Mã</th>
                                        <th class="pb-2">Tour</th>
                                        <th class="pb-2 text-right">Số khách</th>
                                        <th class="pb-2 text-right">Tổng tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                        <tr class="border-b">
                                            <td class="py-2 font-mono text-xs"><?php echo esc_html($booking['code']); ?></td>
                                            <td class="py-2"><?php echo esc_html($booking['tour_name']); ?></td>
                                            <td class="py-2 text-right"><?php echo esc_html($booking['total_people']); ?></td>
                                            <td class="py-2 text-right font-medium"><?php echo $instance->format_currency($booking['total_amount']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                if (typeof lucide !== 'undefined') lucide.createIcons();

                $('#btn-apply').on('click', function() {
                    var dateFrom = $('#date-from').val();
                    var dateTo = $('#date-to').val();
                    loadReport(dateFrom, dateTo);
                });

                $('#preset-range').on('change', function() {
                    var preset = $(this).val();
                    var today = new Date();
                    var dateFrom, dateTo;

                    switch(preset) {
                        case 'today':
                            dateFrom = dateTo = today.toISOString().split('T')[0];
                            break;
                        case 'yesterday':
                            var y = new Date(today);
                            y.setDate(y.getDate() - 1);
                            dateFrom = dateTo = y.toISOString().split('T')[0];
                            break;
                        case 'this_week':
                            var monday = new Date(today);
                            monday.setDate(monday.getDate() - monday.getDay() + 1);
                            dateFrom = monday.toISOString().split('T')[0];
                            dateTo = today.toISOString().split('T')[0];
                            break;
                        case 'this_month':
                            dateFrom = today.toISOString().split('T')[0].substring(0, 7) + '-01';
                            dateTo = today.toISOString().split('T')[0];
                            break;
                        case 'last_month':
                            var firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                            var lastDay = new Date(today.getFullYear(), today.getMonth(), 0);
                            dateFrom = firstDay.toISOString().split('T')[0];
                            dateTo = lastDay.toISOString().split('T')[0];
                            break;
                        case 'this_quarter':
                            var quarter = Math.floor(today.getMonth() / 3);
                            dateFrom = today.getFullYear() + '-' + (quarter * 3 + 1).toString().padStart(2, '0') + '-01';
                            dateTo = today.toISOString().split('T')[0];
                            break;
                        default:
                            return;
                    }

                    $('#date-from').val(dateFrom);
                    $('#date-to').val(dateTo);
                    loadReport(dateFrom, dateTo);
                });

                function loadReport(dateFrom, dateTo) {
                    // Reload page with new date range
                    window.location.href = '?page=nt-tour-reports&date_from=' + dateFrom + '&date_to=' + dateTo;
                }

                window.exportReport = function() {
                    var dateFrom = $('#date-from').val();
                    var dateTo = $('#date-to').val();
                    window.open(ntAdmin.apiUrl + '/admin/reports/export?date_from=' + dateFrom + '&date_to=' + dateTo, '_blank');
                };
            });
        </script>

        <?php
        $instance->render_toast();
    }

    private function get_report_stats(string $date_from, string $date_to): array
    {
        global $wpdb;

        $where = "WHERE DATE(b.created_at) >= '{$date_from}' AND DATE(b.created_at) <= '{$date_to}'";

        $total_bookings = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}nt_bookings b {$where}"
        );

        $total_revenue = (float) $wpdb->get_var(
            "SELECT COALESCE(SUM(total_amount), 0) FROM {$wpdb->prefix}nt_bookings b WHERE {$where} AND payment_status = 'paid'"
        );

        $total_passengers = (int) $wpdb->get_var(
            "SELECT COALESCE(SUM(b.total_people), 0) FROM {$wpdb->prefix}nt_bookings b {$where}"
        );

        $avg_booking_value = $total_bookings > 0 ? $total_revenue / $total_bookings : 0;

        $bookings_by_status = [];
        $statuses = [
            'pending_payment' => ['label' => 'Chờ thanh toán', 'color' => '#f59e0b'],
            'confirmed' => ['label' => 'Đã xác nhận', 'color' => '#22c55e'],
            'completed' => ['label' => 'Hoàn thành', 'color' => '#3b82f6'],
            'cancelled' => ['label' => 'Đã hủy', 'color' => '#ef4444'],
        ];

        foreach ($statuses as $status => $info) {
            $count = (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}nt_bookings b {$where} AND b.booking_status = '{$status}'"
            );
            $bookings_by_status[] = array_merge($info, ['status' => $status, 'count' => $count]);
        }

        return [
            'total_bookings' => $total_bookings,
            'total_revenue' => $total_revenue,
            'total_passengers' => $total_passengers,
            'avg_booking_value' => $avg_booking_value,
            'bookings_by_status' => $bookings_by_status,
        ];
    }

    private function get_top_tours(string $date_from, string $date_to, int $limit = 5): array
    {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT p.post_title as tour_name,
                    COUNT(b.id) as booking_count,
                    SUM(CASE WHEN b.payment_status = 'paid' THEN b.total_amount ELSE 0 END) as revenue
             FROM {$wpdb->prefix}nt_bookings b
             JOIN {$wpdb->prefix}nt_tour_departures d ON b.tour_departure_id = d.id
             JOIN {$wpdb->posts} p ON d.tour_id = p.ID
             WHERE DATE(b.created_at) >= %s AND DATE(b.created_at) <= %s
             GROUP BY d.tour_id
             ORDER BY revenue DESC
             LIMIT %d",
            $date_from, $date_to, $limit
        ), ARRAY_A) ?: [];
    }

    private function get_recent_bookings_report(string $date_from, string $date_to, int $limit = 10): array
    {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT b.code, p.post_title as tour_name, b.total_people, b.total_amount, b.created_at
             FROM {$wpdb->prefix}nt_bookings b
             JOIN {$wpdb->prefix}nt_tour_departures d ON b.tour_departure_id = d.id
             JOIN {$wpdb->posts} p ON d.tour_id = p.ID
             WHERE DATE(b.created_at) >= %s AND DATE(b.created_at) <= %s
             ORDER BY b.created_at DESC
             LIMIT %d",
            $date_from, $date_to, $limit
        ), ARRAY_A) ?: [];
    }
}