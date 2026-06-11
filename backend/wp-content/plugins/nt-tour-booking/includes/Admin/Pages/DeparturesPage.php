<?php
/**
 * Departures Page
 *
 * Manage tour departures.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin\Pages;

class DeparturesPage extends BasePage
{
    /**
     * Initialize departures page
     */
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'update_menu']);
    }

    /**
     * Update departures menu
     */
    public static function update_menu(): void
    {
        // Remove old departures and add new one
        remove_submenu_page('nt-tour-booking', 'nt-tour-departures');
        add_submenu_page(
            'nt-tour-booking',
            __('Departures', 'nt-tour-booking'),
            __('Departures', 'nt-tour-booking'),
            'nt_manage_departures',
            'nt-tour-departures',
            [self::class, 'render']
        );
    }

    /**
     * Render departures page
     */
    public static function render(): void
    {
        $instance = new self();
        $instance->enqueue_assets();

        // Get tours for dropdown
        $tours = $instance->get_tours();
        $status_options = [
            'open' => __('Mở bán', 'nt-tour-booking'),
            'closed' => __('Đóng bán', 'nt-tour-booking'),
            'full' => __('Đã đầy', 'nt-tour-booking'),
            'cancelled' => __('Đã hủy', 'nt-tour-booking'),
        ];

        $columns = [
            ['title' => 'ID', 'data' => 'id', 'orderable' => true, 'searchable' => false],
            ['title' => 'Tour', 'data' => 'tour_name', 'orderable' => true, 'searchable' => true],
            ['title' => 'Ngày khởi hành', 'data' => 'start_date', 'orderable' => true, 'searchable' => true],
            ['title' => 'Giờ', 'data' => 'departure_time', 'orderable' => true, 'searchable' => false],
            ['title' => 'Giá NL', 'data' => 'adult_price_formatted', 'orderable' => false, 'searchable' => false],
            ['title' => 'Giá TE', 'data' => 'child_price_formatted', 'orderable' => false, 'searchable' => false],
            ['title' => 'Sức chứa', 'data' => 'capacity_info', 'orderable' => false, 'searchable' => false],
            ['title' => 'Trạng thái', 'data' => 'status_badge', 'orderable' => true, 'searchable' => true],
            ['title' => 'Thao tác', 'data' => 'actions', 'orderable' => false, 'searchable' => false],
        ];

        ?>
        <div class="wrap nt-tour-wrap">
            <?php
            $instance->render_header(
                __('Quản lý lịch khởi hành', 'nt-tour-booking'),
                __('Tạo và quản lý lịch khởi hành cho các tour', 'nt-tour-booking'),
                [
                    [
                        'label' => __('Thêm mới', 'nt-tour-booking'),
                        'url' => '#',
                        'icon' => 'plus',
                        'class' => 'nt-btn-primary nt-modal-open',
                        'onclick' => 'openDepartureModal()',
                    ],
                ]
            );
            ?>

            <!-- Filters -->
            <div class="nt-filters mb-4">
                <div class="flex flex-wrap gap-4">
                    <select id="filter-tour" class="nt-input nt-input-sm" style="width: 200px;">
                        <option value=""><?php _e('Tất cả tours', 'nt-tour-booking'); ?></option>
                        <?php foreach ($tours as $tour): ?>
                            <option value="<?php echo esc_attr($tour['ID']); ?>"><?php echo esc_html($tour['post_title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" id="filter-date-from" class="nt-input nt-input-sm" placeholder="<?php esc_attr_e('Từ ngày', 'nt-tour-booking'); ?>">
                    <input type="date" id="filter-date-to" class="nt-input nt-input-sm" placeholder="<?php esc_attr_e('Đến ngày', 'nt-tour-booking'); ?>">
                    <select id="filter-status" class="nt-input nt-input-sm" style="width: 150px;">
                        <option value=""><?php _e('Tất cả', 'nt-tour-booking'); ?></option>
                        <?php foreach ($status_options as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="btn-filter" class="nt-btn nt-btn-secondary nt-btn-sm">
                        <i data-lucide="filter" class="w-4 h-4 mr-1"></i>
                        <?php _e('Lọc', 'nt-tour-booking'); ?>
                    </button>
                    <button type="button" id="btn-reset" class="nt-btn nt-btn-ghost nt-btn-sm">
                        <?php _e('Reset', 'nt-tour-booking'); ?>
                    </button>
                </div>
            </div>

            <!-- Data Table -->
            <?php $instance->render_table_container('departures-table', $columns); ?>

            <!-- Create/Edit Modal -->
            <?php $instance->render_modal('departure-modal', __('Lịch khởi hành', 'nt-tour-booking'), 'lg'); ?>
        </div>

        <!-- Modal Form Template -->
        <template id="departure-form-template">
            <form id="departure-form" class="space-y-4">
                <input type="hidden" name="id" value="">
                <div class="grid grid-cols-2 gap-4">
                    <?php
                    $instance->render_field('select', 'tour_id', __('Tour', 'nt-tour-booking'), '', [
                        'required' => true,
                        'empty' => '-- Chọn tour --',
                        'choices' => array_combine(array_column($tours, 'ID'), array_column($tours, 'post_title')),
                    ]);
                    ?>
                    <div class="grid grid-cols-2 gap-4">
                        <?php
                        $instance->render_field('date', 'start_date', __('Ngày khởi hành', 'nt-tour-booking'), '', ['required' => true]);
                        $instance->render_field('time', 'departure_time', __('Giờ khởi hành', 'nt-tour-booking'), '');
                        ?>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <?php
                    $instance->render_field('number', 'adult_price', __('Giá người lớn', 'nt-tour-booking'), '', [
                        'required' => true,
                        'placeholder' => '0',
                    ]);
                    $instance->render_field('number', 'child_price', __('Giá trẻ em', 'nt-tour-booking'), '', [
                        'placeholder' => '0',
                    ]);
                    $instance->render_field('number', 'infant_price', __('Giá em bé', 'nt-tour-booking'), '', [
                        'placeholder' => '0',
                    ]);
                    ?>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <?php
                    $instance->render_field('number', 'capacity', __('Sức chứa', 'nt-tour-booking'), '', [
                        'required' => true,
                        'placeholder' => '0',
                    ]);
                    $instance->render_field('select', 'status', __('Trạng thái', 'nt-tour-booking'), 'open', [
                        'choices' => $status_options,
                    ]);
                    ?>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" class="nt-btn nt-btn-ghost nt-modal-close"><?php _e('Hủy', 'nt-tour-booking'); ?></button>
                    <button type="submit" class="nt-btn nt-btn-primary"><?php _e('Lưu', 'nt-tour-booking'); ?></button>
                </div>
            </form>
        </template>

        <script>
            jQuery(document).ready(function($) {
                // Initialize Lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                // Initialize DataTable
                var table = $('#departures-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: ntAdmin.apiUrl + '/admin/departures',
                        type: 'GET',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        },
                        data: function(d) {
                            d.tour_id = $('#filter-tour').val();
                            d.date_from = $('#filter-date-from').val();
                            d.date_to = $('#filter-date-to').val();
                            d.status = $('#filter-status').val();
                        }
                    },
                    columns: [
                        { data: 'id' },
                        { data: 'tour_name' },
                        { data: 'start_date_formatted' },
                        { data: 'departure_time' },
                        { data: 'adult_price_formatted' },
                        { data: 'child_price_formatted' },
                        { data: 'capacity_info' },
                        { data: 'status_badge' },
                        { data: 'actions' }
                    ],
                    order: [[2, 'desc']],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                    }
                });

                // Filter handler
                $('#btn-filter').on('click', function() {
                    table.ajax.reload();
                });

                $('#btn-reset').on('click', function() {
                    $('#filter-tour, #filter-date-from, #filter-date-to, #filter-status').val('');
                    table.ajax.reload();
                });

                // Modal handlers
                window.openDepartureModal = function(id = null) {
                    var modal = $('#departure-modal');
                    var formTemplate = $('#departure-form-template').html();

                    modal.find('.nt-modal-content').html(formTemplate);
                    modal.removeClass('hidden');

                    if (id) {
                        // Load existing departure
                        $.ajax({
                            url: ntAdmin.apiUrl + '/admin/departures/' + id,
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                            },
                            success: function(response) {
                                if (response.success) {
                                    var data = response.data;
                                    $('#departure-form [name="id"]').val(data.id);
                                    $('#departure-form [name="tour_id"]').val(data.tour_id);
                                    $('#departure-form [name="start_date"]').val(data.start_date);
                                    $('#departure-form [name="departure_time"]').val(data.departure_time);
                                    $('#departure-form [name="adult_price"]').val(data.adult_price);
                                    $('#departure-form [name="child_price"]').val(data.child_price);
                                    $('#departure-form [name="infant_price"]').val(data.infant_price);
                                    $('#departure-form [name="capacity"]').val(data.capacity);
                                    $('#departure-form [name="status"]').val(data.status);
                                }
                            }
                        });
                    }

                    // Re-init icons
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                };

                // Form submit
                $(document).on('submit', '#departure-form', function(e) {
                    e.preventDefault();
                    var formData = $(this).serializeJSON();
                    var id = formData.id;
                    var method = id ? 'PUT' : 'POST';
                    var url = id ? ntAdmin.apiUrl + '/admin/departures/' + id : ntAdmin.apiUrl + '/admin/departures';

                    $.ajax({
                        url: url,
                        method: method,
                        data: JSON.stringify(formData),
                        contentType: 'application/json',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#departure-modal').addClass('hidden');
                                table.ajax.reload();
                                showToast('success', id ? 'Đã cập nhật!' : 'Đã tạo mới!');
                            } else {
                                showToast('error', response.message || 'Có lỗi xảy ra');
                            }
                        }
                    });
                });

                // Delete handler
                window.deleteDeparture = function(id) {
                    if (!confirm(ntAdmin.strings.confirm_delete)) return;

                    $.ajax({
                        url: ntAdmin.apiUrl + '/admin/departures/' + id,
                        method: 'DELETE',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                showToast('success', 'Đã xóa!');
                            }
                        }
                    });
                };

                // Toast helper
                function showToast(type, message) {
                    var toast = $('#nt-toast');
                    toast.removeClass('hidden');
                    toast.find('.nt-toast-icon').html(type === 'success' ? '<i data-lucide="check-circle" class="text-green-500"></i>' : '<i data-lucide="x-circle" class="text-red-500"></i>');
                    toast.find('.nt-toast-message').text(message);

                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }

                    setTimeout(function() {
                        toast.addClass('hidden');
                    }, 3000);
                }
            });
        </script>

        <?php
        $instance->render_toast();
    }

    /**
     * Get all tours
     */
    private function get_tours(): array
    {
        $tours = get_posts([
            'post_type' => 'nt_tour',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        return array_map(function($post) {
            return [
                'ID' => $post->ID,
                'post_title' => $post->post_title,
            ];
        }, $tours);
    }
}