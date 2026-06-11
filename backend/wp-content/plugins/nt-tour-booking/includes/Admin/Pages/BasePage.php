<?php
/**
 * Base Page Class
 *
 * Shared functionality for all admin pages.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin\Pages;

class BasePage
{
    /**
     * Plugin version
     */
    const VERSION = '0.1.0';

    /**
     * Get admin assets base URL
     */
    protected function asset_url(string $path): string
    {
        return NT_TOUR_BOOKING_PLUGIN_URL . 'assets/admin/' . ltrim($path, '/');
    }

    /**
     * Enqueue common admin assets
     */
    protected function enqueue_assets(): void
    {
        // DataTables
        wp_enqueue_style(
            'datatables-style',
            'https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css',
            [],
            '1.13.6'
        );
        wp_enqueue_style(
            'datatables-responsive',
            'https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css',
            [],
            '2.5.0'
        );

        wp_enqueue_script(
            'datatables',
            'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
            ['jquery'],
            '1.13.6',
            true
        );
        wp_enqueue_script(
            'datatables-responsive',
            'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js',
            ['jquery', 'datatables'],
            '2.5.0',
            true
        );

        // Tailwind CSS CDN
        wp_enqueue_style(
            'nt-tour-tailwind',
            'https://cdn.tailwindcss.com',
            [],
            self::VERSION
        );

        // Lucide Icons
        wp_enqueue_script(
            'nt-tour-lucide',
            'https://unpkg.com/lucide@latest',
            [],
            self::VERSION,
            true
        );

        // Custom admin CSS
        wp_enqueue_style(
            'nt-tour-admin',
            $this->asset_url('css/nt-tour-admin.css'),
            [],
            self::VERSION
        );

        // Custom admin JS
        wp_enqueue_script(
            'nt-tour-admin',
            $this->asset_url('js/nt-tour-admin.js'),
            ['jquery', 'datatables'],
            self::VERSION,
            true
        );

        // jQuery Serialize JSON
        wp_enqueue_script(
            'jquery-serializejson',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery.serializeJSON/3.2.1/jquery.serializejson.min.js',
            ['jquery'],
            '3.2.1',
            true
        );

        // Localize script data
        wp_localize_script('nt-tour-admin', 'ntAdmin', [
            'apiUrl' => rest_url('nt-tour/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'adminNonce' => wp_create_nonce('nt_tour_admin'),
            'pluginUrl' => NT_TOUR_BOOKING_PLUGIN_URL,
            'strings' => [
                'confirm_delete' => __('Bạn có chắc chắn muốn xóa?', 'nt-tour-booking'),
                'saving' => __('Đang lưu...', 'nt-tour-booking'),
                'saved' => __('Đã lưu!', 'nt-tour-booking'),
                'error' => __('Có lỗi xảy ra', 'nt-tour-booking'),
                'loading' => __('Đang tải...', 'nt-tour-booking'),
            ],
        ]);
    }

    /**
     * Render page header
     */
    protected function render_header(string $title, string $subtitle = '', array $actions = []): void
    {
        ?>
        <div class="nt-page-header">
            <div class="nt-page-header__left">
                <h1 class="nt-page-title"><?php echo esc_html($title); ?></h1>
                <?php if ($subtitle): ?>
                    <p class="nt-page-subtitle"><?php echo esc_html($subtitle); ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($actions)): ?>
                <div class="nt-page-header__actions">
                    <?php foreach ($actions as $action): ?>
                        <a href="<?php echo esc_url($action['url'] ?? '#'); ?>"
                           class="nt-btn <?php echo esc_attr($action['class'] ?? 'nt-btn-primary'); ?>"
                           <?php echo isset($action['onclick']) ? 'onclick="' . esc_attr($action['onclick']) . '"' : ''; ?>
                           <?php echo isset($action['target']) ? 'target="' . esc_attr($action['target']) . '"' : ''; ?>>
                            <?php if (isset($action['icon'])): ?>
                                <i data-lucide="<?php echo esc_attr($action['icon']); ?>"></i>
                            <?php endif; ?>
                            <?php echo esc_html($action['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render stat card
     */
    protected function render_stat_card(string $title, string $value, string $icon, string $color = 'blue'): void
    {
        $colors = [
            'blue' => 'bg-blue-50 text-blue-600 border-blue-200',
            'green' => 'bg-green-50 text-green-600 border-green-200',
            'yellow' => 'bg-yellow-50 text-yellow-600 border-yellow-200',
            'red' => 'bg-red-50 text-red-600 border-red-200',
            'purple' => 'bg-purple-50 text-purple-600 border-purple-200',
            'indigo' => 'bg-indigo-50 text-indigo-600 border-indigo-200',
        ];
        $colorClass = $colors[$color] ?? $colors['blue'];
        ?>
        <div class="nt-stat-card border rounded-lg p-4 <?php echo esc_attr($colorClass); ?>">
            <div class="nt-stat-card__icon">
                <i data-lucide="<?php echo esc_attr($icon); ?>"></i>
            </div>
            <div class="nt-stat-card__content">
                <p class="nt-stat-card__value"><?php echo esc_html($value); ?></p>
                <p class="nt-stat-card__title"><?php echo esc_html($title); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Render data table container
     */
    protected function render_table_container(string $id, array $columns): void
    {
        ?>
        <div class="nt-table-wrapper bg-white rounded-lg shadow overflow-hidden">
            <table id="<?php echo esc_attr($id); ?>" class="nt-data-table stripe hover order-column" style="width: 100%;">
                <thead>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <th data-data="<?php echo esc_attr($column['data'] ?? ''); ?>"
                                data-orderable="<?php echo $column['orderable'] ?? true ? 'true' : 'false'; ?>"
                                data-searchable="<?php echo $column['searchable'] ?? true ? 'true' : 'false'; ?>">
                                <?php echo esc_html($column['title'] ?? ''); ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render modal
     */
    protected function render_modal(string $id, string $title, string $size = 'md'): void
    {
        $sizes = [
            'sm' => 'max-w-md',
            'md' => 'max-w-2xl',
            'lg' => 'max-w-4xl',
            'xl' => 'max-w-6xl',
            'full' => 'max-w-full',
        ];
        $sizeClass = $sizes[$size] ?? $sizes['md'];
        ?>
        <div id="<?php echo esc_attr($id); ?>" class="nt-modal hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 w-full <?php echo esc_attr($sizeClass); ?>">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900" id="modal-title"><?php echo esc_html($title); ?></h3>
                            <button type="button" class="nt-modal-close text-gray-400 hover:text-gray-500">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="nt-modal-content">
                            <!-- Content will be injected here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render form field
     */
    protected function render_field(string $type, string $name, string $label, $value = '', array $options = []): void
    {
        $required = $options['required'] ?? false;
        $placeholder = $options['placeholder'] ?? '';
        $class = $options['class'] ?? '';
        $id = $options['id'] ?? 'field_' . $name;
        ?>
        <div class="nt-form-group">
            <label for="<?php echo esc_attr($id); ?>" class="block text-sm font-medium text-gray-700 mb-1">
                <?php echo esc_html($label); ?>
                <?php if ($required): ?><span class="text-red-500">*</span><?php endif; ?>
            </label>
            <?php switch ($type):
                case 'text':
                case 'email':
                case 'tel':
                case 'number':
                case 'date':
                case 'time': ?>
                    <input type="<?php echo esc_attr($type); ?>"
                           id="<?php echo esc_attr($id); ?>"
                           name="<?php echo esc_attr($name); ?>"
                           value="<?php echo esc_attr($value); ?>"
                           placeholder="<?php echo esc_attr($placeholder); ?>"
                           class="nt-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php echo esc_attr($class); ?>"
                        <?php echo $required ? 'required' : ''; ?>>
                    <?php break;

                case 'textarea': ?>
                    <textarea id="<?php echo esc_attr($id); ?>"
                              name="<?php echo esc_attr($name); ?>"
                              placeholder="<?php echo esc_attr($placeholder); ?>"
                              rows="<?php echo esc_attr($options['rows'] ?? 3); ?>"
                              class="nt-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php echo esc_attr($class); ?>"
                        <?php echo $required ? 'required' : ''; ?>><?php echo esc_textarea($value); ?></textarea>
                    <?php break;

                case 'select': ?>
                    <select id="<?php echo esc_attr($id); ?>"
                            name="<?php echo esc_attr($name); ?>"
                            class="nt-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php echo esc_attr($class); ?>"
                        <?php echo $required ? 'required' : ''; ?>>
                        <?php if (!empty($options['empty'])): ?>
                            <option value=""><?php echo esc_html($options['empty']); ?></option>
                        <?php endif; ?>
                        <?php foreach ($options['choices'] ?? [] as $opt_value => $opt_label): ?>
                            <option value="<?php echo esc_attr($opt_value); ?>" <?php selected($value, $opt_value); ?>>
                                <?php echo esc_html($opt_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php break;

                case 'checkbox': ?>
                    <input type="checkbox"
                           id="<?php echo esc_attr($id); ?>"
                           name="<?php echo esc_attr($name); ?>"
                           value="1"
                        <?php checked($value, '1'); ?>
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php echo esc_attr($class); ?>">
                    <?php break;
            endswitch; ?>
        </div>
        <?php
    }

    /**
     * Render toast notification
     */
    protected function render_toast(string $id = 'nt-toast'): void
    {
        ?>
        <div id="<?php echo esc_attr($id); ?>" class="nt-toast hidden fixed bottom-4 right-4 z-50">
            <div class="bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-3">
                <span class="nt-toast-icon"></span>
                <span class="nt-toast-message"></span>
            </div>
        </div>
        <?php
    }

    /**
     * Make API request
     */
    protected function api_request(string $endpoint, string $method = 'GET', array $data = []): array
    {
        $url = rest_url('nt-tour/v1') . ltrim($endpoint, '/');

        $args = [
            'method' => $method,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-WP-Nonce' => wp_create_nonce('wp_rest'),
            ],
        ];

        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $args['body'] = json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status = wp_remote_retrieve_response_code($response);

        return [
            'success' => $status >= 200 && $status < 300,
            'data' => $body,
            'status' => $status,
        ];
    }

    /**
     * Format currency
     */
    protected function format_currency(float $amount): string
    {
        return number_format($amount, 0, ',', '.') . 'đ';
    }

    /**
     * Format date
     */
    protected function format_date(string $date, string $format = 'd/m/Y'): string
    {
        return date($format, strtotime($date));
    }

    /**
     * Get status badge class
     */
    protected function get_status_class(string $status): string
    {
        $classes = [
            'pending_payment' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'completed' => 'bg-blue-100 text-blue-800',
            'expired_hold' => 'bg-gray-100 text-gray-800',
            'no_show' => 'bg-purple-100 text-purple-800',
            'unpaid' => 'bg-yellow-100 text-yellow-800',
            'paid' => 'bg-green-100 text-green-800',
            'deposit_paid' => 'bg-blue-100 text-blue-800',
            'refunded' => 'bg-gray-100 text-gray-800',
            'open' => 'bg-green-100 text-green-800',
            'closed' => 'bg-gray-100 text-gray-800',
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
        ];

        return $classes[$status] ?? 'bg-gray-100 text-gray-800';
    }
}