<?php
/**
 * Tools Page
 *
 * Admin tools for data management.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin\Pages;

class ToolsPage extends BasePage
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'add_menu']);
        add_action('admin_post_nt_install_sample_data', [self::class, 'handle_install_sample_data']);
        add_action('admin_post_nt_export_bookings', [self::class, 'handle_export_bookings']);
    }

    public static function add_menu(): void
    {
        add_submenu_page(
            'nt-tour-booking',
            __('Tools', 'nt-tour-booking'),
            __('Tools', 'nt-tour-booking'),
            'manage_options',
            'nt-tour-tools',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        $instance = new self();
        $instance->enqueue_assets();

        $installer_status = \TourBooking\Installer::is_installed();
        $db_version = get_option('nt_tour_db_version', '0');
        $plugin_version = NT_TOUR_BOOKING_VERSION;

        ?>
        <div class="wrap nt-tour-wrap">
            <?php
            $instance->render_header(
                __('NT Tour Booking Tools', 'nt-tour-booking'),
                __('Quản lý dữ liệu và công cụ hỗ trợ', 'nt-tour-booking')
            );
            ?>

            <!-- Status Cards -->
            <div class="nt-stats-grid mb-6">
                <?php
                $instance->render_stat_card('Plugin Version', $plugin_version, 'info', 'blue');
                $instance->render_stat_card('Database Version', $db_version, 'database', 'green');
                $instance->render_stat_card('Sample Data', $installer_status ? 'Đã cài đặt' : 'Chưa cài đặt', $installer_status ? 'check-circle' : 'x-circle', $installer_status ? 'green' : 'yellow');
                ?>
            </div>

            <!-- Sample Data Section -->
            <div class="nt-card mb-6">
                <div class="nt-card__header">
                    <h3 class="nt-card__title">
                        <i data-lucide="package" class="w-5 h-5 mr-2"></i>
                        <?php _e('Sample Data', 'nt-tour-booking'); ?>
                    </h3>
                </div>
                <div class="nt-card__body">
                    <p class="text-gray-600 mb-4">
                        Cài đặt dữ liệu mẫu để test plugin bao gồm: Tours, Departures, Pickup Points, Vehicles, và Seat Layouts.
                    </p>

                    <?php if ($installer_status): ?>
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg mb-4">
                            <p class="text-green-800 font-medium">✓ Sample data đã được cài đặt</p>
                            <p class="text-sm text-green-600 mt-1">Có thể cài lại để reset dữ liệu.</p>
                        </div>
                    <?php else: ?>
                        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg mb-4">
                            <p class="text-yellow-800 font-medium">⚠ Chưa có sample data</p>
                            <p class="text-sm text-yellow-600 mt-1">Cài đặt sample data để bắt đầu test.</p>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('nt_install_sample_data'); ?>
                        <input type="hidden" name="action" value="nt_install_sample_data">
                        <input type="hidden" name="reinstall" value="<?php echo $installer_status ? '1' : '0'; ?>">
                        <button type="submit" class="nt-btn nt-btn-primary">
                            <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                            <?php echo $installer_status ? 'Cài lại Sample Data' : 'Cài đặt Sample Data'; ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Export Bookings -->
            <div class="nt-card mb-6">
                <div class="nt-card__header">
                    <h3 class="nt-card__title">
                        <i data-lucide="download" class="w-5 h-5 mr-2"></i>
                        <?php _e('Export Bookings', 'nt-tour-booking'); ?>
                    </h3>
                </div>
                <div class="nt-card__body">
                    <p class="text-gray-600 mb-4">
                        Export danh sách bookings ra file CSV để sử dụng trong Excel hoặc Google Sheets.
                    </p>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('nt_export_bookings'); ?>
                        <input type="hidden" name="action" value="nt_export_bookings">
                        <div class="flex gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Từ ngày', 'nt-tour-booking'); ?></label>
                                <input type="date" name="date_from" class="nt-input" value="<?php echo date('Y-m-01'); ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Đến ngày', 'nt-tour-booking'); ?></label>
                                <input type="date" name="date_to" class="nt-input" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <button type="submit" class="nt-btn nt-btn-secondary">
                                <i data-lucide="file-down" class="w-4 h-4 mr-2"></i>
                                <?php _e('Export CSV', 'nt-tour-booking'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- API Info -->
            <div class="nt-card">
                <div class="nt-card__header">
                    <h3 class="nt-card__title">
                        <i data-lucide="code" class="w-5 h-5 mr-2"></i>
                        <?php _e('API Information', 'nt-tour-booking'); ?>
                    </h3>
                </div>
                <div class="nt-card__body">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('API Base URL', 'nt-tour-booking'); ?></label>
                            <div class="flex items-center gap-2">
                                <code class="bg-gray-100 px-3 py-2 rounded font-mono text-sm flex-1"><?php echo esc_html(rest_url('nt-tour/v1')); ?></code>
                                <button type="button" class="nt-btn nt-btn-sm nt-btn-secondary" onclick="copyToClipboard('<?php echo esc_attr(rest_url('nt-tour/v1')); ?>')">
                                    <i data-lucide="copy" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Admin Nonce', 'nt-tour-booking'); ?></label>
                            <div class="flex items-center gap-2">
                                <code class="bg-gray-100 px-3 py-2 rounded font-mono text-sm flex-1" id="admin-nonce"><?php echo esc_html(wp_create_nonce('wp_rest')); ?></code>
                                <button type="button" class="nt-btn nt-btn-sm nt-btn-secondary" onclick="copyToClipboard(document.getElementById('admin-nonce').textContent)">
                                    <i data-lucide="copy" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });

            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(function() {
                    alert('Đã copy!');
                });
            }
        </script>
        <?php
    }

    public static function handle_install_sample_data(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('nt_install_sample_data');

        \TourBooking\Installer::run();

        wp_redirect(admin_url('admin.php?page=nt-tour-tools&installed=1'));
        exit;
    }

    public static function handle_export_bookings(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('nt_export_bookings');

        global $wpdb;

        $date_from = sanitize_text_field($_POST['date_from'] ?? date('Y-m-01'));
        $date_to = sanitize_text_field($_POST['date_to'] ?? date('Y-m-d'));

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT b.*, d.start_date as departure_date, p.post_title as tour_name
             FROM {$wpdb->prefix}nt_bookings b
             LEFT JOIN {$wpdb->prefix}nt_tour_departures d ON b.tour_departure_id = d.id
             LEFT JOIN {$wpdb->posts} p ON d.tour_id = p.ID
             WHERE DATE(b.created_at) >= %s AND DATE(b.created_at) <= %s
             ORDER BY b.created_at DESC",
            $date_from,
            $date_to
        ), ARRAY_A);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=bookings-' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');

        // BOM for Excel UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Headers
        fputcsv($output, ['Mã Booking', 'Tour', 'Ngày KH', 'Khách hàng', 'Phone', 'Email', 'Số khách', 'Tổng tiền', 'Booking Status', 'Payment Status', 'Ngày tạo']);

        // Data
        foreach ($results as $row) {
            fputcsv($output, [
                $row['code'],
                $row['tour_name'] ?? 'N/A',
                $row['departure_date'] ?? '',
                $row['customer_name'],
                $row['customer_phone'],
                $row['customer_email'] ?? '',
                $row['total_people'],
                $row['total_amount'],
                $row['booking_status'],
                $row['payment_status'],
                $row['created_at'],
            ]);
        }

        fclose($output);
        exit;
    }
}
