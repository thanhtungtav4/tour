<?php
/**
 * Doi Dep Adventure Theme Functions
 * Xây dựng hệ thống REST API Headless CMS cho newtrip.com.vn
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

// 3. Các hàm bổ trợ phân tích dữ liệu dạng Textarea (Hỗ trợ ACF bản Free)
function newtrip_parse_newline_separated($value) {
    if (empty($value)) return [];
    if (is_array($value)) return $value; // Nếu đã là array (ACF Pro repeater)
    $lines = explode("\n", str_replace("\r", "", $value));
    return array_values(array_filter(array_map('trim', $lines)));
}

function newtrip_parse_itinerary($value) {
    if (empty($value)) return [];
    if (is_array($value)) return $value;
    
    $lines = explode("\n", str_replace("\r", "", $value));
    $itinerary = [];
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

function newtrip_parse_gallery($value, $post_id) {
    if (empty($value)) {
        // Fallback: Lấy ảnh tiêu biểu (featured image) nếu không có gallery
        $thumbnail_id = get_post_thumbnail_id($post_id);
        if ($thumbnail_id) {
            return [wp_get_attachment_url($thumbnail_id)];
        }
        return ["https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80"];
    }
    if (is_array($value)) {
        // Nếu dùng trường ACF Gallery (trả về array đối tượng ảnh hoặc URL)
        $urls = [];
        foreach ($value as $item) {
            if (is_string($item)) $urls[] = $item;
            elseif (is_array($item) && isset($item['url'])) $urls[] = $item['url'];
            elseif (is_object($item) && isset($item->url)) $urls[] = $item->url;
        }
        return $urls;
    }
    // Nếu dùng trường Textarea nhập các link ảnh (mỗi link 1 dòng)
    return newtrip_parse_newline_separated($value);
}

// Helper lấy meta/field tương thích cả có/không có ACF
function newtrip_get_field($key, $post_id) {
    if (function_exists('get_field')) {
        return get_field($key, $post_id);
    }
    return get_post_meta($post_id, $key, true);
}

// 4. Biến đổi dữ liệu Post sang định dạng JSON mà Frontend yêu cầu
function newtrip_format_tour_list_item($post) {
    $post_id = $post->ID;
    $price = floatval(newtrip_get_field('price', $post_id));
    $gallery = newtrip_parse_gallery(newtrip_get_field('gallery', $post_id), $post_id);
    $departure_dates_raw = newtrip_get_field('departure_dates', $post_id);
    $departure_dates = newtrip_parse_newline_separated($departure_dates_raw);
    
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
        'available_spots' => intval(newtrip_get_field('available_spots', $post_id) ?: 10),
        'departure_times' => [newtrip_get_field('departure_time', $post_id) ?: 'Sáng'],
        'highlights' => newtrip_parse_newline_separated(newtrip_get_field('highlights', $post_id)),
        'next_departure_date' => !empty($departure_dates) ? $departure_dates[0] : '',
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

    // Thiết lập dịch vụ kèm theo (mặc định hoặc cấu hình)
    $services = [
        ['id' => 'transport', 'name' => 'Xe đưa đón', 'description' => 'Xe đưa đón khứ hồi TP.HCM', 'price' => 100000, 'unit' => 'người'],
        ['id' => 'insurance', 'name' => 'Bảo hiểm nâng cao', 'description' => 'Bảo hiểm với mức đền bù cao hơn', 'price' => 50000, 'unit' => 'người'],
        ['id' => 'gear', 'name' => 'Thuê trang bị', 'description' => 'Balo, gậy trekking, giày dép', 'price' => 80000, 'unit' => 'người'],
    ];

    // Xử lý Ngày đi & Trạng thái chỗ
    $departure_dates_raw = newtrip_get_field('departure_dates', $post_id);
    $dates = newtrip_parse_newline_separated($departure_dates_raw);
    $available_spots = intval(newtrip_get_field('available_spots', $post_id) ?: 10);
    
    $departure_dates_formatted = [];
    foreach ($dates as $date) {
        $departure_dates_formatted[] = [
            'date' => $date,
            'available_spots' => $available_spots,
            'total_spots' => $available_spots + 10,
            'status' => $available_spots > 0 ? 'available' : 'full',
        ];
    }

    return array_merge($list_item, [
        'content' => apply_filters('the_content', $post->post_content),
        'itinerary' => newtrip_parse_itinerary(newtrip_get_field('itinerary', $post_id)),
        'included' => newtrip_parse_newline_separated(newtrip_get_field('included', $post_id)),
        'excluded' => newtrip_parse_newline_separated(newtrip_get_field('excluded', $post_id)),
        'notes' => newtrip_get_field('notes', $post_id) ?: '',
        'services' => $services,
        'departure_dates' => $departure_dates_formatted,
        'pickup_points' => $pickup_points,
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
});

// 6. Định nghĩa callbacks cho các API Endpoints

// 6.1 Lấy danh sách Tour
function newtrip_api_get_tours(WP_REST_Request $request) {
    $search = $request->get_param('search');
    $difficulty = $request->get_param('difficulty');
    $duration = $request->get_param('duration');
    $departure_time = $request->get_param('departure_time');
    
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

    // Lọc theo thời lượng (duration) ở PHP để linh hoạt
    if (!empty($duration)) {
        if ($duration === '1day') {
            $data = array_values(array_filter($data, function($t) { return $t['duration'] === '1 ngày'; }));
        } elseif ($duration === 'multi') {
            $data = array_values(array_filter($data, function($t) { return $t['duration'] !== '1 ngày'; }));
        }
    }

    return new WP_REST_Response([
        'success' => true,
        'data' => $data,
        'meta' => [
            'total' => count($data),
            'page' => 1,
            'per_page' => count($data),
            'total_pages' => 1
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

// 6.5 Đặt tour mới (POST)
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

    if (empty($tour_slug) || empty($full_name) || empty($phone) || empty($email)) {
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
    $available_spots = intval(newtrip_get_field('available_spots', $tour_id) ?: 10);

    if ($available_spots < $participants) {
        return new WP_REST_Response([
            'success' => false,
            'error' => ['code' => 'departure_full', 'message' => 'Tour đã hết chỗ trống hoặc không đủ chỗ yêu cầu']
        ], 400);
    }

    $services_total = 0; // Có thể mở rộng cộng dồn dịch vụ chọn thêm
    $rental_total = 0; // Có thể mở rộng cộng dồn giá thuê đồ

    $total_amount = ($price * $participants) + $services_total + $rental_total;
    $booking_code = 'NTR-' . strtoupper(wp_generate_password(6, false));

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

    $update_meta = function($post_id, $key, $value) {
        if (function_exists('update_field')) {
            update_field($key, $value, $post_id);
        } else {
            update_post_meta($post_id, $key, $value);
        }
    };

    $update_meta($booking_id, 'booking_code', $booking_code);
    $update_meta($booking_id, 'tour_id', $tour_id);
    $update_meta($booking_id, 'departure_date', $departure_date);
    $update_meta($booking_id, 'participants', $participants);
    $update_meta($booking_id, 'full_name', $full_name);
    $update_meta($booking_id, 'phone', $phone);
    $update_meta($booking_id, 'email', $email);
    $update_meta($booking_id, 'payment_method', $payment_method);
    $update_meta($booking_id, 'total_amount', $total_amount);
    $update_meta($booking_id, 'notes', $notes);
    $update_meta($booking_id, 'status', 'pending');

    // Cập nhật số chỗ trống của Tour
    $new_available_spots = $available_spots - $participants;
    $update_meta($tour_id, 'available_spots', $new_available_spots);

    // Gửi email thông báo
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
                'rental_items' => []
            ],
            'deposit_amount' => 0,
            'remaining_amount' => $total_amount,
            'payment_method' => $payment_method,
            'payment_status' => 'unpaid',
            'passengers' => [
                [
                    'id' => 1001,
                    'full_name' => $full_name,
                    'qr_code_url' => 'https://img.vietqr.io/image/MB-123456789-compact2.png'
                ]
            ],
            'next_steps' => [
                "Kiểm tra email xác nhận",
                "Thực hiện thanh toán chuyển khoản qua mã QR",
                "Chờ xác nhận từ tổng đài viên"
            ]
        ]
    ], 200);
}

// 6.6 Tra cứu đơn hàng
function newtrip_api_get_booking(WP_REST_Request $request) {
    $booking_code = sanitize_text_field($request->get_param('id'));
    
    // Tìm Booking theo mã đặt tour (meta_query hoặc title)
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

    return new WP_REST_Response([
        'success' => true,
        'data' => [
            'booking_id' => $booking_code,
            'status' => newtrip_get_field('status', $b_id) ?: 'pending',
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
            'passengers' => [
                [
                    'id' => 1,
                    'full_name' => newtrip_get_field('full_name', $b_id),
                    'checked_in' => false,
                ]
            ],
            'payment' => [
                'method' => newtrip_get_field('payment_method', $b_id) ?: 'transfer',
                'total' => $total,
                'paid' => 0,
                'remaining' => $total,
                'status' => 'unpaid',
                'bank_info' => [
                    'bank_name' => 'MB Bank',
                    'bank_bin' => '970422',
                    'account_no' => '123456789',
                    'account_name' => 'DOI DEP ADVENTURE COMPANY',
                    'amount' => $total,
                    'content' => $booking_code,
                    'qr_url' => sprintf('https://img.vietqr.io/image/MB-123456789-compact2.png?amount=%d&addInfo=%s&accountName=DOI+DEP+ADVENTURE+COMPANY', $total, $booking_code),
                ]
            ]
        ]
    ], 200);
}
