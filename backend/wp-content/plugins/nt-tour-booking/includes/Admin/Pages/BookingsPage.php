<?php
/**
 * Bookings Page
 *
 * Manage bookings with DataTables.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin\Pages;

class BookingsPage extends BasePage
{
    /**
     * Initialize bookings page
     */
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'update_menu']);
    }

    /**
     * Update bookings menu
     */
    public static function update_menu(): void
    {
        remove_submenu_page('nt-tour-booking', 'nt-tour-bookings');
        add_submenu_page(
            'nt-tour-booking',
            __('Bookings', 'nt-tour-booking'),
            __('Bookings', 'nt-tour-booking'),
            'nt_manage_bookings',
            'nt-tour-bookings',
            [self::class, 'render']
        );
    }

    /**
     * Render bookings page
     */
    public static function render(): void
    {
        $instance = new self();
        $instance->enqueue_assets();

        $columns = [
            ['title' => 'Mã Booking', 'data' => 'code', 'orderable' => true, 'searchable' => true],
            ['title' => 'Tour', 'data' => 'tour_name', 'orderable' => true, 'searchable' => true],
            ['title' => 'Ngày khởi hành', 'data' => 'departure_date', 'orderable' => true, 'searchable' => true],
            ['title' => 'Khách hàng', 'data' => 'customer', 'orderable' => false, 'searchable' => true],
            ['title' => 'Số khách', 'data' => 'total_people', 'orderable' => true, 'searchable' => false],
            ['title' => 'Tổng tiền', 'data' => 'total_amount_formatted', 'orderable' => true, 'searchable' => false],
            ['title' => 'Booking', 'data' => 'booking_status_badge', 'orderable' => true, 'searchable' => true],
            ['title' => 'Thanh toán', 'data' => 'payment_status_badge', 'orderable' => true, 'searchable' => true],
            ['title' => 'Thao tác', 'data' => 'actions', 'orderable' => false, 'searchable' => false],
        ];

        ?>
        <div class="wrap nt-tour-wrap">
            <?php
            $instance->render_header(
                __('Quản lý Bookings', 'nt-tour-booking'),
                __('Xem và quản lý tất cả bookings', 'nt-tour-booking'),
                [
                    [
                        'label' => __('Tạo Booking', 'nt-tour-booking'),
                        'url' => '#',
                        'icon' => 'plus',
                        'class' => 'nt-btn-primary',
                        'onclick' => 'openBookingModal()',
                    ],
                    [
                        'label' => __('Export CSV', 'nt-tour-booking'),
                        'url' => '#',
                        'icon' => 'download',
                        'class' => 'nt-btn-secondary',
                        'onclick' => 'exportBookings()',
                    ],
                ]
            );
            ?>

            <!-- Filters -->
            <div class="nt-filters mb-4">
                <div class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Tìm kiếm', 'nt-tour-booking'); ?></label>
                        <input type="text" id="filter-search" class="nt-input nt-input-sm" style="width: 200px;" placeholder="<?php esc_attr_e('Mã booking, tên, phone...', 'nt-tour-booking'); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Ngày tạo', 'nt-tour-booking'); ?></label>
                        <input type="date" id="filter-date-from" class="nt-input nt-input-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Đến ngày', 'nt-tour-booking'); ?></label>
                        <input type="date" id="filter-date-to" class="nt-input nt-input-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Trạng thái booking', 'nt-tour-booking'); ?></label>
                        <select id="filter-booking-status" class="nt-input nt-input-sm" style="width: 150px;">
                            <option value=""><?php _e('Tất cả', 'nt-tour-booking'); ?></option>
                            <option value="pending_payment"><?php _e('Chờ thanh toán', 'nt-tour-booking'); ?></option>
                            <option value="confirmed"><?php _e('Đã xác nhận', 'nt-tour-booking'); ?></option>
                            <option value="completed"><?php _e('Hoàn thành', 'nt-tour-booking'); ?></option>
                            <option value="cancelled"><?php _e('Đã hủy', 'nt-tour-booking'); ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Thanh toán', 'nt-tour-booking'); ?></label>
                        <select id="filter-payment-status" class="nt-input nt-input-sm" style="width: 150px;">
                            <option value=""><?php _e('Tất cả', 'nt-tour-booking'); ?></option>
                            <option value="unpaid"><?php _e('Chưa thanh toán', 'nt-tour-booking'); ?></option>
                            <option value="paid"><?php _e('Đã thanh toán', 'nt-tour-booking'); ?></option>
                            <option value="deposit_paid"><?php _e('Đặt cọc', 'nt-tour-booking'); ?></option>
                        </select>
                    </div>
                    <div>
                        <button type="button" id="btn-filter" class="nt-btn nt-btn-primary nt-btn-sm">
                            <i data-lucide="filter" class="w-4 h-4 mr-1"></i>
                            <?php _e('Lọc', 'nt-tour-booking'); ?>
                        </button>
                        <button type="button" id="btn-reset" class="nt-btn nt-btn-ghost nt-btn-sm">
                            <?php _e('Reset', 'nt-tour-booking'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <?php $instance->render_table_container('bookings-table', $columns); ?>

            <!-- Booking Detail Modal -->
            <?php $instance->render_modal('booking-modal', __('Chi tiết Booking', 'nt-tour-booking'), 'xl'); ?>
        </div>

        <script>
            jQuery(document).ready(function($) {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                var table = $('#bookings-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: ntAdmin.apiUrl + '/admin/bookings',
                        type: 'GET',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        },
                        data: function(d) {
                            d.search = $('#filter-search').val();
                            d.date_from = $('#filter-date-from').val();
                            d.date_to = $('#filter-date-to').val();
                            d.status = $('#filter-booking-status').val();
                            d.payment_status = $('#filter-payment-status').val();
                        }
                    },
                    columns: [
                        { data: 'code' },
                        { data: 'tour_name' },
                        { data: 'departure_date' },
                        { data: 'customer' },
                        { data: 'total_people' },
                        { data: 'total_amount_formatted' },
                        { data: 'booking_status_badge' },
                        { data: 'payment_status_badge' },
                        { data: 'actions' }
                    ],
                    order: [[0, 'desc']],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                    }
                });

                $('#btn-filter').on('click', function() {
                    table.ajax.reload();
                });

                $('#btn-reset').on('click', function() {
                    $('#filter-search, #filter-date-from, #filter-date-to').val('');
                    $('#filter-booking-status, #filter-payment-status').val('');
                    table.ajax.reload();
                });

                // Enter key to search
                $('#filter-search').on('keypress', function(e) {
                    if (e.which === 13) {
                        table.ajax.reload();
                    }
                });

                window.openBookingModal = function(code) {
                    $.ajax({
                        url: ntAdmin.apiUrl + '/admin/bookings/' + code,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        },
                        success: function(response) {
                            if (response.success) {
                                showBookingDetail(response.data);
                            }
                        }
                    });
                };

                function showBookingDetail(data) {
                    var modal = $('#booking-modal');
                    var html = '<div class="space-y-6">';

                    // Booking Info
                    html += '<div class="grid grid-cols-2 gap-4">';
                    html += '<div class="p-4 bg-gray-50 rounded-lg"><h4 class="font-medium mb-2">Thông tin Booking</h4>';
                    html += '<p><span class="text-gray-500">Mã:</span> <strong>' + data.code + '</strong></p>';
                    html += '<p><span class="text-gray-500">Ngày tạo:</span> ' + data.created_at + '</p>';
                    html += '<p><span class="text-gray-500">Nguồn:</span> ' + (data.source || 'website') + '</p>';
                    html += '</div>';

                    html += '<div class="p-4 bg-gray-50 rounded-lg"><h4 class="font-medium mb-2">Tour</h4>';
                    html += '<p><span class="text-gray-500">Tour:</span> ' + (data.tour ? data.tour.name : 'N/A') + '</p>';
                    html += '<p><span class="text-gray-500">Ngày:</span> ' + (data.departure ? data.departure.date : 'N/A') + '</p>';
                    html += '<p><span class="text-gray-500">Giờ:</span> ' + (data.departure ? (data.departure.departure_time || '-') : '-') + '</p>';
                    html += '</div>';
                    html += '</div>';

                    // Customer Info
                    html += '<div class="p-4 bg-blue-50 rounded-lg"><h4 class="font-medium mb-2">Khách hàng</h4>';
                    html += '<p><span class="text-gray-500">Tên:</span> ' + data.main_contact.full_name + '</p>';
                    html += '<p><span class="text-gray-500">Phone:</span> ' + data.main_contact.phone + '</p>';
                    html += '<p><span class="text-gray-500">Email:</span> ' + (data.main_contact.email || '-') + '</p>';
                    html += '</div>';

                    // Passengers
                    html += '<div><h4 class="font-medium mb-2">Hành khách (' + (data.passengers ? data.passengers.length : 0) + ')</h4>';
                    html += '<table class="w-full text-sm"><thead><tr class="text-left text-gray-500"><th class="pb-2">STT</th><th class="pb-2">Tên</th><th class="pb-2">Ghế</th><th class="pb-2">Check-in</th></tr></thead><tbody>';
                    if (data.passengers && data.passengers.length > 0) {
                        data.passengers.forEach(function(p, i) {
                            var checkedIn = p.checked_in ? '<span class="text-green-600">✓ Đã check-in</span>' : '<span class="text-gray-400">Chưa check-in</span>';
                            html += '<tr class="border-b"><td class="py-2">' + (i + 1) + '</td><td class="py-2">' + p.full_name + '</td><td class="py-2">' + (p.seat || '-') + '</td><td class="py-2">' + checkedIn + '</td></tr>';
                        });
                    } else {
                        html += '<tr><td colspan="4" class="py-2 text-gray-500">Chưa có thông tin</td></tr>';
                    }
                    html += '</tbody></table></div>';

                    // Payment
                    html += '<div class="p-4 bg-green-50 rounded-lg"><h4 class="font-medium mb-2">Thanh toán</h4>';
                    html += '<p><span class="text-gray-500">Tổng tiền:</span> <strong class="text-lg">' + ntFormatCurrency(data.payment.total) + '</strong></p>';
                    html += '<p><span class="text-gray-500">Đã thanh toán:</span> ' + ntFormatCurrency(data.payment.paid) + '</p>';
                    html += '<p><span class="text-gray-500">Còn lại:</span> ' + ntFormatCurrency(data.payment.remaining) + '</p>';
                    html += '<p><span class="text-gray-500">Trạng thái:</span> ' + data.payment.status + '</p>';

                    if (data.payment.bank_info) {
                        html += '<div class="mt-3 p-3 bg-white rounded border"><p class="text-sm"><strong>Chuyển khoản:</strong></p>';
                        html += '<p class="text-sm">Ngân hàng: ' + data.payment.bank_info.bank_name + '</p>';
                        html += '<p class="text-sm">STK: ' + data.payment.bank_info.account_no + '</p>';
                        html += '<p class="text-sm">Nội dung: <strong>' + data.payment.bank_info.content + '</strong></p></div>';
                    }
                    html += '</div>';

                    // Actions
                    html += '<div class="flex gap-3 pt-4 border-t">';
                    if (data.booking_status === 'pending_payment' || data.payment_status === 'unpaid') {
                        html += '<button type="button" class="nt-btn nt-btn-primary" onclick="confirmPayment(\'' + data.code + '\')">Xác nhận thanh toán</button>';
                    }
                    html += '<button type="button" class="nt-btn nt-btn-secondary" onclick="sendMagicLink(\'' + data.code + '\')">Gửi Magic Link</button>';
                    if (data.booking_status !== 'cancelled' && data.booking_status !== 'completed') {
                        html += '<button type="button" class="nt-btn nt-btn-danger" onclick="cancelBooking(\'' + data.code + '\')">Hủy Booking</button>';
                    }
                    html += '</div>';

                    html += '</div>';

                    modal.find('.nt-modal-content').html(html);
                    modal.removeClass('hidden');

                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }

                window.confirmPayment = function(code) {
                    if (!confirm('Xác nhận thanh toán cho booking ' + code + '?')) return;
                    $.ajax({
                        url: ntAdmin.apiUrl + '/admin/bookings/' + code + '/confirm-payment',
                        method: 'POST',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#booking-modal').addClass('hidden');
                                table.ajax.reload();
                                showToast('success', 'Đã xác nhận thanh toán!');
                            }
                        }
                    });
                };

                window.sendMagicLink = function(code) {
                    $.ajax({
                        url: ntAdmin.apiUrl + '/admin/bookings/' + code + '/send-magic-link',
                        method: 'POST',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        },
                        success: function(response) {
                            if (response.success && response.data.magic_link) {
                                prompt('Magic Link đã được tạo:', response.data.magic_link);
                            } else {
                                showToast('error', response.message || 'Không thể tạo magic link');
                            }
                        }
                    });
                };

                window.cancelBooking = function(code) {
                    var reason = prompt('Lý do hủy booking:');
                    if (reason === null) return;

                    $.ajax({
                        url: ntAdmin.apiUrl + '/admin/bookings/' + code + '/cancel',
                        method: 'POST',
                        data: JSON.stringify({ reason: reason }),
                        contentType: 'application/json',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#booking-modal').addClass('hidden');
                                table.ajax.reload();
                                showToast('success', 'Đã hủy booking!');
                            } else {
                                showToast('error', response.message || 'Không thể hủy booking');
                            }
                        }
                    });
                };

                window.exportBookings = function() {
                    var params = new URLSearchParams({
                        date_from: $('#filter-date-from').val(),
                        date_to: $('#filter-date-to').val(),
                        status: $('#filter-booking-status').val(),
                        payment_status: $('#filter-payment-status').val(),
                    });
                    window.open(ntAdmin.apiUrl + '/admin/bookings/export?' + params.toString(), '_blank');
                };

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
}