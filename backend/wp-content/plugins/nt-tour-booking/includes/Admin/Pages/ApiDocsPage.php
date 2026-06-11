<?php
/**
 * API Docs Page
 *
 * Interactive API documentation for developers.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin\Pages;

class ApiDocsPage extends BasePage
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'update_menu']);
    }

    public static function update_menu(): void
    {
        add_submenu_page(
            'nt-tour-booking',
            __('API Docs', 'nt-tour-booking'),
            __('API Docs', 'nt-tour-booking'),
            'nt_manage_api',
            'nt-tour-api-docs',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        $instance = new self();
        $instance->enqueue_assets();

        $api_base = rest_url('nt-tour/v1');

        ?>
        <div class="wrap nt-tour-wrap">
            <?php
            $instance->render_header(
                __('API Documentation', 'nt-tour-booking'),
                __('Tài liệu API cho ứng dụng Next.js của bạn', 'nt-tour-booking'),
                [
                    [
                        'label' => __('Test API', 'nt-tour-booking'),
                        'url' => '#',
                        'icon' => 'terminal',
                        'class' => 'nt-btn-primary',
                        'onclick' => 'openApiTester()',
                    ],
                ]
            );
            ?>

            <!-- API Base URL -->
            <div class="nt-card mb-6">
                <div class="nt-card__header">
                    <h3 class="nt-card__title">
                        <i data-lucide="code" class="w-5 h-5 mr-2"></i>
                        <?php _e('Base URL', 'nt-tour-booking'); ?>
                    </h3>
                </div>
                <div class="nt-card__body">
                    <div class="flex items-center gap-4">
                        <code class="bg-gray-100 px-4 py-2 rounded font-mono text-sm flex-1"><?php echo esc_html($api_base); ?></code>
                        <button type="button" class="nt-btn nt-btn-secondary nt-btn-sm" onclick="copyToClipboard('<?php echo esc_attr($api_base); ?>')">
                            <i data-lucide="copy" class="w-4 h-4 mr-1"></i>Copy
                        </button>
                    </div>
                </div>
            </div>

            <!-- Auth Info -->
            <div class="nt-card mb-6">
                <div class="nt-card__header">
                    <h3 class="nt-card__title">
                        <i data-lucide="shield" class="w-5 h-5 mr-2"></i>
                        <?php _e('Authentication', 'nt-tour-booking'); ?>
                    </h3>
                </div>
                <div class="nt-card__body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium mb-2"><?php _e('Public Endpoints', 'nt-tour-booking'); ?></h4>
                            <p class="text-sm text-gray-600 mb-2"><?php _e('Không cần authentication cho các public endpoints.', 'nt-tour-booking'); ?></p>
                            <pre class="bg-gray-100 p-3 rounded text-sm overflow-x-auto"><code>Content-Type: application/json</code></pre>
                        </div>
                        <div>
                            <h4 class="font-medium mb-2"><?php _e('Admin Endpoints', 'nt-tour-booking'); ?></h4>
                            <p class="text-sm text-gray-600 mb-2"><?php _e('Cần nonce token từ WordPress admin.', 'nt-tour-booking'); ?></p>
                            <pre class="bg-gray-100 p-3 rounded text-sm overflow-x-auto"><code>Content-Type: application/json
X-WP-Nonce: [your-nonce]</code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Start -->
            <div class="nt-card mb-6">
                <div class="nt-card__header">
                    <h3 class="nt-card__title">
                        <i data-lucide="rocket" class="w-5 h-5 mr-2"></i>
                        <?php _e('Quick Start - Next.js Integration', 'nt-tour-booking'); ?>
                    </h3>
                </div>
                <div class="nt-card__body">
                    <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>// 1. Tạo API client trong lib/api.ts
const API_BASE = '<?php echo esc_html($api_base); ?>';

export async function createBooking(data: any) {
  const res = await fetch(\`\${API_BASE}/bookings\`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });
  return res.json();
}

// 2. Lấy thông tin booking
export async function getBooking(code: string) {
  const res = await fetch(\`\${API_BASE}/bookings/\${code}\`);
  return res.json();
}

// 3. Admin: List bookings (cần nonce)
export async function listBookingsAdmin(filters: any, nonce: string) {
  const params = new URLSearchParams(filters);
  const res = await fetch(\`\${API_BASE}/admin/bookings?\${params}\`, {
    headers: { 'X-WP-Nonce': nonce },
  });
  return res.json();
}

// 4. Admin: Confirm payment
export async function confirmPayment(code: string, nonce: string) {
  const res = await fetch(\`\${API_BASE}/admin/bookings/\${code}/confirm-payment\`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
  });
  return res.json();
}</code></pre>
                </div>
            </div>

            <!-- Endpoint Reference -->
            <div class="space-y-6">
                <!-- Public APIs -->
                <div class="nt-card">
                    <div class="nt-card__header">
                        <h3 class="nt-card__title">
                            <i data-lucide="unlock" class="w-5 h-5 mr-2"></i>
                            <?php _e('Public APIs', 'nt-tour-booking'); ?>
                        </h3>
                    </div>
                    <div class="nt-card__body space-y-4">
                        <?php $instance->render_endpoint('POST', '/bookings', 'Tạo booking mới', ['tour_slug', 'departure_date', 'participants', 'main_contact', 'payment_method'], '{"tour_slug": "tours-1", "departure_date": "2024-12-25", "participants": 2, "main_contact": {"full_name": "Nguyen Van A", "phone": "0912345678", "email": "email@example.com"}, "payment_method": "transfer"}'); ?>
                        <?php $instance->render_endpoint('GET', '/bookings/{booking_id}', 'Lấy thông tin booking'); ?>
                        <?php $instance->render_endpoint('POST', '/bookings/lookup', 'Tra cứu booking', ['email', 'phone']); ?>
                        <?php $instance->render_endpoint('GET', '/departures', 'Danh sách departures', ['tour_id', 'per_page']); ?>
                        <?php $instance->render_endpoint('GET', '/departures/{id}', 'Chi tiết departure'); ?>
                        <?php $instance->render_endpoint('POST', '/checkin/verify', 'Xác minh QR check-in', ['qr_token', 'departure_id']); ?>
                    </div>
                </div>

                <!-- Admin APIs -->
                <div class="nt-card">
                    <div class="nt-card__header">
                        <h3 class="nt-card__title">
                            <i data-lucide="lock" class="w-5 h-5 mr-2"></i>
                            <?php _e('Admin APIs (Cần X-WP-Nonce)', 'nt-tour-booking'); ?>
                        </h3>
                    </div>
                    <div class="nt-card__body space-y-4">
                        <?php $instance->render_endpoint('GET', '/admin/bookings', 'Danh sách bookings', ['date_from', 'date_to', 'status', 'payment_status', 'search']); ?>
                        <?php $instance->render_endpoint('GET', '/admin/bookings/{code}', 'Chi tiết booking'); ?>
                        <?php $instance->render_endpoint('POST', '/admin/bookings/{code}/confirm-payment', 'Xác nhận thanh toán'); ?>
                        <?php $instance->render_endpoint('POST', '/admin/bookings/{code}/cancel', 'Hủy booking', ['reason']); ?>
                        <?php $instance->render_endpoint('POST', '/admin/bookings/{code}/send-magic-link', 'Gửi magic link'); ?>
                        <?php $instance->render_endpoint('GET', '/admin/departures', 'Danh sách departures', ['tour_id', 'date_from', 'date_to']); ?>
                        <?php $instance->render_endpoint('POST', '/admin/departures', 'Tạo departure', ['tour_id', 'start_date', 'adult_price', 'capacity']); ?>
                        <?php $instance->render_endpoint('PUT', '/admin/departures/{id}', 'Cập nhật departure'); ?>
                        <?php $instance->render_endpoint('DELETE', '/admin/departures/{id}', 'Xóa departure'); ?>
                        <?php $instance->render_endpoint('GET', '/admin/pickup-points', 'Danh sách điểm đón'); ?>
                        <?php $instance->render_endpoint('POST', '/admin/pickup-points', 'Tạo điểm đón', ['name', 'address', 'map_url']); ?>
                        <?php $instance->render_endpoint('GET', '/admin/vehicles', 'Danh sách xe'); ?>
                        <?php $instance->render_endpoint('GET', '/admin/vehicle-layouts', 'Danh sách seat layouts'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Tester Modal -->
        <?php $instance->render_modal('api-tester-modal', __('API Tester', 'nt-tour-booking'), 'xl'); ?>

        <script>
            jQuery(document).ready(function($) {
                if (typeof lucide !== 'undefined') lucide.createIcons();

                window.copyToClipboard = function(text) {
                    navigator.clipboard.writeText(text).then(function() {
                        showToast('success', '<?php esc_attr_e('Đã copy!', 'nt-tour-booking'); ?>');
                    });
                };

                window.openApiTester = function() {
                    var modal = $('#api-tester-modal');
                    var html = '<div class="space-y-4">';
                    html += '<div class="grid grid-cols-4 gap-4">';
                    html += '<div class="col-span-1">';
                    html += '<label class="block text-sm font-medium mb-1">Method</label>';
                    html += '<select id="tester-method" class="nt-input"><option value="GET">GET</option><option value="POST">POST</option><option value="PUT">PUT</option><option value="DELETE">DELETE</option></select>';
                    html += '</div>';
                    html += '<div class="col-span-3">';
                    html += '<label class="block text-sm font-medium mb-1">Endpoint</label>';
                    html += '<input type="text" id="tester-endpoint" class="nt-input font-mono" placeholder="/admin/bookings">';
                    html += '</div>';
                    html += '</div>';
                    html += '<div>';
                    html += '<label class="block text-sm font-medium mb-1">Headers (JSON)</label>';
                    html += '<textarea id="tester-headers" class="nt-input font-mono text-sm" rows="2">{"Content-Type": "application/json"}</textarea>';
                    html += '</div>';
                    html += '<div>';
                    html += '<label class="block text-sm font-medium mb-1">Body (JSON)</label>';
                    html += '<textarea id="tester-body" class="nt-input font-mono text-sm" rows="5" placeholder="{}"></textarea>';
                    html += '</div>';
                    html += '<button type="button" id="btn-test-api" class="nt-btn nt-btn-primary w-full">Send Request</button>';
                    html += '<div id="tester-result" class="hidden mt-4">';
                    html += '<div id="tester-status" class="mb-2 font-medium"></div>';
                    html += '<pre id="tester-response" class="bg-gray-100 p-4 rounded text-sm overflow-auto max-h-80"></pre>';
                    html += '</div>';
                    html += '</div>';

                    modal.find('.nt-modal-content').html(html);
                    modal.removeClass('hidden');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                };

                $(document).on('click', '#btn-test-api', function() {
                    var method = $('#tester-method').val();
                    var endpoint = $('#tester-endpoint').val();
                    var headers = $('#tester-headers').val();
                    var body = $('#tester-body').val();

                    var url = '<?php echo esc_js($api_base); ?>' + endpoint;
                    var headerObj = {};
                    try { headerObj = JSON.parse(headers); } catch(e) {}

                    $.ajax({
                        url: url,
                        method: method,
                        headers: headerObj,
                        data: method !== 'GET' && body ? body : null,
                        success: function(response, status, xhr) {
                            $('#tester-result').removeClass('hidden');
                            $('#tester-status').html('<span class="text-green-600">Status: ' + xhr.status + ' OK</span>');
                            $('#tester-response').text(JSON.stringify(response, null, 2));
                        },
                        error: function(xhr) {
                            $('#tester-result').removeClass('hidden');
                            $('#tester-status').html('<span class="text-red-600">Status: ' + xhr.status + ' Error</span>');
                            try {
                                var err = JSON.parse(xhr.responseText);
                                $('#tester-response').text(JSON.stringify(err, null, 2));
                            } catch(e) {
                                $('#tester-response').text(xhr.responseText || 'No response');
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

    private function render_endpoint(string $method, string $endpoint, string $description, array $params = [], string $example = ''): void
    {
        $method_colors = [
            'GET' => 'bg-green-500',
            'POST' => 'bg-blue-500',
            'PUT' => 'bg-yellow-500',
            'PATCH' => 'bg-orange-500',
            'DELETE' => 'bg-red-500',
        ];
        $bg_class = $method_colors[$method] ?? 'bg-gray-500';
        ?>
        <div class="border rounded-lg p-4 hover:bg-gray-50">
            <div class="flex items-center gap-3 mb-2">
                <span class="px-2 py-1 rounded text-xs font-bold text-white <?php echo esc_attr($bg_class); ?>"><?php echo esc_html($method); ?></span>
                <code class="flex-1 font-mono text-sm bg-gray-100 px-3 py-1 rounded"><?php echo esc_html($endpoint); ?></code>
                <button type="button" class="nt-btn nt-btn-ghost nt-btn-sm" onclick="copyToClipboard('<?php echo esc_attr($endpoint); ?>')" title="Copy endpoint">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-2"><?php echo esc_html($description); ?></p>
            <?php if (!empty($params)): ?>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($params as $param): ?>
                        <code class="text-xs bg-gray-200 px-2 py-1 rounded"><?php echo esc_html($param); ?></code>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($example): ?>
                <details class="mt-3">
                    <summary class="text-xs font-medium text-blue-600 cursor-pointer hover:text-blue-800">Xem ví dụ</summary>
                    <pre class="mt-2 bg-gray-900 text-gray-100 p-3 rounded text-xs overflow-x-auto"><code><?php echo esc_html($example); ?></code></pre>
                </details>
            <?php endif; ?>
        </div>
        <?php
    }
}
