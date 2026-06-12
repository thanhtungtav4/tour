<?php
/**
 * Migration script to transfer Customer CPT data to wp_newtrip_customers custom table
 * Run via WP-CLI: wp eval-file migrate_cpt_to_custom_table.php --allow-root
 */

if (!defined('ABSPATH')) {
    echo "This script must be run via WP-CLI: wp eval-file <filename>\n";
    exit(1);
}

echo "Starting customer migration from CPT to Custom Table...\n";

global $wpdb;
$table_name = $wpdb->prefix . 'newtrip_customers';

// Fetch all CPT customer posts
$customers = get_posts([
    'post_type' => 'customer',
    'posts_per_page' => -1,
    'post_status' => 'any',
]);

echo "Found " . count($customers) . " Customer CPT posts to migrate.\n";
$migrated_count = 0;
$skipped_count = 0;
$merged_count = 0;

foreach ($customers as $c) {
    $full_name = sanitize_text_field($c->post_title);
    $phone = newtrip_normalize_phone(get_post_meta($c->ID, 'phone', true));
    $email = sanitize_email(get_post_meta($c->ID, 'email', true));
    $birth_date_raw = get_post_meta($c->ID, 'birth_date', true);
    $birth_date = !empty($birth_date_raw) ? newtrip_normalize_date($birth_date_raw) : null;
    $id_number = sanitize_text_field(get_post_meta($c->ID, 'id_number', true));
    
    $total_bookings = intval(get_post_meta($c->ID, 'total_bookings', true) ?: 0);
    $total_spent = floatval(get_post_meta($c->ID, 'total_spent', true) ?: 0.00);
    
    $history_raw = get_post_meta($c->ID, 'bookings_history', true);
    $history = [];
    if (!empty($history_raw)) {
        $history = is_array($history_raw) ? $history_raw : json_decode($history_raw, true);
    }
    if (!is_array($history)) {
        $history = [];
    }
    
    $last_booking_date_raw = get_post_meta($c->ID, 'last_booking_date', true);
    $last_booking_date = !empty($last_booking_date_raw) ? date('Y-m-d H:i:s', strtotime($last_booking_date_raw)) : null;

    if (empty($phone)) {
        echo "Customer CPT ID {$c->ID} has no phone number. Skipping.\n";
        $skipped_count++;
        continue;
    }

    // Check if phone already exists in the custom table
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id, total_bookings, total_spent, bookings_history, last_booking_date FROM $table_name WHERE phone = %s",
        $phone
    ));

    if ($existing) {
        // Merge records
        $existing_id = intval($existing->id);
        
        // Merge bookings history
        $existing_history_raw = $existing->bookings_history;
        $existing_history = [];
        if (!empty($existing_history_raw)) {
            $existing_history = json_decode($existing_history_raw, true);
        }
        if (!is_array($existing_history)) {
            $existing_history = [];
        }
        
        // Union history by booking_id
        $merged_history = $existing_history;
        foreach ($history as $h) {
            $booking_id = $h['booking_id'] ?? 0;
            $found = false;
            foreach ($merged_history as $eh) {
                if (($eh['booking_id'] ?? 0) == $booking_id) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $merged_history[] = $h;
            }
        }
        
        // Recalculate stats based on merged history
        $new_total_bookings = 0;
        $new_total_spent = 0.00;
        foreach ($merged_history as $h) {
            $h_id = $h['booking_id'] ?? 0;
            if (!$h_id) continue;
            
            $h_status = newtrip_get_field('status', $h_id) ?: ($h['status'] ?? 'pending');
            if (!in_array($h_status, ['cancelled', 'refunded'])) {
                $new_total_bookings++;
                if ($h['is_representative'] ?? false) {
                    $new_total_spent += floatval(newtrip_get_field('total_amount', $h_id) ?: 0);
                }
            }
        }
        
        // Determine last booking date
        $new_last_booking_date = $existing->last_booking_date;
        if ($last_booking_date) {
            if (empty($new_last_booking_date) || strtotime($last_booking_date) > strtotime($new_last_booking_date)) {
                $new_last_booking_date = $last_booking_date;
            }
        }
        
        $wpdb->update(
            $table_name,
            [
                'total_bookings' => $new_total_bookings,
                'total_spent' => $new_total_spent,
                'bookings_history' => json_encode($merged_history, JSON_UNESCAPED_UNICODE),
                'last_booking_date' => $new_last_booking_date,
            ],
            ['id' => $existing_id],
            ['%d', '%f', '%s', '%s'],
            ['%d']
        );
        
        echo "Merged duplicate CPT Customer ID {$c->ID} into existing customer ID {$existing_id} (Phone: {$phone})\n";
        $merged_count++;
    } else {
        // Insert new record
        $inserted = $wpdb->insert(
            $table_name,
            [
                'full_name' => $full_name,
                'phone' => $phone,
                'email' => $email,
                'birth_date' => $birth_date,
                'id_number' => $id_number,
                'total_bookings' => $total_bookings,
                'total_spent' => $total_spent,
                'bookings_history' => json_encode($history, JSON_UNESCAPED_UNICODE),
                'last_booking_date' => $last_booking_date,
                'created_at' => $c->post_date,
            ],
            ['%s', '%s', '%s', '%s', '%s', '%d', '%f', '%s', '%s', '%s']
        );
        
        if ($inserted) {
            $migrated_count++;
        } else {
            echo "Failed to migrate CPT Customer ID {$c->ID} (Phone: {$phone})\n";
        }
    }
    
    // Delete the original CPT customer post to clean up
    wp_delete_post($c->ID, true);
}

echo "\nMigration finished.\n";
echo "Migrated: {$migrated_count}\n";
echo "Merged: {$merged_count}\n";
echo "Skipped: {$skipped_count}\n";
