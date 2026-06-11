<?php
/**
 * Pickup Points Page
 *
 * Manage pickup points for tours.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin\Pages;

class PickupPointsPage extends BasePage
{
    /**
     * Initialize pickup points page
     */
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'update_menu']);
    }

    /**
     * Update pickup points menu
     */
    public static function update_menu(): void
    {
        remove_submenu_page('nt-tour-booking', 'nt-tour-pickup-points');
        add_submenu_page(
            'nt-tour-booking',
            __('Pickup Points', 'nt-tour-booking'),
            __('Pickup Points', 'nt-tour-booking'),
            'nt_manage_pickup_points',
            'nt-tour-pickup-points',
            [self::class, 'render']
        );
    }

    /**
     * Render pickup points page
     */
    public static function render(): void
    {
        $instance = new self();
        $instance->enqueue_assets();

        $status_options = [
            'active' => __('Hoạt động', 'nt-tour-booking'),
            'inactive' => __('Không hoạt động', 'nt-tour-booking'),
        ];

        $columns = [
            ['title' => 'ID', 'data' => 'id', 'orderable' => true, 'searchable' => false],
            ['title' => 'STT', 'data' => 'sort_order', 'orderable' => true, 'searchable' => false],
            ['title' => 'Tên điểm đón', 'data' => 'name', 'orderable' => true, 'searchable' => true],
            ['title' => 'Địa chỉ', 'data' => 'address', 'orderable' => false, 'searchable' => true],
            ['title' => 'Bản đồ', 'data' => 'map_link', 'orderable' => false, 'searchable' => false],
            ['title' => 'Trạng thái', 'data' => 'status_badge', 'orderable' => true, 'searchable' => true],
            ['title' => 'Thao tác', 'data' => 'actions', 'orderable' => false, 'searchable' => false],
        ];

        ?>
        <div class="wrap nt-tour-wrap">
            <?php
            $instance->render_header(
                __('Quản lý điểm đón', 'nt-tour-booking'),
                __('Tạo và quản lý các điểm đón khách', 'nt-tour-booking'),
                [
                    [
                        'label' => __('Thêm mới', 'nt-tour-booking'),
                        'url' => '#',
                        'icon' => 'plus',
                        'class' => 'nt-btn-primary nt-modal-open',
                        'onclick' => 'openPickupPointModal()',
                    ],
                ]
            );
            ?>

            <!-- Data Table -->
            <?php $instance->render_table_container('pickup-points-table', $columns); ?>

            <!-- Create/Edit Modal -->
            <?php $instance->render_modal('pickup-point-modal', __('Điểm đón', 'nt-tour-booking'), 'md'); ?>
        </div>

        <!-- Modal Form Template -->
        <template id="pickup-point-form-template">
            <form id="pickup-point-form" class="space-y-4">
                <input type="hidden" name="id" value="">
                <?php
                $instance->render_field('text', 'name', __('Tên điểm đón', 'nt-tour-booking'), '', [
                    'required' => true,
                    'placeholder' => 'Ví dụ: Bến xe Miền Đông',
                ]);
                $instance->render_field('textarea', 'address', __('Địa chỉ', 'nt-tour-booking'), '', [
                    'placeholder' => 'Ví dụ: 292 Điện Biên Phủ, P.3, Q.Bình Thạnh, TP.HCM',
                    'rows' => 2,
                ]);
                $instance->render_field('text', 'map_url', __('Link Google Maps', 'nt-tour-booking'), '', [
                    'placeholder' => 'https://maps.google.com/...',
                ]);
                $instance->render_field('textarea', 'note', __('Ghi chú', 'nt-tour-booking'), '', [
                    'placeholder' => 'VD: Gần cổng số 2, đối diện cửa hàng...',
                    'rows' => 2,
                ]);
                $instance->render_field('number', 'sort_order', __('Thứ tự', 'nt-tour-booking'), '0', [
                    'placeholder' => '0',
                ]);
                $instance->render_field('select', 'status', __('Trạng thái', 'nt-tour-booking'), 'active', [
                    'choices' => $status_options,
                ]);
                ?>
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" class="nt-btn nt-btn-ghost nt-modal-close"><?php _e('Hủy', 'nt-tour-booking'); ?></button>
                    <button type="submit" class="nt-btn nt-btn-primary"><?php _e('Lưu', 'nt-tour-booking'); ?></button>
                </div>
            </form>
        </template>

        <script>
            jQuery(document).ready(function($) {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                var table = $('#pickup-points-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: ntAdmin.apiUrl + '/admin/pickup-points',
                        type: 'GET',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        }
                    },
                    columns: [
                        { data: 'id' },
                        { data: 'sort_order' },
                        { data: 'name' },
                        { data: 'address' },
                        { data: 'map_link' },
                        { data: 'status_badge' },
                        { data: 'actions' }
                    ],
                    order: [[1, 'asc']],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                    }
                });

                window.openPickupPointModal = function(id = null) {
                    var modal = $('#pickup-point-modal');
                    modal.find('.nt-modal-content').html($('#pickup-point-form-template').html());
                    modal.removeClass('hidden');

                    if (id) {
                        $.ajax({
                            url: ntAdmin.apiUrl + '/admin/pickup-points/' + id,
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                            },
                            success: function(response) {
                                if (response.success) {
                                    var data = response.data;
                                    $('#pickup-point-form [name="id"]').val(data.id);
                                    $('#pickup-point-form [name="name"]').val(data.name);
                                    $('#pickup-point-form [name="address"]').val(data.address);
                                    $('#pickup-point-form [name="map_url"]').val(data.map_url);
                                    $('#pickup-point-form [name="note"]').val(data.note);
                                    $('#pickup-point-form [name="sort_order"]').val(data.sort_order);
                                    $('#pickup-point-form [name="status"]').val(data.status);
                                }
                            }
                        });
                    }

                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                };

                $(document).on('submit', '#pickup-point-form', function(e) {
                    e.preventDefault();
                    var formData = $(this).serializeJSON();
                    var id = formData.id;
                    var method = id ? 'PUT' : 'POST';
                    var url = id ? ntAdmin.apiUrl + '/admin/pickup-points/' + id : ntAdmin.apiUrl + '/admin/pickup-points';

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
                                $('#pickup-point-modal').addClass('hidden');
                                table.ajax.reload();
                                showToast('success', id ? 'Đã cập nhật!' : 'Đã tạo mới!');
                            } else {
                                showToast('error', response.message || 'Có lỗi xảy ra');
                            }
                        }
                    });
                });

                window.deletePickupPoint = function(id) {
                    if (!confirm(ntAdmin.strings.confirm_delete)) return;
                    $.ajax({
                        url: ntAdmin.apiUrl + '/admin/pickup-points/' + id,
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