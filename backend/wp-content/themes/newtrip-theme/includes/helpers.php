<?php
/**
 * Helper functions for NewTrip Theme
 * 
 * Các hàm tiện ích dùng chung trong theme
 */

// =============================================================================
// PHONE NORMALIZATION
// =============================================================================

/**
 * Normalize Vietnamese phone number to standard format 0xxxxxxxxxx
 * Handles: +84, 84, 0 prefixes
 * 
 * @param string $phone Raw phone number
 * @return string Normalized phone number or empty string
 */
function newtrip_normalize_phone($phone) {
    if (empty($phone)) return '';
    
    // Remove all non-digit characters
    $clean = preg_replace('/[^0-9]/', '', $phone);
    
    // Handle +84 prefix
    if (substr($clean, 0, 3) === '840') {
        $clean = '0' . substr($clean, 3);
    }
    // Handle 84 prefix (without +)
    elseif (substr($clean, 0, 2) === '84') {
        $clean = '0' . substr($clean, 2);
    }
    
    return $clean;
}

// =============================================================================
// ACF FIELD GROUPS - Customer CPT
// =============================================================================

/**
 * Register ACF field group for Customer CPT (ACF Pro)
 */
add_action('acf/init', 'newtrip_register_customer_acf_fields');
function newtrip_register_customer_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) return;
    
    acf_add_local_field_group([
        'key' => 'group_customer_fields',
        'title' => 'Thông tin Khách hàng',
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'customer',
                ],
            ],
        ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => [],
        'fields' => [
            // Tab: Thông tin liên hệ
            [
                'key' => 'field_customer_tab_contact',
                'label' => 'Thông tin liên hệ',
                'name' => 'tab_contact',
                'type' => 'tab',
            ],
            [
                'key' => 'field_customer_phone',
                'label' => 'Số điện thoại',
                'name' => 'phone',
                'type' => 'text',
                'required' => 1,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_customer_email',
                'label' => 'Email',
                'name' => 'email',
                'type' => 'email',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_customer_birth_date',
                'label' => 'Ngày sinh',
                'name' => 'birth_date',
                'type' => 'date_picker',
                'display_format' => 'd/m/Y',
                'return_format' => 'd/m/Y',
                'wrapper' => ['width' => '33'],
            ],
            // Tab: Thông tin kinh doanh
            [
                'key' => 'field_customer_tab_stats',
                'label' => 'Thông tin kinh doanh',
                'name' => 'tab_stats',
                'type' => 'tab',
            ],
            [
                'key' => 'field_customer_total_bookings',
                'label' => 'Số chuyến đi',
                'name' => 'total_bookings',
                'type' => 'number',
                'disabled' => 1,
                'wrapper' => ['width' => '33'],
            ],
            [
                'key' => 'field_customer_total_spent',
                'label' => 'Tổng chi tiêu',
                'name' => 'total_spent',
                'type' => 'number',
                'disabled' => 1,
                'wrapper' => ['width' => '33'],
            ],
            [
                'key' => 'field_customer_last_booking',
                'label' => 'Đặt gần nhất',
                'name' => 'last_booking_date',
                'type' => 'text',
                'disabled' => 1,
                'wrapper' => ['width' => '34'],
            ],
            [
                'key' => 'field_customer_bookings_history',
                'label' => 'Lịch sử đặt tour',
                'name' => 'bookings_history',
                'type' => 'textarea',
                'disabled' => 1,
                'rows' => 6,
            ],
            // Tab: Ghi chú
            [
                'key' => 'field_customer_tab_notes',
                'label' => 'Ghi chú',
                'name' => 'tab_notes',
                'type' => 'tab',
            ],
            [
                'key' => 'field_customer_tags',
                'label' => 'Tags / Phân loại',
                'name' => 'tags',
                'type' => 'text',
                'placeholder' => 'VD: VIP, Tiềm năng, Khách quen',
            ],
            [
                'key' => 'field_customer_notes',
                'label' => 'Ghi chú',
                'name' => 'notes',
                'type' => 'textarea',
                'rows' => 4,
                'placeholder' => 'Thông tin thêm về khách hàng...',
            ],
        ],
    ]);
}

/**
 * Định dạng lịch sử đặt tour (bookings_history) của Khách hàng để hiển thị trực quan trong WordPress Admin
 */
add_filter('acf/load_value/name=bookings_history', 'newtrip_format_customer_bookings_history', 10, 3);
function newtrip_format_customer_bookings_history($value, $post_id, $field) {
    if (empty($value)) {
        return 'Chưa có lịch sử đặt tour.';
    }
    
    // Hỗ trợ cả mảng PHP (WP Post Meta tự unserialize) và JSON string
    $history = is_array($value) ? $value : json_decode($value, true);
    if (!is_array($history) || empty($history)) {
        return 'Chưa có lịch sử đặt tour.';
    }
    
    $lines = [];
    foreach ($history as $h) {
        $code = $h['booking_code'] ?? 'N/A';
        $tour = $h['tour_name'] ?? 'Không rõ tour';
        $dep_date = $h['departure_date'] ?? 'N/A';
        $date = !empty($dep_date) ? date('d/m/Y', strtotime($dep_date)) : 'N/A';
        
        $status = $h['status'] ?? 'pending';
        $status_label = 'Chờ xử lý';
        if ($status === 'confirmed') $status_label = 'Đã xác nhận';
        elseif ($status === 'completed') $status_label = 'Đã hoàn thành';
        elseif ($status === 'cancelled') $status_label = 'Đã hủy';
        elseif ($status === 'refunded') $status_label = 'Đã hoàn tiền';
        
        $payment = $h['payment_status'] ?? 'unpaid';
        $payment_label = 'Chưa thanh toán';
        if ($payment === 'paid') $payment_label = 'Đã thanh toán';
        elseif ($payment === 'partial') $payment_label = 'Thanh toán một phần';
        
        $role = ($h['is_representative'] ?? false) ? 'Người đặt' : 'Hành khách';
        
        $lines[] = sprintf(
            "- Mã: %s | Tour: %s | Ngày đi: %s | Trạng thái: %s (%s) | Vai trò: %s",
            $code,
            $tour,
            $date,
            $status_label,
            $payment_label,
            $role
        );
    }
    
    return implode("\n", $lines);
}

/**
 * Định dạng ngày đặt gần nhất để hiển thị trực quan trong WordPress Admin
 */
add_filter('acf/load_value/name=last_booking_date', 'newtrip_format_customer_last_booking_date', 10, 3);
function newtrip_format_customer_last_booking_date($value, $post_id, $field) {
    if (empty($value)) {
        return '—';
    }
    return date('d/m/Y H:i', strtotime($value));
}

/**
 * Register Customer Tag taxonomy
 */
add_action('init', 'newtrip_register_customer_tag_taxonomy');
function newtrip_register_customer_tag_taxonomy() {
    register_taxonomy('customer_tag', 'customer', [
        'labels' => [
            'name' => __('Tags Khách hàng', 'newtrip-theme'),
            'singular_name' => __('Tag', 'newtrip-theme'),
            'add_new_item' => __('Thêm Tag mới', 'newtrip-theme'),
        ],
        'public' => false,
        'show_ui' => true,
        'hierarchical' => false,
        'show_admin_column' => true,
        'query_var' => true,
    ]);
}

// =============================================================================
// REST API - Customer endpoints
// =============================================================================

// Permission check for staff endpoints
function newtrip_checkin_permission_check(WP_REST_Request $request) {
    $token = $request->get_header('x_staff_token');
    if (empty($token)) {
        $token = $request->get_header('x-staff-token');
    }
    if (empty($token)) {
        return new WP_Error('checkin_unauthorized', 'Thiếu token xác thực nhân viên', ['status' => 401]);
    }
    $stored = get_transient('newtrip_checkin_token_' . $token);
    if (!$stored) {
        return new WP_Error('checkin_unauthorized', 'Token hết hạn hoặc không hợp lệ', ['status' => 401]);
    }
    return true;
}

// Register routes
add_action('rest_api_init', function () {
    register_rest_route('newtrip/v1', '/customers', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_customers',
        'permission_callback' => 'newtrip_checkin_permission_check',
    ]);

    register_rest_route('newtrip/v1', '/customers/export', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_export_customers',
        'permission_callback' => 'newtrip_checkin_permission_check',
    ]);
});

function newtrip_api_get_customers(WP_REST_Request $request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'newtrip_customers';
    
    $search = sanitize_text_field((string) $request->get_param('search'));
    $page = max(1, intval($request->get_param('page') ?: 1));
    $per_page = min(100, max(10, intval($request->get_param('per_page') ?: 20)));
    $offset = ($page - 1) * $per_page;
    
    $orderby = sanitize_key($request->get_param('orderby') ?: 'id');
    $order = strtoupper(sanitize_key($request->get_param('order') ?: 'DESC'));
    if (!in_array($order, ['ASC', 'DESC'])) {
        $order = 'DESC';
    }
    
    $allowed_orderby = ['id', 'full_name', 'phone', 'email', 'birth_date', 'id_number', 'total_bookings', 'total_spent', 'last_booking_date', 'created_at', 'updated_at'];
    if (!in_array($orderby, $allowed_orderby)) {
        $orderby = 'id';
    }
    
    $where = "1=1";
    $params = [];
    
    if (!empty($search)) {
        $where .= " AND (full_name LIKE %s OR phone LIKE %s OR email LIKE %s OR id_number LIKE %s OR bookings_history LIKE %s)";
        $like = '%' . $wpdb->esc_like($search) . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    
    // Total count
    if (!empty($params)) {
        $total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE $where", $params));
    } else {
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");
    }
    
    $query_sql = "SELECT * FROM $table_name WHERE $where ORDER BY $orderby $order LIMIT %d OFFSET %d";
    $query_params = array_merge($params, [$per_page, $offset]);
    
    $rows = $wpdb->get_results($wpdb->prepare($query_sql, $query_params));
    
    $results = [];
    foreach ($rows as $row) {
        $history_raw = $row->bookings_history;
        $history = [];
        if (!empty($history_raw)) {
            $history = json_decode($history_raw, true);
        }
        if (!is_array($history)) {
            $history = [];
        }
        
        $birth_date = '';
        if (!empty($row->birth_date) && $row->birth_date !== '0000-00-00') {
            $time = strtotime($row->birth_date);
            $birth_date = $time ? date('d/m/Y', $time) : $row->birth_date;
        }
        
        $results[] = [
            'id' => intval($row->id),
            'name' => $row->full_name,
            'phone' => $row->phone,
            'email' => $row->email,
            'birth_date' => $birth_date,
            'id_number' => $row->id_number,
            'total_bookings' => intval($row->total_bookings),
            'total_spent' => floatval($row->total_spent),
            'last_booking_date' => $row->last_booking_date,
            'recent_tours' => array_slice($history, -3, 3),
        ];
    }
    
    $total_pages = ceil($total_items / $per_page);
    
    return new WP_REST_Response([
        'success' => true,
        'data' => $results,
        'meta' => [
            'total' => intval($total_items),
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => $total_pages,
        ]
    ], 200);
}

function newtrip_api_export_customers(WP_REST_Request $request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'newtrip_customers';
    
    $rows = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=customers_export_' . date('Y-m-d') . '.csv');
    
    // Thêm UTF-8 BOM để Excel hiển thị đúng dấu tiếng Việt
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Họ tên', 'SĐT', 'Email', 'Ngày sinh', 'Số CMND/CCCD', 'Số chuyến', 'Tổng chi tiêu', 'Ngày đặt gần nhất']);
    
    foreach ($rows as $row) {
        $birth_date = '';
        if (!empty($row->birth_date) && $row->birth_date !== '0000-00-00') {
            $time = strtotime($row->birth_date);
            $birth_date = $time ? date('d/m/Y', $time) : $row->birth_date;
        }
        
        fputcsv($output, [
            $row->id,
            $row->full_name,
            $row->phone,
            $row->email,
            $birth_date,
            $row->id_number,
            $row->total_bookings,
            $row->total_spent,
            $row->last_booking_date,
        ]);
    }
    fclose($output);
    exit;
}
