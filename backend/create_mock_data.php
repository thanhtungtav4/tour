<?php
/**
 * Script to create mock data for Doi Dep Adventure WordPress Backend
 * Run this via wp-cli: wp eval-file create_mock_data.php
 */

// Include WordPress bootstrap if run directly
if (!defined('ABSPATH')) {
    exit;
}

echo "Starting mock data creation...\n";

// Function to helper-create posts and set ACF fields
function create_mock_post($post_title, $post_type, $acf_fields, $post_content = '') {
    // Check if post already exists
    $existing = get_page_by_title($post_title, OBJECT, $post_type);
    if ($existing) {
        echo "Post '{$post_title}' of type '{$post_type}' already exists. Updating fields...\n";
        $post_id = $existing->ID;
        if (!empty($post_content)) {
            wp_update_post([
                'ID' => $post_id,
                'post_content' => $post_content,
            ]);
        }
    } else {
        $post_id = wp_insert_post([
            'post_title' => $post_title,
            'post_type' => $post_type,
            'post_status' => 'publish',
            'post_content' => $post_content,
        ]);
        if (is_wp_error($post_id)) {
            echo "Error creating post '{$post_title}': " . $post_id->get_error_message() . "\n";
            return null;
        }
        echo "Created post '{$post_title}' with ID: {$post_id}\n";
    }

    if (function_exists('update_field')) {
        foreach ($acf_fields as $key => $value) {
            update_field($key, $value, $post_id);
        }
    } else {
        foreach ($acf_fields as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
    }

    return $post_id;
}

// 1. Create Pickup Points
echo "\n--- Creating Pickup Points ---\n";
$pickup_points = [
    [
        'title' => 'Nhà Văn hóa Thanh niên Quận 1',
        'fields' => [
            'address' => '04 Phạm Ngọc Thạch, Bến Nghé, Quận 1, TP.HCM',
            'pickup_time' => '05:30',
            'latitude' => 10.7818,
            'longitude' => 106.6974,
            'is_active' => true,
        ]
    ],
    [
        'title' => 'Cây xăng Comeco Hàng Xanh',
        'fields' => [
            'address' => '178/9 Điện Biên Phủ, Phường 21, Bình Thạnh, TP.HCM',
            'pickup_time' => '05:45',
            'latitude' => 10.8015,
            'longitude' => 106.7118,
            'is_active' => true,
        ]
    ],
    [
        'title' => 'Ngã tư Thủ Đức',
        'fields' => [
            'address' => 'Trước cổng Đại học Sư phạm Kỹ thuật, TP. Thủ Đức, TP.HCM',
            'pickup_time' => '06:00',
            'latitude' => 10.8514,
            'longitude' => 106.7721,
            'is_active' => true,
        ]
    ]
];

$pickup_ids = [];
foreach ($pickup_points as $pp) {
    $id = create_mock_post($pp['title'], 'pickup_point', $pp['fields']);
    if ($id) {
        $pickup_ids[] = $id;
    }
}

// 2. Create Rental Items
echo "\n--- Creating Rental Items ---\n";
$rental_items = [
    [
        'title' => 'Gậy trekking',
        'fields' => [
            'description' => 'Gậy trekking hợp kim nhôm siêu nhẹ, có giảm xóc',
            'price' => 30000,
            'unit' => 'ngày',
            'category' => 'trekking',
            'icon' => '🦯',
            'stock_available' => 50,
            'is_active' => true,
        ]
    ],
    [
        'title' => 'Balo leo núi 35L',
        'fields' => [
            'description' => 'Balo trợ lực, có áo mưa chống nước đi kèm',
            'price' => 50000,
            'unit' => 'ngày',
            'category' => 'trekking',
            'icon' => '🎒',
            'stock_available' => 30,
            'is_active' => true,
        ]
    ],
    [
        'title' => 'Đèn pin đội đầu',
        'fields' => [
            'description' => 'Đèn pin siêu sáng, pin sạc USB, chống mưa nhẹ',
            'price' => 20000,
            'unit' => 'ngày',
            'category' => 'accessories',
            'icon' => '🔦',
            'stock_available' => 40,
            'is_active' => true,
        ]
    ]
];

foreach ($rental_items as $ri) {
    create_mock_post($ri['title'], 'rental_item', $ri['fields']);
}

// 3. Create Tours
echo "\n--- Creating Tours ---\n";

// Dynamic dates: +10 days, +17 days, +24 days
$date1 = date('Y-m-d', strtotime('+10 days'));
$date2 = date('Y-m-d', strtotime('+17 days'));
$date3 = date('Y-m-d', strtotime('+24 days'));

$tours = [
    [
        'title' => 'Trekking Tà Năng - Phan Dũng',
        'content' => 'Hành trình trekking Tà Năng Phan Dũng đưa bạn qua những đồi cỏ xanh ngút ngàn, những cánh rừng thông lộng gió và khám phá vẻ đẹp hoang sơ kỳ vĩ. Đây là một trong những cung đường trekking đẹp nhất Việt Nam, nối liền hai tỉnh Lâm Đồng và Bình Thuận.',
        'fields' => [
            'price' => 2500000,
            'difficulty' => 'medium',
            'duration' => '2 ngày 1 đêm',
            'departure_time' => '05:30 Sáng',
            'highlights' => "Chinh phục 2 tỉnh Lâm Đồng - Bình Thuận\nĐón bình minh trên đồi cỏ xanh ngắt\nThưởng thức BBQ tối giữa đại ngàn",
            'included' => "Xe đưa đón khứ hồi từ TP.HCM\nCác bữa ăn trong chương trình (1 sáng, 2 trưa, 1 tối BBQ)\nLều trại, túi ngủ, thảm cách nhiệt\nHướng dẫn viên và người dẫn đường địa phương\nBảo hiểm du lịch (tối đa 50.000.000đ/vụ)",
            'excluded' => "Nước uống và đồ ăn nhẹ ngoài chương trình\nTiền tip cho HDV (không bắt buộc)\nThuế VAT",
            'notes' => 'Vui lòng chuẩn bị giày trekking có độ bám tốt, quần áo nhanh khô và balo chống nước.',
            'gallery' => [
                'https://images.unsplash.com/photo-1501555088652-021faa106b9b?w=800&q=80',
                'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&q=80',
                'https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=800&q=80',
                'https://images.unsplash.com/photo-1472214222541-d510753a8707?w=800&q=80',
                'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800&q=80'
            ],
            'distance' => '28 km',
            'elevation' => '1.500m',
            'max_altitude' => '1.700m',
            'terrain' => 'Đồi cỏ, dốc đá, rừng thông',
            'age_min' => '18+',
            'fitness' => 'Tốt',
            'gear_list' => [
                ['icon' => '👟', 'name' => 'Giày trekking chuyên dụng', 'important' => 1],
                ['icon' => '🎒', 'name' => 'Balo leo núi 30L', 'important' => 1],
                ['icon' => '🧴', 'name' => 'Kem chống côn trùng', 'important' => 1],
                ['icon' => '🧥', 'name' => 'Áo khoác gió nhẹ', 'important' => 0]
            ],
            'departure_dates' => [
                [
                    'date' => $date1,
                    'available_spots' => 15
                ],
                [
                    'date' => $date2,
                    'available_spots' => 12
                ],
                [
                    'date' => $date3,
                    'available_spots' => 15
                ]
            ],
            'itinerary' => [
                [
                    'time' => '05:30',
                    'activity' => 'Đón khách tại Nhà Văn hóa Thanh niên (Quận 1, TP.HCM)'
                ],
                [
                    'time' => '11:30',
                    'activity' => 'Đến Tà Năng, dùng bữa trưa tại nhà dân địa phương'
                ],
                [
                    'time' => '13:00',
                    'activity' => 'Bắt đầu trekking cung đường đồi cỏ xanh rì'
                ],
                [
                    'time' => '17:30',
                    'activity' => 'Đến bãi cắm trại, dựng lều và đón hoàng hôn'
                ],
                [
                    'time' => '18:30',
                    'activity' => 'Thưởng thức tiệc nướng BBQ và giao lưu lửa trại'
                ],
                [
                    'time' => 'Ngày 2 - 05:00',
                    'activity' => 'Thức dậy đón bình minh và sương mờ trên đồi'
                ],
                [
                    'time' => '07:00',
                    'activity' => 'Dùng bữa sáng và thưởng thức cà phê giữa núi rừng'
                ],
                [
                    'time' => '08:00',
                    'activity' => 'Trekking chặng còn lại qua rừng tre hướng về Phan Dũng'
                ],
                [
                    'time' => '13:00',
                    'activity' => 'Đến Phan Dũng, dùng bữa trưa và xe đón về lại TP.HCM'
                ]
            ]
        ]
    ],
    [
        'title' => 'Langbiang',
        'content' => 'Langbiang được mệnh danh là nóc nhà của thành phố sương mù Đà Lạt. Hành trình leo núi Langbiang trekking xuyên rừng thông cổ thụ, qua rừng lá rộng nhiệt đới và chinh phục đỉnh radar cao 2.167m, mở ra tầm nhìn bao quát toàn bộ Suối Vàng, Suối Bạc và thành phố Đà Lạt từ trên cao.',
        'fields' => [
            'price' => 450000,
            'difficulty' => 'easy',
            'duration' => '1 ngày',
            'departure_time' => '07:30 Sáng',
            'highlights' => "Khám phá nóc nhà Đà Lạt ở độ cao 2.167m\nTrekking xuyên rừng thông cổ thụ\nNgắm toàn cảnh hồ Đankia - Suối Vàng",
            'included' => "Xe trung chuyển từ trung tâm Đà Lạt\nBữa trưa picnic trên đỉnh núi\nVé vào cổng khu du lịch Langbiang\nHướng dẫn viên trekking chuyên nghiệp",
            'excluded' => "Xe jeep lên đỉnh (nếu không muốn trekking)\nChi phí cá nhân ngoài chương trình",
            'notes' => 'Chuẩn bị áo khoác mỏng vì trên đỉnh Langbiang gió khá lạnh.',
            'gallery' => [
                'https://images.unsplash.com/photo-1542401886-65d6c61db217?w=800&q=80',
                'https://images.unsplash.com/photo-1486873249359-2731bd6dafc7?w=800&q=80',
                'https://images.unsplash.com/photo-1473448912268-2022ce9509d8?w=800&q=80',
                'https://images.unsplash.com/photo-1448375240586-882707db888b?w=800&q=80',
                'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=800&q=80'
            ],
            'distance' => '8-10 km',
            'elevation' => '1.200m',
            'max_altitude' => '2.167m',
            'terrain' => 'Rừng thông, dốc thoải',
            'age_min' => '10+',
            'fitness' => 'Trung bình',
            'gear_list' => [
                ['icon' => '👟', 'name' => 'Giày trekking/thể thao', 'important' => 1],
                ['icon' => '🧥', 'name' => 'Áo khoác giữ ấm nhẹ', 'important' => 1],
                ['icon' => '🧴', 'name' => 'Kem chống nắng', 'important' => 1],
                ['icon' => '💧', 'name' => 'Bình nước 1.5L', 'important' => 1]
            ],
            'departure_dates' => [
                [
                    'date' => $date1,
                    'available_spots' => 15
                ],
                [
                    'date' => $date2,
                    'available_spots' => 21
                ],
                [
                    'date' => $date3,
                    'available_spots' => 18
                ]
            ],
            'itinerary' => [
                [
                    'time' => '07:30',
                    'activity' => 'Xe đón khách tại điểm hẹn trung tâm Đà Lạt'
                ],
                [
                    'time' => '08:00',
                    'activity' => 'Đến chân núi Langbiang, khởi động và phổ biến nội quy'
                ],
                [
                    'time' => '08:30',
                    'activity' => 'Bắt đầu trekking xuyên qua những cánh rừng thông'
                ],
                [
                    'time' => '11:30',
                    'activity' => 'Chinh phục đỉnh núi, ngắm toàn cảnh thung lũng Đà Lạt'
                ],
                [
                    'time' => '12:00',
                    'activity' => 'Dùng bữa trưa picnic trên đỉnh Langbiang'
                ],
                [
                    'time' => '13:30',
                    'activity' => 'Chụp ảnh lưu niệm và tự do khám phá'
                ],
                [
                    'time' => '14:30',
                    'activity' => 'Bắt đầu đi xuống chân núi theo đường mòn'
                ],
                [
                    'time' => '16:30',
                    'activity' => 'Về đến chân núi, xe trung chuyển đưa về lại điểm đón'
                ]
            ]
        ]
    ],
    [
        'title' => 'Trekking Núi Chứa Chan',
        'content' => 'Núi Chứa Chan (Đồng Nai) là ngọn núi cao thứ hai ở miền Nam với độ cao 886m. Cung đường leo núi theo đường cột điện với dốc đá quanh co thử thách thể lực bền bỉ, sau đó đi xuống bằng đường chùa để viếng Gia Lào tự cổ kính, mang đến trải nghiệm trekking tâm linh đặc sắc gần TP.HCM.',
        'fields' => [
            'price' => 950000,
            'difficulty' => 'easy',
            'duration' => '1 ngày',
            'departure_time' => '05:00 Sáng',
            'highlights' => "Chinh phục ngọn núi cao thứ 2 Nam Bộ (886m)\nViếng chùa Gia Lào cổ kính linh thiêng\nTrekking đường cột điện đầy thử thách",
            'included' => "Xe ghế ngồi khứ hồi TP.HCM - Đồng Nai\nBữa trưa tại quán ăn địa phương chân núi\nNước uống bổ sung thể lực dọc đường\nHướng dẫn viên hỗ trợ suốt tuyến",
            'excluded' => "Vé cáp treo (nếu khách muốn đi xuống bằng cáp treo)\nChi phí cá nhân",
            'notes' => 'Mang theo ít nhất 2 lít nước vì cung đường leo cột điện khá nóng và mất nước.',
            'gallery' => [
                'https://images.unsplash.com/photo-1465146344425-f00d5f5c8f07?w=800&q=80',
                'https://images.unsplash.com/photo-1502082553048-f009c37129b9?w=800&q=80',
                'https://images.unsplash.com/photo-1447752875215-b2761acb3c5d?w=800&q=80',
                'https://images.unsplash.com/photo-1425913397330-cf8af2ff40a1?w=800&q=80',
                'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?w=800&q=80'
            ],
            'distance' => '7 km',
            'elevation' => '600m',
            'max_altitude' => '886m',
            'terrain' => 'Dốc đá, bậc thang, cỏ tranh',
            'age_min' => '12+',
            'fitness' => 'Trung bình',
            'gear_list' => [
                ['icon' => '👟', 'name' => 'Giày trekking/thể thao độ bám tốt', 'important' => 1],
                ['icon' => '🧢', 'name' => 'Mũ che nắng rộng vành', 'important' => 1],
                ['icon' => '🧴', 'name' => 'Kem chống nắng', 'important' => 0],
                ['icon' => '💧', 'name' => 'Bình nước 2L', 'important' => 1]
            ],
            'departure_dates' => [
                [
                    'date' => $date1,
                    'available_spots' => 20
                ],
                [
                    'date' => $date2,
                    'available_spots' => 15
                ]
            ],
            'itinerary' => [
                [
                    'time' => '05:00',
                    'activity' => 'Đón khách tại điểm hẹn Quận 1, di chuyển đi Đồng Nai'
                ],
                [
                    'time' => '07:30',
                    'activity' => 'Đến chân núi Chứa Chan, ăn nhẹ tự túc và khởi động'
                ],
                [
                    'time' => '08:00',
                    'activity' => 'Bắt đầu trekking leo núi theo lối đường cột điện'
                ],
                [
                    'time' => '11:30',
                    'activity' => 'Chinh phục đỉnh Chứa Chan, chụp ảnh cột mốc 886m'
                ],
                [
                    'time' => '12:00',
                    'activity' => 'Ăn trưa picnic trên đỉnh và nghỉ ngơi dưới bóng mát'
                ],
                [
                    'time' => '13:30',
                    'activity' => 'Đi xuống núi theo đường chùa, viếng chùa Gia Lào'
                ],
                [
                    'time' => '15:30',
                    'activity' => 'Về đến chân núi, rửa mặt nghỉ ngơi'
                ],
                [
                    'time' => '16:30',
                    'activity' => 'Lên xe khởi hành về lại TP.HCM'
                ],
                [
                    'time' => '19:30',
                    'activity' => 'Về đến điểm đón ban đầu, kết thúc hành trình'
                ]
            ]
        ]
    ],
    [
        'title' => 'Rừng Cát Tiên',
        'content' => 'Rừng Cát Tiên là điểm đến lý tưởng cho hoạt động đạp xe và đi bộ khám phá rừng ngập mặn, ngắm nhìn thảm thực vật phong phú và tìm hiểu về các loài động vật hoang dã quý hiếm. Chuyến đi mang lại cảm giác thư thái gần gũi thiên nhiên nguyên sơ chỉ cách TP.HCM vài giờ di chuyển.',
        'fields' => [
            'price' => 380000,
            'difficulty' => 'easy',
            'duration' => '1 ngày',
            'departure_time' => 'Sáng',
            'highlights' => "Rừng ngập mặn\nĐộng vật hoang dã\nThiên nhiên nguyên sơ",
            'included' => "Xe đưa đón khứ hồi từ TP.HCM\nVé cổng Vườn quốc gia Cát Tiên\nHướng dẫn viên suốt tuyến\nNước uống 1.5L",
            'excluded' => "Chi phí cá nhân ngoài chương trình\nThuê xe đạp tự túc\nThuế VAT",
            'notes' => 'Vui lòng chuẩn bị quần áo dài chống muỗi và vắt, giày đi bộ thoải mái.',
            'gallery' => [
                'https://images.unsplash.com/photo-1447752875215-b2761acb3c5d?w=800&q=80',
                'https://images.unsplash.com/photo-1433086966358-54859d0ed716?w=800&q=80',
                'https://images.unsplash.com/photo-1542273917363-3b1817f69a8d?w=800&q=80',
                'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=800&q=80',
                'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&q=80'
            ],
            'distance' => '8-10 km',
            'elevation' => '1.200m',
            'max_altitude' => '1.500m',
            'terrain' => 'Rừng, đồi, suối',
            'age_min' => '16+',
            'fitness' => 'Trung bình',
            'gear_list' => [
                ['icon' => '👟', 'name' => 'Giày trekking', 'important' => 1],
                ['icon' => '🎒', 'name' => 'Ba lô 20-30L', 'important' => 1],
                ['icon' => '🧴', 'name' => 'Kem chống nắng', 'important' => 1],
                ['icon' => '🧢', 'name' => 'Mũ/nón', 'important' => 1],
                ['icon' => '👕', 'name' => 'Áo thun thoáng khí', 'important' => 0],
                ['icon' => '🩳', 'name' => 'Quần dài trekking', 'important' => 0],
                ['icon' => '🔦', 'name' => 'Đèn pin/flashlight', 'important' => 0],
                ['icon' => '💧', 'name' => 'Bình nước 1.5L', 'important' => 1]
            ],
            'departure_dates' => [
                [
                    'date' => $date1,
                    'available_spots' => 12
                ],
                [
                    'date' => $date2,
                    'available_spots' => 17
                ],
                [
                    'date' => $date3,
                    'available_spots' => 8
                ]
            ],
            'itinerary' => [
                [
                    'time' => '05:30 - 06:00',
                    'activity' => 'Đón khách tại điểm hẹn'
                ],
                [
                    'time' => '07:00 - 09:00',
                    'activity' => 'Di chuyển đến điểm xuất phát'
                ],
                [
                    'time' => '09:00 - 09:30',
                    'activity' => 'Khởi động & Brief về an toàn'
                ],
                [
                    'time' => '09:30 - 12:00',
                    'activity' => 'Bắt đầu Trekking - Đoạn 1'
                ],
                [
                    'time' => '12:00 - 13:00',
                    'activity' => 'Nghỉ trưa & Bữa trưa'
                ],
                [
                    'time' => '13:00 - 15:30',
                    'activity' => 'Trekking - Đoạn 2 (Đỉnh)'
                ],
                [
                    'time' => '15:30 - 16:00',
                    'activity' => 'Đạt đỉnh - Ngắm cảnh'
                ],
                [
                    'time' => '16:00 - 18:00',
                    'activity' => 'Xuống núi'
                ],
                [
                    'time' => '18:00 - 20:00',
                    'activity' => 'Về đến TP.HCM'
                ]
            ]
        ]
    ]
];

foreach ($tours as $t) {
    create_mock_post($t['title'], 'tour', $t['fields'], $t['content']);
}

echo "\nFinished mock data creation successfully!\n";
