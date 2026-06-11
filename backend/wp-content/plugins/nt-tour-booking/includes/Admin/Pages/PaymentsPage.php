<?php
/**
 * Payments Page
 *
 * Manage payments and confirmations.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin\Pages;

class PaymentsPage extends BasePage
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'update_menu']);
    }

    public static function update_menu(): void
    {
        remove_submenu_page('nt-tour-booking', 'nt-tour-payments');
        add_submenu_page(
            'nt-tour-booking',
            __('Payments', 'nt-tour-booking'),
            __('Payments', 'nt-tour-booking'),
            'nt_confirm_payments',
            'nt-tour-payments',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        $instance = new self();
        $instance->enqueue_assets();

        $columns = [
            ['title' => 'ID', 'data' => 'id', 'orderable' => true, 'searchable' => false],
            ['title' => 'Mã Booking', 'data' => 'booking_code', 'orderable' => true, 'searchable' => true],
            ['title' => 'Khách hàng', 'data' => 'customer', 'orderable' => false, 'searchable' => true],
            ['title' => 'Số tiền booking', 'data' => 'booking_amount', 'orderable' => true, 'searchable' => false],
            ['title' => 'Đã thanh toán', 'data' => 'paid_amount', 'orderable' => true, 'searchable' => false],
            ['title' => 'Còn lại', 'data' => 'remaining', 'orderable' => true, 'searchable' => false],
            ['title' => 'Trạng thái', 'data' => 'payment_status_badge', 'orderable' => true, 'searchable' => true],
            ['title' => 'Thao tác', 'data' => 'actions', 'orderable' => false, 'searchable' => false],
        ];

        ?>
        <div class="wrap nt-tour-wrap">
            <?php
            $instance->render_header(
                __('Quản lý Thanh toán', 'nt-tour-booking'),
                __('Theo dõi và xác nhận thanh toán', 'nt-tour-booking'),
                [
                    [
                        'label' => __('Xem tất cả Payments', 'nt-tour-booking'),
                        'url' => admin_url('admin.php?page=nt-tour-payments&view=all'),
                        'icon' => 'list',
                        'class' => 'nt-btn-secondary',
                    ],
                ]
            );
            ?>

            <!-- Stats -->
            <div class="nt-stats-grid mb-6">
                <?php
                $stats = $instance->get_payment_stats();
                $instance->render_stat_card('Chưa thanh toán', number_format($stats['unpaid_count']), 'clock', 'yellow');
                $instance->render_stat_card('Đã thanh toán (tháng)', $instance->format_currency($stats['month_paid']), 'check-circle', 'green');
                $instance->render_stat_card('Cần xác nhận', number_format($stats['pending_count']), 'alert-circle', 'red');
                ?>
            </div>

            <div class="nt-filters mb-4">
                <div class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Tìm kiếm', 'nt-tour-booking'); ?></label>
                        <input type="text" id="filter-search" class="nt-input nt-input-sm" style="width: 200px;" placeholder="<?php esc_attr_e('Mã booking, tên...', 'nt-tour-booking'); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Trạng thái', 'nt-tour-booking'); ?></label>
                        <select id="filter-status" class="nt-input nt-input-sm" style="width: 150px;">
                            <option value=""><?php _e('Tất cả', 'nt-tour-booking'); ?></option>
                            <option value="unpaid"><?php _e('Chưa thanh toán', 'nt-tour-booking'); ?></option>
                            <option value="paid"><?php _e('Đã thanh toán', 'nt-tour-booking'); ?></option>
                            <option value="deposit_paid"><?php _e('Đặt cọc', 'nt-tour-booking'); ?></option>
                            <option value="underpaid"><?php _e('Thiếu tiền', 'nt-tour-booking'); ?></option>
                        </select>
                    </div>
                    <button type="button" id="btn-filter" class="nt-btn nt-btn-primary nt-btn-sm">
                        <i data-lucide="filter" class="w-4 h-4 mr-1"></i><?php _e('Lọc', 'nt-tour-booking'); ?>
                    </button>
                    <button type="button" id="btn-reset" class="nt-btn nt-btn-ghost nt-btn-sm"><?php _e('Reset', 'nt-tour-booking'); ?></button>
                </div>
            </div>

            <?php $instance->render_table_container('payments-table', $columns); ?>

            <!-- Confirm Payment Modal -->
            <?php $instance->render_modal('payment-modal', __('Xác nhận thanh toán', 'nt-tour-booking'), 'md'); ?>
        </div>

        <script>
            jQuery(document).ready(function($) {
                if (typeof lucide !== 'undefined') lucide.createIcons();

                var table = $('#payments-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: ntAdmin.apiUrl + '/admin/payments',
                        type: 'GET',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        },
                        data: function(d) {
                            d.search = $('#filter-search').val();
                            d.status = $('#filter-status').val();
                        }
                    },
                    columns: [
                        { data: 'id' },
                        { data: 'booking_code' },
                        { data: 'customer' },
                        { data: 'booking_amount' },
                        { data: 'paid_amount' },
                        { data: 'remaining' },
                        { data: 'payment_status_badge' },
                        { data: 'actions' }
                    ],
                    order: [[0, 'desc']],
                    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json' }
                });

                $('#btn-filter').on('click', function() { table.ajax.reload(); });
                $('#btn-reset').on('click', function() {
                    $('#filter-search, #filter-status').val('');
                    table.ajax.reload();
                });

                window.confirmPaymentFromList = function(code, amount) {
                    var modal = $('#payment-modal');
                    var html = '<form id="confirm-payment-form" class="space-y-4">';
                    html += '<p>Bạn đang xác nhận thanh toán cho booking <strong>' + code + '</strong></p>';
                    html += '<div class="p-4 bg-green-50 rounded-lg"><p class="text-lg">Số tiền: <strong>' + ntFormatCurrency(amount) + '</strong></p></div>';
                    html += '<input type="hidden" name="booking_code" value="' + code + '">';
                    html += '<div class="flex justify-end gap-3 pt-4 border-t">';
                    html += '<button type="button" class="nt-btn nt-btn-ghost nt-modal-close">Hủy</button>';
                    html += '<button type="submit" class="nt-btn nt-btn-primary">Xác nhận</button>';
                    html += '</div></form>';
                    modal.find('.nt-modal-content').html(html);
                    modal.removeClass('hidden');
                };

                $(document).on('submit', '#confirm-payment-form', function(e) {
                    e.preventDefault();
                    var code = $(this).find('[name="booking_code"]').val();
                    $.ajax({
                        url: ntAdmin.apiUrl + '/admin/bookings/' + code + '/confirm-payment',
                        method: 'POST',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#payment-modal').addClass('hidden');
                                table.ajax.reload();
                                showToast('success', 'Đã xác nhận thanh toán!');
                            }
                        }
                    });
                });

                function showToast(type, message) {
                    var toast = $('#nt-toast');
                    toast.removeClass('hidden');
                    toast.find('.nt-toast-icon').html(type === 'success' ? '<i data-lucide="check-circle" class="text-green-500"></i>' : '<i data-lucide="x-circle" class="text-red-500"></i>');
                    toast.find('.nt-toast-message').text(message);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    setTimeout(function() { toast.addClass('hidden'); }, 3000);
                }
            });
        </script>

        <?php
        $instance->render_toast();
    }

    private function get_payment_stats(): array
    {
        global $wpdb;

        $month_start = date('Y-m-01');

        return [
            'unpaid_count' => (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}nt_bookings WHERE payment_status = 'unpaid' AND booking_status = 'pending_payment'"
            ),
            'month_paid' => (float) $wpdb->get_var($wpdb->prepare(
                "SELECT COALESCE(SUM(total_amount), 0) FROM {$wpdb->prefix}nt_bookings WHERE payment_status = 'paid' AND DATE(created_at) >= %s",
                $month_start
            )),
            'pending_count' => (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}nt_bookings WHERE payment_status IN ('unpaid', 'deposit_paid') AND booking_status = 'pending_payment'"
            ),
        ];
    }
}