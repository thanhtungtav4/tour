<?php
/**
 * Script to create demo blog posts in WordPress
 * Run via WP-CLI: wp eval-file create_demo_posts.php --allow-root
 */

if (!defined('ABSPATH')) {
    exit;
}

echo "Starting creation of demo blog posts...\n";

// Helper to check and create a category if not exists
function get_or_create_term($name, $taxonomy) {
    $term = get_term_by('name', $name, $taxonomy);
    if ($term) {
        return $term->term_id;
    }
    $created = wp_insert_term($name, $taxonomy);
    if (is_wp_error($created)) {
        return 0;
    }
    return $created['term_id'];
}

// Ensure categories exist
$cat_experience = get_or_create_term('Kinh nghiệm', 'category');
$cat_guide = get_or_create_term('Cẩm nang', 'category');
$cat_gear = get_or_create_term('Chuẩn bị đồ', 'category');

// Ensure tags exist
$tag_trekking = get_or_create_term('Trekking', 'post_tag');
$tag_camping = get_or_create_term('Cắm trại', 'post_tag');
$tag_safety = get_or_create_term('An toàn', 'post_tag');
$tag_survival = get_or_create_term('Sinh tồn', 'post_tag');

// Define 5 rich demo posts
$demo_posts = [
    [
        'title' => 'Cẩm nang trekking cho người mới bắt đầu: Cần chuẩn bị những gì?',
        'slug' => 'cam-nang-trekking-cho-nguoi-moi-bat-dau',
        'excerpt' => 'Bạn đang lên kế hoạch cho chuyến trekking đầu tiên trong đời? Hãy tham khảo danh sách những thứ không thể thiếu để có một hành trình an toàn và trọn vẹn.',
        'content' => '
            <p>Trekking là một hoạt động dã ngoại đầy thử thách nhưng cũng cực kỳ thú vị, đưa bạn đến gần hơn với thiên nhiên hoang sơ. Tuy nhiên, đối với người mới bắt đầu, việc chuẩn bị không kỹ càng có thể biến chuyến đi thành một cơn ác mộng. Dưới đây là những kinh nghiệm xương máu giúp bạn chuẩn bị tốt nhất.</p>
            
            <h2>1. Thể lực và tinh thần là yếu tố quyết định</h2>
            <p>Trước chuyến đi khoảng 2-3 tuần, bạn nên bắt đầu các bài tập rèn luyện sức bền như đi bộ nhanh, chạy bộ, leo cầu thang hoặc squat. Hãy chuẩn bị một tinh thần kiên cường, sẵn sàng đối mặt với việc không có sóng điện thoại, không có nhà vệ sinh tiện nghi và thời tiết thay đổi thất thường.</p>
            
            <h2>2. Trang thiết bị cá nhân thiết yếu</h2>
            <ul>
                <li><strong>Giày trekking chuyên dụng:</strong> Chọn giày có độ bám tốt, chống nước nhẹ và lớn hơn chân nửa size để tránh đau ngón chân khi xuống dốc.</li>
                <li><strong>Balo leo núi:</strong> Chọn loại có hệ thống trợ lực tốt để dàn đều trọng lượng lên hông thay vì vai.</li>
                <li><strong>Quần áo nhanh khô:</strong> Tránh mặc quần jean. Hãy chọn quần áo thể thao chất liệu polyester nhanh khô và nhẹ.</li>
            </ul>

            <h2>3. Nguyên tắc sinh tồn cốt lõi</h2>
            <p>Luôn đi theo đoàn, không tự ý tách nhóm. Hãy chuẩn bị sẵn một bộ sơ cứu y tế cá nhân, thuốc chống côn trùng và còi sinh tồn để sử dụng trong trường hợp khẩn cấp.</p>
        ',
        'category' => $cat_experience,
        'tags' => [$tag_trekking, $tag_safety],
        'author_name' => 'Nguyễn Minh Triết',
        'author_bio' => 'Hướng dẫn viên chuyên nghiệp với hơn 7 năm kinh nghiệm dẫn các cung trekking Tây Nguyên và Tây Bắc.',
        'read_time' => '5 phút',
        'image_url' => 'https://images.unsplash.com/photo-1501555088652-021faa106b9b?auto=format&fit=crop&w=1200&q=80',
    ],
    [
        'title' => 'Kỹ năng dựng lều và sinh tồn qua đêm trong rừng hoang sơ',
        'slug' => 'ky-nang-dung-leu-sinh-ton-qua-dem-trong-rung',
        'excerpt' => 'Trải nghiệm qua đêm giữa đại ngàn luôn là phần thú vị nhất của mỗi chuyến đi. Nắm vững các nguyên tắc chọn vị trí dựng lều và giữ ấm cơ thể.',
        'content' => '
            <p>Khi hoàng hôn buông xuống, khu rừng sẽ thay đổi hoàn toàn diện mạo. Để có một đêm ngủ an toàn và ấm áp giữa thiên nhiên hoang dã, việc dựng lều đúng cách là cực kỳ quan trọng.</p>
            
            <h2>1. Chọn địa điểm hạ trại an toàn</h2>
            <p>Không bao giờ dựng lều ngay sát mép sông suối vì lũ quét có thể ập về bất ngờ trong đêm. Hãy chọn bãi đất bằng phẳng, cao ráo, tránh gốc cây khô có nguy cơ gãy đổ và hướng gió thổi trực diện.</p>
            
            <h2>2. Kỹ thuật dựng lều chuẩn xác</h2>
            <p>Hãy đảm bảo bạn đã căng dây neo thật chặt và đóng cọc lều chắc chắn phòng trường hợp gió lớn giông lốc vào ban đêm. Luôn đóng kín cửa lưới của lều để ngăn ngừa muỗi, vắt, côn trùng và các loài bò sát bò vào trong.</p>
            
            <h2>3. Giữ ấm cơ thể và bảo quản thực phẩm</h2>
            <p>Sử dụng tấm cách nhiệt lót dưới túi ngủ để ngăn hơi lạnh từ lòng đất bốc lên. Thực phẩm cần được bọc kín treo lên cao cách xa lều trại để tránh thu hút thú rừng.</p>
        ',
        'category' => $cat_camping,
        'tags' => [$tag_camping, $tag_survival],
        'author_name' => 'Trần Thế Anh',
        'author_bio' => 'Chuyên gia sinh tồn dã ngoại, cựu thành viên đội cứu hộ rừng quốc gia cát tiên.',
        'read_time' => '6 phút',
        'image_url' => 'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?auto=format&fit=crop&w=1200&q=80',
    ],
    [
        'title' => '10 Vật dụng sinh tồn không thể thiếu trong balo của mọi Trekker',
        'slug' => '10-vat-dung-sinh-ton-khong-the-thieu',
        'excerpt' => 'Dù là cung đường ngắn ngày hay dài ngày, 10 món đồ này sẽ là vị cứu tinh của bạn trong những tình huống nguy cấp ngoài ý muốn.',
        'content' => '
            <p>Một chuyến đi dã ngoại suôn sẻ đôi khi phụ thuộc vào những món đồ nhỏ bé nằm sâu trong balo của bạn. Đây là danh sách 10 vật dụng sinh tồn được các chuyên gia khuyên dùng hàng đầu.</p>
            
            <h2>Danh sách 10 món đồ sinh tồn cốt lõi:</h2>
            <ol>
                <li><strong>Bản đồ và La bàn/GPS:</strong> Công nghệ có thể hết pin, la bàn cơ và bản đồ giấy không bao giờ phản bội bạn.</li>
                <li><strong>Đèn pin đội đầu:</strong> Giúp bạn rảnh hai tay để bám đá, cầm gậy leo núi khi di chuyển lúc trời tối.</li>
                <li><strong>Hộp quẹt/Đánh lửa sinh tồn:</strong> Lửa dùng để giữ ấm, nấu chín nước uống và xua đuổi thú rừng.</li>
                <li><strong>Dao đa năng (Multi-tool):</strong> Cắt dây rừng, mở đồ hộp, sửa chữa trang thiết bị hư hỏng.</li>
                <li><strong>Còi cứu hộ:</strong> Âm thanh tiếng còi vang xa hơn và đỡ tốn sức hơn tiếng la hét của bạn rất nhiều.</li>
                <li><strong>Tấm bạt cứu sinh (Emergency Blanket):</strong> Giúp giữ nhiệt phản xạ lại 90% nhiệt độ cơ thể khi lạnh đột ngột.</li>
            </ol>
        ',
        'category' => $cat_gear,
        'tags' => [$tag_trekking, $tag_survival],
        'author_name' => 'Phạm Hoàng Nam',
        'author_bio' => 'Người đam mê khám phá mạo hiểm, đã chinh phục thành công 4 đỉnh núi cao nhất Việt Nam.',
        'read_time' => '4 phút',
        'image_url' => 'https://images.unsplash.com/photo-1478131148058-76f55979f204?auto=format&fit=crop&w=1200&q=80',
    ],
    [
        'title' => 'Làm gì khi bị lạc trong rừng sâu? Quy tắc cứu mạng STAR',
        'slug' => 'lam-gi-khi-bi-lac-trong-rung-sau',
        'excerpt' => 'Mất phương hướng giữa rừng là tình huống đáng sợ nhất. Nắm vững quy tắc STAR để giữ bình tĩnh và đưa ra quyết định sáng suốt nhất.',
        'content' => '
            <p>Bị lạc trong rừng sâu là một tình huống cực kỳ nguy hiểm có thể xảy ra với bất kỳ ai, ngay cả những người đi rừng lâu năm. Điều quan trọng nhất khi nhận ra mình bị lạc là giữ bình tĩnh và áp dụng quy tắc STAR.</p>
            
            <h2>Quy tắc STAR là gì?</h2>
            <ul>
                <li><strong>S - Stop (Dừng lại):</strong> Ngay khi phát hiện mình lạc đường, hãy dừng lại ngay lập tức. Càng đi tiếp bạn càng dễ bị lạc sâu hơn và tốn sức vô ích.</li>
                <li><strong>T - Think (Suy nghĩ):</strong> Bình tĩnh nhớ lại lối đi cuối cùng bạn nhớ rõ, hướng đi mặt trời, hoặc các mốc đánh dấu tự nhiên xung quanh.</li>
                <li><strong>A - Assess (Đánh giá):</strong> Kiểm tra lại lượng nước uống, thức ăn, pin điện thoại, dụng cụ sơ cứu còn lại trong balo.</li>
                <li><strong>R - React (Hành động):</strong> Đưa ra quyết định sáng suốt. Nếu trời sắp tối, hãy tập trung tìm chỗ hạ trại trú ẩn an toàn thay vì cố gắng tìm đường ra.</li>
            </ul>
        ',
        'category' => $cat_experience,
        'tags' => [$tag_safety, $tag_survival],
        'author_name' => 'Nguyễn Minh Triết',
        'author_bio' => 'Hướng dẫn viên chuyên nghiệp với hơn 7 năm kinh nghiệm dẫn các cung trekking Tây Nguyên và Tây Bắc.',
        'read_time' => '5 phút',
        'image_url' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1200&q=80',
    ],
    [
        'title' => 'Top 5 cung đường trekking đẹp và thử thách nhất miền Nam',
        'slug' => 'top-5-cung-duong-trekking-dep-mien-nam',
        'excerpt' => 'Từ cung đường tà năng phan dũng huyền thoại đến núi chứa chan hoang sơ. Khám phá ngay địa điểm lý tưởng cho chuyến đi cuối tuần.',
        'content' => '
            <p>Miền Nam Việt Nam không chỉ có những cánh đồng sông nước mà còn sở hữu những cung đường trekking tuyệt đẹp, đa dạng địa hình từ đồi cỏ trọc đến rừng rậm nhiệt đới.</p>
            
            <h2>1. Tà Năng - Phan Dũng (Lâm Đồng - Bình Thuận)</h2>
            <p>Được mệnh danh là cung đường trekking đẹp nhất Việt Nam với chiều dài khoảng 30-40km đi qua 3 tỉnh. Nổi bật với những đồi cỏ xanh mướt trải dài tít tắp vào mùa mưa và đồi cỏ cháy vàng thơ mộng vào mùa khô.</p>
            
            <h2>2. Núi Bà Đen (Tây Ninh)</h2>
            <p>"Nóc nhà Nam Bộ" cao 986m luôn là đích đến yêu thích của các trekker thử thách bản thân vào dịp cuối tuần thông qua đường cột điện hoặc đường đá trắng dốc đứng.</p>
            
            <h2>3. Núi Chứa Chan (Đồng Nai)</h2>
            <p>Cung đường trekking nhẹ nhàng hơn với độ cao 837m, thích hợp cho việc trekking đi về trong ngày hoặc cắm trại qua đêm đón bình minh trên đỉnh núi.</p>
        ',
        'category' => $cat_experience,
        'tags' => [$tag_trekking, $tag_camping],
        'author_name' => 'Trần Thế Anh',
        'author_bio' => 'Chuyên gia sinh tồn dã ngoại, cựu thành viên đội cứu hộ rừng quốc gia cát tiên.',
        'read_time' => '7 phút',
        'image_url' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
    ]
];

foreach ($demo_posts as $p_data) {
    // Check if post already exists by slug
    $existing = new WP_Query([
        'post_type' => 'post',
        'name' => $p_data['slug'],
        'posts_per_page' => 1,
    ]);
    
    if ($existing->have_posts()) {
        echo "Post '{$p_data['title']}' already exists. Skipping...\n";
        continue;
    }
    
    // Insert post
    $post_id = wp_insert_post([
        'post_type' => 'post',
        'post_title' => $p_data['title'],
        'post_name' => $p_data['slug'],
        'post_content' => trim($p_data['content']),
        'post_excerpt' => $p_data['excerpt'],
        'post_status' => 'publish',
    ]);
    
    if (is_wp_error($post_id)) {
        echo "Error creating post '{$p_data['title']}': " . $post_id->get_error_message() . "\n";
        continue;
    }
    
    // Assign category
    wp_set_post_categories($post_id, [$p_data['category']]);
    
    // Assign tags
    wp_set_post_tags($post_id, $p_data['tags']);
    
    // Update ACF fields
    if (function_exists('update_field')) {
        update_field('field_post_author_name', $p_data['author_name'], $post_id);
        update_field('field_post_author_bio', $p_data['author_bio'], $post_id);
        update_field('field_post_read_time', $p_data['read_time'], $post_id);
        update_field('field_post_featured_image_url', $p_data['image_url'], $post_id);
    } else {
        update_post_meta($post_id, 'author_name', $p_data['author_name']);
        update_post_meta($post_id, 'author_bio', $p_data['author_bio']);
        update_post_meta($post_id, 'read_time', $p_data['read_time']);
        update_post_meta($post_id, 'featured_image_url', $p_data['image_url']);
    }
    
    echo "Created post '{$p_data['title']}' with ID {$post_id}\n";
}

echo "All demo blog posts created successfully!\n";
