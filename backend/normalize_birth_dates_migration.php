<?php
/**
 * Migration script to normalize all birth dates to YYYY-MM-DD
 * Run via WP-CLI: wp eval-file normalize_birth_dates_migration.php --allow-root
 */

if (!defined('ABSPATH')) {
    echo "This script must be run via WP-CLI: wp eval-file <filename>\n";
    exit(1);
}

echo "Starting birth date normalization migration...\n";

// 1. Normalize Customer post birth dates
$customers = get_posts([
    'post_type' => 'customer',
    'posts_per_page' => -1,
    'post_status' => 'any',
]);

echo "Found " . count($customers) . " customers.\n";
$customer_updated_count = 0;

foreach ($customers as $c) {
    $birth_date_raw = get_post_meta($c->ID, 'birth_date', true);
    if (!empty($birth_date_raw)) {
        $normalized = newtrip_normalize_date($birth_date_raw);
        if ($normalized !== $birth_date_raw && !empty($normalized)) {
            update_post_meta($c->ID, 'birth_date', $normalized);
            // Also update ACF field to standard date format YYYYMMDD
            $formatted_acf = date('Ymd', strtotime($normalized));
            if (function_exists('update_field')) {
                update_field('field_customer_birth_date', $formatted_acf, $c->ID);
            }
            echo "Updated Customer ID {$c->ID}: '{$birth_date_raw}' -> '{$normalized}'\n";
            $customer_updated_count++;
        }
    }
}

echo "Completed Customers. Updated: {$customer_updated_count}\n\n";

// 2. Normalize Booking post passengers birth dates
$bookings = get_posts([
    'post_type' => 'booking',
    'posts_per_page' => -1,
    'post_status' => 'any',
]);

echo "Found " . count($bookings) . " bookings.\n";
$booking_updated_count = 0;

foreach ($bookings as $b) {
    $passengers = null;
    if (function_exists('get_field')) {
        // ACF format
        $passengers = get_field('passengers', $b->ID);
    } else {
        $passengers = get_post_meta($b->ID, 'passengers', true);
    }
    
    if (is_array($passengers)) {
        $updated = false;
        foreach ($passengers as $idx => $p) {
            $p_birth = $p['birth_date'] ?? '';
            if (!empty($p_birth)) {
                $normalized = newtrip_normalize_date($p_birth);
                if ($normalized !== $p_birth && !empty($normalized)) {
                    $passengers[$idx]['birth_date'] = $normalized;
                    $updated = true;
                    echo "Booking ID {$b->ID}, Passenger {$idx} ({$p['full_name']}): '{$p_birth}' -> '{$normalized}'\n";
                }
            }
        }
        
        if ($updated) {
            if (function_exists('update_field')) {
                update_field('field_booking_passengers', $passengers, $b->ID);
            } else {
                update_post_meta($b->ID, 'passengers', $passengers);
            }
            $booking_updated_count++;
        }
    }
}

echo "Completed Bookings. Updated: {$booking_updated_count}\n\n";
echo "Migration finished successfully.\n";
