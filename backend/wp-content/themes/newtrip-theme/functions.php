<?php
/**
 * Doi Dep Adventure Theme Functions
 */

// 1. Đăng ký Custom Post Types (Tours & Bookings)
function newtrip_register_post_types() {
    // Đăng ký CPT Tour
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
        'show_in_rest' => true, // Cho phép truy cập qua WP REST API
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        'menu_icon' => 'dashicons-palmtree',
    ]);

    // Đăng ký CPT Booking (Đơn đặt tour)
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
}
add_action('init', 'newtrip_register_post_types');

// 2. Cấu hình tự động đồng bộ trường dữ liệu ACF (Advanced Custom Fields) sang Git dưới dạng JSON
add_filter('acf/settings/save_json', 'newtrip_acf_json_save_point');
function newtrip_acf_json_save_point($path) {
    return get_stylesheet_directory() . '/acf-json';
}

add_filter('acf/settings/load_json', 'newtrip_acf_json_load_point');
function newtrip_acf_json_load_point($paths) {
    unset($paths[0]); // Loại bỏ đường dẫn mặc định
    $paths[] = get_stylesheet_directory() . '/acf-json';
    return $paths;
}

// 3. Đăng ký Endpoint REST API tùy biến xử lý đặt tour cho Next.js
add_action('rest_api_init', function () {
    register_rest_route('newtrip/v1', '/booking', [
        'methods' => 'POST',
        'callback' => 'newtrip_handle_booking_api',
        'permission_callback' => '__return_true', // Bạn có thể tùy chọn thêm API Token hoặc chữ ký bảo mật ở đây
    ]);
});

function newtrip_handle_booking_api(WP_REST_Request $request) {
    $params = $request->get_json_params();
    
    // Sanitize và kiểm tra dữ liệu nhận từ Next.js
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
        return new WP_Error('missing_fields', 'Vui lòng nhập đầy đủ các thông tin bắt buộc', ['status' => 400]);
    }

    // Tìm bài viết Tour theo Slug
    $tour_query = new WP_Query([
        'post_type' => 'tour',
        'name' => $tour_slug,
        'posts_per_page' => 1,
    ]);

    if (!$tour_query->have_posts()) {
        return new WP_Error('tour_not_found', 'Không tìm thấy tour tương ứng', ['status' => 404]);
    }

    $tour_post = $tour_query->posts[0];
    $tour_id = $tour_post->ID;

    // Lấy giá tour và số chỗ trống từ Custom Fields
    // Sử dụng get_field() nếu plugin ACF kích hoạt, ngược lại fallback về get_post_meta()
    $price = function_exists('get_field') ? get_field('price', $tour_id) : get_post_meta($tour_id, 'price', true);
    $price = floatval($price);
    
    $available_spots = function_exists('get_field') ? get_field('available_spots', $tour_id) : get_post_meta($tour_id, 'available_spots', true);
    $available_spots = intval($available_spots);

    // Kiểm tra chỗ trống
    if ($available_spots < $participants) {
        return new WP_Error('departure_full', 'Tour đã hết chỗ hoặc không đủ số lượng chỗ yêu cầu', ['status' => 400]);
    }

    // Tính toán tổng tiền
    $total_amount = $price * $participants;

    // Sinh mã đặt tour ngẫu nhiên
    $booking_code = 'NTR-' . strtoupper(wp_generate_password(6, false));

    // Tạo bài viết Booking mới
    $booking_id = wp_insert_post([
        'post_type' => 'booking',
        'post_title' => sprintf('Đặt tour %s - %s [%s]', $tour_post->post_title, $full_name, $booking_code),
        'post_status' => 'publish',
    ]);

    if (is_wp_error($booking_id)) {
        return new WP_Error('database_error', 'Không thể khởi tạo đơn hàng mới trên hệ thống', ['status' => 500]);
    }

    // Hàm phụ trợ lưu meta hỗ trợ cả khi có/không có plugin ACF
    $update_meta = function($post_id, $key, $value) {
        if (function_exists('update_field')) {
            update_field($key, $value, $post_id);
        } else {
            update_post_meta($post_id, $key, $value);
        }
    };

    // Lưu các thông tin Custom Fields cho đơn Booking
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
    $update_meta($booking_id, 'status', 'pending'); // pending, confirmed, cancelled

    // Trừ đi số chỗ trống của Tour
    $new_available_spots = $available_spots - $participants;
    $update_meta($tour_id, 'available_spots', $new_available_spots);

    // Gửi email xác nhận đặt tour cho khách hàng và admin
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
    wp_mail(get_option('admin_email'), '[Admin Alert] Đơn đặt tour mới: ' . $booking_code, $body);

    return new WP_REST_Response([
        'booking_id' => $booking_code,
        'total_amount' => $total_amount,
        'status' => 'pending',
    ], 200);
}
