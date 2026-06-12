<?php
/**
 * Customer Rollback Functions
 * 
 * Xu ly rollback khi booking bi huy/refund
 */

// Rollback customer stats khi booking bi cancelled/refunded
function newtrip_rollback_customer_for_booking($booking_id) {
    $booking_code = get_post_meta($booking_id, 'booking_code', true) ?: '';
    $total_amount = floatval(newtrip_get_field('total_amount', $booking_id) ?: 0);
    
    $rep_phone = newtrip_get_field('phone', $booking_id) ?: '';
    if (!empty($rep_phone)) {
        $rep_phone = newtrip_normalize_phone($rep_phone);
        newtrip_subtract_from_customer($rep_phone, $booking_id, $booking_code, $total_amount);
    }
    
    $passengers = newtrip_get_field('passengers', $booking_id);
    if (is_array($passengers)) {
        foreach ($passengers as $p) {
            $p_phone = $p['phone'] ?? '';
            if (!empty($p_phone)) {
                $p_phone = newtrip_normalize_phone($p_phone);
                if (!empty($p_phone)) {
                    newtrip_subtract_from_customer($p_phone, $booking_id, $booking_code, 0);
                }
            }
        }
    }
}

// Tru 1 booking khoi customer history va cap nhat total_spent
function newtrip_subtract_from_customer($phone, $booking_id, $booking_code, $amount_to_subtract) {
    if (empty($phone)) return;
    
    $customer_query = new WP_Query([
        'post_type' => 'customer',
        'meta_query' => [
            ['key' => 'phone', 'value' => $phone, 'compare' => '=']
        ],
        'posts_per_page' => 1,
        'post_status' => 'any'
    ]);
    
    if (!$customer_query->have_posts()) return;
    $customer_id = $customer_query->posts[0]->ID;
    
    $history_raw = get_post_meta($customer_id, 'bookings_history', true) ?: [];
    $history = is_array($history_raw) ? $history_raw : [];
    
    $new_history = [];
    foreach ($history as $h) {
        if (($h['booking_id'] ?? 0) != $booking_id && ($h['booking_code'] ?? '') !== $booking_code) {
            $new_history[] = $h;
        }
    }
    
    update_post_meta($customer_id, 'bookings_history', $new_history);
    update_post_meta($customer_id, 'total_bookings', count($new_history));
    
    if ($amount_to_subtract > 0) {
        $current_spent = floatval(get_post_meta($customer_id, 'total_spent', true) ?: 0);
        $new_spent = max(0, $current_spent - $amount_to_subtract);
        update_post_meta($customer_id, 'total_spent', $new_spent);
    }
    
    if (!empty($new_history)) {
        $last_entry = end($new_history);
        update_post_meta($customer_id, 'last_booking_date', $last_entry['booking_date'] ?? date('Y-m-d H:i:s'));
    } else {
        update_post_meta($customer_id, 'last_booking_date', '');
    }
}
