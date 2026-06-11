<?php
/**
 * Passengers Page
 *
 * Manage all passengers.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin\Pages;

class PassengersPage extends BasePage
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'update_menu']);
    }

    public static function update_menu(): void
    {
        remove_submenu_page('nt-tour-booking', 'nt-tour-passengers');
        add_submenu_page(
            'nt-tour-booking',
            __('Passengers', 'nt-tour-booking'),
            __('Passengers', 'nt-tour-booking'),
            'nt_manage_passengers',
            'nt-tour-passengers',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        $instance = new self();
        $instance->enqueue_assets();

        $columns = [
            ['title' => 'ID', 'data' => 'id', 'orderable' => true, 'searchable' => false],
            ['title' => 'Tên', 'data' => 'name', 'orderable' => true, 'searchable' => true],
            ['title' => 'Phone', 'data' => 'phone', 'orderable' => true, 'searchable' => true],
            ['title' => 'Booking', 'data' => 'booking_code', 'orderable' => true, 'searchable' => true],
            ['title' => 'Tour', 'data' => 'tour_name', 'orderable' => true, 'searchable' => true],
            ['title' => 'Ngày KH', 'data' => 'departure_date', 'orderable' => true, 'searchable' => true],
            ['title' => 'Ghế', 'data' => 'seat_code', 'orderable' => true, 'searchable' => true],
            ['title' => 'Check-in', 'data' => 'checkin_status_badge', 'orderable' => true, 'searchable' => true],
            ['title' => 'Hồ sơ', 'data' => 'profile_status_badge', 'orderable' => true, 'searchable' => true],
        ];

        ?>
        <div class="wrap nt-tour-wrap">
            <?php
            $instance->render_header(
                __('Quản lý Hành khách', 'nt-tour-booking'),
                __('Xem tất cả hành khách từ các bookings', 'nt-tour-booking'),
                [
                    [
                        'label' => __('Export CSV', 'nt-tour-booking'),
                        'url' => '#',
                        'icon' => 'download',
                        'class' => 'nt-btn-secondary',
                        'onclick' => 'exportPassengers()',
                    ],
                ]
            );
            ?>

            <div class="nt-filters mb-4">
                <div class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Tìm kiếm', 'nt-tour-booking'); ?></label>
                        <input type="text" id="filter-search" class="nt-input nt-input-sm" style="width: 200px;" placeholder="<?php esc_attr_e('Tên, phone, ID...', 'nt-tour-booking'); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Check-in', 'nt-tour-booking'); ?></label>
                        <select id="filter-checkin" class="nt-input nt-input-sm" style="width: 140px;">
                            <option value=""><?php _e('Tất cả', 'nt-tour-booking'); ?></option>
                            <option value="checked_in"><?php _e('Đã check-in', 'nt-tour-booking'); ?></option>
                            <option value="not_checked_in"><?php _e('Chưa check-in', 'nt-tour-booking'); ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Hồ sơ', 'nt-tour-booking'); ?></label>
                        <select id="filter-profile" class="nt-input nt-input-sm" style="width: 140px;">
                            <option value=""><?php _e('Tất cả', 'nt-tour-booking'); ?></option>
                            <option value="completed"><?php _e('Đã đủ', 'nt-tour-booking'); ?></option>
                            <option value="missing"><?php _e('Thiếu', 'nt-tour-booking'); ?></option>
                        </select>
                    </div>
                    <button type="button" id="btn-filter" class="nt-btn nt-btn-primary nt-btn-sm">
                        <i data-lucide="filter" class="w-4 h-4 mr-1"></i><?php _e('Lọc', 'nt-tour-booking'); ?>
                    </button>
                    <button type="button" id="btn-reset" class="nt-btn nt-btn-ghost nt-btn-sm"><?php _e('Reset', 'nt-tour-booking'); ?></button>
                </div>
            </div>

            <?php $instance->render_table_container('passengers-table', $columns); ?>
        </div>

        <script>
            jQuery(document).ready(function($) {
                if (typeof lucide !== 'undefined') lucide.createIcons();

                var table = $('#passengers-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: ntAdmin.apiUrl + '/admin/passengers',
                        type: 'GET',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        },
                        data: function(d) {
                            d.search = $('#filter-search').val();
                            d.checkin_status = $('#filter-checkin').val();
                            d.profile_status = $('#filter-profile').val();
                        }
                    },
                    columns: [
                        { data: 'id' },
                        { data: 'name' },
                        { data: 'phone' },
                        { data: 'booking_code' },
                        { data: 'tour_name' },
                        { data: 'departure_date' },
                        { data: 'seat_code' },
                        { data: 'checkin_status_badge' },
                        { data: 'profile_status_badge' },
                    ],
                    order: [[0, 'desc']],
                    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json' }
                });

                $('#btn-filter').on('click', function() { table.ajax.reload(); });
                $('#btn-reset').on('click', function() {
                    $('#filter-search, #filter-checkin, #filter-profile').val('');
                    table.ajax.reload();
                });

                window.exportPassengers = function() {
                    var params = new URLSearchParams({
                        search: $('#filter-search').val(),
                        checkin_status: $('#filter-checkin').val(),
                        profile_status: $('#filter-profile').val(),
                    });
                    window.open(ntAdmin.apiUrl + '/admin/passengers/export?' + params.toString(), '_blank');
                };
            });
        </script>

        <?php
        $instance->render_toast();
    }
}