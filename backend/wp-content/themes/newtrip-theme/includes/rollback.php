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
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'newtrip_customers';
    
    // Find customer by phone
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id, bookings_history FROM $table_name WHERE phone = %s",
        $phone
    ));
    
    if (!$existing) return;
    $customer_id = intval($existing->id);
    
    $history_raw = $existing->bookings_history;
    $history = [];
    if (!empty($history_raw)) {
        $history = is_array($history_raw) ? $history_raw : json_decode($history_raw, true);
    }
    if (!is_array($history)) {
        $history = [];
    }
    
    $new_history = [];
    foreach ($history as $h) {
        if (($h['booking_id'] ?? 0) != $booking_id && (!empty($booking_code) && ($h['booking_code'] ?? '') !== $booking_code)) {
            $new_history[] = $h;
        }
    }
    
    // Tính toán lại động tổng số chuyến đi và tổng chi tiêu từ lịch sử mới để tránh sai lệch cộng dồn
    $total_bookings = 0;
    $total_spent = 0;
    
    foreach ($new_history as $h) {
        $h_id = $h['booking_id'] ?? 0;
        if (!$h_id) continue;
        
        $h_status = newtrip_get_field('status', $h_id) ?: ($h['status'] ?? 'pending');
        if (!in_array($h_status, ['cancelled', 'refunded'])) {
            $total_bookings++;
            if ($h['is_representative'] ?? false) {
                $total_spent += floatval(newtrip_get_field('total_amount', $h_id) ?: 0);
            }
        }
    }
    
    $last_booking_date = null;
    if (!empty($new_history)) {
        $latest_time = 0;
        foreach ($new_history as $h) {
            $b_date = $h['booking_date'] ?? '';
            if (!empty($b_date)) {
                $t = strtotime($b_date);
                if ($t > $latest_time) {
                    $latest_time = $t;
                }
            }
        }
        if ($latest_time > 0) {
            $last_booking_date = date('Y-m-d H:i:s', $latest_time);
        }
    }
    
    $wpdb->update(
        $table_name,
        [
            'total_bookings' => $total_bookings,
            'total_spent' => $total_spent,
            'bookings_history' => json_encode($new_history, JSON_UNESCAPED_UNICODE),
            'last_booking_date' => $last_booking_date,
        ],
        ['id' => $customer_id],
        ['%d', '%f', '%s', '%s'],
        ['%d']
    );
}

