<?php
/**
 * Installer / Sample Data
 *
 * Creates sample data for testing and demonstration.
 *
 * @since 0.1.0
 */

namespace TourBooking;

class Installer
{
    /**
     * Run installer
     */
    public static function run(): void
    {
        self::create_sample_tours();
        self::create_sample_departures();
        self::create_sample_pickup_points();
        self::create_sample_vehicles();
        self::create_sample_layouts();

        update_option('nt_tour_sample_data_installed', true);
    }

    /**
     * Check if sample data was installed
     */
    public static function is_installed(): bool
    {
        return (bool) get_option('nt_tour_sample_data_installed', false);
    }

    /**
     * Create sample tours
     */
    private static function create_sample_tours(): void
    {
        $tours = [
            [
                'title' => 'Tour Đà Lạt 3N2Đ - Khởi hành từ Sài Gòn',
                'slug' => 'tour-da-lat-3n2d-sai-gon',
                'excerpt' => 'Khám phá Đà Lạt mộng mơ với lịch trình 3 ngày 2 đêm',
                'content' => 'Tour Đà Lạt 3 ngày 2 đêm khởi hành từ Sài Gòn. Bao gồm thăm quan các điểm du lịch nổi tiếng như Thung lũng Tình Yêu, Hồ Xuân Hương, Nhà thờ Domaine de Marie, Chợ đêm Đà Lạt.',
                'price' => 2500000,
                'destination' => 'Đà Lạt',
                'duration_days' => 3,
                'duration_nights' => 2,
                'departure_location' => 'TP. Hồ Chí Minh',
            ],
            [
                'title' => 'Tour Phú Quốc 4N3Đ - Đảo Ngọc',
                'slug' => 'tour-phu-quoc-4n3d',
                'excerpt' => 'Trải nghiệm thiên đường biển đảo Phú Quốc',
                'content' => 'Tour Phú Quốc 4 ngày 3 đêm - khám phá đảo ngọc với Vinpearl Safari, Grand World, Safari và các bãi biển đẹp.',
                'price' => 4500000,
                'destination' => 'Phú Quốc',
                'duration_days' => 4,
                'duration_nights' => 3,
                'departure_location' => 'TP. Hồ Chí Minh',
            ],
            [
                'title' => 'Tour Nha Trang 2N1Đ - Biển Xanh Cát Trắng',
                'slug' => 'tour-nha-trang-2n1d',
                'excerpt' => 'Cuộc dạo chơi ngắn ngày tại thành phố biển Nha Trang',
                'content' => 'Tour Nha Trang 2 ngày 1 đêm - tham quan Tháp Trầm Hương, Vinpearl Land, bãi biển Trần Phú.',
                'price' => 1500000,
                'destination' => 'Nha Trang',
                'duration_days' => 2,
                'duration_nights' => 1,
                'departure_location' => 'TP. Hồ Chí Minh',
            ],
            [
                'title' => 'Tour Miền Tây 1N - Cần Thơ, Chợ Nổi',
                'slug' => 'tour-mien-tay-1n-cantho',
                'excerpt' => 'Khám phá miền sông nước miền Tây Nam Bộ',
                'content' => 'Tour miền Tây 1 ngày - thăm chợ nổi Cái Răng, Bến Ninh Kiều, vườn cây ăn trái.',
                'price' => 850000,
                'destination' => 'Cần Thơ',
                'duration_days' => 1,
                'duration_nights' => 0,
                'departure_location' => 'TP. Hồ Chí Minh',
            ],
            [
                'title' => 'Tour Hội An 3N2Đ - Phố cổ di sản',
                'slug' => 'tour-hoi-an-3n2d',
                'excerpt' => 'Khám phá phố cổ Hội An về đêm lung linh',
                'content' => 'Tour Hội An 3 ngày 2 đêm - thăm phố cổ, Cầu Nhật Tảo, Chùa Cầu, Khu phố Pháp, làng rau Trà Quế.',
                'price' => 3200000,
                'destination' => 'Hội An',
                'duration_days' => 3,
                'duration_nights' => 2,
                'departure_location' => 'TP. Hồ Chí Minh',
            ],
        ];

        foreach ($tours as $tour) {
            // Check if tour already exists
            $existing = get_posts([
                'post_type' => 'nt_tour',
                'name' => $tour['slug'],
                'posts_per_page' => 1,
            ]);

            if (!empty($existing)) {
                continue;
            }

            $post_id = wp_insert_post([
                'post_type' => 'nt_tour',
                'post_title' => $tour['title'],
                'post_name' => $tour['slug'],
                'post_excerpt' => $tour['excerpt'],
                'post_content' => $tour['content'],
                'post_status' => 'publish',
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, 'tour_code', 'TOUR' . str_pad($post_id, 4, '0', STR_PAD_LEFT));
                update_post_meta($post_id, 'destination', $tour['destination']);
                update_post_meta($post_id, 'duration_days', $tour['duration_days']);
                update_post_meta($post_id, 'duration_nights', $tour['duration_nights']);
                update_post_meta($post_id, 'departure_location', $tour['departure_location']);
                update_post_meta($post_id, 'base_price', $tour['price']);
            }
        }
    }

    /**
     * Create sample departures
     */
    private static function create_sample_departures(): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'nt_tour_departures';

        // Get tours
        $tours = get_posts([
            'post_type' => 'nt_tour',
            'posts_per_page' => 5,
            'post_status' => 'publish',
        ]);

        if (empty($tours)) {
            return;
        }

        // Create departures for next 14 days
        $base_prices = [2500000, 4500000, 1500000, 850000, 3200000];
        $child_discounts = [0.7, 0.7, 0.7, 0.5, 0.7];
        $capacities = [45, 29, 45, 50, 35];

        foreach ($tours as $index => $tour) {
            $base_price = $base_prices[$index] ?? 2500000;
            $child_price = (int) ($base_price * ($child_discounts[$index] ?? 0.7));
            $capacity = $capacities[$index] ?? 45;

            // Create departures for next 14 days
            for ($day = 1; $day <= 14; $day++) {
                // Skip some days randomly for variety
                if (rand(0, 3) === 0) {
                    continue;
                }

                $departure_date = date('Y-m-d', strtotime("+{$day} days"));
                $departure_time = sprintf('%02d:00:00', 6 + rand(0, 2));

                // Check if departure exists
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table} WHERE tour_id = %d AND start_date = %s",
                    $tour->ID,
                    $departure_date
                ));

                if ($exists > 0) {
                    continue;
                }

                $wpdb->insert($table, [
                    'tour_id' => $tour->ID,
                    'departure_code' => 'D' . date('ymd', strtotime($departure_date)) . '-' . ($index + 1),
                    'start_date' => $departure_date,
                    'end_date' => date('Y-m-d', strtotime($departure_date . ' + ' . (get_post_meta($tour->ID, 'duration_days', true) - 1) . ' days')),
                    'departure_time' => $departure_time,
                    'adult_price' => $base_price,
                    'child_price' => $child_price,
                    'infant_price' => (int) ($base_price * 0.1),
                    'capacity' => $capacity,
                    'status' => 'open',
                    'created_at' => current_time('mysql'),
                ]);
            }
        }
    }

    /**
     * Create sample pickup points
     */
    private static function create_sample_pickup_points(): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'nt_pickup_points';

        $pickup_points = [
            ['name' => 'Bến xe Miền Đông', 'address' => '292 Điện Biên Phủ, P.3, Q.Bình Thạnh, TP.HCM', 'map_url' => 'https://maps.google.com/?q=Ben+Xe+Mien+Dong', 'sort_order' => 1],
            ['name' => 'Bến xe Quận 8', 'address' => '169 Dương Bạch Tuý, Q.8, TP.HCM', 'map_url' => 'https://maps.google.com/?q=Ben+Xe+Quan+8', 'sort_order' => 2],
            ['name' => 'Vạn Hạnh Mall', 'address' => '11 Quang Trung, Q.Gò Vấp, TP.HCM', 'map_url' => 'https://maps.google.com/?q=Vạn+Hạnh+Mall', 'sort_order' => 3],
            ['name' => 'Ngã 4 Hóc Môn', 'address' => 'QL22, Hóc Môn, TP.HCM', 'map_url' => 'https://maps.google.com/?q=Ngã+4+Hóc+Môn', 'sort_order' => 4],
            ['name' => 'Công viên Phần mềm Quang Trung', 'address' => 'Đường số 1, Q.12, TP.HCM', 'map_url' => 'https://maps.google.com/?q=CVPM+Quang+Trung', 'sort_order' => 5],
        ];

        foreach ($pickup_points as $point) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE name = %s",
                $point['name']
            ));

            if ($exists > 0) {
                continue;
            }

            $wpdb->insert($table, [
                'name' => $point['name'],
                'address' => $point['address'],
                'map_url' => $point['map_url'],
                'status' => 'active',
                'sort_order' => $point['sort_order'],
                'created_at' => current_time('mysql'),
            ]);
        }
    }

    /**
     * Create sample vehicle layouts
     */
    private static function create_sample_layouts(): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'nt_vehicle_layouts';

        $layouts = [
            [
                'name' => 'Layout Xe 29 Chỗ (2+1)',
                'vehicle_type' => 'bus_29',
                'total_seats' => 29,
                'layout_json' => json_encode(self::generate_bus_29_layout()),
            ],
            [
                'name' => 'Layout Xe 45 Chỗ (2+2)',
                'vehicle_type' => 'bus_45',
                'total_seats' => 45,
                'layout_json' => json_encode(self::generate_bus_45_layout()),
            ],
            [
                'name' => 'Layout Limousine 34 Chỗ',
                'vehicle_type' => 'limousine',
                'total_seats' => 34,
                'layout_json' => json_encode(self::generate_limousine_layout()),
            ],
        ];

        foreach ($layouts as $layout) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE name = %s",
                $layout['name']
            ));

            if ($exists > 0) {
                continue;
            }

            $wpdb->insert($table, [
                'name' => $layout['name'],
                'vehicle_type' => $layout['vehicle_type'],
                'total_seats' => $layout['total_seats'],
                'layout_json' => $layout['layout_json'],
                'created_at' => current_time('mysql'),
            ]);
        }
    }

    /**
     * Create sample vehicles
     */
    private static function create_sample_vehicles(): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'nt_vehicles';
        $layouts_table = $wpdb->prefix . 'nt_vehicle_layouts';

        $vehicles = [
            ['name' => 'Xe 01 - Toyota 29 chỗ', 'plate_number' => '51A-123.45', 'vehicle_type' => 'bus_29'],
            ['name' => 'Xe 02 - Hyundai 29 chỗ', 'plate_number' => '51B-234.56', 'vehicle_type' => 'bus_29'],
            ['name' => 'Xe 03 - Isuzu 45 chỗ', 'plate_number' => '51C-345.67', 'vehicle_type' => 'bus_45'],
            ['name' => 'Xe 04 - Thaco 45 chỗ', 'plate_number' => '51D-456.78', 'vehicle_type' => 'bus_45'],
            ['name' => 'Xe 05 - Limousine City', 'plate_number' => '51E-567.89', 'vehicle_type' => 'limousine'],
        ];

        // Get layout IDs
        $bus_29_layout = $wpdb->get_var("SELECT id FROM {$layouts_table} WHERE vehicle_type = 'bus_29' LIMIT 1");
        $bus_45_layout = $wpdb->get_var("SELECT id FROM {$layouts_table} WHERE vehicle_type = 'bus_45' LIMIT 1");
        $limousine_layout = $wpdb->get_var("SELECT id FROM {$layouts_table} WHERE vehicle_type = 'limousine' LIMIT 1");

        foreach ($vehicles as $vehicle) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE name = %s",
                $vehicle['name']
            ));

            if ($exists > 0) {
                continue;
            }

            $layout_id = null;
            $total_seats = 0;

            switch ($vehicle['vehicle_type']) {
                case 'bus_29':
                    $layout_id = $bus_29_layout;
                    $total_seats = 29;
                    break;
                case 'bus_45':
                    $layout_id = $bus_45_layout;
                    $total_seats = 45;
                    break;
                case 'limousine':
                    $layout_id = $limousine_layout;
                    $total_seats = 34;
                    break;
            }

            $wpdb->insert($table, [
                'name' => $vehicle['name'],
                'plate_number' => $vehicle['plate_number'],
                'vehicle_type' => $vehicle['vehicle_type'],
                'total_seats' => $total_seats,
                'layout_id' => $layout_id,
                'status' => 'active',
                'created_at' => current_time('mysql'),
            ]);
        }
    }

    /**
     * Generate 29-seat bus layout
     */
    private static function generate_bus_29_layout(): array
    {
        $layout = [];
        $seat_num = 1;

        // 5 rows, 6 columns
        for ($r = 0; $r < 5; $r++) {
            $layout[$r] = [];
            for ($c = 0; $c < 6; $c++) {
                // Aisle in middle (column 3)
                if ($c === 3) {
                    $layout[$r][$c] = ['type' => 'aisle', 'label' => '-'];
                } else {
                    $row_letter = chr(65 + $r);
                    $layout[$r][$c] = ['type' => 'seat', 'label' => $row_letter . $seat_num];
                    $seat_num++;
                }
            }
        }

        return $layout;
    }

    /**
     * Generate 45-seat bus layout
     */
    private static function generate_bus_45_layout(): array
    {
        $layout = [];
        $seat_num = 1;

        // 8 rows, 6 columns
        for ($r = 0; $r < 8; $r++) {
            $layout[$r] = [];
            for ($c = 0; $c < 6; $c++) {
                // Aisle in middle
                if ($c === 3) {
                    $layout[$r][$c] = ['type' => 'aisle', 'label' => '-'];
                } else {
                    $row_letter = chr(65 + $r);
                    $layout[$r][$c] = ['type' => 'seat', 'label' => $row_letter . $seat_num];
                    $seat_num++;
                }
            }
        }

        return $layout;
    }

    /**
     * Generate limousine layout
     */
    private static function generate_limousine_layout(): array
    {
        $layout = [];
        $seat_num = 1;

        // 6 rows, 5 columns (business class style)
        for ($r = 0; $r < 6; $r++) {
            $layout[$r] = [];
            for ($c = 0; $c < 5; $c++) {
                // Aisle on right side
                if ($c === 2) {
                    $layout[$r][$c] = ['type' => 'aisle', 'label' => '-'];
                } else {
                    $row_letter = chr(65 + $r);
                    $layout[$r][$c] = ['type' => 'seat', 'label' => $row_letter . $seat_num];
                    $seat_num++;
                }
            }
        }

        return $layout;
    }

    /**
     * Uninstall sample data
     */
    public static function uninstall(): void
    {
        // Delete sample tours
        $tours = get_posts([
            'post_type' => 'nt_tour',
            'posts_per_page' => -1,
        ]);

        foreach ($tours as $tour) {
            wp_delete_post($tour->ID, true);
        }

        // Clear options
        delete_option('nt_tour_sample_data_installed');
    }
}
