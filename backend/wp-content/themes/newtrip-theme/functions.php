<?php
/**
 * Doi Dep Adventure Theme Functions
 * Xây dựng hệ thống REST API Headless CMS cho newtrip.com.vn (Hỗ trợ ACF Pro)
 */

// 0. Theme support — bật Featured Image cho mọi CPT có khai 'thumbnail'
add_action('after_setup_theme', function () {
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('automatic-feed-links');
});

// Include helper functions
require_once get_template_directory() . '/includes/helpers.php';
require_once get_template_directory() . '/includes/rollback.php';

// CORS cho Frontend (Vercel + dev local)
add_action('rest_api_init', function () {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
    add_filter('rest_pre_serve_request', function ($value) {
        $allowed_origins = [
            'https://doi-dep.vercel.app',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
        ];
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        if (in_array($origin, $allowed_origins, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, OPTIONS');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: Authorization, X-WP-Nonce, Content-Type');
            header('Vary: Origin');
        }
        return $value;
    });
}, 15);

add_action( 'after_setup_theme', 'register_my_menu' );
function register_my_menu() {
  register_nav_menu( 'primary-menu', __( 'Primary Menu', 'newtrip-theme' ) );
  register_nav_menu( 'footer-menu', __( 'Footer Menu', 'newtrip-theme' ) );
}


// 1. Đăng ký Custom Post Types (Tours, Bookings, Rental Items, Pickup Points)
function newtrip_register_post_types() {
    // 1.1 CPT Tour (Chuyến đi)
    register_post_type('tour', [
        'labels' => [
            'name' => __('Tours', 'newtrip-theme'),
            'singular_name' => __('Tour', 'newtrip-theme'),
            'add_new_item' => __('Thêm Tour mới', 'newtrip-theme'),
            'edit_item' => __('Chỉnh sửa Tour', 'newtrip-theme'),
            'all_items' => __('Tất cả Tour', 'newtrip-theme'),
            'view_item' => __('Xem Tour', 'newtrip-theme'),
        ],
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        'menu_icon' => 'dashicons-palmtree',
    ]);

    // 1.2 CPT Booking (Đơn đặt tour)
    register_post_type('booking', [
        'labels' => [
            'name' => __('Đơn Đặt Tour', 'newtrip-theme'),
            'singular_name' => __('Đơn Đặt Tour', 'newtrip-theme'),
            'add_new_item' => __('Thêm Đơn đặt mới', 'newtrip-theme'),
            'edit_item' => __('Xem Đơn đặt', 'newtrip-theme'),
            'all_items' => __('Tất cả Đơn đặt', 'newtrip-theme'),
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'supports' => ['title', 'custom-fields'],
        'menu_icon' => 'dashicons-tickets-alt',
    ]);

    // 1.3 CPT Rental Item (Đồ thuê)
    register_post_type('rental_item', [
        'labels' => [
            'name' => __('Đồ Thuê', 'newtrip-theme'),
            'singular_name' => __('Đồ Thuê', 'newtrip-theme'),
            'add_new_item' => __('Thêm Đồ thuê mới', 'newtrip-theme'),
            'edit_item' => __('Chỉnh sửa Đồ thuê', 'newtrip-theme'),
            'all_items' => __('Tất cả Đồ thuê', 'newtrip-theme'),
        ],
        'public' => true,
        'show_in_rest' => true,
        'supports' => ['title', 'custom-fields'],
        'menu_icon' => 'dashicons-store',
    ]);

    // 1.4 CPT Pickup Point (Điểm đón)
    register_post_type('pickup_point', [
        'labels' => [
            'name' => __('Điểm Đón', 'newtrip-theme'),
            'singular_name' => __('Điểm Đón', 'newtrip-theme'),
            'add_new_item' => __('Thêm Điểm đón mới', 'newtrip-theme'),
            'edit_item' => __('Chỉnh sửa Điểm đón', 'newtrip-theme'),
            'all_items' => __('Tất cả Điểm đón', 'newtrip-theme'),
        ],
        'public' => true,
        'show_in_rest' => true,
        'supports' => ['title', 'custom-fields'],
        'menu_icon' => 'dashicons-location',
    ]);

    // 1.5 CPT Tour Service (Dịch vụ tour bổ sung)
    register_post_type('tour_service', [
        'labels' => [
            'name' => __('Dịch vụ Tour', 'newtrip-theme'),
            'singular_name' => __('Dịch vụ Tour', 'newtrip-theme'),
            'add_new_item' => __('Thêm Dịch vụ mới', 'newtrip-theme'),
            'edit_item' => __('Chỉnh sửa Dịch vụ', 'newtrip-theme'),
            'all_items' => __('Tất cả Dịch vụ', 'newtrip-theme'),
        ],
        'public' => true,
        'show_in_rest' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'menu_icon' => 'dashicons-plus-alt',
    ]);

    // 1.6 CPT Customer (Khách hàng - Remarketing)
    register_post_type('customer', [
        'labels' => [
            'name' => __('Khách Hàng', 'newtrip-theme'),
            'singular_name' => __('Khách Hàng', 'newtrip-theme'),
            'add_new_item' => __('Thêm Khách hàng mới', 'newtrip-theme'),
            'edit_item' => __('Chỉnh sửa Khách hàng', 'newtrip-theme'),
            'all_items' => __('Tất cả Khách hàng', 'newtrip-theme'),
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'supports' => ['title', 'custom-fields'],
        'menu_icon' => 'dashicons-admin-users',
    ]);
}
add_action('init', 'newtrip_register_post_types');

// 1.6 ACF Options Page — Cấu hình thanh toán (VietQR)
add_action('acf/init', function () {
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => 'Cấu hình thanh toán',
            'menu_title' => 'Thanh toán',
            'menu_slug'  => 'newtrip-payment-settings',
            'capability' => 'manage_options',
            'icon_url'   => 'dashicons-money-alt',
            'redirect'   => false,
        ]);

        acf_add_options_page([
            'page_title' => 'Cài đặt chung Đôi Dép',
            'menu_title' => 'Cài đặt chung',
            'menu_slug'  => 'newtrip-general-settings',
            'capability' => 'manage_options',
            'icon_url'   => 'dashicons-admin-settings',
            'redirect'   => false,
        ]);
    }
});

// Helper lấy bank info từ ACF options. Trả null nếu admin chưa cấu hình đủ.
// KHÔNG fallback default — bank info bắt buộc admin phải set, để tránh chuyển tiền sai TK.
function newtrip_get_bank_info() {
    if (!function_exists('get_field')) {
        return null;
    }
    $bank_name    = trim((string) get_field('bank_name', 'option'));
    $bank_bin     = trim((string) get_field('bank_bin', 'option'));
    $account_no   = trim((string) get_field('account_no', 'option'));
    $account_name = trim((string) get_field('account_name', 'option'));

    if ($bank_name === '' || $bank_bin === '' || $account_no === '' || $account_name === '') {
        return null;
    }
    return [
        'bank_name'    => $bank_name,
        'bank_bin'     => $bank_bin,
        'account_no'   => $account_no,
        'account_name' => $account_name,
    ];
}

// Helper format 1 tour_service post thành mảng phẳng cho REST output
function newtrip_format_tour_service($service_post) {
    $sid = $service_post->ID;
    return [
        'id'          => $service_post->post_name,
        'post_id'     => $sid,
        'name'        => $service_post->post_title,
        'description' => (string) newtrip_get_field('description', $sid),
        'price'       => floatval(newtrip_get_field('price', $sid)),
        'unit'        => (string) newtrip_get_field('unit', $sid),
        'icon'        => (string) newtrip_get_field('icon', $sid),
    ];
}

// Lấy danh sách services gắn vào 1 tour. Trả mảng đã format. Nếu tour chưa khai → mảng rỗng.
function newtrip_get_tour_services_for_post($tour_id) {
    $service_ids = newtrip_get_field('services', $tour_id);
    if (empty($service_ids) || !is_array($service_ids)) {
        return [];
    }
    $out = [];
    foreach ($service_ids as $sid) {
        $sid = is_object($sid) ? $sid->ID : intval($sid);
        $sp = get_post($sid);
        if ($sp && $sp->post_type === 'tour_service' && $sp->post_status === 'publish') {
            $out[] = newtrip_format_tour_service($sp);
        }
    }
    return $out;
}

// Tra cứu 1 service theo slug (cho booking flow) — chỉ trong scope của tour
function newtrip_find_tour_service_by_slug($tour_id, $slug) {
    foreach (newtrip_get_tour_services_for_post($tour_id) as $s) {
        if ($s['id'] === $slug || (string)$s['post_id'] === (string)$slug) return $s;
    }
    return null;
}

// 2. Cấu hình tự động đồng bộ trường dữ liệu ACF sang Git dưới dạng JSON
add_filter('acf/settings/save_json', 'newtrip_acf_json_save_point');
function newtrip_acf_json_save_point($path) {
    return get_stylesheet_directory() . '/acf-json';
}

add_filter('acf/settings/load_json', 'newtrip_acf_json_load_point');
function newtrip_acf_json_load_point($paths) {
    unset($paths[0]);
    $paths[] = get_stylesheet_directory() . '/acf-json';
    return $paths;
}

// 3. Các hàm bổ trợ phân tích dữ liệu (Hỗ trợ ACF Pro)
function newtrip_parse_newline_separated($value) {
    if (empty($value)) return [];
    if (is_array($value)) return $value; 
    $lines = explode("\n", str_replace("\r", "", $value));
    return array_values(array_filter(array_map('trim', $lines)));
}

// Lấy meta/field tương thích cả khi có/không có ACF
function newtrip_get_field($key, $post_id) {
    if (function_exists('get_field')) {
        return get_field($key, $post_id);
    }
    return get_post_meta($post_id, $key, true);
}

// Xử lý Thư viện ảnh ACF Pro. Thứ tự fallback:
// 1. Thư viện ảnh ACF (gallery field)
// 2. Featured Image (ảnh đại diện WP)
// 3. Ảnh mặc định trong Options Page → "Cài đặt chung"
// 4. Empty array (FE tự fallback)
function newtrip_parse_gallery_pro($value, $post_id) {
    if (empty($value)) {
        $thumbnail_id = get_post_thumbnail_id($post_id);
        if ($thumbnail_id) {
            $url = wp_get_attachment_url($thumbnail_id);
            if ($url) return [$url];
        }
        if (function_exists('get_field')) {
            $default = get_field('default_tour_image', 'option');
            if (is_string($default) && $default !== '') {
                return [$default];
            }
            if (is_array($default) && !empty($default['url'])) {
                return [$default['url']];
            }
        }
        return [];
    }
    if (is_array($value)) {
        $urls = [];
        foreach ($value as $item) {
            if (is_string($item)) {
                $urls[] = $item;
            } elseif (is_array($item) && isset($item['url'])) {
                $urls[] = $item['url'];
            }
        }
        return $urls;
    }
    // Hỗ trợ fallback dạng văn bản nếu chưa cấu hình ACF Pro Gallery
    return newtrip_parse_newline_separated($value);
}

// Xử lý Lịch trình ACF Pro Repeater
function newtrip_parse_itinerary_pro($value) {
    if (empty($value)) return [];
    $itinerary = [];
    if (is_array($value)) {
        foreach ($value as $row) {
            $itinerary[] = [
                'time' => isset($row['time']) ? trim($row['time']) : '',
                'activity' => isset($row['activity']) ? trim($row['activity']) : '',
                'icon' => isset($row['icon']) ? trim($row['icon']) : 'default'
            ];
        }
        return $itinerary;
    }
    
    // Fallback nếu cấu hình dạng Textarea cũ (Giờ|Hoạt động)
    $lines = explode("\n", str_replace("\r", "", $value));
    foreach ($lines as $line) {
        $parts = explode('|', $line, 2);
        if (count($parts) === 2) {
            $itinerary[] = [
                'time' => trim($parts[0]),
                'activity' => trim($parts[1]),
                'icon' => 'default'
            ];
        }
    }
    return $itinerary;
}

// Xử lý Thông số trekking ACF Pro Repeater (với fallback sang trường cũ)
function newtrip_parse_specs_pro($value, $post_id) {
    $specs = [];
    if (is_array($value) && !empty($value)) {
        foreach ($value as $row) {
            $specs[] = [
                'label' => isset($row['label']) ? trim($row['label']) : '',
                'value' => isset($row['value']) ? trim($row['value']) : '',
                'icon' => isset($row['icon']) ? trim($row['icon']) : 'footprints'
            ];
        }
        return $specs;
    }
    
    // Fallback sang các trường cấu hình đơn lẻ cũ
    $distance = newtrip_get_field('distance', $post_id);
    $elevation = newtrip_get_field('elevation', $post_id);
    $max_altitude = newtrip_get_field('max_altitude', $post_id);
    $terrain = newtrip_get_field('terrain', $post_id);
    $age_min = newtrip_get_field('age_min', $post_id);
    $fitness = newtrip_get_field('fitness', $post_id);
    
    if ($distance) {
        $specs[] = [
            'label' => 'Quãng đường',
            'value' => $distance,
            'icon' => 'footprints'
        ];
    }
    if ($elevation) {
        $specs[] = [
            'label' => 'Độ cao dốc',
            'value' => $elevation,
            'icon' => 'mountain'
        ];
    }
    if ($max_altitude) {
        $specs[] = [
            'label' => 'Độ cao cực đại',
            'value' => $max_altitude,
            'icon' => 'chevron-right'
        ];
    }
    if ($terrain) {
        $specs[] = [
            'label' => 'Địa hình',
            'value' => $terrain,
            'icon' => 'compass'
        ];
    }
    if ($age_min) {
        $specs[] = [
            'label' => 'Độ tuổi',
            'value' => $age_min,
            'icon' => 'users'
        ];
    }
    if ($fitness) {
        $specs[] = [
            'label' => 'Thể lực',
            'value' => $fitness,
            'icon' => 'flame'
        ];
    }
    
    return $specs;
}

// Xử lý Ngày đi & Số chỗ trống từ ACF Pro Repeater
function newtrip_parse_departure_dates_pro($value, $post_id) {
    $dates = [];
    if (is_array($value)) {
        foreach ($value as $row) {
            if (!empty($row['date'])) {
                $spots = intval($row['available_spots'] ?? 15);
                $dates[] = [
                    'date' => $row['date'],
                    'available_spots' => $spots,
                    'total_spots' => $spots + 10,
                    'status' => $spots > 0 ? 'available' : 'full',
                ];
            }
        }
        return $dates;
    }
    
    // Fallback nếu cấu hình dạng Textarea cũ
    $dates_list = newtrip_parse_newline_separated($value);
    $available_spots = intval(newtrip_get_field('available_spots', $post_id) ?: 10);
    foreach ($dates_list as $d) {
        $dates[] = [
            'date' => $d,
            'available_spots' => $available_spots,
            'total_spots' => $available_spots + 10,
            'status' => $available_spots > 0 ? 'available' : 'full',
        ];
    }
    return $dates;
}

// Xử lý Danh sách trang bị từ ACF Pro Repeater
function newtrip_parse_gear_list_pro($value) {
    if (empty($value)) return [];
    $gear_list = [];
    if (is_array($value)) {
        foreach ($value as $row) {
            $gear_list[] = [
                'icon' => isset($row['icon']) ? trim($row['icon']) : '👟',
                'name' => isset($row['name']) ? trim($row['name']) : '',
                'important' => !empty($row['important'])
            ];
        }
        return $gear_list;
    }
    return [];
}

// Loại bỏ dấu tiếng Việt để tạo nội dung chuyển khoản chuẩn không dấu
function newtrip_remove_accents($str) {
    $unicode = array(
        'a'=>'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
        'd'=>'đ',
        'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'i'=>'í|ì|ỉ|ĩ|ị',
        'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
        'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
        'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
        'D'=>'Đ',
        'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
        'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
        'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
        'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
        'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
    );
    foreach($unicode as $nonUnicode=>$uni){
        $str = preg_replace("/($uni)/i", $nonUnicode, $str);
    }
    return $str;
}

// Auto-strip dấu tiếng Việt khi WP tạo slug cho post tour / tour_service / post / pickup_point / rental_item.
// Hook vào `sanitize_title` với priority 9 (chạy TRƯỚC `sanitize_title_with_dashes` priority 10).
add_filter('sanitize_title', function ($title, $raw_title = '', $context = 'display') {
    if ($context !== 'save') {
        return $title;
    }
    // Nếu raw_title chứa ký tự tiếng Việt → strip dấu trước khi sanitize tiếp.
    $source = !empty($raw_title) ? $raw_title : $title;
    if (preg_match('/[À-ỹĐđ]/u', $source)) {
        return newtrip_remove_accents($source);
    }
    return $title;
}, 9, 3);

// Tính checksum CRC-16 CCITT cho VietQR/EMVCo
function newtrip_calculate_crc16($data) {
    $crc = 0xFFFF;
    $polynomial = 0x1021;
    $length = strlen($data);
    for ($i = 0; $i < $length; $i++) {
        $crc ^= (ord($data[$i]) << 8);
        for ($j = 0; $j < 8; $j++) {
            if (($crc & 0x8000) !== 0) {
                $crc = (($crc << 1) ^ $polynomial) & 0xFFFF;
            } else {
                $crc = ($crc << 1) & 0xFFFF;
            }
        }
    }
    return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
}

// Tạo chuỗi VietQR chuẩn EMVCo
function newtrip_generate_vietqr_payload($bank_bin, $account_no, $amount, $description, $account_name = '') {
    $emvco = '';
    
    // Tag 00: Payload Format Indicator (01)
    $emvco .= sprintf('%02s%02d%s', '00', 2, '01');
    
    // Tag 01: Point of Initiation Method (12: Dynamic QR with amount, 11: Static)
    $emvco .= sprintf('%02s%02d%s', '01', 2, '12');
    
    // Tag 38: Merchant Account Information
    $sub_sub_tag00 = sprintf('%02s%02d%s', '00', strlen($bank_bin), $bank_bin);
    $sub_sub_tag01 = sprintf('%02s%02d%s', '01', strlen($account_no), $account_no);
    $sub_tag01 = sprintf('%02s%02d%s', '01', strlen($sub_sub_tag00 . $sub_sub_tag01), $sub_sub_tag00 . $sub_sub_tag01);
    $sub_tag00 = sprintf('%02s%02d%s', '00', 10, 'A000000727');
    $emvco .= sprintf('%02s%02d%s', '38', strlen($sub_tag00 . $sub_tag01), $sub_tag00 . $sub_tag01);
    
    // Tag 53: Transaction Currency (704: VND)
    $emvco .= sprintf('%02s%02d%s', '53', 3, '704');
    
    // Tag 54: Transaction Amount
    $emvco .= sprintf('%02s%02d%s', '54', strlen($amount), $amount);
    
    // Tag 58: Country Code (VN)
    $emvco .= sprintf('%02s%02d%s', '58', 2, 'VN');
    
    // Tag 59: Merchant Name (Account Name)
    $clean_account_name = newtrip_remove_accents($account_name);
    $clean_account_name = strtoupper(preg_replace('/[^A-Za-z0-9 ]/', '', $clean_account_name));
    if (!empty($clean_account_name)) {
        $emvco .= sprintf('%02s%02d%s', '59', strlen($clean_account_name), $clean_account_name);
    }
    
    // Tag 62: Additional Data Template
    $clean_desc = newtrip_remove_accents($description);
    $clean_desc = strtoupper(preg_replace('/[^A-Za-z0-9 ]/', '', $clean_desc));
    $sub_tag08 = sprintf('%02s%02d%s', '08', strlen($clean_desc), $clean_desc);
    $emvco .= sprintf('%02s%02d%s', '62', strlen($sub_tag08), $sub_tag08);
    
    // Tag 63: CRC16 checksum
    $emvco .= '6304';
    $crc = newtrip_calculate_crc16($emvco);
    
    return $emvco . $crc;
}

// Hỗ trợ trích xuất URL ảnh an toàn từ các trường dữ liệu ảnh Yoast SEO (chuỗi hoặc mảng)
function newtrip_get_yoast_image($image_field) {
    if (empty($image_field)) {
        return '';
    }
    if (is_array($image_field)) {
        if (isset($image_field[0]['url'])) {
            return $image_field[0]['url'];
        }
        $first = reset($image_field);
        if (is_array($first) && isset($first['url'])) {
            return $first['url'];
        }
        if (is_string($first)) {
            return $first;
        }
    }
    if (is_string($image_field)) {
        return $image_field;
    }
    return '';
}

// Lấy gói Yoast SEO meta cho 1 post. Trả null nếu Yoast chưa bật.
// Yoast expose method WPSEO_Frontend / API: `the_seo_framework` không có; dùng class `WPSEO_Meta` + getter.
function newtrip_get_yoast_seo($post_id) {
    if (!class_exists('WPSEO_Frontend') && !function_exists('YoastSEO')) {
        return null;
    }

    // Yoast >=14 cung cấp REST helper qua YoastSEO()->meta->for_post()
    if (function_exists('YoastSEO')) {
        try {
            $meta = YoastSEO()->meta->for_post($post_id);
            if (!$meta) return null;
            return [
                'title'           => (string) ($meta->title ?? ''),
                'description'     => (string) ($meta->description ?? ''),
                'canonical'       => (string) ($meta->canonical ?? ''),
                'og_title'        => (string) ($meta->open_graph_title ?? ''),
                'og_description'  => (string) ($meta->open_graph_description ?? ''),
                'og_image'        => newtrip_get_yoast_image($meta->open_graph_image ?? $meta->open_graph_images ?? ''),
                'og_type'         => (string) ($meta->open_graph_type ?? 'article'),
                'twitter_title'   => (string) ($meta->twitter_title ?? ''),
                'twitter_image'   => newtrip_get_yoast_image($meta->twitter_image ?? $meta->twitter_images ?? ''),
                'robots'          => is_array($meta->robots ?? null) ? implode(',', $meta->robots) : (string) ($meta->robots ?? 'index,follow'),
                'schema'          => method_exists($meta, 'schema') ? $meta->schema : null,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
    return null;
}

// 4. Biến đổi dữ liệu Post sang định dạng JSON mà Frontend yêu cầu
function newtrip_format_tour_list_item($post) {
    $post_id = $post->ID;
    $price = floatval(newtrip_get_field('price', $post_id));
    $gallery = newtrip_parse_gallery_pro(newtrip_get_field('gallery', $post_id), $post_id);
    
    $departure_dates_raw = newtrip_get_field('departure_dates', $post_id);
    $departure_dates = newtrip_parse_departure_dates_pro($departure_dates_raw, $post_id);
    
    // Tổng số chỗ còn lại của chuyến bay/chuyến đi tiếp theo
    $available_spots = !empty($departure_dates) ? $departure_dates[0]['available_spots'] : 0;
    $next_date = !empty($departure_dates) ? $departure_dates[0]['date'] : '';

    return [
        'id' => $post_id,
        'slug' => $post->post_name,
        'name' => $post->post_title,
        'description' => get_the_excerpt($post),
        'thumbnail' => !empty($gallery) ? $gallery[0] : '',
        'gallery' => $gallery,
        'image_filename' => basename($gallery[0] ?? ''),
        'price' => $price,
        'price_formatted' => number_format($price, 0, ',', '.') . 'đ',
        'difficulty' => newtrip_get_field('difficulty', $post_id) ?: 'easy',
        'duration' => newtrip_get_field('duration', $post_id) ?: '1 ngày',
        'available_spots' => $available_spots,
        'departure_times' => [newtrip_get_field('departure_time', $post_id) ?: 'Sáng'],
        'highlights' => newtrip_parse_newline_separated(newtrip_get_field('highlights', $post_id)),
        'next_departure_date' => $next_date,
        'total_departures' => count($departure_dates),
        'rating' => 4.9,
        'review_count' => 128,
        'seo' => newtrip_get_yoast_seo($post_id),
    ];
}

function newtrip_format_tour_detail($post) {
    $list_item = newtrip_format_tour_list_item($post);
    $post_id = $post->ID;

    // Lấy danh sách điểm đón
    $pickup_points_query = new WP_Query([
        'post_type' => 'pickup_point',
        'posts_per_page' => -1,
    ]);
    $pickup_points = [];
    if ($pickup_points_query->have_posts()) {
        foreach ($pickup_points_query->posts as $p) {
            $p_id = $p->ID;
            $pickup_points[] = [
                'id' => $p_id,
                'name' => $p->post_title,
                'address' => newtrip_get_field('address', $p_id),
                'pickup_time' => newtrip_get_field('pickup_time', $p_id) ?: '05:30',
                'time' => newtrip_get_field('pickup_time', $p_id) ?: '05:30',
            ];
        }
    }

    // Thiết lập dịch vụ kèm theo — đọc từ ACF relationship trên Tour (fallback sang mặc định)
    $services = newtrip_get_tour_services_for_post($post_id);

    $departure_dates_raw = newtrip_get_field('departure_dates', $post_id);
    $departure_dates = newtrip_parse_departure_dates_pro($departure_dates_raw, $post_id);

    return array_merge($list_item, [
        'content' => apply_filters('the_content', $post->post_content),
        'itinerary' => newtrip_parse_itinerary_pro(newtrip_get_field('itinerary', $post_id)),
        'included' => newtrip_parse_newline_separated(newtrip_get_field('included', $post_id)),
        'excluded' => newtrip_parse_newline_separated(newtrip_get_field('excluded', $post_id)),
        'notes' => newtrip_get_field('notes', $post_id) ?: '',
        'services' => $services,
        'departure_dates' => $departure_dates,
        'pickup_points' => $pickup_points,
        'distance' => newtrip_get_field('distance', $post_id) ?: '8-10 km',
        'elevation' => newtrip_get_field('elevation', $post_id) ?: '1.200m',
        'max_altitude' => newtrip_get_field('max_altitude', $post_id) ?: '1.500m',
        'terrain' => newtrip_get_field('terrain', $post_id) ?: 'Rừng, đồi, suối',
        'age_min' => newtrip_get_field('age_min', $post_id) ?: '16+',
        'fitness' => newtrip_get_field('fitness', $post_id) ?: 'Trung bình',
        'specs' => newtrip_parse_specs_pro(newtrip_get_field('specs', $post_id), $post_id),
        'gear_list' => newtrip_parse_gear_list_pro(newtrip_get_field('gear_list', $post_id)),
    ]);
}

// 5. Đăng ký các Endpoint REST API
add_action('rest_api_init', function () {
    // 5.1 GET /wp-json/newtrip/v1/tours - Lấy danh sách tour có bộ lọc
    register_rest_route('newtrip/v1', '/tours', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_tours',
        'permission_callback' => '__return_true',
    ]);

    // 5.2 GET /wp-json/newtrip/v1/tours/<slug> - Lấy chi tiết tour
    register_rest_route('newtrip/v1', '/tours/(?P<slug>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_tour_by_slug',
        'permission_callback' => '__return_true',
    ]);

    // 5.3 GET /wp-json/newtrip/v1/rental-items - Danh sách đồ thuê
    register_rest_route('newtrip/v1', '/rental-items', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_rental_items',
        'permission_callback' => '__return_true',
    ]);

    // 5.4 GET /wp-json/newtrip/v1/pickup-points - Danh sách điểm đón
    register_rest_route('newtrip/v1', '/pickup-points', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_pickup_points',
        'permission_callback' => '__return_true',
    ]);

    // 5.5 POST /wp-json/newtrip/v1/booking - Tạo booking đặt tour
    register_rest_route('newtrip/v1', '/booking', [
        'methods' => 'POST',
        'callback' => 'newtrip_api_create_booking',
        'permission_callback' => '__return_true',
    ]);

    // 5.6a GET /wp-json/newtrip/v1/booking/lookup - Tra cứu đơn theo email/phone
    // PHẢI ĐĂNG KÝ TRƯỚC route /booking/<id> để regex không nuốt từ 'lookup'
    register_rest_route('newtrip/v1', '/booking/lookup', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_lookup_bookings',
        'permission_callback' => '__return_true',
    ]);

    // 5.6 GET /wp-json/newtrip/v1/booking/<id> - Tra cứu đơn đặt tour
    register_rest_route('newtrip/v1', '/booking/(?P<id>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_booking',
        'permission_callback' => '__return_true',
    ]);

    // 5.6b POST /wp-json/newtrip/v1/booking/<id>/status - Admin cập nhật trạng thái booking
    register_rest_route('newtrip/v1', '/booking/(?P<id>[a-zA-Z0-9-]+)/status', [
        'methods' => 'POST',
        'callback' => 'newtrip_api_update_booking_status',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
    ]);

    // 5.6c POST /wp-json/newtrip/v1/booking/<id>/update-passengers - Cập nhật thông tin hành khách
    register_rest_route('newtrip/v1', '/booking/(?P<id>[a-zA-Z0-9-]+)/update-passengers', [
        'methods' => 'POST',
        'callback' => 'newtrip_api_update_booking_passengers',
        'permission_callback' => '__return_true',
    ]);

    // 5.6d POST /wp-json/newtrip/v1/upload - Tải tệp lên công cộng bảo mật
    register_rest_route('newtrip/v1', '/upload', [
        'methods' => 'POST',
        'callback' => 'newtrip_api_upload_file',
        'permission_callback' => '__return_true',
    ]);

    // 5.7 GET /wp-json/newtrip/v1/posts - Lấy danh sách bài viết
    register_rest_route('newtrip/v1', '/posts', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_posts',
        'permission_callback' => '__return_true',
    ]);

    // 5.8 GET /wp-json/newtrip/v1/posts/<id> - Lấy chi tiết bài viết
    register_rest_route('newtrip/v1', '/posts/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_post_by_id',
        'permission_callback' => '__return_true',
    ]);

    // 5.9 GET /wp-json/newtrip/v1/settings - Lấy cấu hình chung
    register_rest_route('newtrip/v1', '/settings', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_general_settings',
        'permission_callback' => '__return_true',
    ]);

    // 5.10 GET /wp-json/newtrip/v1/pages/<slug> - Lấy nội dung trang tĩnh (chính sách)
    register_rest_route('newtrip/v1', '/pages/(?P<slug>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_page_by_slug',
        'permission_callback' => '__return_true',
    ]);

    // 5.11 GET /wp-json/newtrip/v1/menus - Lấy danh sách menu (primary & footer)
    register_rest_route('newtrip/v1', '/menus', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_menus',
        'permission_callback' => '__return_true',
    ]);

    // 5.12 GET /wp-json/newtrip/v1/homepage - Lấy dữ liệu tĩnh của Trang chủ
    register_rest_route('newtrip/v1', '/homepage', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_homepage_data',
        'permission_callback' => '__return_true',
    ]);

    // 5.13 GET /wp-json/newtrip/v1/about - Lấy dữ liệu tĩnh của Trang Giới thiệu (About)
    register_rest_route('newtrip/v1', '/about', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_about_data',
        'permission_callback' => '__return_true',
    ]);

    // 5.14 GET /wp-json/newtrip/v1/contact - Lấy dữ liệu tĩnh của Trang Liên hệ (Contact)
    register_rest_route('newtrip/v1', '/contact', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_contact_data',
        'permission_callback' => '__return_true',
    ]);

    // 5.15 GET /wp-json/newtrip/v1/checkin/passengers - Lấy danh sách thành viên check-in
    register_rest_route('newtrip/v1', '/checkin/passengers', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_checkin_passengers',
        'permission_callback' => '__return_true',
    ]);

    // 5.16 POST /wp-json/newtrip/v1/checkin/toggle - Cập nhật trạng thái check-in (lên xe hoặc tập trung)
    register_rest_route('newtrip/v1', '/checkin/toggle', [
        'methods' => 'POST',
        'callback' => 'newtrip_api_toggle_checkin',
        'permission_callback' => '__return_true',
    ]);
});

// 6. Định nghĩa callbacks cho các API Endpoints

// 6.1 Lấy danh sách Tour
function newtrip_api_get_tours(WP_REST_Request $request) {
    $search = $request->get_param('search');
    $difficulty = $request->get_param('difficulty');
    $duration = $request->get_param('duration');
    $departure_time = $request->get_param('departure_time');
    $price_min = $request->get_param('price_min');
    $price_max = $request->get_param('price_max');
    $sort = $request->get_param('sort');
    
    $args = [
        'post_type' => 'tour',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ];

    if (!empty($search)) {
        $args['s'] = sanitize_text_field($search);
    }

    $meta_query = [];
    if (!empty($difficulty) && $difficulty !== 'all') {
        $meta_query[] = [
            'key' => 'difficulty',
            'value' => sanitize_text_field($difficulty),
            'compare' => '=',
        ];
    }
    if (!empty($departure_time) && $departure_time !== 'all') {
        $meta_query[] = [
            'key' => 'departure_time',
            'value' => sanitize_text_field($departure_time),
            'compare' => '=',
        ];
    }
    if (isset($price_min) && $price_min !== '') {
        $meta_query[] = [
            'key' => 'price',
            'value' => floatval($price_min),
            'type' => 'NUMERIC',
            'compare' => '>=',
        ];
    }
    if (isset($price_max) && $price_max !== '') {
        $meta_query[] = [
            'key' => 'price',
            'value' => floatval($price_max),
            'type' => 'NUMERIC',
            'compare' => '<=',
        ];
    }
    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }

    $query = new WP_Query($args);
    $data = [];
    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            $data[] = newtrip_format_tour_list_item($post);
        }
    }

    // Lọc theo thời lượng (duration) ở PHP
    if (!empty($duration)) {
        if ($duration === '1day') {
            $data = array_values(array_filter($data, function($t) { return $t['duration'] === '1 ngày'; }));
        } elseif ($duration === 'multi') {
            $data = array_values(array_filter($data, function($t) { return $t['duration'] !== '1 ngày'; }));
        }
    }

    // Sắp xếp (sorting) ở PHP
    if ($sort === 'price-asc') {
        usort($data, function($a, $b) {
            return $a['price'] <=> $b['price'];
        });
    } elseif ($sort === 'price-desc') {
        usort($data, function($a, $b) {
            return $b['price'] <=> $a['price'];
        });
    } elseif ($sort === 'rating-desc') {
        usort($data, function($a, $b) {
            return $b['review_count'] <=> $a['review_count']; // Sort by popularity
        });
    }

    // Phân trang ở PHP (để đảm bảo chính xác sau khi lọc ở PHP)
    $page = intval($request->get_param('page') ?: 1);
    $per_page = intval($request->get_param('per_page') ?: 10);
    
    $total_items = count($data);
    $total_pages = max(1, ceil($total_items / $per_page));
    
    $offset = ($page - 1) * $per_page;
    $paginated_data = array_slice($data, $offset, $per_page);

    return new WP_REST_Response([
        'success' => true,
        'data' => $paginated_data,
        'meta' => [
            'total' => $total_items,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => $total_pages
        ]
    ], 200);
}

// 6.2 Lấy chi tiết Tour
function newtrip_api_get_tour_by_slug(WP_REST_Request $request) {
    $slug = sanitize_text_field($request->get_param('slug'));
    
    $query = new WP_Query([
        'post_type' => 'tour',
        'name' => $slug,
        'posts_per_page' => 1,
    ]);

    if (!$query->have_posts()) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'tour_not_found', 'message' => 'Không tìm thấy tour yêu cầu']
        ], 404);
    }

    $detail = newtrip_format_tour_detail($query->posts[0]);
    return new WP_REST_Response([
        'success' => true,
        'data' => $detail
    ], 200);
}

// 6.3 Danh sách đồ thuê
function newtrip_api_get_rental_items(WP_REST_Request $request) {
    $query = new WP_Query([
        'post_type' => 'rental_item',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ]);
    
    $data = [];
    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            $p_id = $post->ID;
            $price = floatval(newtrip_get_field('price', $p_id));
            $data[] = [
                'id' => $post->post_name,
                'post_id' => $p_id,
                'name' => $post->post_title,
                'description' => newtrip_get_field('description', $p_id) ?: '',
                'price' => $price,
                'price_formatted' => number_format($price, 0, ',', '.') . 'đ',
                'unit' => newtrip_get_field('unit', $p_id) ?: 'ngày',
                'category' => newtrip_get_field('category', $p_id) ?: 'trekking',
                'icon' => newtrip_get_field('icon', $p_id) ?: '🎒',
                'stock_available' => intval(newtrip_get_field('stock_available', $p_id) ?: 10),
                'is_active' => true,
            ];
        }
    }
    
    return new WP_REST_Response([
        'success' => true,
        'data' => $data
    ], 200);
}

// 6.4 Danh sách điểm đón
function newtrip_api_get_pickup_points(WP_REST_Request $request) {
    $query = new WP_Query([
        'post_type' => 'pickup_point',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ]);
    
    $data = [];
    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            $p_id = $post->ID;
            $data[] = [
                'id' => $p_id,
                'name' => $post->post_title,
                'address' => newtrip_get_field('address', $p_id) ?: '',
                'latitude' => floatval(newtrip_get_field('latitude', $p_id) ?: 10.77),
                'longitude' => floatval(newtrip_get_field('longitude', $p_id) ?: 106.69),
                'pickup_time' => newtrip_get_field('pickup_time', $p_id) ?: '05:30',
                'notes' => newtrip_get_field('notes', $p_id) ?: '',
                'is_active' => true,
            ];
        }
    }
    
    return new WP_REST_Response([
        'success' => true,
        'data' => $data
    ], 200);
}

// 6.5 Đặt tour mới (POST) và khấu trừ số chỗ trống của ngày khởi hành tương ứng trong Repeater
function newtrip_api_create_booking(WP_REST_Request $request) {
    $params = $request->get_json_params();
    
    // Log request params
    file_put_contents(ABSPATH . 'wp-content/booking_debug.log', date('Y-m-d H:i:s') . " - CREATE BOOKING PARAMS: " . print_r($params, true) . "\n", FILE_APPEND);
    
    $tour_slug = isset($params['tour_slug']) ? sanitize_text_field($params['tour_slug']) : '';
    $departure_date = isset($params['departure_date']) ? sanitize_text_field($params['departure_date']) : '';
    $participants = isset($params['participants']) ? intval($params['participants']) : 1;
    $payment_method = isset($params['payment_method']) ? sanitize_text_field($params['payment_method']) : 'transfer';
    $notes = isset($params['notes']) ? sanitize_textarea_field($params['notes']) : '';
    
    $main_contact = isset($params['main_contact']) ? $params['main_contact'] : [];
    $full_name = isset($main_contact['full_name']) ? sanitize_text_field($main_contact['full_name']) : '';
    $phone = isset($main_contact['phone']) ? sanitize_text_field($main_contact['phone']) : '';
    $email = isset($main_contact['email']) ? sanitize_email($main_contact['email']) : '';

    if (empty($tour_slug) || empty($full_name) || empty($phone) || empty($email) || empty($departure_date)) {
        file_put_contents(ABSPATH . 'wp-content/booking_debug.log', date('Y-m-d H:i:s') . " - ERROR: missing_fields\n", FILE_APPEND);
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'missing_fields', 'message' => 'Vui lòng nhập đầy đủ các trường bắt buộc']
        ], 400);
    }

    if (!is_email($email)) {
        file_put_contents(ABSPATH . 'wp-content/booking_debug.log', date('Y-m-d H:i:s') . " - ERROR: invalid_email ($email)\n", FILE_APPEND);
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'invalid_email', 'message' => 'Email không hợp lệ']
        ], 400);
    }

    // Phone VN: bắt đầu bằng 0 hoặc +84, tổng cộng 10-11 chữ số (đã trừ '+')
    $phone_normalized = preg_replace('/[^\d+]/', '', $phone);
    if (!preg_match('/^(\+?84|0)\d{9,10}$/', $phone_normalized)) {
        file_put_contents(ABSPATH . 'wp-content/booking_debug.log', date('Y-m-d H:i:s') . " - ERROR: invalid_phone ($phone -> $phone_normalized)\n", FILE_APPEND);
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'invalid_phone', 'message' => 'Số điện thoại không hợp lệ (phải có 10-11 chữ số, bắt đầu bằng 0 hoặc +84)']
        ], 400);
    }

    if (!in_array($payment_method, ['cash', 'transfer'], true)) {
        file_put_contents(ABSPATH . 'wp-content/booking_debug.log', date('Y-m-d H:i:s') . " - ERROR: invalid_payment_method ($payment_method)\n", FILE_APPEND);
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'invalid_payment_method', 'message' => "payment_method phải là 'cash' hoặc 'transfer'"]
        ], 400);
    }

    $tour_query = new WP_Query([
        'post_type' => 'tour',
        'name' => $tour_slug,
        'posts_per_page' => 1,
    ]);

    if (!$tour_query->have_posts()) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'tour_not_found', 'message' => 'Không tìm thấy thông tin tour']
        ], 404);
    }

    $tour_post = $tour_query->posts[0];
    $tour_id = $tour_post->ID;

    $price = floatval(newtrip_get_field('price', $tour_id));

    // Khấu trừ chỗ trong ACF Pro Repeater của Tour
    $departure_dates_raw = newtrip_get_field('departure_dates', $tour_id);
    $updated_dates = [];
    $found_date = false;
    $available_spots = 0;

    if (is_array($departure_dates_raw)) {
        foreach ($departure_dates_raw as $row) {
            $row_date = isset($row['date']) ? $row['date'] : '';
            if ($row_date === $departure_date) {
                $available_spots = intval($row['available_spots'] ?? 0);
                if ($available_spots >= $participants) {
                    $row['available_spots'] = $available_spots - $participants;
                    $found_date = true;
                }
            }
            $updated_dates[] = $row;
        }
    }

    if (!$found_date) {
        file_put_contents(ABSPATH . 'wp-content/booking_debug.log', date('Y-m-d H:i:s') . " - ERROR: departure_full (requested: $departure_date, avail: $available_spots, parts: $participants)\n", FILE_APPEND);
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'departure_full', 'message' => 'Ngày khởi hành yêu cầu đã hết chỗ hoặc không đủ số lượng chỗ trống']
        ], 400);
    }

    // 1. Tính toán Dịch vụ bổ sung chọn kèm — đọc từ ACF services của tour
    $selected_services = isset($params['services']) && is_array($params['services']) ? $params['services'] : [];
    $services_data = [];
    $services_total = 0;
    foreach ($selected_services as $s_id) {
        $s_id = sanitize_text_field($s_id);
        $service = newtrip_find_tour_service_by_slug($tour_id, $s_id);
        if ($service) {
            $services_total += $service['price'] * $participants;
            $services_data[] = [
                'service_id' => $service['post_id'], // Save numeric Post ID
                'name'       => $service['name'],
                'price'      => $service['price'],
            ];
        }
    }

    // 2. Tính toán tiền Đồ thuê bổ sung
    $requested_rentals = isset($params['rental_items']) && is_array($params['rental_items']) ? $params['rental_items'] : [];
    $rental_items_data = [];
    $rental_total = 0;
    foreach ($requested_rentals as $r_id => $qty) {
        $qty = intval($qty);
        if ($qty <= 0) continue;
        
        $query_args = [
            'post_type' => 'rental_item',
            'posts_per_page' => 1,
        ];
        if (is_numeric($r_id)) {
            $query_args['p'] = intval($r_id);
        } else {
            $query_args['name'] = sanitize_title($r_id);
        }
        $rental_query = new WP_Query($query_args);
        
        if ($rental_query->have_posts()) {
            $rental_post = $rental_query->posts[0];
            $rental_item_id = $rental_post->ID;
            $r_price = floatval(newtrip_get_field('price', $rental_item_id));
            $r_name = $rental_post->post_title;
            
            $subtotal = $r_price * $qty;
            $rental_total += $subtotal;
            
            $rental_items_data[] = [
                'item_id' => $rental_item_id, // Save numeric Post ID
                'item_slug' => $rental_post->post_name,
                'name' => $r_name,
                'qty' => $qty,
                'price' => $r_price,
                'subtotal' => $subtotal,
            ];
        }
    }

    $total_amount = ($price * $participants) + $services_total + $rental_total;
    $booking_code = 'NTR-' . strtoupper(wp_generate_password(6, false));

    // 3. Khởi tạo Danh sách thành viên đặt chỗ (Passengers)
    $raw_passengers = isset($params['passengers']) && is_array($params['passengers']) ? $params['passengers'] : [];
    if (empty($raw_passengers)) {
        $raw_passengers = [
            [
                'full_name' => $full_name,
                'phone' => $phone,
                'email' => $email,
                'birth_year' => '',
                'id_number' => '',
                'pickup_point_id' => isset($params['pickup_point_id']) ? intval($params['pickup_point_id']) : 0,
            ]
        ];
    }

    $passengers_acf_data = [];
    $passengers_response_data = [];
    $p_id_counter = 1000;
    
    foreach ($raw_passengers as $idx => $p) {
        $p_name = isset($p['full_name']) ? sanitize_text_field($p['full_name']) : '';
        if (empty($p_name)) continue;
        
        $p_phone = isset($p['phone']) ? preg_replace('/[^\d+]/', '', sanitize_text_field($p['phone'])) : '';
        $p_email = isset($p['email']) ? sanitize_email($p['email']) : '';
        $p_birth = isset($p['birth_date']) ? sanitize_text_field($p['birth_date']) : (isset($p['birth_year']) ? sanitize_text_field($p['birth_year']) : '');
        $p_id_no = isset($p['id_number']) ? sanitize_text_field($p['id_number']) : '';
        $p_health = isset($p['health_status']) ? sanitize_text_field($p['health_status']) : '';
        $p_seat = isset($params['selected_seats'][$idx]) ? sanitize_text_field($params['selected_seats'][$idx]) : '';
        
        $p_pickup_point_id = isset($p['pickup_point_id']) ? intval($p['pickup_point_id']) : 0;
        if (!$p_pickup_point_id) {
            $p_pickup_point_id = isset($params['pickup_point_id']) ? intval($params['pickup_point_id']) : 0;
        }
        
        $pickup_name = '';
        if ($p_pickup_point_id) {
            $pickup_post = get_post($p_pickup_point_id);
            if ($pickup_post) {
                $pickup_name = $pickup_post->post_title;
            }
        }
        
        $passengers_acf_data[] = [
            'full_name' => $p_name,
            'phone' => $p_phone,
            'email' => $p_email,
            'birth_date' => $p_birth,
            'id_number' => $p_id_no,
            'pickup_point_id' => $p_pickup_point_id ?: null,
            'seat' => $p_seat,
            'checked_in' => 0,
            'health_status' => $p_health,
        ];
        
        $passengers_response_data[] = [
            'id' => $p_id_counter + $idx,
            'full_name' => $p_name,
            'seat' => $p_seat,
            'pickup_point' => $pickup_name,
            'checked_in' => false,
            'birth_date' => $p_birth,
            'id_number' => $p_id_no,
            'health_status' => $p_health,
            'qr_code_url' => sprintf('%s/qr/%s-P%d.png', untrailingslashit(get_site_url()), $booking_code, $p_id_counter + $idx),
        ];
    }

    // Lưu booking mới vào database
    $booking_id = wp_insert_post([
        'post_type' => 'booking',
        'post_title' => sprintf('[%s] %s', $booking_code, $full_name),
        'post_status' => 'publish',
    ]);

    if (is_wp_error($booking_id)) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'database_error', 'message' => 'Không thể lưu đơn đặt tour']
        ], 500);
    }

    // Lưu trữ thông tin meta và các trường ACF (Khuyên dùng ACF keys)
    if (function_exists('update_field')) {
        update_field('field_booking_code', $booking_code, $booking_id);
        update_field('field_booking_tour_id', $tour_id, $booking_id);
        update_field('field_booking_departure_date', $departure_date, $booking_id);
        update_field('field_booking_participants', $participants, $booking_id);
        update_field('field_booking_full_name', $full_name, $booking_id);
        update_field('field_booking_phone', $phone, $booking_id);
        update_field('field_booking_email', $email, $booking_id);
        update_field('field_booking_payment_method', $payment_method, $booking_id);
        update_field('field_booking_total_amount', $total_amount, $booking_id);
        update_field('field_booking_status', 'pending', $booking_id);
        update_field('field_booking_notes', $notes, $booking_id);

        $booking_pickup_point_id = isset($params['pickup_point_id']) ? intval($params['pickup_point_id']) : 0;
        update_field('field_booking_pickup_point_id', $booking_pickup_point_id ?: null, $booking_id);

        update_field('field_booking_services', $services_data, $booking_id);
        update_field('field_booking_rental_items', $rental_items_data, $booking_id);
        update_field('field_booking_passengers', $passengers_acf_data, $booking_id);
    } else {
        // Fallback lưu trực tiếp meta
        update_post_meta($booking_id, 'booking_code', $booking_code);
        update_post_meta($booking_id, 'tour_id', $tour_id);
        update_post_meta($booking_id, 'departure_date', $departure_date);
        update_post_meta($booking_id, 'participants', $participants);
        update_post_meta($booking_id, 'full_name', $full_name);
        update_post_meta($booking_id, 'phone', $phone);
        update_post_meta($booking_id, 'email', $email);
        update_post_meta($booking_id, 'payment_method', $payment_method);
        update_post_meta($booking_id, 'total_amount', $total_amount);
        update_post_meta($booking_id, 'status', 'pending');
        update_post_meta($booking_id, 'notes', $notes);
        
        $booking_pickup_point_id = isset($params['pickup_point_id']) ? intval($params['pickup_point_id']) : 0;
        update_post_meta($booking_id, 'pickup_point_id', $booking_pickup_point_id ?: null);
        update_post_meta($booking_id, 'services', $services_data);
        update_post_meta($booking_id, 'rental_items', $rental_items_data);
        update_post_meta($booking_id, 'passengers', $passengers_acf_data);
    }

    // payment_status & paid_amount lưu vào meta thường (không phải ACF field) để admin có thể cập nhật qua endpoint riêng
    update_post_meta($booking_id, 'payment_status', 'unpaid');
    update_post_meta($booking_id, 'paid_amount', 0);

    // Cập nhật lại số chỗ còn trống trên Tour
    if (function_exists('update_field')) {
        update_field('departure_dates', $updated_dates, $tour_id);
    } else {
        update_post_meta($tour_id, 'departure_dates', $updated_dates);
    }

    // Gửi email xác nhận kèm link bảo mật có token hết hạn trước chuyến đi
    $expires = newtrip_get_booking_update_expiration($departure_date);
    $secret_key = defined('NONCE_KEY') ? NONCE_KEY : 'newtrip_secure_salt_key_123';
    $token = hash_hmac('sha256', $booking_code . '|' . $email . '|' . $expires, $secret_key);
    
    $update_url = sprintf(
        'https://doi-dep.vercel.app/booking/update?bookingId=%s&email=%s&expires=%d&token=%s',
        $booking_code,
        urlencode($email),
        $expires,
        $token
    );
    $subject = sprintf('Xác nhận đặt tour %s [%s]', $tour_post->post_title, $booking_code);
    $body = sprintf(
        "Chào %s,\n\nCảm ơn bạn đã đặt tour tại Đôi Dép Adventure!\n\nChi tiết đơn đặt tour:\n" .
        "- Mã đặt tour: %s\n" .
        "- Tour: %s\n" .
        "- Ngày khởi hành: %s\n" .
        "- Số người: %d\n" .
        "- Tổng thanh toán: %s đ\n" .
        "- Phương thức: %s\n\n" .
        "Để bổ sung, cập nhật thông tin của các thành viên tham gia chuyến đi (như Ngày sinh, SĐT, Bệnh lý...), quý khách vui lòng truy cập liên kết sau:\n" .
        "%s\n\n" .
        "Chúng tôi sẽ liên hệ lại qua số điện thoại %s để xác nhận thông tin sớm nhất.\n\nTrân trọng,\nĐôi Dép Adventure.",
        $full_name,
        $booking_code,
        $tour_post->post_title,
        $departure_date,
        $participants,
        number_format($total_amount, 0, ',', '.'),
        $payment_method === 'transfer' ? 'Chuyển khoản ngân hàng' : 'Tiền mặt',
        $update_url,
        $phone
    );
    wp_mail($email, $subject, $body);

    return new WP_REST_Response([
        'success' => true,
        'data' => [
            'booking_id' => $booking_code,
            'status' => 'pending',
            'hold_expires_at' => date('c', time() + 7200),
            'total_amount' => $total_amount,
            'total_amount_formatted' => number_format($total_amount, 0, ',', '.') . 'đ',
            'breakdown' => [
                'tour_price' => $price * $participants,
                'services_total' => $services_total,
                'rental_total' => $rental_total,
                'rental_items' => array_map(function($item) {
                    return [
                        'id' => $item['item_slug'],
                        'name' => $item['name'],
                        'qty' => $item['qty'],
                        'subtotal' => $item['subtotal']
                    ];
                }, $rental_items_data)
            ],
            'deposit_amount' => 0,
            'remaining_amount' => $total_amount,
            'payment_method' => $payment_method,
            'payment_status' => 'unpaid',
            'passengers' => $passengers_response_data,
            'next_steps' => [
                "Kiểm tra email xác nhận",
                $payment_method === 'transfer' ? "Thực hiện thanh toán chuyển khoản qua mã QR" : "Thanh toán bằng tiền mặt khi gặp HDV",
                "Chờ xác nhận từ tổng đài viên"
            ]
        ]
    ], 200);
}

// 6.6 Tra cứu đơn hàng
function newtrip_api_get_booking(WP_REST_Request $request) {
    $booking_code = sanitize_text_field($request->get_param('id'));
    $email = sanitize_email($request->get_param('email'));
    $expires = intval($request->get_param('expires'));
    $token = sanitize_text_field($request->get_param('token'));

    if (empty($email)) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'missing_email', 'message' => 'Cần cung cấp email liên hệ để xác thực']
        ], 400);
    }

    // Nếu truyền token (từ trang update), tiến hành kiểm tra hết hạn và tính hợp lệ của chữ ký
    if (!empty($token)) {
        $verify = newtrip_verify_booking_token($booking_code, $email, $expires, $token);
        if ($verify === false) {
            return new WP_REST_Response([
                'success' => false,
                'error' => ['code' => 'invalid_token', 'message' => 'Liên kết xác thực không hợp lệ hoặc đã bị thay đổi']
            ], 403);
        } elseif ($verify === 'expired') {
            return new WP_REST_Response([
                'success' => false,
                'error' => ['code' => 'link_expired', 'message' => 'Liên kết cập nhật thông tin đã hết hạn trước khi chuyến đi bắt đầu']
            ], 400);
        }
    }
    
    $query = new WP_Query([
        'post_type' => 'booking',
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'booking_code',
                'value' => $booking_code,
                'compare' => '=',
            ],
            [
                'key' => 'email',
                'value' => $email,
                'compare' => '=',
            ]
        ],
        'posts_per_page' => 1,
    ]);

    if (!$query->have_posts()) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'booking_not_found', 'message' => 'Không tìm thấy đơn đặt tour yêu cầu']
        ], 404);
    }

    $post = $query->posts[0];
    $b_id = $post->ID;
    $tour_id_raw = newtrip_get_field('tour_id', $b_id);
    if (is_object($tour_id_raw)) {
        $tour_id = $tour_id_raw->ID;
    } elseif (is_array($tour_id_raw)) {
        $tour_id = $tour_id_raw['ID'] ?? 0;
    } else {
        $tour_id = intval($tour_id_raw);
    }
    if (empty($tour_id)) {
        $tour_id = intval(get_post_meta($b_id, 'tour_id', true));
    }
    $tour_post = get_post($tour_id);
    $total = floatval(newtrip_get_field('total_amount', $b_id));

    // Lấy thông tin thành viên tham gia (Passengers)
    $passengers_raw = newtrip_get_field('passengers', $b_id);
    $passengers = [];
    if (is_array($passengers_raw)) {
        foreach ($passengers_raw as $idx => $p) {
            $p_pickup_point_id = 0;
            if (isset($p['pickup_point_id'])) {
                if (is_object($p['pickup_point_id'])) {
                    $p_pickup_point_id = $p['pickup_point_id']->ID;
                } elseif (is_array($p['pickup_point_id']) && isset($p['pickup_point_id']['ID'])) {
                    $p_pickup_point_id = $p['pickup_point_id']['ID'];
                } else {
                    $p_pickup_point_id = intval($p['pickup_point_id']);
                }
            }
            $pickup_name = '';
            if ($p_pickup_point_id) {
                $pickup_post = get_post($p_pickup_point_id);
                if ($pickup_post) {
                    $pickup_name = $pickup_post->post_title;
                }
            }
            $passengers[] = [
                'id' => 1000 + $idx,
                'full_name' => $p['full_name'] ?? '',
                'phone' => $p['phone'] ?? '',
                'email' => $p['email'] ?? '',
                'seat' => $p['seat'] ?? '',
                'pickup_point' => $pickup_name,
                'pickup_point_id' => $p_pickup_point_id,
                'checked_in' => !empty($p['checked_in']) ? (bool)$p['checked_in'] : false,
                'birth_date' => $p['birth_date'] ?? ($p['birth_year'] ?? ''),
                'id_number' => $p['id_number'] ?? '',
                'health_status' => $p['health_status'] ?? '',
            ];
        }
    } else {
        $p_pickup_point_id = 0;
        $p_pickup_point = newtrip_get_field('pickup_point_id', $b_id);
        if ($p_pickup_point) {
            if (is_object($p_pickup_point)) {
                $p_pickup_point_id = $p_pickup_point->ID;
            } elseif (is_array($p_pickup_point) && isset($p_pickup_point['ID'])) {
                $p_pickup_point_id = $p_pickup_point['ID'];
            } else {
                $p_pickup_point_id = intval($p_pickup_point);
            }
        }
        
        $passengers = [
            [
                'id' => 1000,
                'full_name' => newtrip_get_field('full_name', $b_id),
                'phone' => newtrip_get_field('phone', $b_id),
                'email' => newtrip_get_field('email', $b_id),
                'pickup_point_id' => $p_pickup_point_id,
                'checked_in' => !empty(newtrip_get_field('checked_in', $b_id)) ? (bool)newtrip_get_field('checked_in', $b_id) : false,
                'birth_date' => newtrip_get_field('birth_date', $b_id) ?: (newtrip_get_field('birth_year', $b_id) ?: ''),
                'id_number' => newtrip_get_field('id_number', $b_id) ?: '',
                'health_status' => newtrip_get_field('health_status', $b_id) ?: '',
            ]
        ];
    }

    // Lấy thông tin đồ thuê (Rental items)
    $rental_items_raw = newtrip_get_field('rental_items', $b_id);
    $rental_items = [];
    if (is_array($rental_items_raw)) {
        foreach ($rental_items_raw as $r) {
            $item_id_raw = $r['item_id'] ?? '';
            $r_id = '';
            $r_name = $r['name'] ?? '';
            $r_price = isset($r['price']) && $r['price'] !== '' ? floatval($r['price']) : 0;
            $qty = intval($r['qty'] ?? 0);
            
            $post_obj = null;
            if (is_object($item_id_raw)) {
                $post_obj = $item_id_raw;
            } elseif (is_numeric($item_id_raw)) {
                $post_obj = get_post(intval($item_id_raw));
            }
            
            if ($post_obj && $post_obj->post_type === 'rental_item') {
                $r_id = $post_obj->post_name; // Lấy slug để đồng bộ FE
                if (empty($r_name)) {
                    $r_name = $post_obj->post_title;
                }
                if ($r_price <= 0) {
                    $r_price = floatval(newtrip_get_field('price', $post_obj->ID));
                }
            } else {
                $r_id = (string) $item_id_raw;
            }
            
            $subtotal = isset($r['subtotal']) && $r['subtotal'] !== '' ? floatval($r['subtotal']) : ($r_price * $qty);
            
            $rental_items[] = [
                'id' => $r_id,
                'name' => $r_name,
                'qty' => $qty,
                'subtotal' => $subtotal,
            ];
        }
    }

    // Lấy thông tin dịch vụ (Services)
    $services_raw = newtrip_get_field('services', $b_id);
    $services = [];
    if (is_array($services_raw)) {
        foreach ($services_raw as $s) {
            $service_id_raw = $s['service_id'] ?? '';
            $s_id = '';
            $s_name = $s['name'] ?? '';
            $s_price = isset($s['price']) && $s['price'] !== '' ? floatval($s['price']) : 0;
            
            $post_obj = null;
            if (is_object($service_id_raw)) {
                $post_obj = $service_id_raw;
            } elseif (is_numeric($service_id_raw)) {
                $post_obj = get_post(intval($service_id_raw));
            }
            
            if ($post_obj && $post_obj->post_type === 'tour_service') {
                $s_id = $post_obj->post_name; // Return slug string for frontend compatibility
                if (empty($s_name)) {
                    $s_name = $post_obj->post_title;
                }
                if ($s_price <= 0) {
                    $s_price = floatval(newtrip_get_field('price', $post_obj->ID));
                }
            } else {
                $s_id = (string) $service_id_raw;
            }
            
            $services[] = [
                'id' => $s_id,
                'name' => $s_name,
                'price' => $s_price,
            ];
        }
    }

    $method = newtrip_get_field('payment_method', $b_id);
    $status = newtrip_get_field('status', $b_id) ?: 'pending';

    // payment_status đọc từ field thật, fallback sang paid nếu status là confirmed hoặc completed
    $payment_status = get_post_meta($b_id, 'payment_status', true);
    if (empty($payment_status) || (($status === 'confirmed' || $status === 'completed') && $payment_status === 'unpaid')) {
        $payment_status = ($status === 'confirmed' || $status === 'completed') ? 'paid' : 'unpaid';
    }
    $paid_amount = floatval(get_post_meta($b_id, 'paid_amount', true));
    if ($payment_status === 'paid' && $paid_amount < $total) {
        $paid_amount = $total;
    }
    $remaining_amount = max(0, $total - $paid_amount);

    $bank_info = null;
    if ($method === 'transfer') {
        $bank_config = newtrip_get_bank_info();
        if ($bank_config) {
            $qr_payload = newtrip_generate_vietqr_payload(
                $bank_config['bank_bin'],
                $bank_config['account_no'],
                $total,
                $booking_code,
                $bank_config['account_name']
            );

            $bank_info = [
                'bank_name'    => $bank_config['bank_name'],
                'bank_bin'     => $bank_config['bank_bin'],
                'account_no'   => $bank_config['account_no'],
                'account_name' => $bank_config['account_name'],
                'amount'       => $total,
                'content'      => $booking_code,
                'qr_payload'   => $qr_payload,
                'qr_url'       => sprintf('https://img.vietqr.io/image/%s-%s-compact.png?amount=%d&addInfo=%s&accountName=%s', $bank_config['bank_bin'], $bank_config['account_no'], $total, urlencode($booking_code), urlencode($bank_config['account_name'])),
                'deeplink'     => sprintf('https://link.vietqr.io/2.0/referral/vietqr?bin=%s&account=%s&amount=%d&addInfo=%s', $bank_config['bank_bin'], $bank_config['account_no'], $total, $booking_code),
            ];
        }
    }

    return new WP_REST_Response([
        'success' => true,
        'data' => [
            'booking_id' => $booking_code,
            'status' => $status,
            'created_at' => get_the_date('c', $b_id),
            'tour' => [
                'name' => $tour_post ? $tour_post->post_title : 'Tour',
                'slug' => $tour_post ? $tour_post->post_name : '',
            ],
            'departure' => [
                'date' => newtrip_get_field('departure_date', $b_id),
                'departure_time' => $tour_post ? (string) newtrip_get_field('departure_time', $tour_id) : '',
            ],
            'main_contact' => [
                'full_name' => newtrip_get_field('full_name', $b_id),
                'phone' => newtrip_get_field('phone', $b_id),
                'email' => newtrip_get_field('email', $b_id),
            ],
            'passengers' => $passengers,
            'services' => $services,
            'rental_items' => $rental_items,
            'payment' => [
                'method' => $method,
                'total' => $total,
                'paid' => $paid_amount,
                'remaining' => $remaining_amount,
                'status' => $payment_status,
                'bank_info' => $bank_info
            ]
        ]
    ], 200);
}

// 6.6a Tra cứu danh sách đơn theo email/phone
function newtrip_api_lookup_bookings(WP_REST_Request $request) {
    $email = sanitize_email($request->get_param('email'));
    $phone = sanitize_text_field($request->get_param('phone'));

    if (empty($email) && empty($phone)) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'missing_params', 'message' => 'Cần cung cấp email hoặc số điện thoại để tra cứu']
        ], 400);
    }

    $meta_query = ['relation' => 'OR'];
    if (!empty($email)) {
        $meta_query[] = ['key' => 'email', 'value' => $email, 'compare' => '='];
    }
    if (!empty($phone)) {
        $meta_query[] = ['key' => 'phone', 'value' => $phone, 'compare' => '='];
    }

    $query = new WP_Query([
        'post_type'      => 'booking',
        'posts_per_page' => 50,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => $meta_query,
    ]);

    $rows = [];
    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            $b_id = $post->ID;
            $tour_id_raw = newtrip_get_field('tour_id', $b_id);
            if (is_object($tour_id_raw)) {
                $tour_id = $tour_id_raw->ID;
            } elseif (is_array($tour_id_raw)) {
                $tour_id = $tour_id_raw['ID'] ?? 0;
            } else {
                $tour_id = intval($tour_id_raw);
            }
            if (empty($tour_id)) {
                $tour_id = intval(get_post_meta($b_id, 'tour_id', true));
            }
            $tour_post = $tour_id ? get_post($tour_id) : null;
            $status = newtrip_get_field('status', $b_id) ?: 'pending';
            $passengers_raw = newtrip_get_field('passengers', $b_id);
            $passengers_count = is_array($passengers_raw) ? count($passengers_raw) : 1;
            $payment_status = get_post_meta($b_id, 'payment_status', true);
            if (empty($payment_status) || (($status === 'confirmed' || $status === 'completed') && $payment_status === 'unpaid')) {
                $payment_status = ($status === 'confirmed' || $status === 'completed') ? 'paid' : 'unpaid';
            }
            $rows[] = [
                'booking_id'       => newtrip_get_field('booking_code', $b_id),
                'tour_name'        => $tour_post ? $tour_post->post_title : '',
                'departure_date'   => newtrip_get_field('departure_date', $b_id),
                'status'           => $status,
                'passengers_count' => $passengers_count,
                'payment_method'   => newtrip_get_field('payment_method', $b_id),
                'total_amount'     => floatval(newtrip_get_field('total_amount', $b_id)),
                'payment_status'   => $payment_status,
            ];
        }
    }

    return new WP_REST_Response([
        'success' => true,
        'data' => $rows,
        'meta' => ['total' => count($rows)],
    ], 200);
}

// 6.6b Admin cập nhật trạng thái booking (xác nhận thanh toán, hủy đơn,...)
function newtrip_api_update_booking_status(WP_REST_Request $request) {
    $booking_code = sanitize_text_field($request->get_param('id'));
    $params = $request->get_json_params();
    if (!is_array($params)) $params = [];

    $allowed_status = ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'];
    $allowed_payment_status = ['unpaid', 'partial', 'paid', 'refunded'];

    $status = isset($params['status']) ? sanitize_text_field($params['status']) : null;
    $payment_status = isset($params['payment_status']) ? sanitize_text_field($params['payment_status']) : null;
    $paid_amount = isset($params['paid_amount']) ? floatval($params['paid_amount']) : null;
    $note = isset($params['note']) ? sanitize_textarea_field($params['note']) : '';

    if ($status !== null && !in_array($status, $allowed_status, true)) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'invalid_status', 'message' => 'Trạng thái không hợp lệ', 'data' => ['allowed' => $allowed_status]]
        ], 400);
    }
    if ($payment_status !== null && !in_array($payment_status, $allowed_payment_status, true)) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'invalid_payment_status', 'message' => 'Trạng thái thanh toán không hợp lệ', 'data' => ['allowed' => $allowed_payment_status]]
        ], 400);
    }
    if ($status === null && $payment_status === null && $paid_amount === null) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'missing_fields', 'message' => 'Cần cung cấp ít nhất một trong: status, payment_status, paid_amount']
        ], 400);
    }

    $query = new WP_Query([
        'post_type'  => 'booking',
        'meta_query' => [['key' => 'booking_code', 'value' => $booking_code, 'compare' => '=']],
        'posts_per_page' => 1,
    ]);

    if (!$query->have_posts()) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'booking_not_found', 'message' => 'Không tìm thấy đơn đặt tour']
        ], 404);
    }

    $b_id = $query->posts[0]->ID;

    if ($status !== null) {
        update_field('field_booking_status', $status, $b_id);
    }
    if ($payment_status !== null) {
        update_post_meta($b_id, 'payment_status', $payment_status);
    }
    if ($paid_amount !== null) {
        update_post_meta($b_id, 'paid_amount', $paid_amount);
    }
    if (!empty($note)) {
        $existing = get_post_meta($b_id, 'status_history', true);
        $history = is_array($existing) ? $existing : [];
        $history[] = [
            'time'           => current_time('mysql'),
            'user_id'        => get_current_user_id(),
            'status'         => $status,
            'payment_status' => $payment_status,
            'paid_amount'    => $paid_amount,
            'note'           => $note,
        ];
        update_post_meta($b_id, 'status_history', $history);
    }

    return new WP_REST_Response([
        'success' => true,
        'data' => [
            'booking_id'     => $booking_code,
            'status'         => $status ?? newtrip_get_field('status', $b_id),
            'payment_status' => $payment_status ?? get_post_meta($b_id, 'payment_status', true),
            'paid_amount'    => $paid_amount ?? floatval(get_post_meta($b_id, 'paid_amount', true)),
            'updated_at'     => current_time('c'),
        ],
    ], 200);
}

// 6.7 Lấy danh sách bài viết
function newtrip_api_get_posts(WP_REST_Request $request) {
    $posts = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    $data = [];
    if (!empty($posts)) {
        foreach ($posts as $post) {
            $data[] = newtrip_format_wp_post($post);
        }
    }

    return new WP_REST_Response([
        'success' => true,
        'data' => $data
    ], 200);
}

// 6.8 Lấy chi tiết bài viết theo ID
function newtrip_api_get_post_by_id(WP_REST_Request $request) {
    $id = intval($request->get_param('id'));
    
    $post = get_post($id);
    if ($post && $post->post_type === 'post' && $post->post_status === 'publish') {
        return new WP_REST_Response([
            'success' => true,
            'data' => newtrip_format_wp_post($post)
        ], 200);
    }

    return new WP_REST_Response([
        'success' => false,
        'error' => ['code' => 'post_not_found', 'message' => 'Không tìm thấy bài viết yêu cầu']
    ], 404);
}

// 6.9 Lấy cấu hình chung website Đôi Dép
function newtrip_api_get_general_settings(WP_REST_Request $request) {
    if (!function_exists('get_field')) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'acf_not_active', 'message' => 'ACF Pro is not active']
        ], 500);
    }
    
    $settings = [
        'default_tour_image' => get_field('default_tour_image', 'option') ?: '/images/default-tour.jpg',
        'hotline'            => get_field('hotline', 'option') ?: '096 180 43 59',
        'zalo_link'          => get_field('zalo_link', 'option') ?: 'https://zalo.me/0961804359',
        'contact_email'      => get_field('contact_email', 'option') ?: 'doidepadventure@gmail.com',
        'company_address'    => get_field('company_address', 'option') ?: 'TP. Hồ Chí Minh',
        'facebook_link'      => get_field('facebook_link', 'option') ?: '',
        'instagram_link'     => get_field('instagram_link', 'option') ?: '',
        'payment'            => newtrip_get_bank_info(),
    ];
    
    return new WP_REST_Response([
        'success' => true,
        'data' => $settings
    ], 200);
}

// Định dạng bài viết từ WordPress
function newtrip_format_wp_post($post) {
    $post_id = $post->ID;
    
    $categories = get_the_category($post_id);
    $category_name = !empty($categories) ? $categories[0]->name : 'Kinh nghiệm';
    
    $tags = get_the_tags($post_id);
    $tag_names = [];
    if (is_array($tags)) {
        foreach ($tags as $t) {
            $tag_names[] = $t->name;
        }
    }
    if (empty($tag_names)) {
        $tag_names = ['Trekking', 'Chia sẻ'];
    }

    $image = get_the_post_thumbnail_url($post_id, 'large');
    if (empty($image)) {
        $image = get_post_meta($post_id, 'featured_image_url', true);
    }
    if (empty($image)) {
        $image = '/images/default-tour.jpg';
    }

    $author_name = get_post_meta($post_id, 'author_name', true);
    if (empty($author_name)) {
        $author_name = get_the_author_meta('display_name', $post->post_author) ?: 'Admin';
    }

    $author_bio = get_post_meta($post_id, 'author_bio', true);
    if (empty($author_bio)) {
        $author_bio = get_the_author_meta('description', $post->post_author) ?: 'Đội ngũ biên tập viên Đôi Dép Adventure.';
    }

    $author_avatar = get_post_meta($post_id, 'author_avatar', true);
    if (empty($author_avatar)) {
        $author_avatar = get_avatar_url($post->post_author, ['size' => 96]) ?: '';
    }

    $read_time = get_post_meta($post_id, 'read_time', true);
    if (empty($read_time)) {
        $word_count = str_word_count(strip_tags($post->post_content));
        $read_time_min = ceil($word_count / 200);
        $read_time = ($read_time_min > 0 ? $read_time_min : 5) . ' phút';
    }

    $colors = [
        'from-emerald-500 to-emerald-600', 
        'from-blue-500 to-blue-600', 
        'from-red-500 to-red-600', 
        'from-amber-500 to-amber-600', 
        'from-violet-500 to-violet-600', 
        'from-teal-500 to-teal-600'
    ];
    $color = $colors[$post_id % count($colors)];

    return [
        'id' => $post_id,
        'slug' => $post->post_name,
        'title' => $post->post_title,
        'excerpt' => get_the_excerpt($post),
        'author' => $author_name,
        'author_bio' => $author_bio,
        'author_avatar' => $author_avatar,
        'date' => get_the_date('d/m/Y', $post_id),
        'read_time' => $read_time,
        'category' => $category_name,
        'tags' => $tag_names,
        'image' => $image,
        'color' => $color,
        'content' => apply_filters('the_content', $post->post_content),
        'seo' => newtrip_get_yoast_seo($post_id),
    ];
}

// Danh sách bài viết mẫu chất lượng cao làm fallback
function newtrip_get_mock_posts() {
    return [
        [
            'id' => 1,
            'slug' => 'meo-chon-giay-trekking',
            'title' => 'Mẹo Chọn Giày Khi Đi Trekking Không Bị Đau Chân – Bí Quyết Dân Trekking Cần Biết',
            'excerpt' => 'Việc chọn đúng đôi giày trekking có thể quyết định trải nghiệm của bạn. Hãy cùng Đôi Dép Adventure khám phá những bí quyết...',
            'author' => 'Ne',
            'author_bio' => 'Chuyên gia trekking với 5 năm kinh nghiệm chinh phục các đỉnh núi Việt Nam. Đam mê chia sẻ kiến thức về outdoor và sinh tồn.',
            'date' => '30/1/2026',
            'read_time' => '8 phút',
            'category' => 'Kinh nghiệm',
            'tags' => ['Trekking', 'Giày', 'Kỹ năng', 'Công cụ'],
            'image' => 'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?w=800&q=80',
            'color' => 'from-emerald-500 to-emerald-600',
            'content' => '
              <p class="lead">Việc chọn đúng đôi giày trekking có thể quyết định hoàn toàn trải nghiệm của bạn trên đường đi. Một đôi giày phù hợp không chỉ giúp bạn di chuyển thoải mái mà còn bảo vệ đôi chân khỏi những chấn thương không đáng có.</p>
        
              <h2>Tại sao giày trekking quan trọng?</h2>
              <p>Khác với giày thể thao thông thường, giày trekking được thiết kế đặc biệt để đối phó với địa hình phức tạp: đá sỏi, bùn lầy, suối nước... Đế giày có độ bám cao, cổ giày bảo vệ mắt cá, và chất liệu chống thấm nước.</p>
              
              <blockquote>
                "Một đôi giày tốt có thể làm cho một chuyến đi tồi tệ trở nên chấp nhận được, và một chuyến đi tốt trở nên tuyệt vời."
              </blockquote>
        
              <h2>Cách chọn size giày phù hợp</h2>
              <p>Một sai lầm phổ biến là chọn giày vừa khít như giày hàng ngày. Khi trekking, chân sẽ sưng lên sau nhiều giờ đi bộ. Hãy chọn giày lớn hơn 0.5-1 size so với bình thường, đặc biệt nếu bạn mang vớ dày.</p>
              
              <div class="tip-box">
                <strong>💡 Mẹo:</strong> Thử giày vào buổi chiều khi chân đã nở to nhất. Mang vớ trekking và đi thử trong cửa hàng ít nhất 15 phút.
              </div>
        
              <h2>Loại đế giày</h2>
              <p>Đế giày là yếu tố quan trọng nhất quyết định độ bám và độ bền của giày:</p>
              <ul>
                <li><strong>Đế Vibram:</strong> Độ bám tốt nhất, bền, phù hợp địa hình đá và bùn. Được sử dụng bởi hầu hết các thương hiệu cao cấp.</li>
                <li><strong>Đế cao su tổng hợp:</strong> Nhẹ hơn, phù hợp đường mòn dễ. Giá thành rẻ hơn nhưng độ bám kém hơn.</li>
                <li><strong>Đế có rãnh sâu:</strong> Thoát nước tốt, chống trượt trên bề mặt ẩm. Phù hợp cho mùa mưa.</li>
              </ul>
        
              <h2>Chất liệu giày</h2>
              <p>Mỗi loại chất liệu có ưu nhược điểm riêng:</p>
              <div class="comparison-grid">
                <div class="comparison-item">
                  <h4>Da bò</h4>
                  <p>Bền, chống nước tốt nhưng nặng và cần thời gian break-in 2-3 tuần.</p>
                </div>
                <div class="comparison-item">
                  <h4>Vải tổng hợp</h4>
                  <p>Nhẹ, thoáng khí, khô nhanh nhưng ít bền hơn và chống nước kém.</p>
                </div>
                <div class="comparison-item">
                  <h4>Da + Vải kết hợp</h4>
                  <p>Cân bằng giữa độ bền và trọng lượng - lựa chọn phổ biến nhất.</p>
                </div>
              </div>
        
              <h2>Những lỗi thường gặp</h2>
              <p>Đây là những sai lầm mà nhiều người mới mắc phải:</p>
              <ul>
                <li>Mang giày mới chưa break-in cho tour dài → Phồng rộp, đau chân</li>
                <li>Không buộc dây đúng cách → Dây tuột, chân không được cố định</li>
                <li>Chọn giày không thấm nước cho tour có vượt suối → Chân ướt, lạnh</li>
                <li>Quên mang vớ dự phòng → Vớ ướt không thay được</li>
              </ul>
        
              <div class="warning-box">
                <strong>⚠️ Lưu ý quan trọng:</strong> Không bao giờ đi barefoot trong giày trekking, ngay cả khi trời nóng. Vớ trekking chuyên dụng giúp hấp thụ mồ hôi và giảm ma sát.
              </div>
        
              <h2>Bảo quản giày sau tour</h2>
              <p>Sau mỗi chuyến đi, hãy vệ sinh giày sạch sẽ, để khô tự nhiên (không phơi nắng trực tiếp), và xịt chống thấm định kỳ 2-3 tháng/lần. Điều này giúp giày bền hơn và sẵn sàng cho chuyến đi tiếp theo.</p>
              
              <h3>Các bước vệ sinh giày trekking:</h3>
              <ol>
                <li>Tháo lớp lót và dây giày ra</li>
                <li>Dùng bàn chải mềm chải sạch bùn đất</li>
                <li>Rửa nhẹ bằng nước ấm (không dùng xà phòng mạnh)</li>
                <li>Để khô tự nhiên ở nơi thoáng mát</li>
                <li>Xịt chống thấm và bảo quản trong túi</li>
              </ol>
        
              <p class="conclusion"><em>Chúc bạn tìm được đôi giày trekking hoàn hảo cho hành trình sắp tới! Nếu có câu hỏi, hãy để lại bình luận bên dưới.</em></p>
            '
        ],
        [
            'id' => 2,
            'slug' => 'trekking-tu-tuc-loi-ich-nguy-hiem',
            'title' => 'Trekking Tự Túc: Lợi Ích & Nguy Hiểm – Những Điều Cần Lưu Ý Trước Chuyến Đi',
            'excerpt' => 'Trekking tự túc mang lại nhiều trải nghiệm độc đáo nhưng cũng tiềm ẩn không ít nguy hiểm. Cùng tìm hiểu...',
            'author' => 'Mi',
            'author_bio' => 'Travel blogger và hiking enthusiast. Đã khám phá hơn 50 cung đường trekking khắp Việt Nam và Đông Nam Á.',
            'date' => '30/1/2026',
            'read_time' => '10 phút',
            'category' => 'An toàn',
            'tags' => ['Trekking tự túc', 'An toàn', 'Kinh nghiệm'],
            'image' => 'https://images.unsplash.com/photo-1501555088652-021faa106b9b?w=800&q=80',
            'color' => 'from-red-500 to-red-600',
            'content' => '
              <p class="lead">Trekking tự túc đang trở thành xu hướng của nhiều bạn trẻ yêu thích khám phá. Tuy nhiên, giữa lợi ích và nguy hiểm chỉ cách nhau một ranh giới mong manh.</p>
        
              <h2>Lợi ích của trekking tự túc</h2>
              <p>Khi tự mình bước vào hành trình, bạn sẽ nhận được những điều mà tour không thể mang lại:</p>
              <ul>
                <li><strong>Tự do lịch trình:</strong> Bạn quyết định đi đâu, dừng đâu, ở lại bao lâu. Không bị gò bó theo lịch trình cố định.</li>
                <li><strong>Tiết kiệm chi phí:</strong> Không phải trả phí hướng dẫn viên, có thể tự nấu ăn và cắm trại.</li>
                <li><strong>Trải nghiệm thực tế:</strong> Tự mình xử lý mọi tình huống, rèn luyện kỹ năng sinh tồn.</li>
                <li><strong>Kết nối sâu hơn:</strong> Được hòa mình vào thiên nhiên một cách trọn vẹn.</li>
              </ul>
        
              <h2>Nguy hiểm tiềm ẩn</h2>
              <p>Bên cạnh những lợi ích, bạn cần nhận thức rõ các rủi ro:</p>
              
              <div class="danger-list">
                <div class="danger-item">
                  <span class="danger-icon">🧭</span>
                  <div>
                    <strong>Lạc đường</strong>
                    <p>Đây là rủi ro phổ biến nhất. Nhiều cung trekking không có biển báo rõ ràng, đặc biệt ở vùng núi cao.</p>
                  </div>
                </div>
                <div class="danger-item">
                  <span class="danger-icon">⛈️</span>
                  <div>
                    <strong>Thời tiết bất ngờ</strong>
                    <p>Mưa rừng, sương mù, lũ quét có thể xảy ra mà không báo trước, đặc biệt vào mùa mưa.</p>
                  </div>
                </div>
                <div class="danger-item">
                  <span class="danger-icon">🏥</span>
                  <div>
                    <strong>Thiếu kỹ năng sơ cứu</strong>
                    <p>Khi bị thương giữa rừng, không có HDV hỗ trợ. Bạn cần tự xử lý trong khả năng của mình.</p>
                  </div>
                </div>
                <div class="danger-item">
                  <span class="danger-icon">🦎</span>
                  <div>
                    <strong>Động vật hoang dã</strong>
                    <p>Rắn, côn trùng, lợn rừng... có thể gây nguy hiểm nếu bạn không biết cách xử lý.</p>
                  </div>
                </div>
              </div>
        
              <h2>Chuẩn bị trước khi đi</h2>
              <p>Dưới đây là checklist những thứ bạn cần chuẩn bị:</p>
              <ul>
                <li>Nghiên cứu kỹ cung đường, tải offline map (Maps.me, Gaia GPS)</li>
                <li>Thông báo lịch trình cho người thân</li>
                <li>Mang đủ nước (tối thiểu 2L/người), lương khô, bộ sơ cứu</li>
                <li>Kiểm tra thời tiết 3 ngày trước khi đi</li>
                <li>Có phương án dự phòng (số điện thoại cứu hộ, đường rút ngắn)</li>
                <li>Mang theo thiết bị liên lạc (điện thoại đã sạc pin, pin dự phòng)</li>
              </ul>
        
              <div class="tip-box">
                <strong>📍 Mẹo an toàn:</strong> Luôn để lại kế hoạch chi tiết (lộ trình, thời gian dự kiến) cho ai đó ở nhà. Nếu không liên lạc được sau thời gian dự kiến, họ có thể báo cứu.
              </div>
        
              <h2>Khi nào nên đi theo tour?</h2>
              <p>Nếu bạn là người mới, chưa có kinh nghiệm đi rừng, hoặc đi đến cung đường khó (độ cao > 2000m, địa hình hiểm trở), hãy đi theo tour có HDV. Chi phí bỏ ra xứng đáng với sự an toàn của bạn.</p>
              
              <p><em>Adventure is out there – but safety comes first!</em></p>
            '
        ],
        [
            'id' => 3,
            'slug' => 'top-10-dinh-nui-trekking-viet-nam',
            'title' => 'Top 10 Đỉnh Núi Trekking Đẹp Nhất Việt Nam',
            'excerpt' => 'Việt Nam có vô số đỉnh núi đẹp mê hồn, từ Bắc vào Nam. Cùng Đôi Dép Adventure khám phá top 10 đỉnh núi không thể bỏ qua...',
            'author' => 'Admin',
            'author_bio' => 'Đội ngũ biên tập viên Đôi Dép Adventure. Cung cấp thông tin du lịch và kinh nghiệm trekking chính xác nhất.',
            'date' => '25/1/2026',
            'read_time' => '12 phút',
            'category' => 'Địa điểm',
            'tags' => ['Địa điểm', 'Trekking', 'Việt Nam'],
            'image' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&q=80',
            'color' => 'from-blue-500 to-blue-600',
            'content' => '
              <p class="lead">Việt Nam với địa hình 3/4 là đồi núi sở hữu vô số cung đường trekking tuyệt đẹp. Từ những đỉnh núi cao vút mây phủ ở Tây Bắc đến những cung đường ven biển lộng gió miền Trung, mỗi nơi đều mang một vẻ đẹp riêng biệt.</p>
              <h2>1. Đỉnh Fansipan (3.143m) - Lào Cai</h2>
              <p>Được mệnh danh là "Nóc nhà Đông Dương", Fansipan luôn là thử thách mà bất kỳ trekker nào cũng muốn chinh phục ít nhất một lần trong đời. Cảm giác đứng trên đỉnh cao nhất, ngắm nhìn biển mây bồng bềnh dưới chân thật không gì tả xiết.</p>
              <h2>2. Tà Năng - Phan Dũng (Lâm Đồng - Bình Thuận)</h2>
              <p>Cung đường trekking được mệnh danh là đẹp nhất Việt Nam với những đồi cỏ xanh mướt trải dài tít tắp, những rừng thông reo trong gió và khoảnh khắc đón bình minh tuyệt diệu trên đồi cao.</p>
              <h2>3. Lảo Thẩn (Lào Cai)</h2>
              <p>Nơi được coi là "thiên đường săn mây" của Tây Bắc. Với độ khó trung bình, Lảo Thẩn rất phù hợp cho những ai mới bắt đầu làm quen với bộ môn trekking săn mây.</p>
              <h2>4. Bạch Mộc Lương Tử (Kỳ Quan San)</h2>
              <p>Một trong những đỉnh núi cao và hiểm trở nhất Việt Nam, nhưng bù lại, cảnh quan kỳ vĩ và biển mây ở Muối hay đỉnh Kỳ Quan San sẽ làm say đắm bất kỳ ai đặt chân tới.</p>
              <p><em>Hãy chuẩn bị thể lực thật tốt và cùng Đôi Dép Adventure lên đường chinh phục những đỉnh cao này nhé!</em></p>
            '
        ],
        [
            'id' => 4,
            'slug' => 'camping-101-cho-nguoi-moi-bat-dau',
            'title' => 'Camping 101: Hướng Dẫn Cho Người Mới Bắt Đầu',
            'excerpt' => 'Bạn mới bắt đầu với camping? Đừng lo lắng! Đôi Dép Adventure sẽ hướng dẫn bạn từ A đến Z để có một chuyến camping hoàn hảo...',
            'author' => 'Hoàng Nam',
            'author_bio' => 'Chuyên gia cắm trại và sinh tồn dã ngoại. Thích khám phá thiên nhiên hoang dã.',
            'date' => '20/1/2026',
            'read_time' => '7 phút',
            'category' => 'Hướng dẫn',
            'tags' => ['Camping', 'Cắm trại', 'Hướng dẫn', 'Người mới'],
            'image' => 'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?w=800&q=80',
            'color' => 'from-amber-500 to-amber-600',
            'content' => '
              <p class="lead">Cắm trại (Camping) là một cách tuyệt vời để trốn khỏi khói bụi thành phố và hòa mình vào thiên nhiên. Đối với người mới bắt đầu, sự chuẩn bị kỹ lưỡng là chìa khóa để có một chuyến đi an toàn và đáng nhớ.</p>
              <h2>Lựa chọn địa điểm cắm trại</h2>
              <p>Cho chuyến đi đầu tiên, hãy chọn những khu cắm trại dịch vụ (glamping/camping site) có đầy đủ tiện ích cơ bản như nước ngọt, nhà vệ sinh. Tránh tự ý cắm trại ở những vùng rừng sâu, hẻo lánh khi chưa có kinh nghiệm.</p>
              <h2>Trang thiết bị thiết yếu</h2>
              <p>Đừng quên mang theo những thứ sau:</p>
              <ul>
                <li>Lều chống mưa gió và cọc ghim lều</li>
                <li>Túi ngủ và tấm trải cách nhiệt lót lều</li>
                <li>Đèn lều, đèn pin cá nhân và pin dự phòng</li>
                <li>Dụng cụ nấu ăn dã ngoại và bếp gas mini</li>
                <li>Hộp sơ cứu y tế cá nhân</li>
              </ul>
              <h2>Nguyên tắc "Không Để Lại Dấu Vết" (Leave No Trace)</h2>
              <p>Hãy luôn bảo vệ môi trường bằng cách dọn sạch rác thải trước khi ra về, không bẻ cành cây tươi, hạn chế đốt lửa trực tiếp trên mặt đất để giữ gìn vẻ đẹp tự nhiên của khu cắm trại.</p>
            '
        ],
        [
            'id' => 5,
            'slug' => 'sai-lam-khi-trekking-mua-mua',
            'title' => 'Những Sai Lầm Thường Gặp Khi Đi Trekking Mùa Mưa',
            'excerpt' => 'Đi trekking mùa mưa có những rủi ro riêng. Hãy tránh những sai lầm phổ biến để chuyến đi của bạn an toàn hơn...',
            'author' => 'Thu Hà',
            'author_bio' => 'Trekker và nhiếp ảnh gia tự do. Thích ghi lại những khoảnh khắc đẹp của thiên nhiên dưới những cơn mưa rừng.',
            'date' => '15/1/2026',
            'read_time' => '9 phút',
            'category' => 'An toàn',
            'tags' => ['An toàn', 'Mùa mưa', 'Trekking'],
            'image' => 'https://images.unsplash.com/photo-1534274988757-a28bf1a57c17?w=800&q=80',
            'color' => 'from-teal-500 to-teal-600',
            'content' => '
              <p class="lead">Trekking mùa mưa mang lại những trải nghiệm rất khác biệt: thác nước đầy ắp, rừng cây xanh tốt hơn. Tuy nhiên, nó cũng tiềm ẩn nhiều mối nguy hiểm nếu bạn phạm phải những sai lầm phổ biến dưới đây.</p>
              <h2>1. Chủ quan không mang áo mưa chuyên dụng</h2>
              <p>Nhiều người nghĩ chỉ cần ô hoặc áo mưa giấy mỏng. Giữa rừng, gió lớn và cành cây có thể xé rách áo mưa giấy chỉ trong vài phút. Hãy trang bị áo mưa bộ hoặc áo mưa cánh dơi chất liệu bền dai.</p>
              <h2>2. Đi giày không chống trơn trượt</h2>
              <p>Đường rừng mùa mưa cực kỳ trơn trượt do bùn lầy và rêu phong. Đi một đôi giày có gai đế nông hoặc giày chạy bộ trơn trượt sẽ khiến bạn dễ ngã và chấn thương.</p>
              <h2>3. Không bảo vệ đồ điện tử và quần áo dự phòng</h2>
              <p>Nước mưa có thể thấm qua balo. Hãy bọc quần áo dự phòng và đồ điện tử trong túi nilon chống nước trước khi cho vào balo.</p>
              <h2>4. Không chú ý quan sát lũ quét ở suối</h2>
              <p>Mưa lớn ở thượng nguồn có thể gây ra lũ quét rất nhanh. Không bao giờ cố vượt suối khi thấy nước bắt đầu đục và chảy xiết.</p>
            '
        ],
        [
            'id' => 6,
            'slug' => 'trekking-dinh-langbiang-da-lat',
            'title' => 'Trải Nghiệm Trekking Đỉnh Langbiang - Ký Ức Không Quên',
            'excerpt' => 'Chinh phục đỉnh Langbiang 2163m là một trong những trải nghiệm đáng nhớ nhất của nhiều trekker. Cùng lắng nghe...',
            'author' => 'Văn Đức',
            'author_bio' => 'Local guide tại Đà Lạt. Đã dẫn hàng trăm đoàn khách chinh phục đỉnh Langbiang qua đường rừng.',
            'date' => '10/1/2026',
            'read_time' => '6 phút',
            'category' => 'Trải nghiệm',
            'tags' => ['Trải nghiệm', 'Langbiang', 'Đà Lạt'],
            'image' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=800&q=80',
            'color' => 'from-violet-500 to-violet-600',
            'content' => '
              <p class="lead">Langbiang từ lâu đã là biểu tượng của thành phố Đà Lạt mộng mơ. Nhưng thay vì đi xe jeep lên đồi radar thông thường, việc tự mình trekking qua rừng già để lên đỉnh Langbiang cao 2.163m mới thực sự là một trải nghiệm khó quên.</p>
              <h2>Hành trình bắt đầu từ rừng thông</h2>
              <p>Đoạn đầu cung đường khá dễ chịu với những lối mòn đi dưới tán rừng thông mát rượi. Tiếng thông reo và không khí se lạnh của Đà Lạt làm bước chân thêm nhẹ nhàng.</p>
              <h2>Chặng leo dốc thử thách qua rừng già</h2>
              <p>Sau khi qua trạm kiểm lâm, địa hình thay đổi rõ rệt. Đường đi dốc hơn, độ ẩm cao hơn và bạn sẽ bước vào rừng lá rộng nguyên sinh với những gốc cây cổ thụ rêu phong phủ kín. Chặng leo dốc đứng cuối cùng đòi hỏi sự kiên trì lớn.</p>
              <h2>Phần thưởng ngọt ngào trên đỉnh cao</h2>
              <p>Đứng trên đỉnh Langbiang cao 2.163m, phóng tầm mắt ngắm toàn cảnh thành phố Đà Lạt, hồ Đankia hiện ra thơ mộng dưới làn sương mờ. Cảm giác mệt mỏi lập tức tan biến, nhường chỗ cho sự tự hào và sảng khoái.</p>
            '
        ]
    ];
}

// 7. Cấu hình các cột hiển thị trong danh sách Đơn đặt tour (WP Admin)
add_filter('manage_booking_posts_columns', 'newtrip_set_booking_columns');
function newtrip_set_booking_columns($columns) {
    $new_columns = [];
    $new_columns['cb'] = $columns['cb']; // checkbox
    $new_columns['booking_code'] = 'Mã đặt tour';
    $new_columns['title'] = 'Tiêu đề';
    $new_columns['tour'] = 'Tour đã đặt';
    $new_columns['departure_date'] = 'Ngày khởi hành';
    $new_columns['participants'] = 'Số người';
    $new_columns['total_amount'] = 'Tổng tiền';
    $new_columns['status'] = 'Trạng thái đơn';
    $new_columns['payment_status'] = 'Thanh toán';
    $new_columns['date'] = $columns['date']; // Ngày đặt
    return $new_columns;
}

add_action('manage_booking_posts_custom_column', 'newtrip_booking_custom_column_content', 10, 2);
function newtrip_booking_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'booking_code':
            $code = get_post_meta($post_id, 'booking_code', true);
            echo '<strong>' . esc_html($code ? $code : 'NTR-XXXXXX') . '</strong>';
            break;
            
        case 'tour':
            $tour_id = get_post_meta($post_id, 'tour_id', true);
            if ($tour_id) {
                echo '<a href="' . get_edit_post_link($tour_id) . '">' . esc_html(get_the_title($tour_id)) . '</a>';
            } else {
                echo '<span class="description">—</span>';
            }
            break;
            
        case 'departure_date':
            $date = get_post_meta($post_id, 'departure_date', true);
            if ($date) {
                echo esc_html(date('d/m/Y', strtotime($date)));
            } else {
                echo '<span class="description">—</span>';
            }
            break;
            
        case 'participants':
            $slots = get_post_meta($post_id, 'participants', true);
            echo esc_html($slots ? $slots . ' người' : '1 người');
            break;
            
        case 'total_amount':
            $total = get_post_meta($post_id, 'total_amount', true);
            if ($total) {
                echo '<strong>' . number_format(floatval($total), 0, ',', '.') . 'đ</strong>';
            } else {
                echo '0đ';
            }
            break;
            
        case 'status':
            $status = get_post_meta($post_id, 'status', true) ?: 'pending';
            $labels = [
                'pending'   => '<span class="badge" style="background:#ffb900;color:#fff;padding:3px 8px;border-radius:4px;font-weight:600;font-size:11px;">Chờ xác nhận</span>',
                'confirmed' => '<span class="badge" style="background:#16a249;color:#fff;padding:3px 8px;border-radius:4px;font-weight:600;font-size:11px;">Đã xác nhận</span>',
                'cancelled' => '<span class="badge" style="background:#d9381e;color:#fff;padding:3px 8px;border-radius:4px;font-weight:600;font-size:11px;">Đã hủy</span>',
                'completed' => '<span class="badge" style="background:#0068ff;color:#fff;padding:3px 8px;border-radius:4px;font-weight:600;font-size:11px;">Đã hoàn thành</span>'
            ];
            echo isset($labels[$status]) ? $labels[$status] : esc_html($status);
            break;
            
        case 'payment_status':
            $pay_status = get_post_meta($post_id, 'payment_status', true) ?: 'unpaid';
            $labels = [
                'unpaid'   => '<span class="badge" style="border:1px solid #d9381e;color:#d9381e;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600;">Chưa trả</span>',
                'partial'  => '<span class="badge" style="border:1px solid #ffb900;color:#ffb900;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600;">Trả một phần</span>',
                'paid'     => '<span class="badge" style="border:1px solid #16a249;color:#16a249;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600;">Đã thanh toán</span>',
                'refunded' => '<span class="badge" style="border:1px solid #727272;color:#727272;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600;">Đã hoàn tiền</span>'
            ];
            echo isset($labels[$pay_status]) ? $labels[$pay_status] : esc_html($pay_status);
            break;
    }
}

// Cho phép sắp xếp theo các cột
add_filter('manage_edit-booking_sortable_columns', 'newtrip_booking_sortable_columns');
function newtrip_booking_sortable_columns($columns) {
    $columns['departure_date'] = 'departure_date';
    $columns['total_amount'] = 'total_amount';
    $columns['status'] = 'status';
    return $columns;
}

// Tự động tính toán lại chi phí khi quản trị viên lưu/cập nhật đơn đặt tour ở Admin Dashboard
add_action('acf/save_post', 'newtrip_calculate_booking_totals_on_save', 20);
function newtrip_calculate_booking_totals_on_save($post_id) {
    if (get_post_type($post_id) !== 'booking') {
        return;
    }
    
    // Tạm dừng hook để tránh lặp vô hạn khi gọi update_field
    remove_action('acf/save_post', 'newtrip_calculate_booking_totals_on_save', 20);
    
    $tour_id = newtrip_get_field('tour_id', $post_id);
    if (!$tour_id) {
        add_action('acf/save_post', 'newtrip_calculate_booking_totals_on_save', 20);
        return;
    }
    
    if (is_object($tour_id)) {
        $tour_id = $tour_id->ID;
    } elseif (is_array($tour_id)) {
        $tour_id = $tour_id['ID'] ?? 0;
    }
    $tour_id = intval($tour_id);
    
    $tour_price = floatval(newtrip_get_field('price', $tour_id));
    $participants = intval(newtrip_get_field('participants', $post_id));
    if ($participants <= 0) {
        $participants = 1;
    }
    
    // 1. Tính toán lại Dịch vụ
    $services_raw = newtrip_get_field('services', $post_id);
    $services_data = [];
    $services_total = 0;
    if (is_array($services_raw)) {
        foreach ($services_raw as $s) {
            $service_id_raw = $s['service_id'] ?? '';
            $service_id = $service_id_raw;
            if (is_object($service_id)) {
                $service_id = $service_id->ID;
            } elseif (is_array($service_id)) {
                $service_id = $service_id['ID'] ?? 0;
            }
            
            $resolved_id = 0;
            if (is_numeric($service_id) && intval($service_id) > 0) {
                $resolved_id = intval($service_id);
            } else {
                $service_slug = (string)$service_id;
                if (!empty($service_slug)) {
                    $query = new WP_Query([
                        'post_type' => 'tour_service',
                        'name' => $service_slug,
                        'posts_per_page' => 1,
                        'fields' => 'ids',
                        'post_status' => 'any',
                    ]);
                    $resolved_id = !empty($query->posts) ? $query->posts[0] : 0;
                }
            }
            
            if ($resolved_id <= 0) continue;
            
            $s_price = isset($s['price']) && $s['price'] !== '' ? floatval($s['price']) : 0;
            if ($s_price <= 0 && $resolved_id > 0) {
                $s_price = floatval(newtrip_get_field('price', $resolved_id));
            }
            
            $s_name = $s['name'] ?? '';
            if (empty($s_name) && $resolved_id > 0) {
                $s_name = get_the_title($resolved_id);
            }
            
            $services_total += $s_price * $participants;
            $services_data[] = [
                'service_id' => $resolved_id,
                'name' => $s_name,
                'price' => $s_price,
            ];
        }
        update_field('field_booking_services', $services_data, $post_id);
    }
    
    // 2. Tính toán lại Đồ thuê
    $rental_items_raw = newtrip_get_field('rental_items', $post_id);
    $rental_items_data = [];
    $rental_total = 0;
    if (is_array($rental_items_raw)) {
        foreach ($rental_items_raw as $r) {
            $item_id_raw = $r['item_id'] ?? '';
            $item_id = $item_id_raw;
            if (is_object($item_id)) {
                $item_id = $item_id->ID;
            } elseif (is_array($item_id)) {
                $item_id = $item_id['ID'] ?? 0;
            }
            
            $resolved_id = 0;
            if (is_numeric($item_id) && intval($item_id) > 0) {
                $resolved_id = intval($item_id);
            } else {
                $item_slug = (string)$item_id;
                if (!empty($item_slug)) {
                    $query = new WP_Query([
                        'post_type' => 'rental_item',
                        'name' => $item_slug,
                        'posts_per_page' => 1,
                        'fields' => 'ids',
                        'post_status' => 'any',
                    ]);
                    $resolved_id = !empty($query->posts) ? $query->posts[0] : 0;
                }
            }
            
            if ($resolved_id <= 0) continue;
            
            $r_price = isset($r['price']) && $r['price'] !== '' ? floatval($r['price']) : 0;
            if ($r_price <= 0 && $resolved_id > 0) {
                $r_price = floatval(newtrip_get_field('price', $resolved_id));
            }
            
            $r_name = $r['name'] ?? '';
            if (empty($r_name) && $resolved_id > 0) {
                $r_name = get_the_title($resolved_id);
            }
            
            $qty = intval($r['qty'] ?? 1);
            if ($qty <= 0) $qty = 1;
            
            $subtotal = $r_price * $qty;
            $rental_total += $subtotal;
            
            $rental_items_data[] = [
                'item_id' => $resolved_id,
                'name' => $r_name,
                'qty' => $qty,
                'price' => $r_price,
                'subtotal' => $subtotal,
            ];
        }
        update_field('field_booking_rental_items', $rental_items_data, $post_id);
    }
    
    // 3. Tính lại Tổng tiền thanh toán
    $total_amount = ($tour_price * $participants) + $services_total + $rental_total;
    update_field('field_booking_total_amount', $total_amount, $post_id);

    // Sync payment status if booking status is confirmed or completed (Confirmed = Đã xác nhận & Đã thanh toán)
    $status = newtrip_get_field('status', $post_id) ?: 'pending';
    if ($status === 'confirmed' || $status === 'completed') {
        $payment_status = get_post_meta($post_id, 'payment_status', true);
        if ($payment_status !== 'paid' && $payment_status !== 'partial') {
            update_post_meta($post_id, 'payment_status', 'paid');
            update_post_meta($post_id, 'paid_amount', $total_amount);
        }
    }

    // Sync post title (only contain booking code and customer name)
    $booking_code = get_post_meta($post_id, 'booking_code', true);
    $full_name = newtrip_get_field('full_name', $post_id) ?: '';
    if (!empty($booking_code) && !empty($full_name)) {
        $new_title = sprintf('[%s] %s', $booking_code, $full_name);
        global $wpdb;
        $wpdb->update($wpdb->posts, ['post_title' => $new_title], ['ID' => $post_id]);
        clean_post_cache($post_id);
    }

    // Tự động đồng bộ thông tin khách hàng sang bảng Khách hàng (customer) cho mục đích remarketing
    newtrip_sync_booking_to_customer($post_id);
    
    // Rollback customer stats khi booking bi huy hoac refund
    if ($status === 'cancelled' || $status === 'refunded') {
        newtrip_rollback_customer_for_booking($post_id);
    }
    
    // Đăng ký lại hook sau khi hoàn tất cập nhật
    add_action('acf/save_post', 'newtrip_calculate_booking_totals_on_save', 20);
}

// 6.10 Lấy nội dung trang tĩnh theo slug (cho trang chính sách)
function newtrip_api_get_page_by_slug(WP_REST_Request $request) {
    $slug = sanitize_text_field($request->get_param('slug'));
    
    $query = new WP_Query([
        'post_type' => 'page',
        'name' => $slug,
        'posts_per_page' => 1,
        'post_status' => 'publish',
    ]);
    
    if (!$query->have_posts()) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'page_not_found', 'message' => 'Không tìm thấy trang yêu cầu']
        ], 404);
    }
    
    $post = $query->posts[0];
    $data = [
        'id' => $post->ID,
        'slug' => $post->post_name,
        'title' => $post->post_title,
        'content' => apply_filters('the_content', $post->post_content),
        'seo' => newtrip_get_yoast_seo($post->ID),
    ];
    
    return new WP_REST_Response([
        'success' => true,
        'data' => $data
    ], 200);
}

// 6.11 Tải tệp lên công cộng bảo mật
function newtrip_api_upload_file(WP_REST_Request $request) {
    $files = $request->get_file_params();
    if (empty($files) || !isset($files['file'])) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'missing_file', 'message' => 'Vui lòng cung cấp tệp tin để tải lên']
        ], 400);
    }

    $file = $files['file'];

    // Giới hạn 5MB
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'file_too_large', 'message' => 'Kích thước tệp tin không được vượt quá 5MB']
        ], 400);
    }

    // Chỉ cho phép ảnh
    $allowed_exts = ['jpg', 'jpeg', 'png'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_exts, true)) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'invalid_file_type', 'message' => 'Chỉ chấp nhận các định dạng ảnh: jpg, jpeg, png']
        ], 400);
    }

    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
    }

    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($file, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        $attachment = array(
            'guid'           => $movefile['url'],
            'post_mime_type' => $movefile['type'],
            'post_title'     => preg_replace('/\.[^.]+$/', '', basename($file['name'])),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $movefile['file']);
        $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
        wp_update_attachment_metadata($attach_id, $attach_data);

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'id' => $attach_id,
                'url' => $movefile['url'],
            ]
        ], 200);
    } else {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'upload_error', 'message' => $movefile['error'] ?? 'Lỗi xảy ra khi tải ảnh lên']
        ], 500);
    }
}

// 6.12 Cập nhật thông tin hành khách du lịch
function newtrip_api_update_booking_passengers(WP_REST_Request $request) {
    $booking_code = sanitize_text_field($request->get_param('id'));
    $params = $request->get_json_params();
    if (!is_array($params)) $params = [];

    $email = sanitize_email($params['email'] ?? '');
    $passengers = $params['passengers'] ?? [];
    $expires = intval($params['expires'] ?? 0);
    $token = sanitize_text_field($params['token'] ?? '');

    // Bắt buộc phải xác thực token và thời gian hết hạn trước chuyến đi
    $verify = newtrip_verify_booking_token($booking_code, $email, $expires, $token);
    if ($verify === false) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'invalid_token', 'message' => 'Liên kết xác thực không hợp lệ hoặc đã bị thay đổi']
        ], 403);
    } elseif ($verify === 'expired') {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'link_expired', 'message' => 'Liên kết cập nhật thông tin đã hết hạn trước khi chuyến đi bắt đầu']
        ], 400);
    }

    if (empty($email)) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'missing_email', 'message' => 'Cần cung cấp email liên hệ để xác thực']
        ], 400);
    }

    if (!is_array($passengers)) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'invalid_passengers', 'message' => 'Danh sách thành viên không hợp lệ']
        ], 400);
    }

    $query = new WP_Query([
        'post_type'  => 'booking',
        'meta_query' => [
            'relation' => 'AND',
            ['key' => 'booking_code', 'value' => $booking_code, 'compare' => '='],
            ['key' => 'email', 'value' => $email, 'compare' => '='],
        ],
        'posts_per_page' => 1,
    ]);

    if (!$query->have_posts()) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'booking_not_found', 'message' => 'Không tìm thấy đơn hàng khớp với thông tin cung cấp']
        ], 404);
    }

    $b_id = $query->posts[0]->ID;

    $passengers_data = [];
    foreach ($passengers as $idx => $p) {
        $p_name = sanitize_text_field($p['full_name'] ?? '');
        if (empty($p_name)) continue;

        $p_phone = preg_replace('/[^\d+]/', '', sanitize_text_field($p['phone'] ?? ''));
        $p_email = sanitize_email($p['email'] ?? '');
        $p_birth = sanitize_text_field($p['birth_date'] ?? '');
        $p_id_num = sanitize_text_field($p['id_number'] ?? '');
        $p_health = sanitize_text_field($p['health_status'] ?? '');
        $p_seat = sanitize_text_field($p['seat'] ?? '');
        $p_checkin = !empty($p['checked_in']) ? 1 : 0;
        $p_pickup_point_id = intval($p['pickup_point_id'] ?? 0);

        $passengers_data[] = [
            'full_name' => $p_name,
            'phone' => $p_phone,
            'email' => $p_email,
            'birth_date' => $p_birth,
            'id_number' => $p_id_num,
            'pickup_point_id' => $p_pickup_point_id ?: null,
            'seat' => $p_seat,
            'checked_in' => $p_checkin,
            'health_status' => $p_health,
        ];
    }

    if (empty($passengers_data)) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'empty_passengers', 'message' => 'Danh sách thành viên phải chứa ít nhất một người hợp lệ']
        ], 400);
    }

    if (function_exists('update_field')) {
        update_field('field_booking_passengers', $passengers_data, $b_id);
    } else {
        update_post_meta($b_id, 'passengers', $passengers_data);
    }

    // Kích hoạt save hook để tính toán lại giá/tiêu đề
    do_action('acf/save_post', $b_id);

    return new WP_REST_Response([
        'success' => true,
        'message' => 'Cập nhật thông tin thành viên thành công'
    ], 200);
}

// 6.13 Helper lấy thời gian hết hạn của liên kết cập nhật (00:00:00 của ngày khởi hành)
function newtrip_get_booking_update_expiration($departure_date_str) {
    $dep_time = strtotime($departure_date_str . ' 00:00:00');
    if (!$dep_time) {
        return time() + 7 * 24 * 3600;
    }
    
    // Nếu ngày khởi hành quá gần (dưới 12 tiếng) hoặc trong quá khứ, 
    // cho phép sửa ít nhất 2 tiếng kể từ thời điểm đặt để xử lý last-minute booking
    if ($dep_time - time() < 12 * 3600) {
        return time() + 2 * 3600;
    }
    
    return $dep_time;
}

// 6.14 Helper xác thực token bảo mật và thời gian hết hạn
function newtrip_verify_booking_token($booking_code, $email, $expires, $token) {
    if (empty($token) || empty($expires)) {
        return false;
    }
    
    // Kiểm tra hết hạn trước chuyến đi
    if (time() > $expires) {
        return 'expired';
    }
    
    // Xác thực chữ ký cryptographic hmac
    $secret_key = defined('NONCE_KEY') ? NONCE_KEY : 'newtrip_secure_salt_key_123';
    $expected_token = hash_hmac('sha256', $booking_code . '|' . $email . '|' . $expires, $secret_key);
    
    if (!hash_equals($expected_token, $token)) {
        return false;
    }
    
    return true;
}

// 6.15 Lấy danh sách menu (primary & footer) cho Frontend
function newtrip_api_get_menus(WP_REST_Request $request) {
    $primary = newtrip_get_menu_items_by_location('primary-menu');
    $footer = newtrip_get_menu_items_by_location('footer-menu');

    // Fallback if menus are empty in backend
    if (empty($primary)) {
        $primary = [
            ['id' => 1, 'title' => 'Trang chủ', 'url' => '/', 'parent' => 0, 'order' => 1],
            ['id' => 2, 'title' => 'Tuyến đường', 'url' => '/booking', 'parent' => 0, 'order' => 2],
            ['id' => 3, 'title' => 'Trải nghiệm', 'url' => '/experience', 'parent' => 0, 'order' => 3],
            ['id' => 4, 'title' => 'Về chúng tôi', 'url' => '/about', 'parent' => 0, 'order' => 4],
            ['id' => 5, 'title' => 'Liên hệ', 'url' => '/contact', 'parent' => 0, 'order' => 5],
        ];
    }

    if (empty($footer)) {
        $footer = [
            ['id' => 10, 'title' => 'Tra cứu đơn đặt tour', 'url' => '/booking/lookup', 'parent' => 0, 'order' => 1],
            ['id' => 11, 'title' => 'Chính sách an toàn', 'url' => '/policies/safety', 'parent' => 0, 'order' => 2],
            ['id' => 12, 'title' => 'Chính sách hủy vé', 'url' => '/policies/cancel', 'parent' => 0, 'order' => 3],
            ['id' => 13, 'title' => 'Chính sách đổi vé, bảo lưu', 'url' => '/policies/exchange', 'parent' => 0, 'order' => 4],
            ['id' => 14, 'title' => 'Chính sách hoàn tiền', 'url' => '/policies/refund', 'parent' => 0, 'order' => 5],
        ];
    }

    return new WP_REST_Response([
        'success' => true,
        'data' => [
            'primary' => $primary,
            'footer' => $footer,
        ]
    ], 200);
}

// Helper lấy menu items theo location
function newtrip_get_menu_items_by_location($location) {
    $locations = get_nav_menu_locations();
    if (!isset($locations[$location])) {
        return [];
    }
    
    $menu = wp_get_nav_menu_object($locations[$location]);
    if (!$menu) {
        return [];
    }
    
    $menu_items = wp_get_nav_menu_items($menu->term_id);
    if (!$menu_items) {
        return [];
    }
    
    $items = [];
    foreach ($menu_items as $item) {
        $url = $item->url;
        $site_url = get_site_url();
        
        // Trích xuất path tương đối cho liên kết trong cùng hệ thống
        $parsed_url = parse_url($url);
        $path = $parsed_url['path'] ?? '/';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        $relative_url = $path . $query . $fragment;
        
        $host = $parsed_url['host'] ?? '';
        $backend_host = parse_url($site_url, PHP_URL_HOST);
        
        if (!empty($host) && $host !== $backend_host && !str_contains($host, 'vercel.app') && !str_contains($host, 'localhost') && !str_contains($host, 'newtrip.com.vn')) {
            $final_url = $url;
        } else {
            $final_url = $relative_url;
        }

        $items[] = [
            'id' => $item->ID,
            'title' => $item->title,
            'url' => $final_url,
            'parent' => intval($item->menu_item_parent),
            'order' => $item->menu_order,
        ];
    }
    
    // Sắp xếp theo thứ tự menu_order
    usort($items, function($a, $b) {
        return $a['order'] - $b['order'];
    });
    
    return $items;
}

// 6.16 Lấy dữ liệu tĩnh của Trang chủ cho Frontend
function newtrip_api_get_homepage_data(WP_REST_Request $request) {
    // Tìm trang chủ tĩnh
    $homepage_id = null;
    $front_page_id = get_option('page_on_front');
    if ($front_page_id) {
        $homepage_id = intval($front_page_id);
    } else {
        // Dự phòng: tìm kiếm theo path 'trang-chu'
        $homepage = get_page_by_path('trang-chu');
        if ($homepage) {
            $homepage_id = $homepage->ID;
        }
    }

    $hero_banner = $homepage_id ? get_field('hero_banner', $homepage_id) : '';
    $hero_badge = $homepage_id ? get_field('hero_badge', $homepage_id) : '';
    $hero_title = $homepage_id ? get_field('hero_title', $homepage_id) : '';
    $hero_subtitle = $homepage_id ? get_field('hero_subtitle', $homepage_id) : '';

    $about_image = $homepage_id ? get_field('about_image', $homepage_id) : '';
    $about_badge = $homepage_id ? get_field('about_badge', $homepage_id) : '';
    $about_title = $homepage_id ? get_field('about_title', $homepage_id) : '';
    $about_features = $homepage_id ? get_field('about_features', $homepage_id) : null;

    $partners_badge = $homepage_id ? get_field('partners_badge', $homepage_id) : '';
    $partners_title = $homepage_id ? get_field('partners_title', $homepage_id) : '';
    $partners_subtitle = $homepage_id ? get_field('partners_subtitle', $homepage_id) : '';
    $partners_items = $homepage_id ? get_field('partners_items', $homepage_id) : null;

    // Định dạng features của About Section
    $formatted_features = [];
    if (!empty($about_features) && is_array($about_features)) {
        foreach ($about_features as $feature) {
            $formatted_features[] = [
                'icon' => $feature['icon'] ?? 'users',
                'gradient' => $feature['gradient'] ?? 'from-[#16a249] to-[#10b981]',
                'title' => $feature['title'] ?? '',
                'description' => $feature['description'] ?? '',
            ];
        }
    }

    // Định dạng items của Partners Section
    $formatted_partners = [];
    if (!empty($partners_items) && is_array($partners_items)) {
        foreach ($partners_items as $item) {
            $formatted_partners[] = [
                'icon' => $item['icon'] ?? '🛡️',
                'name' => $item['name'] ?? '',
                'desc' => $item['desc'] ?? '',
                'color' => $item['color'] ?? 'from-blue-500 to-blue-600',
            ];
        }
    }

    return new WP_REST_Response([
        'success' => true,
        'data' => [
            'seo' => $homepage_id ? newtrip_get_yoast_seo($homepage_id) : null,
            'hero' => [
                'banner' => $hero_banner ?: '/images/banner3.jpg',
                'badge' => $hero_badge ?: 'Trekking & Camping Experience',
                'title' => $hero_title ?: 'Khám phá thiên nhiên Việt Nam',
                'subtitle' => $hero_subtitle ?: 'Trải nghiệm những chuyến đi trekking, camping tuyệt vời nhất cùng đội ngũ hướng dẫn viên chuyên nghiệp',
            ],
            'about' => [
                'image' => $about_image ?: '/images/about-adventure.jpg',
                'badge' => $about_badge ?: 'Tại sao chọn Đôi Dép Adventure',
                'title' => $about_title ?: 'Đối tác tin cậy cho hành trình đáng nhớ',
                'features' => !empty($formatted_features) ? $formatted_features : null,
            ],
            'ecosystem' => [
                'badge' => $partners_badge ?: 'Đối tác chiến lược',
                'title' => $partners_title ?: 'Hệ sinh thái Đôi Dép Adventure',
                'subtitle' => $partners_subtitle ?: 'Kết nối đa dạng dịch vụ để mang đến trải nghiệm trekking trọn vẹn nhất',
                'items' => !empty($formatted_partners) ? $formatted_partners : null,
            ]
        ]
    ], 200);
}

// 6.17 Lấy dữ liệu tĩnh của Trang Giới thiệu (About)
function newtrip_api_get_about_data(WP_REST_Request $request) {
    $about_id = null;
    $about_page = get_page_by_path('gioi-thieu');
    if ($about_page) {
        $about_id = $about_page->ID;
    }

    $hero_badge = $about_id ? get_field('about_hero_badge', $about_id) : '';
    $hero_title = $about_id ? get_field('about_hero_title', $about_id) : '';
    $hero_subtitle = $about_id ? get_field('about_hero_subtitle', $about_id) : '';

    $stats_data = $about_id ? get_field('about_stats', $about_id) : null;
    $mission_badge = $about_id ? get_field('mission_badge', $about_id) : '';
    $mission_title = $about_id ? get_field('mission_title', $about_id) : '';
    $mission_desc = $about_id ? get_field('mission_desc', $about_id) : '';
    $mission_points = $about_id ? get_field('mission_points', $about_id) : null;
    $mission_right_title = $about_id ? get_field('mission_right_title', $about_id) : '';
    $mission_right_subtitle = $about_id ? get_field('mission_right_subtitle', $about_id) : '';
    $mission_right_icon = $about_id ? get_field('mission_right_icon', $about_id) : '';

    $team_badge = $about_id ? get_field('team_badge', $about_id) : '';
    $team_title = $about_id ? get_field('team_title', $about_id) : '';
    $team_members = $about_id ? get_field('team_members', $about_id) : null;

    // Định dạng stats
    $formatted_stats = [];
    if (!empty($stats_data) && is_array($stats_data)) {
        foreach ($stats_data as $stat) {
            $formatted_stats[] = [
                'number' => $stat['number'] ?? '',
                'label' => $stat['label'] ?? '',
            ];
        }
    }

    // Định dạng mission points
    $formatted_points = [];
    if (!empty($mission_points) && is_array($mission_points)) {
        foreach ($mission_points as $point) {
            $formatted_points[] = [
                'icon' => $point['icon'] ?? 'users',
                'title' => $point['title'] ?? '',
                'desc' => $point['desc'] ?? '',
            ];
        }
    }

    // Định dạng team members
    $formatted_members = [];
    if (!empty($team_members) && is_array($team_members)) {
        foreach ($team_members as $member) {
            $formatted_members[] = [
                'name' => $member['name'] ?? '',
                'role' => $member['role'] ?? '',
                'avatar_text' => $member['avatar_text'] ?? '',
                'avatar_image' => $member['avatar_image'] ?? '',
            ];
        }
    }

    return new WP_REST_Response([
        'success' => true,
        'data' => [
            'hero' => [
                'badge' => $hero_badge ?: 'Về chúng tôi',
                'title' => $hero_title ?: 'Câu chuyện của Đôi Dép Adventure',
                'subtitle' => $hero_subtitle ?: 'Đôi Dép Adventure được thành lập với niềm đam mê khám phá thiên nhiên Việt Nam. Chúng tôi tin rằng mỗi người đều xứng đáng được trải nghiệm những điều tuyệt vời nhất mà thiên nhiên mang lại.',
            ],
            'stats' => !empty($formatted_stats) ? $formatted_stats : [
                ['number' => '500+', 'label' => 'Chuyến đã tổ chức'],
                ['number' => '3000+', 'label' => 'Khách hàng'],
                ['number' => '50+', 'label' => 'Tuyến đường'],
                ['number' => '5', 'label' => 'Năm kinh nghiệm'],
            ],
            'mission' => [
                'badge' => $mission_badge ?: 'Sứ mệnh',
                'title' => $mission_title ?: 'Mang thiên nhiên đến gần hơn với mọi người',
                'description' => $mission_desc ?: 'Chúng tôi không chỉ tổ chức các chuyến đi - chúng tôi tạo ra những trải nghiệm đáng nhớ, an toàn và phù hợp với mọi lứa tuổi. Từ những buổi dã ngoại đơn giản đến những chuyến trekking đầy thử thách, Đôi Dép Adventure luôn đồng hành cùng bạn.',
                'points' => !empty($formatted_points) ? $formatted_points : [
                    ['icon' => 'users', 'title' => 'Đội ngũ chuyên nghiệp', 'desc' => 'Hướng dẫn viên giàu kinh nghiệm, được đào tạo bài bản'],
                    ['icon' => 'shield', 'title' => 'An toàn là ưu tiên số 1', 'desc' => 'Trang thiết bị chất lượng cao, quy trình an toàn nghiêm ngặt'],
                ],
                'right_title' => $mission_right_title ?: 'Khám phá Việt Nam',
                'right_subtitle' => $mission_right_subtitle ?: 'Từ Bắc vào Nam',
                'right_icon' => $mission_right_icon ?: 'map-pin',
            ],
            'team' => [
                'badge' => $team_badge ?: 'Đội ngũ',
                'title' => $team_title ?: 'Những người đam mê khám phá',
                'members' => !empty($formatted_members) ? $formatted_members : [
                    ['name' => 'Minh Anh', 'role' => 'Founder & CEO', 'avatar_text' => 'MA'],
                    ['name' => 'Hoàng Nam', 'role' => 'Head Guide', 'avatar_text' => 'HN'],
                    ['name' => 'Thu Hà', 'role' => 'Operations Manager', 'avatar_text' => 'TH'],
                    ['name' => 'Văn Đức', 'role' => 'Lead Trekker', 'avatar_text' => 'VD'],
                ],
            ],
            'seo' => $about_id ? newtrip_get_yoast_seo($about_id) : null
        ]
    ], 200);
}

// 6.18 Lấy dữ liệu tĩnh của Trang Liên hệ (Contact)
function newtrip_api_get_contact_data(WP_REST_Request $request) {
    $contact_id = null;
    $contact_page = get_page_by_path('lien-he');
    if ($contact_page) {
        $contact_id = $contact_page->ID;
    }

    $hero_badge = $contact_id ? get_field('contact_hero_badge', $contact_id) : '';
    $hero_title = $contact_id ? get_field('contact_hero_title', $contact_id) : '';
    $hero_subtitle = $contact_id ? get_field('contact_hero_subtitle', $contact_id) : '';

    $form_title = $contact_id ? get_field('contact_form_title', $contact_id) : '';
    $hours = $contact_id ? get_field('contact_hours', $contact_id) : '';
    $days = $contact_id ? get_field('contact_days', $contact_id) : '';

    return new WP_REST_Response([
        'success' => true,
        'data' => [
            'seo' => $contact_id ? newtrip_get_yoast_seo($contact_id) : null,
            'hero' => [
                'badge' => $hero_badge ?: 'Liên hệ',
                'title' => $hero_title ?: 'Kết nối với Đôi Dép Adventure',
                'subtitle' => $hero_subtitle ?: 'Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn. Liên hệ ngay để được tư vấn về các chuyến đi.',
            ],
            'working_hours' => [
                'hours' => $hours ?: '8:00 - 20:00',
                'days' => $days ?: 'Thứ 2 - Chủ nhật',
            ],
            'form_title' => $form_title ?: 'Gửi tin nhắn cho chúng tôi',
        ]
    ], 200);
}

// 7. Đồng bộ điều kiện hiển thị của nhóm ACF About & Contact động dựa trên Slug
add_filter('acf/load_field_group', function($group) {
    if ($group['key'] === 'group_about_page') {
        $page = get_page_by_path('gioi-thieu');
        if ($page) {
            $group['location'] = [
                [
                    [
                        'param' => 'post',
                        'operator' => '==',
                        'value' => $page->ID,
                    ]
                ]
            ];
        }
    }
    if ($group['key'] === 'group_contact_page') {
        $page = get_page_by_path('lien-he');
        if ($page) {
            $group['location'] = [
                [
                    [
                        'param' => 'post',
                        'operator' => '==',
                        'value' => $page->ID,
                    ]
                ]
            ];
        }
    }
    return $group;
});

// Callback API lấy danh sách thành viên check-in
function newtrip_api_get_checkin_passengers(WP_REST_Request $request) {
    $tour_id = intval($request->get_param('tour_id'));
    $departure_date = sanitize_text_field((string) $request->get_param('departure_date'));
    
    $args = [
        'post_type' => 'booking',
        'post_status' => 'any',
        'posts_per_page' => 500,
    ];
    
    // Loại bỏ các booking đã huỷ / chờ duyệt khỏi danh sách check-in
    $excluded_statuses = ['cancelled', 'pending', 'refunded'];
    $args['meta_query'] = [
        [
            'key' => 'status',
            'value' => $excluded_statuses,
            'compare' => 'NOT IN'
        ]
    ];
    
    if ($tour_id > 0) {
        $args['meta_query'][] = [
            'key' => 'tour_id',
            'value' => $tour_id,
            'compare' => '='
        ];
    }
    
    if ($departure_date !== '') {
        $args['meta_query'][] = [
            'key' => 'departure_date',
            'value' => $departure_date,
            'compare' => '='
        ];
    }
    
    $query = new WP_Query($args);
    $results = [];
    $departures = [];
    
    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            $b_id = $post->ID;
            $booking_code = get_post_meta($b_id, 'booking_code', true) ?: '';
            
            $t_id_raw = newtrip_get_field('tour_id', $b_id);
            $t_id = 0;
            if (is_object($t_id_raw)) {
                $t_id = $t_id_raw->ID;
            } elseif (is_array($t_id_raw)) {
                $t_id = $t_id_raw['ID'] ?? 0;
            } else {
                $t_id = intval($t_id_raw);
            }
            if (empty($t_id)) {
                $t_id = intval(get_post_meta($b_id, 'tour_id', true));
            }
            
            $tour_post = get_post($t_id);
            $tour_name = $tour_post ? $tour_post->post_title : 'Chuyến đi';
            
            $departure_date = newtrip_get_field('departure_date', $b_id) ?: '';
            if (!empty($departure_date)) {
                $departures[$departure_date] = true;
            }
            
            $passengers = newtrip_get_field('passengers', $b_id);
            if (is_array($passengers)) {
                foreach ($passengers as $idx => $p) {
                    $p_pickup_point_id = 0;
                    if (isset($p['pickup_point_id'])) {
                        if (is_object($p['pickup_point_id'])) {
                            $p_pickup_point_id = $p['pickup_point_id']->ID;
                        } elseif (is_array($p['pickup_point_id']) && isset($p['pickup_point_id']['ID'])) {
                            $p_pickup_point_id = $p['pickup_point_id']->ID;
                        } else {
                            $p_pickup_point_id = intval($p['pickup_point_id']);
                        }
                    }
                    $pickup_name = '';
                    if ($p_pickup_point_id) {
                        $pickup_post = get_post($p_pickup_point_id);
                        if ($pickup_post) {
                            $pickup_name = $pickup_post->post_title;
                        }
                    }
                    
                    $results[] = [
                        'id' => $b_id . '_' . $idx,
                        'booking_id' => $b_id,
                        'booking_code' => $booking_code,
                        'passenger_index' => $idx,
                        'full_name' => $p['full_name'] ?? '',
                        'phone' => $p['phone'] ?? '',
                        'birth_date' => $p['birth_date'] ?? ($p['birth_year'] ?? ''),
                        'health_status' => $p['health_status'] ?? '',
                        'seat' => $p['seat'] ?? '',
                        'pickup_point' => $pickup_name,
                        'checked_in' => !empty($p['checked_in']) ? true : false,
                        'checked_in_gathering' => !empty($p['checked_in_gathering']) ? true : false,
                        'tour_id' => $t_id,
                        'tour_name' => $tour_name,
                        'departure_date' => $departure_date,
                    ];
                }
            }
        }
    }
    
    $departure_dates = array_keys($departures);
    sort($departure_dates);
    
    return new WP_REST_Response([
        'success' => true,
        'data' => $results,
        'meta' => [
            'departure_dates' => $departure_dates,
        ]
    ], 200);
}

// Callback API toggle check-in
function newtrip_api_toggle_checkin(WP_REST_Request $request) {
    $params = $request->get_json_params();
    if (!is_array($params)) $params = [];
    
    $booking_id = intval($params['booking_id'] ?? 0);
    $passenger_index = isset($params['passenger_index']) ? intval($params['passenger_index']) : -1;
    $type = sanitize_text_field($params['type'] ?? '');
    $value = !empty($params['value']) ? 1 : 0;
    
    if (!$booking_id || $passenger_index < 0 || !in_array($type, ['boarding', 'gathering'])) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'invalid_params', 'message' => 'Tham số không hợp lệ']
        ], 400);
    }
    
    $passengers = newtrip_get_field('passengers', $booking_id);
    if (!is_array($passengers) || !isset($passengers[$passenger_index])) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'passenger_not_found', 'message' => 'Không tìm thấy hành khách tương ứng']
        ], 404);
    }
    
    if ($type === 'boarding') {
        $passengers[$passenger_index]['checked_in'] = $value;
    } else {
        $passengers[$passenger_index]['checked_in_gathering'] = $value;
    }
    
    $updated = update_field('field_booking_passengers', $passengers, $booking_id);
    if (!$updated) {
        $updated = update_field('passengers', $passengers, $booking_id);
    }
    
    return new WP_REST_Response([
        'success' => true,
        'message' => 'Cập nhật trạng thái check-in thành công',
        'data' => [
            'booking_id' => $booking_id,
            'passenger_index' => $passenger_index,
            'type' => $type,
            'value' => $value ? true : false
        ]
    ], 200);
}

// Tự động đồng bộ khách hàng và hành khách sang CPT customer để quản lý dữ liệu và remarketing
function newtrip_sync_booking_to_customer($booking_id) {
    // Chỉ đồng bộ khi đơn đặt tour Đã xác nhận/Đã thanh toán (confirmed) hoặc Đã hoàn thành (completed) hoặc trạng thái thanh toán là paid
    $booking_status = newtrip_get_field('status', $booking_id) ?: 'pending';
    $payment_status = get_post_meta($booking_id, 'payment_status', true) ?: 'unpaid';
    
    if ($booking_status !== 'confirmed' && $booking_status !== 'completed' && $payment_status !== 'paid') {
        return; // Chưa đủ điều kiện đồng bộ
    }
    
    $people = [];
    
    // 1. Trích xuất thông tin người đại diện (người đặt tour)
    $rep_name = newtrip_get_field('full_name', $booking_id) ?: '';
    $rep_phone = newtrip_get_field('phone', $booking_id) ?: '';
    $rep_email = newtrip_get_field('email', $booking_id) ?: '';
    $rep_birth_date = newtrip_get_field('birth_date', $booking_id) ?: (newtrip_get_field('birth_year', $booking_id) ?: '');
    
    if (!empty($rep_phone) && !empty($rep_name)) {
        $people[] = [
            'full_name' => $rep_name,
            'phone' => $rep_phone,
            'email' => $rep_email,
            'birth_date' => $rep_birth_date,
            'is_representative' => true
        ];
    }
    
    // 2. Trích xuất thông tin hành khách đi cùng
    $passengers = newtrip_get_field('passengers', $booking_id);
    if (is_array($passengers)) {
        foreach ($passengers as $p) {
            $p_name = $p['full_name'] ?? '';
            $p_phone = $p['phone'] ?? '';
            $p_email = $p['email'] ?? '';
            $p_birth_date = $p['birth_date'] ?? ($p['birth_year'] ?? '');
            
            if (!empty($p_phone) && !empty($p_name)) {
                $people[] = [
                    'full_name' => $p_name,
                    'phone' => $p_phone,
                    'email' => $p_email,
                    'birth_date' => $p_birth_date,
                    'is_representative' => false
                ];
            }
        }
    }
    
    if (empty($people)) {
        return;
    }
    
    // Thu thập thông tin tour của Booking
    $booking_code = get_post_meta($booking_id, 'booking_code', true) ?: '';
    $tour_id_raw = newtrip_get_field('tour_id', $booking_id);
    $tour_id = 0;
    if (is_object($tour_id_raw)) {
        $tour_id = $tour_id_raw->ID;
    } elseif (is_array($tour_id_raw)) {
        $tour_id = $tour_id_raw['ID'] ?? 0;
    } else {
        $tour_id = intval($tour_id_raw);
    }
    if (empty($tour_id)) {
        $tour_id = intval(get_post_meta($booking_id, 'tour_id', true));
    }
    
    $tour_post = get_post($tour_id);
    $tour_name = $tour_post ? $tour_post->post_title : 'Chuyến đi';
    $departure_date = newtrip_get_field('departure_date', $booking_id) ?: '';
    
    foreach ($people as $person) {
        $phone = newtrip_normalize_phone(sanitize_text_field($person['phone']));
        $full_name = sanitize_text_field($person['full_name']);
        $email = sanitize_email($person['email']);
        $birth_date = sanitize_text_field($person['birth_date']);
        
        // Truy vấn xem số điện thoại này đã tồn tại trong danh sách Khách hàng chưa
        $customer_query = new WP_Query([
            'post_type' => 'customer',
            'meta_query' => [
                [
                    'key' => 'phone',
                    'value' => $phone,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1,
            'post_status' => 'any'
        ]);
        
        $customer_id = 0;
        if ($customer_query->have_posts()) {
            $customer_id = $customer_query->posts[0]->ID;
        } else {
            // Chưa có -> Tạo khách hàng mới
            $customer_id = wp_insert_post([
                'post_type' => 'customer',
                'post_title' => sprintf('%s - %s', $full_name, $phone),
                'post_status' => 'publish'
            ]);
            
            update_post_meta($customer_id, 'phone', $phone);
            update_post_meta($customer_id, 'full_name', $full_name);
        }
        
        if (!$customer_id) continue;
        
        // Cập nhật thông tin profile nếu trống hoặc có cập nhật mới
        if (!empty($email)) {
            update_post_meta($customer_id, 'email', $email);
        }
        if (!empty($birth_date)) {
            update_post_meta($customer_id, 'birth_date', $birth_date);
        }
        
        // Xử lý Lịch sử mua hàng (bookings_history)
        $history_raw = get_post_meta($customer_id, 'bookings_history', true);
        $history = [];
        if (!empty($history_raw)) {
            $history = is_array($history_raw) ? $history_raw : json_decode($history_raw, true);
        }
        if (!is_array($history)) $history = [];
        
        // Kiểm tra xem đơn hàng này đã được đồng bộ trong lịch sử của họ chưa
        $exists_in_history = false;
        foreach ($history as $h) {
            if (($h['booking_code'] ?? '') === $booking_code || ($h['booking_id'] ?? 0) == $booking_id) {
                $exists_in_history = true;
                break;
            }
        }
        
        if (!$exists_in_history) {
            // Thêm booking này vào lịch sử
            $history[] = [
                'booking_id' => $booking_id,
                'booking_code' => $booking_code,
                'tour_id' => $tour_id,
                'tour_name' => $tour_name,
                'departure_date' => $departure_date,
                'booking_date' => get_the_date('Y-m-d', $booking_id),
                'status' => $booking_status,
                'payment_status' => $payment_status,
                'is_representative' => $person['is_representative']
            ];
            
            // Tính toán lại Tổng chi tiêu
            $total_spent = floatval(get_post_meta($customer_id, 'total_spent', true) ?: 0);
            if ($person['is_representative']) {
                // Nếu là người đặt tour thì cộng toàn bộ tiền đơn hàng vào chi tiêu
                $total_spent += floatval(newtrip_get_field('total_amount', $booking_id));
            }
            
            update_post_meta($customer_id, 'total_spent', $total_spent);
            update_post_meta($customer_id, 'bookings_history', $history);
            update_post_meta($customer_id, 'total_bookings', count($history));
            update_post_meta($customer_id, 'last_booking_date', date('Y-m-d H:i:s'));
        }
    }
}

// Đăng ký các cột hiển thị trong Admin Table cho Customer CPT
add_filter('manage_customer_posts_columns', 'newtrip_customer_table_columns');
function newtrip_customer_table_columns($columns) {
    return [
        'cb' => $columns['cb'],
        'title' => __('Họ tên', 'newtrip-theme'),
        'phone' => __('Số điện thoại', 'newtrip-theme'),
        'email' => __('Email', 'newtrip-theme'),
        'birth_date' => __('Ngày sinh', 'newtrip-theme'),
        'total_bookings' => __('Số chuyến đi', 'newtrip-theme'),
        'total_spent' => __('Tổng chi tiêu', 'newtrip-theme'),
        'last_booking_date' => __('Ngày đặt gần nhất', 'newtrip-theme'),
    ];
}

add_action('manage_customer_posts_custom_column', 'newtrip_customer_table_column_content', 10, 2);
function newtrip_customer_table_column_content($column, $post_id) {
    switch ($column) {
        case 'phone':
            echo esc_html(get_post_meta($post_id, 'phone', true) ?: '—');
            break;
        case 'email':
            echo esc_html(get_post_meta($post_id, 'email', true) ?: '—');
            break;
        case 'birth_date':
            echo esc_html(get_post_meta($post_id, 'birth_date', true) ?: '—');
            break;
        case 'total_bookings':
            echo esc_html(get_post_meta($post_id, 'total_bookings', true) ?: '0');
            break;
        case 'total_spent':
            $spent = floatval(get_post_meta($post_id, 'total_spent', true) ?: 0);
            echo esc_html(number_format($spent) . ' đ');
            break;
        case 'last_booking_date':
            $date = get_post_meta($post_id, 'last_booking_date', true);
            echo esc_html($date ? date('d/m/Y H:i', strtotime($date)) : '—');
            break;
    }
}




