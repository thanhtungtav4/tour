<?php
/**
 * Checkin Page
 *
 * QR code scanner and manual check-in interface.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin\Pages;

class CheckinPage extends BasePage
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'update_menu']);
    }

    public static function update_menu(): void
    {
        remove_submenu_page('nt-tour-booking', 'nt-tour-checkin');
        add_submenu_page(
            'nt-tour-booking',
            __('Check-in', 'nt-tour-booking'),
            __('Check-in', 'nt-tour-booking'),
            'nt_checkin_passengers',
            'nt-tour-checkin',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        $instance = new self();
        $instance->enqueue_assets();

        // Get departures for today
        $departures = $instance->get_today_departures();

        ?>
        <div class="wrap nt-tour-wrap">
            <?php
            $instance->render_header(
                __('Check-in Hành khách', 'nt-tour-booking'),
                __('Quét QR hoặc tìm kiếm để check-in', 'nt-tour-booking'),
                [
                    [
                        'label' => __('Lịch sử Check-in', 'nt-tour-booking'),
                        'url' => admin_url('admin.php?page=nt-tour-checkin-history'),
                        'icon' => 'history',
                        'class' => 'nt-btn-secondary',
                    ],
                ]
            );
            ?>

            <!-- Departure Selector -->
            <div class="nt-filters mb-6">
                <div class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Chọn lịch khởi hành', 'nt-tour-booking'); ?></label>
                        <select id="departure-select" class="nt-input" style="width: 100%;">
                            <option value="">-- Chọn lịch khởi hành --</option>
                            <?php foreach ($departures as $d): ?>
                                <option value="<?php echo esc_attr($d['id']); ?>"
                                    data-tour="<?php echo esc_attr($d['tour_name']); ?>"
                                    data-date="<?php echo esc_attr($d['start_date']); ?>"
                                    data-time="<?php echo esc_attr($d['departure_time']); ?>"
                                    data-capacity="<?php echo esc_attr($d['capacity']); ?>"
                                    data-booked="<?php echo esc_attr($d['booked_count']); ?>">
                                    <?php echo esc_html($d['tour_name'] . ' - ' . $d['start_date'] . ($d['departure_time'] ? ' (' . $d['departure_time'] . ')' : '')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Stats Bar (hidden until departure selected) -->
            <div id="checkin-stats" class="nt-stats-grid mb-6 hidden">
                <?php
                $instance->render_stat_card('Tổng khách', '<span id="stat-total">0</span>', 'users', 'blue');
                $instance->render_stat_card('Đã check-in', '<span id="stat-checked" class="text-green-600">0</span>', 'user-check', 'green');
                $instance->render_stat_card('Chưa check-in', '<span id="stat-pending">0</span>', 'user-x', 'yellow');
                ?>
            </div>

            <!-- Main Content Grid -->
            <div class="nt-dashboard-grid">
                <!-- QR Scanner -->
                <div class="nt-card">
                    <div class="nt-card__header">
                        <h3 class="nt-card__title">
                            <i data-lucide="scan" class="w-5 h-5 mr-2"></i>
                            <?php _e('Quét QR Code', 'nt-tour-booking'); ?>
                        </h3>
                    </div>
                    <div class="nt-card__body">
                        <div id="qr-reader" class="mb-4" style="width: 100%; min-height: 250px; background: #f3f4f6; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <p class="text-gray-500"><?php _e('Chọn lịch khởi hành để bắt đầu quét', 'nt-tour-booking'); ?></p>
                        </div>
                        <div id="qr-result" class="hidden p-4 rounded-lg mb-4"></div>
                        <div class="flex gap-2">
                            <button type="button" id="btn-start-scan" class="nt-btn nt-btn-primary flex-1" disabled>
                                <i data-lucide="camera" class="w-4 h-4 mr-2"></i>
                                <?php _e('Bắt đầu quét', 'nt-tour-booking'); ?>
                            </button>
                            <button type="button" id="btn-stop-scan" class="nt-btn nt-btn-secondary hidden">
                                <?php _e('Dừng', 'nt-tour-booking'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Passenger List -->
                <div class="nt-card">
                    <div class="nt-card__header">
                        <h3 class="nt-card__title">
                            <i data-lucide="list" class="w-5 h-5 mr-2"></i>
                            <?php _e('Danh sách hành khách', 'nt-tour-booking'); ?>
                        </h3>
                        <div class="flex gap-2">
                            <input type="text" id="search-passenger" class="nt-input nt-input-sm" placeholder="<?php esc_attr_e('Tìm kiếm...', 'nt-tour-booking'); ?>" style="width: 150px;">
                        </div>
                    </div>
                    <div class="nt-card__body" style="max-height: 500px; overflow-y: auto;">
                        <div id="passenger-list">
                            <p class="text-gray-500 text-center py-8"><?php _e('Chọn lịch khởi hành để xem danh sách', 'nt-tour-booking'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manual Lookup Modal -->
            <?php $instance->render_modal('lookup-modal', __('Tra cứu booking', 'nt-tour-booking'), 'md'); ?>
        </div>

        <!-- QR Scanner Library -->
        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

        <script>
            jQuery(document).ready(function($) {
                if (typeof lucide !== 'undefined') lucide.createIcons();

                var html5QrCode = null;
                var selectedDepartureId = null;

                // Departure change
                $('#departure-select').on('change', function() {
                    selectedDepartureId = $(this).val();
                    if (selectedDepartureId) {
                        loadPassengers(selectedDepartureId);
                        $('#checkin-stats').removeClass('hidden');
                        $('#btn-start-scan').prop('disabled', false);
                        $('#qr-reader').html('<p class="text-gray-500">Nhấn "Bắt đầu quét" để quét QR</p>');
                    } else {
                        $('#checkin-stats').addClass('hidden');
                        $('#btn-start-scan').prop('disabled', true);
                        $('#passenger-list').html('<p class="text-gray-500 text-center py-8">Chọn lịch khởi hành để xem danh sách</p>');
                    }
                });

                // Load passengers
                function loadPassengers(departureId) {
                    $.ajax({
                        url: ntAdmin.apiUrl + '/checkin/passengers/' + departureId,
                        type: 'GET',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        },
                        success: function(response) {
                            if (response.success) {
                                renderPassengerList(response.data.passengers);
                                updateStats(response.data.stats);
                            }
                        }
                    });
                }

                function renderPassengerList(passengers) {
                    if (!passengers || passengers.length === 0) {
                        $('#passenger-list').html('<p class="text-gray-500 text-center py-8">Không có hành khách</p>');
                        return;
                    }

                    var html = '<div class="space-y-2">';
                    passengers.forEach(function(p) {
                        var bgClass = p.checked_in ? 'bg-green-50' : 'bg-white';
                        var checkIcon = p.checked_in ? '<i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>' : '<i data-lucide="circle" class="w-5 h-5 text-gray-300"></i>';
                        var btnText = p.checked_in ? 'Đã check-in' : 'Check-in';
                        var btnClass = p.checked_in ? 'nt-btn nt-btn-sm nt-btn-success' : 'nt-btn nt-btn-sm nt-btn-primary';

                        html += '<div class="flex items-center justify-between p-3 rounded-lg border ' + bgClass + '" data-passenger-id="' + p.id + '">';
                        html += '<div class="flex items-center gap-3">';
                        html += checkIcon;
                        html += '<div><p class="font-medium">' + p.name + '</p><p class="text-sm text-gray-500">' + (p.seat || 'Chưa có ghế') + ' | ' + p.phone + '</p></div>';
                        html += '</div>';
                        html += '<button type="button" class="' + btnClass + '" onclick="checkinPassenger(' + p.id + ', ' + selectedDepartureId + ')">' + btnText + '</button>';
                        html += '</div>';
                    });
                    html += '</div>';

                    $('#passenger-list').html(html);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }

                function updateStats(stats) {
                    $('#stat-total').text(stats.total || 0);
                    $('#stat-checked').text(stats.checked_in || 0);
                    $('#stat-pending').text(stats.pending || 0);
                }

                // Check-in passenger
                window.checkinPassenger = function(passengerId, departureId) {
                    $.ajax({
                        url: ntAdmin.apiUrl + '/checkin/verify',
                        method: 'POST',
                        data: JSON.stringify({ passenger_id: passengerId, departure_id: departureId }),
                        contentType: 'application/json',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('success', 'Check-in thành công!');
                                loadPassengers(departureId);
                                playBeep();
                            } else {
                                showToast('error', response.message || 'Check-in thất bại');
                            }
                        }
                    });
                };

                // QR Scanner
                $('#btn-start-scan').on('click', function() {
                    startScanner();
                });

                $('#btn-stop-scan').on('click', function() {
                    stopScanner();
                });

                function startScanner() {
                    html5QrCode = new Html5Qrcode("qr-reader");
                    html5QrCode.start(
                        { facingMode: "environment" },
                        {
                            fps: 10,
                            qrbox: { width: 250, height: 250 }
                        },
                        onScanSuccess,
                        onScanFailure
                    );
                    $('#btn-start-scan').addClass('hidden');
                    $('#btn-stop-scan').removeClass('hidden');
                }

                function stopScanner() {
                    if (html5QrCode) {
                        html5QrCode.stop().then(() => {
                            $('#qr-reader').html('<p class="text-gray-500">Đã dừng quét</p>');
                        });
                    }
                    $('#btn-start-scan').removeClass('hidden');
                    $('#btn-stop-scan').addClass('hidden');
                }

                function onScanSuccess(decodedText) {
                    // Try to decode as passenger checkin token
                    $.ajax({
                        url: ntAdmin.apiUrl + '/checkin/verify',
                        method: 'POST',
                        data: JSON.stringify({ qr_token: decodedText, departure_id: selectedDepartureId }),
                        contentType: 'application/json',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#qr-result').removeClass('hidden').addClass('bg-green-100 border border-green-300').html(
                                    '<p class="text-green-800"><strong>✓ Thành công!</strong> ' + response.data.passenger_name + '</p>'
                                );
                                loadPassengers(selectedDepartureId);
                                playBeep();
                            } else {
                                $('#qr-result').removeClass('hidden').addClass('bg-red-100 border border-red-300').html(
                                    '<p class="text-red-800"><strong>✗ Thất bại!</strong> ' + (response.message || 'Không tìm thấy') + '</p>'
                                );
                            }
                            setTimeout(function() {
                                $('#qr-result').addClass('hidden');
                            }, 3000);
                        }
                    });
                }

                function onScanFailure(error) {
                    // Silently ignore scan failures
                }

                function playBeep() {
                    var audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2teleQAAAA==');
                    audio.play().catch(function() {});
                }

                function showToast(type, message) {
                    var toast = $('#nt-toast');
                    toast.removeClass('hidden');
                    toast.find('.nt-toast-icon').html(type === 'success' ? '<i data-lucide="check-circle" class="text-green-500"></i>' : '<i data-lucide="x-circle" class="text-red-500"></i>');
                    toast.find('.nt-toast-message').text(message);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    setTimeout(function() { toast.addClass('hidden'); }, 3000);
                }

                // Search passengers
                $('#search-passenger').on('keyup', function() {
                    var search = $(this).val().toLowerCase();
                    $('#passenger-list .space-y-2 > div').each(function() {
                        var name = $(this).find('.font-medium').text().toLowerCase();
                        var phone = $(this).find('.text-gray-500').text().toLowerCase();
                        $(this).toggle(name.includes(search) || phone.includes(search));
                    });
                });
            });
        </script>

        <?php
        $instance->render_toast();
    }

    private function get_today_departures(): array
    {
        global $wpdb;

        $today = date('Y-m-d');

        return $wpdb->get_results($wpdb->prepare(
            "SELECT d.*, p.post_title as tour_name,
                    (SELECT COUNT(*) FROM {$wpdb->prefix}nt_bookings b WHERE b.tour_departure_id = d.id AND b.booking_status IN ('pending_payment', 'confirmed')) as booked_count
             FROM {$wpdb->prefix}nt_tour_departures d
             LEFT JOIN {$wpdb->posts} p ON d.tour_id = p.ID
             WHERE d.start_date = %s AND d.status = 'open'
             ORDER BY d.departure_time ASC",
            $today
        ), ARRAY_A) ?: [];
    }
}