<?php
/**
 * Seat Layouts Page
 *
 * Manage vehicle seat layouts with visual editor.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin\Pages;

class SeatLayoutsPage extends BasePage
{
    /**
     * Initialize seat layouts page
     */
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'update_menu']);
    }

    /**
     * Update seat layouts menu
     */
    public static function update_menu(): void
    {
        remove_submenu_page('nt-tour-booking', 'nt-tour-seat-layouts');
        add_submenu_page(
            'nt-tour-booking',
            __('Seat Layouts', 'nt-tour-booking'),
            __('Seat Layouts', 'nt-tour-booking'),
            'nt_manage_vehicles',
            'nt-tour-seat-layouts',
            [self::class, 'render']
        );
    }

    /**
     * Render seat layouts page
     */
    public static function render(): void
    {
        $instance = new self();
        $instance->enqueue_assets();

        $type_options = [
            'bus_29' => __('Xe 29 chỗ', 'nt-tour-booking'),
            'bus_45' => __('Xe 45 chỗ', 'nt-tour-booking'),
            'limousine' => __('Limousine', 'nt-tour-booking'),
            'other' => __('Khác', 'nt-tour-booking'),
        ];

        $columns = [
            ['title' => 'ID', 'data' => 'id', 'orderable' => true, 'searchable' => false],
            ['title' => 'Tên Layout', 'data' => 'name', 'orderable' => true, 'searchable' => true],
            ['title' => 'Loại xe', 'data' => 'type_label', 'orderable' => true, 'searchable' => true],
            ['title' => 'Số ghế', 'data' => 'total_seats', 'orderable' => true, 'searchable' => false],
            ['title' => 'Xem trước', 'data' => 'preview', 'orderable' => false, 'searchable' => false],
            ['title' => 'Thao tác', 'data' => 'actions', 'orderable' => false, 'searchable' => false],
        ];

        ?>
        <div class="wrap nt-tour-wrap">
            <?php
            $instance->render_header(
                __('Quản lý Layout ghế', 'nt-tour-booking'),
                __('Tạo và quản lý bố trí ghế cho từng loại xe', 'nt-tour-booking'),
                [
                    [
                        'label' => __('Tạo Layout mới', 'nt-tour-booking'),
                        'url' => '#',
                        'icon' => 'plus',
                        'class' => 'nt-btn-primary nt-modal-open',
                        'onclick' => 'openLayoutModal()',
                    ],
                ]
            );
            ?>

            <!-- Data Table -->
            <?php $instance->render_table_container('layouts-table', $columns); ?>

            <!-- Create/Edit Modal -->
            <?php $instance->render_modal('layout-modal', __('Layout ghế', 'nt-tour-booking'), 'xl'); ?>
        </div>

        <!-- Modal Form Template -->
        <template id="layout-form-template">
            <form id="layout-form" class="space-y-4">
                <input type="hidden" name="id" value="">
                <div class="grid grid-cols-3 gap-4">
                    <?php
                    $instance->render_field('text', 'name', __('Tên Layout', 'nt-tour-booking'), '', [
                        'required' => true,
                        'placeholder' => 'Ví dụ: Layout xe 29 chỗ',
                    ]);
                    $instance->render_field('select', 'vehicle_type', __('Loại xe', 'nt-tour-booking'), 'bus_29', [
                        'required' => true,
                        'choices' => $type_options,
                    ]);
                    $instance->render_field('number', 'total_seats', __('Số ghế', 'nt-tour-booking'), '', [
                        'required' => true,
                        'placeholder' => '29',
                    ]);
                    ?>
                </div>

                <!-- Layout Editor -->
                <div class="border rounded-lg p-4 bg-gray-50">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-medium text-gray-700"><?php _e('Bố trí ghế', 'nt-tour-booking'); ?></h4>
                        <div class="flex gap-2">
                            <button type="button" id="btn-reset-layout" class="nt-btn nt-btn-ghost nt-btn-sm">
                                <i data-lucide="rotate-ccw" class="w-4 h-4 mr-1"></i>
                                <?php _e('Đặt lại', 'nt-tour-booking'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="flex flex-wrap gap-4 mb-4 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-blue-100 border-2 border-blue-300 rounded text-center text-sm leading-8">A1</div>
                            <span><?php _e('Ghế thường', 'nt-tour-booking'); ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-gray-200 border-2 border-gray-400 rounded text-center leading-8 text-gray-500">-</div>
                            <span><?php _e('Lối đi', 'nt-tour-booking'); ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-green-100 border-2 border-green-300 rounded text-center leading-8">R</div>
                            <span><?php _e('Cửa lên/xuống', 'nt-tour-booking'); ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-yellow-100 border-2 border-yellow-300 rounded text-center leading-8">D</div>
                            <span><?php _e('Tài xế', 'nt-tour-booking'); ?></span>
                        </div>
                    </div>

                    <!-- Grid Editor -->
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php _e('Số hàng:', 'nt-tour-booking'); ?></label>
                            <input type="number" id="layout-rows" value="5" min="1" max="20" class="nt-input nt-input-sm w-24">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php _e('Số cột:', 'nt-tour-booking'); ?></label>
                            <input type="number" id="layout-cols" value="6" min="1" max="10" class="nt-input nt-input-sm w-24">
                        </div>
                        <div class="flex-1 flex items-end">
                            <button type="button" id="btn-generate-grid" class="nt-btn nt-btn-secondary nt-btn-sm">
                                <i data-lucide="grid" class="w-4 h-4 mr-1"></i>
                                <?php _e('Tạo lưới', 'nt-tour-booking'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Seat Grid -->
                    <div id="seat-grid" class="mt-4 font-mono text-sm"></div>

                    <!-- Layout JSON (hidden) -->
                    <input type="hidden" name="layout_json" id="layout-json" value="">
                </div>

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

                var table = $('#layouts-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: ntAdmin.apiUrl + '/admin/vehicle-layouts',
                        type: 'GET',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        }
                    },
                    columns: [
                        { data: 'id' },
                        { data: 'name' },
                        { data: 'type_label' },
                        { data: 'total_seats' },
                        { data: 'preview' },
                        { data: 'actions' }
                    ],
                    order: [[1, 'asc']],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                    }
                });

                // Layout Editor State
                var layoutGrid = [];
                var seatCounter = 1;
                var rows = 5;
                var cols = 6;

                function generateLayout(rows, cols, existingLayout = null) {
                    layoutGrid = [];
                    seatCounter = 1;

                    for (var r = 0; r < rows; r++) {
                        layoutGrid[r] = [];
                        for (var c = 0; c < cols; c++) {
                            if (existingLayout && existingLayout[r] && existingLayout[r][c]) {
                                layoutGrid[r][c] = existingLayout[r][c];
                                if (existingLayout[r][c].type === 'seat') {
                                    seatCounter++;
                                }
                            } else {
                                // Default: aisle for column 3 (middle), entrance/exit for first/last row
                                var type = 'seat';
                                var label = '';

                                if (c === Math.floor(cols / 2)) {
                                    type = 'aisle';
                                    label = '-';
                                } else if (r === rows - 1 && (c === 0 || c === cols - 1)) {
                                    type = 'entrance';
                                    label = 'R';
                                } else if (r === 0 && c === Math.floor(cols / 2) + 1) {
                                    type = 'driver';
                                    label = 'D';
                                } else {
                                    var rowLetter = String.fromCharCode(65 + r);
                                    var seatNum = seatCounter;
                                    label = rowLetter + seatNum;
                                    seatCounter++;
                                }

                                layoutGrid[r][c] = { type: type, label: label };
                            }
                        }
                    }
                }

                function renderGrid() {
                    var html = '<div class="overflow-x-auto"><table class="mx-auto border-collapse">';

                    for (var r = 0; r < layoutGrid.length; r++) {
                        html += '<tr>';
                        for (var c = 0; c < layoutGrid[r].length; c++) {
                            var cell = layoutGrid[r][c];
                            var bgClass = 'bg-blue-100 border-blue-300';
                            var textClass = 'text-blue-800';
                            var cursorClass = 'cursor-pointer';

                            if (cell.type === 'aisle') {
                                bgClass = 'bg-gray-200 border-gray-400';
                                textClass = 'text-gray-500';
                                cursorClass = '';
                            } else if (cell.type === 'entrance') {
                                bgClass = 'bg-green-100 border-green-300';
                                textClass = 'text-green-800';
                            } else if (cell.type === 'driver') {
                                bgClass = 'bg-yellow-100 border-yellow-300';
                                textClass = 'text-yellow-800';
                            } else if (cell.type === 'empty') {
                                bgClass = 'bg-transparent border-transparent';
                                textClass = '';
                                cursorClass = '';
                            }

                            html += '<td class="w-10 h-10 border-2 rounded text-center leading-8 select-none ' + bgClass + ' ' + textClass + ' ' + cursorClass + '" data-row="' + r + '" data-col="' + c + '">' + cell.label + '</td>';
                        }
                        html += '</tr>';
                    }

                    html += '</table></div>';
                    html += '<p class="mt-2 text-sm text-gray-500">Click vào ghế để thay đổi loại (Ghế → Lối đi → Cửa → Tài xế → Trống)</p>';

                    $('#seat-grid').html(html);
                    updateJson();
                }

                function updateJson() {
                    $('#layout-json').val(JSON.stringify(layoutGrid));
                }

                function cycleCellType(r, c) {
                    var cell = layoutGrid[r][c];
                    var types = ['seat', 'aisle', 'entrance', 'driver', 'empty'];
                    var currentIndex = types.indexOf(cell.type);
                    var nextIndex = (currentIndex + 1) % types.length;
                    var nextType = types[nextIndex];

                    var rowLetter = String.fromCharCode(65 + r);
                    var seatNum = '';

                    if (nextType === 'seat') {
                        // Auto-generate seat label
                        var seatCount = 1;
                        for (var i = 0; i <= r; i++) {
                            for (var j = 0; j < layoutGrid[i].length; j++) {
                                if (layoutGrid[i][j].type === 'seat') {
                                    if (i === r && j === c) break;
                                    seatCount++;
                                }
                            }
                        }
                        seatNum = seatCount;
                        cell.label = rowLetter + seatNum;
                    } else if (nextType === 'aisle') {
                        cell.label = '-';
                    } else if (nextType === 'entrance') {
                        cell.label = 'R';
                    } else if (nextType === 'driver') {
                        cell.label = 'D';
                    } else {
                        cell.label = '';
                    }

                    cell.type = nextType;
                    renderGrid();
                }

                window.openLayoutModal = function(id = null) {
                    var modal = $('#layout-modal');
                    modal.find('.nt-modal-content').html($('#layout-form-template').html());
                    modal.removeClass('hidden');

                    if (id) {
                        $.ajax({
                            url: ntAdmin.apiUrl + '/admin/vehicle-layouts/' + id,
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                            },
                            success: function(response) {
                                if (response.success) {
                                    var data = response.data;
                                    $('#layout-form [name="id"]').val(data.id);
                                    $('#layout-form [name="name"]').val(data.name);
                                    $('#layout-form [name="vehicle_type"]').val(data.vehicle_type);
                                    $('#layout-form [name="total_seats"]').val(data.total_seats);

                                    // Parse layout JSON
                                    try {
                                        var layoutData = JSON.parse(data.layout_json);
                                        generateLayout(layoutData.length, layoutData[0] ? layoutData[0].length : 0, layoutData);
                                        $('#layout-rows').val(layoutData.length);
                                        $('#layout-cols').val(layoutData[0] ? layoutData[0].length : 0);
                                        rows = layoutData.length;
                                        cols = layoutData[0] ? layoutData[0].length : 0;
                                    } catch (e) {
                                        generateLayout(5, 6);
                                    }
                                }
                            }
                        });
                    } else {
                        generateLayout(5, 6);
                    }

                    renderGrid();

                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                };

                // Event handlers
                $(document).on('click', '#seat-grid td', function() {
                    var r = $(this).data('row');
                    var c = $(this).data('col');
                    if (r !== undefined && c !== undefined) {
                        cycleCellType(r, c);
                    }
                });

                $(document).on('click', '#btn-generate-grid', function() {
                    rows = parseInt($('#layout-rows').val()) || 5;
                    cols = parseInt($('#layout-cols').val()) || 6;
                    generateLayout(rows, cols);
                    renderGrid();
                });

                $(document).on('click', '#btn-reset-layout', function() {
                    rows = parseInt($('#layout-rows').val()) || 5;
                    cols = parseInt($('#layout-cols').val()) || 6;
                    generateLayout(rows, cols);
                    renderGrid();
                });

                $(document).on('submit', '#layout-form', function(e) {
                    e.preventDefault();
                    var formData = $(this).serializeJSON();
                    var id = formData.id;
                    var method = id ? 'PUT' : 'POST';
                    var url = id ? ntAdmin.apiUrl + '/admin/vehicle-layouts/' + id : ntAdmin.apiUrl + '/admin/vehicle-layouts';

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
                                $('#layout-modal').addClass('hidden');
                                table.ajax.reload();
                                showToast('success', id ? 'Đã cập nhật!' : 'Đã tạo mới!');
                            } else {
                                showToast('error', response.message || 'Có lỗi xảy ra');
                            }
                        }
                    });
                });

                window.deleteLayout = function(id) {
                    if (!confirm(ntAdmin.strings.confirm_delete)) return;
                    $.ajax({
                        url: ntAdmin.apiUrl + '/admin/vehicle-layouts/' + id,
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