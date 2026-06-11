<?php
/**
 * Vehicles Page
 *
 * Manage vehicles for tours.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin\Pages;

class VehiclesPage extends BasePage
{
    /**
     * Initialize vehicles page
     */
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'update_menu']);
    }

    /**
     * Update vehicles menu
     */
    public static function update_menu(): void
    {
        remove_submenu_page('nt-tour-booking', 'nt-tour-vehicles');
        add_submenu_page(
            'nt-tour-booking',
            __('Vehicles', 'nt-tour-booking'),
            __('Vehicles', 'nt-tour-booking'),
            'nt_manage_vehicles',
            'nt-tour-vehicles',
            [self::class, 'render']
        );
    }

    /**
     * Render vehicles page
     */
    public static function render(): void
    {
        $instance = new self();
        $instance->enqueue_assets();

        // Get layouts for dropdown
        $layouts = $instance->get_layouts();

        $status_options = [
            'active' => __('Hoạt động', 'nt-tour-booking'),
            'inactive' => __('Không hoạt động', 'nt-tour-booking'),
        ];

        $type_options = [
            'bus_29' => __('Xe 29 chỗ', 'nt-tour-booking'),
            'bus_45' => __('Xe 45 chỗ', 'nt-tour-booking'),
            'limousine' => __('Limousine', 'nt-tour-booking'),
            'other' => __('Khác', 'nt-tour-booking'),
        ];

        $columns = [
            ['title' => 'ID', 'data' => 'id', 'orderable' => true, 'searchable' => false],
            ['title' => 'Tên xe', 'data' => 'name', 'orderable' => true, 'searchable' => true],
            ['title' => 'Biển số', 'data' => 'plate_number', 'orderable' => true, 'searchable' => true],
            ['title' => 'Loại xe', 'data' => 'type_label', 'orderable' => true, 'searchable' => true],
            ['title' => 'Số ghế', 'data' => 'total_seats', 'orderable' => true, 'searchable' => false],
            ['title' => 'Layout', 'data' => 'layout_name', 'orderable' => false, 'searchable' => true],
            ['title' => 'Trạng thái', 'data' => 'status_badge', 'orderable' => true, 'searchable' => true],
            ['title' => 'Thao tác', 'data' => 'actions', 'orderable' => false, 'searchable' => false],
        ];

        // Prepare layout choices
        $layout_choices = [];
        foreach ($layouts as $layout) {
            $layout_choices[$layout['id']] = $layout['name'];
        }

        echo '<div class="wrap nt-tour-wrap">';

        $instance->render_header(
            __('Quản lý xe', 'nt-tour-booking'),
            __('Tạo và quản lý các xe phục vụ tour', 'nt-tour-booking'),
            [
                [
                    'label' => __('Thêm mới', 'nt-tour-booking'),
                    'url' => '#',
                    'icon' => 'plus',
                    'class' => 'nt-btn-primary nt-modal-open',
                    'onclick' => 'openVehicleModal()',
                ],
                [
                    'label' => __('Quản lý Layout', 'nt-tour-booking'),
                    'url' => admin_url('admin.php?page=nt-tour-seat-layouts'),
                    'icon' => 'layout',
                    'class' => 'nt-btn-secondary',
                ],
            ]
        );

        // Data Table
        $instance->render_table_container('vehicles-table', $columns);

        // Create/Edit Modal
        $instance->render_modal('vehicle-modal', __('Xe', 'nt-tour-booking'), 'md');

        echo '</div>';

        // Modal Form Template - use script tag instead of template to avoid PHP parsing issues
        $form_html = '<form id="vehicle-form" class="space-y-4">';
        $form_html .= '<input type="hidden" name="id" value="">';
        $form_html .= '<div class="grid grid-cols-2 gap-4">';
        $form_html .= $instance->build_field_html('text', 'name', __('Tên xe', 'nt-tour-booking'), '', ['required' => true, 'placeholder' => 'Ví dụ: Xe 01']);
        $form_html .= $instance->build_field_html('text', 'plate_number', __('Biển số', 'nt-tour-booking'), '', ['placeholder' => 'Ví dụ: 51A-123.45']);
        $form_html .= '</div>';
        $form_html .= '<div class="grid grid-cols-2 gap-4">';
        $form_html .= $instance->build_field_html('select', 'vehicle_type', __('Loại xe', 'nt-tour-booking'), 'bus_29', ['required' => true, 'choices' => $type_options]);
        $form_html .= $instance->build_field_html('number', 'total_seats', __('Tổng số ghế', 'nt-tour-booking'), '', ['required' => true, 'placeholder' => '0']);
        $form_html .= '</div>';
        $form_html .= $instance->build_field_html('select', 'layout_id', __('Layout ghế', 'nt-tour-booking'), '', ['empty' => '-- Chọn layout --', 'choices' => $layout_choices]);
        $form_html .= $instance->build_field_html('select', 'status', __('Trạng thái', 'nt-tour-booking'), 'active', ['choices' => $status_options]);
        $form_html .= '<div class="flex justify-end gap-3 pt-4 border-t">';
        $form_html .= '<button type="button" class="nt-btn nt-btn-ghost nt-modal-close">' . __('Hủy', 'nt-tour-booking') . '</button>';
        $form_html .= '<button type="submit" class="nt-btn nt-btn-primary">' . __('Lưu', 'nt-tour-booking') . '</button>';
        $form_html .= '</div>';
        $form_html .= '</form>';

        ?>
        <script>
            var vehicleFormTemplate = <?php echo json_encode($form_html); ?>;
        </script>
        <?php

        $instance->render_vehicles_script();
        $instance->render_toast();
    }

    /**
     * Build field HTML string
     */
    private function build_field_html(string $type, string $name, string $label, $value = '', array $options = []): string
    {
        $required = $options['required'] ?? false;
        $placeholder = $options['placeholder'] ?? '';
        $id = 'field_' . $name;

        $html = '<div class="nt-form-group">';
        $html .= '<label for="' . esc_attr($id) . '" class="block text-sm font-medium text-gray-700 mb-1">';
        $html .= esc_html($label);
        if ($required) $html .= '<span class="text-red-500">*</span>';
        $html .= '</label>';

        switch ($type) {
            case 'text':
            case 'email':
            case 'tel':
            case 'number':
            case 'date':
            case 'time':
                $html .= '<input type="' . esc_attr($type) . '" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '" class="nt-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"' . ($required ? ' required' : '') . '>';
                break;

            case 'select':
                $html .= '<select id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" class="nt-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"' . ($required ? ' required' : '') . '>';
                if (!empty($options['empty'])) {
                    $html .= '<option value="">' . esc_html($options['empty']) . '</option>';
                }
                if (!empty($options['choices'])) {
                    foreach ($options['choices'] as $opt_value => $opt_label) {
                        $selected = ($value == $opt_value) ? ' selected' : '';
                        $html .= '<option value="' . esc_attr($opt_value) . '"' . $selected . '>' . esc_html($opt_label) . '</option>';
                    }
                }
                $html .= '</select>';
                break;
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Render vehicles JavaScript
     */
    private function render_vehicles_script(): void
    {
        ?>
        <script>
            jQuery(document).ready(function($) {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                var table = $('#vehicles-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: ntAdmin.apiUrl + '/admin/vehicles',
                        type: 'GET',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        }
                    },
                    columns: [
                        { data: 'id' },
                        { data: 'name' },
                        { data: 'plate_number' },
                        { data: 'type_label' },
                        { data: 'total_seats' },
                        { data: 'layout_name' },
                        { data: 'status_badge' },
                        { data: 'actions' }
                    ],
                    order: [[1, 'asc']],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                    }
                });

                window.openVehicleModal = function(id) {
                    var modal = $('#vehicle-modal');
                    modal.find('.nt-modal-content').html(vehicleFormTemplate);
                    modal.removeClass('hidden');

                    if (id) {
                        $.ajax({
                            url: ntAdmin.apiUrl + '/admin/vehicles/' + id,
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                            },
                            success: function(response) {
                                if (response.success) {
                                    var data = response.data;
                                    $('#vehicle-form [name="id"]').val(data.id);
                                    $('#vehicle-form [name="name"]').val(data.name);
                                    $('#vehicle-form [name="plate_number"]').val(data.plate_number);
                                    $('#vehicle-form [name="vehicle_type"]').val(data.vehicle_type);
                                    $('#vehicle-form [name="total_seats"]').val(data.total_seats);
                                    $('#vehicle-form [name="layout_id"]').val(data.layout_id);
                                    $('#vehicle-form [name="status"]').val(data.status);
                                }
                            }
                        });
                    }

                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                };

                $(document).on('submit', '#vehicle-form', function(e) {
                    e.preventDefault();
                    var formData = $(this).serializeJSON();
                    var id = formData.id;
                    var method = id ? 'PUT' : 'POST';
                    var url = id ? ntAdmin.apiUrl + '/admin/vehicles/' + id : ntAdmin.apiUrl + '/admin/vehicles';

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
                                $('#vehicle-modal').addClass('hidden');
                                table.ajax.reload();
                                showToast('success', id ? 'Đã cập nhật!' : 'Đã tạo mới!');
                            } else {
                                showToast('error', response.message || 'Có lỗi xảy ra');
                            }
                        }
                    });
                });

                window.deleteVehicle = function(id) {
                    if (!confirm(ntAdmin.strings.confirm_delete)) return;
                    $.ajax({
                        url: ntAdmin.apiUrl + '/admin/vehicles/' + id,
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
    }

    /**
     * Get all layouts
     */
    private function get_layouts(): array
    {
        global $wpdb;
        $results = $wpdb->get_results(
            "SELECT id, name FROM {$wpdb->prefix}nt_vehicle_layouts ORDER BY name ASC",
            ARRAY_A
        );
        return $results ?: [];
    }
}