<?php
/**
 * Migration Script: Convert booking services and rental items slugs to numeric Post IDs
 * Run via WP-CLI: wp eval-file migrate_slugs_to_ids.php --allow-root
 */

if (!defined('ABSPATH')) {
    exit;
}

echo "Starting booking slugs-to-IDs migration...\n";

// Get all bookings
$query = new WP_Query([
    'post_type' => 'booking',
    'posts_per_page' => -1,
    'post_status' => 'any',
]);

if (!$query->have_posts()) {
    echo "No bookings found.\n";
    exit;
}

$bookings_count = count($query->posts);
echo "Found {$bookings_count} bookings.\n";

$updated_count = 0;

foreach ($query->posts as $booking) {
    $booking_id = $booking->ID;
    $booking_title = $booking->post_title;
    $has_updates = false;
    
    // 1. Migrate Services
    $services_count = intval(get_post_meta($booking_id, 'services', true));
    for ($i = 0; $i < $services_count; $i++) {
        $meta_key = "services_{$i}_service_id";
        $current_value = get_post_meta($booking_id, $meta_key, true);
        
        if (!empty($current_value) && !is_numeric($current_value)) {
            // It's a slug, resolve it to Post ID
            $svc_query = new WP_Query([
                'post_type' => 'tour_service',
                'name' => sanitize_title($current_value),
                'posts_per_page' => 1,
                'fields' => 'ids',
                'post_status' => 'any',
            ]);
            
            if (!empty($svc_query->posts)) {
                $post_id = $svc_query->posts[0];
                update_post_meta($booking_id, $meta_key, $post_id);
                echo "Booking #{$booking_id}: Service slug '{$current_value}' -> Post ID {$post_id}\n";
                $has_updates = true;
            } else {
                echo "Booking #{$booking_id}: WARNING - Could not resolve service slug '{$current_value}'\n";
            }
        }
    }
    
    // 2. Migrate Rental Items
    $rental_items_count = intval(get_post_meta($booking_id, 'rental_items', true));
    for ($i = 0; $i < $rental_items_count; $i++) {
        $meta_key = "rental_items_{$i}_item_id";
        $current_value = get_post_meta($booking_id, $meta_key, true);
        
        if (!empty($current_value) && !is_numeric($current_value)) {
            // It's a slug, resolve it to Post ID
            $item_query = new WP_Query([
                'post_type' => 'rental_item',
                'name' => sanitize_title($current_value),
                'posts_per_page' => 1,
                'fields' => 'ids',
                'post_status' => 'any',
            ]);
            
            if (!empty($item_query->posts)) {
                $post_id = $item_query->posts[0];
                update_post_meta($booking_id, $meta_key, $post_id);
                echo "Booking #{$booking_id}: Rental Item slug '{$current_value}' -> Post ID {$post_id}\n";
                $has_updates = true;
            } else {
                echo "Booking #{$booking_id}: WARNING - Could not resolve rental item slug '{$current_value}'\n";
            }
        }
    }
    
    if ($has_updates) {
        $updated_count++;
    }
}

echo "Migration finished. Updated {$updated_count} bookings.\n";
