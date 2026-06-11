<?php
/**
 * Doi Dep Adventure Theme Functions
 * Xây dựng hệ thống REST API Headless CMS cho newtrip.com.vn (Hỗ trợ ACF Pro)
 */

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
}
add_action('init', 'newtrip_register_post_types');

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

// Xử lý Thư viện ảnh ACF Pro
function newtrip_parse_gallery_pro($value, $post_id) {
    if (empty($value)) {
        $thumbnail_id = get_post_thumbnail_id($post_id);
        if ($thumbnail_id) {
            return [wp_get_attachment_url($thumbnail_id)];
        }
        return ["/images/logo.png"];
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
                'activity' => isset($row['activity']) ? trim($row['activity']) : ''
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
                'activity' => trim($parts[1])
            ];
        }
    }
    return $itinerary;
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

    // Thiết lập dịch vụ kèm theo (mặc định)
    $services = [
        ['id' => 'transport', 'name' => 'Xe đưa đón', 'description' => 'Xe đưa đón khứ hồi TP.HCM', 'price' => 100000, 'unit' => 'người'],
        ['id' => 'insurance', 'name' => 'Bảo hiểm nâng cao', 'description' => 'Bảo hiểm với mức đền bù cao hơn', 'price' => 50000, 'unit' => 'người'],
        ['id' => 'gear', 'name' => 'Thuê trang bị', 'description' => 'Balo, gậy trekking, giày dép', 'price' => 80000, 'unit' => 'người'],
    ];

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

    // 5.6 GET /wp-json/newtrip/v1/booking/<id> - Tra cứu đơn đặt tour
    register_rest_route('newtrip/v1', '/booking/(?P<id>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'newtrip_api_get_booking',
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
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'missing_fields', 'message' => 'Vui lòng nhập đầy đủ các trường bắt buộc']
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
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'departure_full', 'message' => 'Ngày khởi hành yêu cầu đã hết chỗ hoặc không đủ số lượng chỗ trống']
        ], 400);
    }

    // 1. Tính toán Dịch vụ bổ sung chọn kèm
    $selected_services = isset($params['services']) && is_array($params['services']) ? $params['services'] : [];
    $tour_services = [
        'transport' => ['name' => 'Xe đưa đón', 'price' => 100000],
        'insurance' => ['name' => 'Bảo hiểm nâng cao', 'price' => 50000],
        'gear' => ['name' => 'Thuê trang bị', 'price' => 80000],
    ];
    $services_data = [];
    $services_total = 0;
    foreach ($selected_services as $s_id) {
        if (isset($tour_services[$s_id])) {
            $s_price = $tour_services[$s_id]['price'];
            $services_total += $s_price * $participants;
            $services_data[] = [
                'service_id' => $s_id,
                'name' => $tour_services[$s_id]['name'],
                'price' => $s_price,
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
        
        $rental_query = new WP_Query([
            'post_type' => 'rental_item',
            'name' => $r_id,
            'posts_per_page' => 1,
        ]);
        
        if ($rental_query->have_posts()) {
            $rental_post = $rental_query->posts[0];
            $rental_item_id = $rental_post->ID;
            $r_price = floatval(newtrip_get_field('price', $rental_item_id));
            $r_name = $rental_post->post_title;
            
            $subtotal = $r_price * $qty;
            $rental_total += $subtotal;
            
            $rental_items_data[] = [
                'item_id' => $r_id,
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
        
        $p_phone = isset($p['phone']) ? sanitize_text_field($p['phone']) : '';
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
        'post_title' => sprintf('Đặt tour %s - %s [%s]', $tour_post->post_title, $full_name, $booking_code),
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

    // Cập nhật lại số chỗ còn trống trên Tour
    if (function_exists('update_field')) {
        update_field('departure_dates', $updated_dates, $tour_id);
    } else {
        update_post_meta($tour_id, 'departure_dates', $updated_dates);
    }

    // Gửi email xác nhận
    $subject = sprintf('Xác nhận đặt tour %s [%s]', $tour_post->post_title, $booking_code);
    $body = sprintf(
        "Chào %s,\n\nCảm ơn bạn đã đặt tour tại Đôi Dép Adventure!\n\nChi tiết đơn đặt tour:\n" .
        "- Mã đặt tour: %s\n" .
        "- Tour: %s\n" .
        "- Ngày khởi hành: %s\n" .
        "- Số người: %d\n" .
        "- Tổng thanh toán: %s đ\n" .
        "- Phương thức: %s\n\n" .
        "Chúng tôi sẽ liên hệ lại qua số điện thoại %s để xác nhận thông tin sớm nhất.\n\nTrân trọng,\nĐôi Dép Adventure.",
        $full_name,
        $booking_code,
        $tour_post->post_title,
        $departure_date,
        $participants,
        number_format($total_amount, 0, ',', '.'),
        $payment_method === 'transfer' ? 'Chuyển khoản ngân hàng' : 'Tiền mặt',
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
                        'id' => $item['item_id'],
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
    
    $query = new WP_Query([
        'post_type' => 'booking',
        'meta_query' => [
            [
                'key' => 'booking_code',
                'value' => $booking_code,
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
    $tour_id = intval(newtrip_get_field('tour_id', $b_id));
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
                'checked_in' => !empty($p['checked_in']) ? (bool)$p['checked_in'] : false,
                'birth_date' => $p['birth_date'] ?? ($p['birth_year'] ?? ''),
                'id_number' => $p['id_number'] ?? '',
                'health_status' => $p['health_status'] ?? '',
            ];
        }
    } else {
        $passengers = [
            [
                'id' => 1000,
                'full_name' => newtrip_get_field('full_name', $b_id),
                'phone' => newtrip_get_field('phone', $b_id),
                'email' => newtrip_get_field('email', $b_id),
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
            $rental_items[] = [
                'id' => $r['item_id'] ?? '',
                'name' => $r['name'] ?? '',
                'qty' => intval($r['qty'] ?? 0),
                'subtotal' => floatval($r['subtotal'] ?? 0),
            ];
        }
    }

    $method = newtrip_get_field('payment_method', $b_id) ?: 'transfer';
    $status = newtrip_get_field('status', $b_id) ?: 'pending';
    $payment_status = ($status === 'confirmed') ? 'paid' : 'unpaid';

    $bank_info = null;
    if ($method === 'transfer') {
        $bank_bin = '970422';
        $account_no = '123456789';
        $account_name = 'DOI DEP ADVENTURE COMPANY';
        $qr_payload = newtrip_generate_vietqr_payload($bank_bin, $account_no, $total, $booking_code, $account_name);

        $bank_info = [
            'bank_name' => 'MB Bank',
            'bank_bin' => $bank_bin,
            'account_no' => $account_no,
            'account_name' => $account_name,
            'amount' => $total,
            'content' => $booking_code,
            'qr_payload' => $qr_payload,
            'qr_url' => sprintf('https://chart.googleapis.com/chart?chs=350x350&cht=qr&chl=%s', urlencode($qr_payload)),
            'deeplink' => sprintf('https://link.vietqr.io/2.0/referral/vietqr?bin=%s&account=%s&amount=%d&addInfo=%s', $bank_bin, $account_no, $total, $booking_code)
        ];
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
                'departure_time' => $tour_post ? newtrip_get_field('departure_time', $tour_id) : 'Sáng',
            ],
            'main_contact' => [
                'full_name' => newtrip_get_field('full_name', $b_id),
                'phone' => newtrip_get_field('phone', $b_id),
                'email' => newtrip_get_field('email', $b_id),
            ],
            'passengers' => $passengers,
            'rental_items' => $rental_items,
            'payment' => [
                'method' => $method,
                'total' => $total,
                'paid' => ($status === 'confirmed') ? $total : 0,
                'remaining' => ($status === 'confirmed') ? 0 : $total,
                'status' => $payment_status,
                'bank_info' => $bank_info
            ]
        ]
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
    } else {
        $data = newtrip_get_mock_posts();
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
    
    // Check mock posts
    $mock_posts = newtrip_get_mock_posts();
    foreach ($mock_posts as $mock) {
        if ($mock['id'] === $id) {
            return new WP_REST_Response([
                'success' => true,
                'data' => $mock
            ], 200);
        }
    }

    return new WP_REST_Response([
        'success' => false,
        'error' => ['code' => 'post_not_found', 'message' => 'Không tìm thấy bài viết yêu cầu']
    ], 404);
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
        $image = '/images/logo.png';
    }

    $author_name = get_the_author_meta('display_name', $post->post_author) ?: 'Admin';
    $author_bio = get_the_author_meta('description', $post->post_author) ?: 'Đội ngũ biên tập viên Đôi Dép Adventure.';

    // Tính thời gian đọc ước tính
    $word_count = str_word_count(strip_tags($post->post_content));
    $read_time_min = ceil($word_count / 200);
    $read_time = ($read_time_min > 0 ? $read_time_min : 5) . ' phút';

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
        'date' => get_the_date('d/m/Y', $post_id),
        'read_time' => $read_time,
        'category' => $category_name,
        'tags' => $tag_names,
        'image' => $image,
        'color' => $color,
        'content' => apply_filters('the_content', $post->post_content)
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

