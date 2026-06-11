<?php
/**
 * Database Schema Definitions
 *
 * Contains SQL definitions for all 14 custom tables.
 *
 * @since 0.1.0
 */

namespace TourBooking\Database;

class Schema
{
    /**
     * Get all table definitions
     *
     * @return array<string, string>
     */
    public static function get_tables(): array
    {
        return [
            'nt_tour_departures' => self::departures(),
            'nt_pickup_points' => self::pickup_points(),
            'nt_departure_pickup_points' => self::departure_pickup_points(),
            'nt_vehicle_layouts' => self::vehicle_layouts(),
            'nt_vehicles' => self::vehicles(),
            'nt_departure_vehicles' => self::departure_vehicles(),
            'nt_departure_seats' => self::departure_seats(),
            'nt_bookings' => self::bookings(),
            'nt_booking_passengers' => self::booking_passengers(),
            'nt_booking_access_tokens' => self::booking_access_tokens(),
            'nt_payments' => self::payments(),
            'nt_departure_guides' => self::departure_guides(),
            'nt_checkin_logs' => self::checkin_logs(),
            'nt_activity_logs' => self::activity_logs(),
            'nt_rental_items' => self::rental_items(),
            'nt_booking_rental_items' => self::booking_rental_items(),
            'nt_api_clients' => self::api_clients(),
            'nt_api_request_logs' => self::api_request_logs(),
            'nt_webhook_logs' => self::webhook_logs(),
        ];
    }

    /**
     * Tour Departures Table
     */
    public static function departures(): string
    {
        return "CREATE TABLE {prefix}nt_tour_departures (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            tour_id BIGINT UNSIGNED NOT NULL,
            departure_code VARCHAR(50) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NULL,
            departure_time TIME NULL,
            adult_price DECIMAL(15,2) NOT NULL DEFAULT 0,
            child_price DECIMAL(15,2) NOT NULL DEFAULT 0,
            infant_price DECIMAL(15,2) NOT NULL DEFAULT 0,
            capacity INT NOT NULL DEFAULT 0,
            status VARCHAR(30) NOT NULL DEFAULT 'open',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY tour_id (tour_id),
            KEY start_date (start_date),
            KEY status (status)
        ) {charset_collate};";
    }

    /**
     * Pickup Points Table
     */
    public static function pickup_points(): string
    {
        return "CREATE TABLE {prefix}nt_pickup_points (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            address TEXT NULL,
            map_url TEXT NULL,
            note TEXT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'active',
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY status (status),
            KEY sort_order (sort_order)
        ) {charset_collate};";
    }

    /**
     * Departure Pickup Points Table
     */
    public static function departure_pickup_points(): string
    {
        return "CREATE TABLE {prefix}nt_departure_pickup_points (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            tour_departure_id BIGINT UNSIGNED NOT NULL,
            pickup_point_id BIGINT UNSIGNED NOT NULL,
            pickup_time TIME NULL,
            note TEXT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'active',
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY tour_departure_id (tour_departure_id),
            KEY pickup_point_id (pickup_point_id),
            KEY status (status)
        ) {charset_collate};";
    }

    /**
     * Vehicle Layouts Table
     */
    public static function vehicle_layouts(): string
    {
        return "CREATE TABLE {prefix}nt_vehicle_layouts (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            vehicle_type VARCHAR(50) NOT NULL,
            total_seats INT NOT NULL DEFAULT 0,
            layout_json LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY vehicle_type (vehicle_type)
        ) {charset_collate};";
    }

    /**
     * Vehicles Table
     */
    public static function vehicles(): string
    {
        return "CREATE TABLE {prefix}nt_vehicles (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            plate_number VARCHAR(50) NULL,
            vehicle_type VARCHAR(50) NOT NULL,
            total_seats INT NOT NULL DEFAULT 0,
            layout_id BIGINT UNSIGNED NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY layout_id (layout_id),
            KEY status (status)
        ) {charset_collate};";
    }

    /**
     * Departure Vehicles Table
     */
    public static function departure_vehicles(): string
    {
        return "CREATE TABLE {prefix}nt_departure_vehicles (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            tour_departure_id BIGINT UNSIGNED NOT NULL,
            vehicle_id BIGINT UNSIGNED NOT NULL,
            capacity INT NOT NULL DEFAULT 0,
            status VARCHAR(30) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY tour_departure_id (tour_departure_id),
            KEY vehicle_id (vehicle_id),
            KEY status (status)
        ) {charset_collate};";
    }

    /**
     * Departure Seats Table (CRITICAL - chống trùng đặt)
     */
    public static function departure_seats(): string
    {
        return "CREATE TABLE {prefix}nt_departure_seats (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            tour_departure_id BIGINT UNSIGNED NOT NULL,
            departure_vehicle_id BIGINT UNSIGNED NOT NULL,
            seat_code VARCHAR(30) NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'available',
            booking_id BIGINT UNSIGNED NULL,
            passenger_id BIGINT UNSIGNED NULL,
            hold_expires_at DATETIME NULL,
            booked_at DATETIME NULL,
            checked_in_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_departure_seat (tour_departure_id, departure_vehicle_id, seat_code),
            KEY status (status),
            KEY booking_id (booking_id),
            KEY passenger_id (passenger_id),
            KEY hold_expires_at (hold_expires_at)
        ) {charset_collate};";
    }

    /**
     * Bookings Table
     */
    public static function bookings(): string
    {
        return "CREATE TABLE {prefix}nt_bookings (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            code VARCHAR(50) NOT NULL,
            booking_type VARCHAR(30) NOT NULL DEFAULT 'retail_group',
            tour_departure_id BIGINT UNSIGNED NOT NULL,
            customer_name VARCHAR(191) NOT NULL,
            customer_phone VARCHAR(50) NOT NULL,
            customer_email VARCHAR(191) NULL,
            pickup_point_id BIGINT UNSIGNED NULL,
            expected_people INT NOT NULL DEFAULT 0,
            total_people INT NOT NULL DEFAULT 0,
            adult_count INT NOT NULL DEFAULT 0,
            child_count INT NOT NULL DEFAULT 0,
            infant_count INT NOT NULL DEFAULT 0,
            total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            deposit_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            booking_status VARCHAR(30) NOT NULL DEFAULT 'pending_payment',
            payment_status VARCHAR(30) NOT NULL DEFAULT 'unpaid',
            passenger_info_status VARCHAR(30) NOT NULL DEFAULT 'missing',
            seat_selection_mode VARCHAR(30) NOT NULL DEFAULT 'admin_assign',
            hold_expires_at DATETIME NULL,
            magic_link_sent_at DATETIME NULL,
            confirmed_at DATETIME NULL,
            cancelled_at DATETIME NULL,
            source VARCHAR(50) NOT NULL DEFAULT 'website',
            note TEXT NULL,
            created_by BIGINT UNSIGNED NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY code (code),
            KEY tour_departure_id (tour_departure_id),
            KEY customer_phone (customer_phone),
            KEY customer_email (customer_email),
            KEY booking_status (booking_status),
            KEY payment_status (payment_status),
            KEY passenger_info_status (passenger_info_status)
        ) {charset_collate};";
    }

    /**
     * Booking Passengers Table
     */
    public static function booking_passengers(): string
    {
        return "CREATE TABLE {prefix}nt_booking_passengers (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            booking_id BIGINT UNSIGNED NOT NULL,
            tour_departure_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(191) NULL,
            phone VARCHAR(50) NULL,
            email VARCHAR(191) NULL,
            gender VARCHAR(20) NULL,
            date_of_birth DATE NULL,
            id_number VARCHAR(50) NULL,
            id_issue_date DATE NULL,
            id_issue_place VARCHAR(191) NULL,
            id_front_attachment_id BIGINT UNSIGNED NULL,
            id_back_attachment_id BIGINT UNSIGNED NULL,
            address TEXT NULL,
            emergency_contact LONGTEXT NULL,
            health_notes TEXT NULL,
            dietary_requirements TEXT NULL,
            passenger_type VARCHAR(30) NOT NULL DEFAULT 'adult',
            pickup_point_id BIGINT UNSIGNED NULL,
            seat_code VARCHAR(30) NULL,
            is_placeholder TINYINT(1) NOT NULL DEFAULT 1,
            profile_status VARCHAR(30) NOT NULL DEFAULT 'missing',
            qr_token_hash VARCHAR(191) NULL,
            qr_generated_at DATETIME NULL,
            checkin_status VARCHAR(30) NOT NULL DEFAULT 'not_checked_in',
            checked_in_at DATETIME NULL,
            checked_in_by BIGINT UNSIGNED NULL,
            note TEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY booking_id (booking_id),
            KEY tour_departure_id (tour_departure_id),
            KEY pickup_point_id (pickup_point_id),
            KEY seat_code (seat_code),
            KEY id_number (id_number),
            KEY profile_status (profile_status),
            KEY checkin_status (checkin_status)
        ) {charset_collate};";
    }

    /**
     * Booking Access Tokens Table (Magic Link)
     */
    public static function booking_access_tokens(): string
    {
        return "CREATE TABLE {prefix}nt_booking_access_tokens (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            booking_id BIGINT UNSIGNED NOT NULL,
            token_hash VARCHAR(191) NOT NULL,
            purpose VARCHAR(50) NOT NULL DEFAULT 'complete_passengers',
            expires_at DATETIME NULL,
            used_at DATETIME NULL,
            last_accessed_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY booking_id (booking_id),
            UNIQUE KEY token_hash (token_hash),
            KEY purpose (purpose),
            KEY expires_at (expires_at)
        ) {charset_collate};";
    }

    /**
     * Payments Table
     */
    public static function payments(): string
    {
        return "CREATE TABLE {prefix}nt_payments (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            booking_id BIGINT UNSIGNED NOT NULL,
            amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            method VARCHAR(50) NOT NULL DEFAULT 'bank_transfer',
            bank_account VARCHAR(100) NULL,
            bank_name VARCHAR(100) NULL,
            transaction_code VARCHAR(191) NULL,
            transfer_content VARCHAR(191) NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'pending',
            paid_at DATETIME NULL,
            confirmed_by BIGINT UNSIGNED NULL,
            raw_payload LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY booking_id (booking_id),
            KEY status (status),
            KEY transaction_code (transaction_code),
            KEY paid_at (paid_at)
        ) {charset_collate};";
    }

    /**
     * Departure Guides Table
     */
    public static function departure_guides(): string
    {
        return "CREATE TABLE {prefix}nt_departure_guides (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            tour_departure_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            role_note VARCHAR(100) NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY tour_departure_id (tour_departure_id),
            KEY user_id (user_id)
        ) {charset_collate};";
    }

    /**
     * Check-in Logs Table
     */
    public static function checkin_logs(): string
    {
        return "CREATE TABLE {prefix}nt_checkin_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            tour_departure_id BIGINT UNSIGNED NOT NULL,
            booking_id BIGINT UNSIGNED NOT NULL,
            passenger_id BIGINT UNSIGNED NOT NULL,
            action VARCHAR(50) NOT NULL,
            old_status VARCHAR(30) NULL,
            new_status VARCHAR(30) NULL,
            user_id BIGINT UNSIGNED NULL,
            device_info TEXT NULL,
            ip_address VARCHAR(100) NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY tour_departure_id (tour_departure_id),
            KEY booking_id (booking_id),
            KEY passenger_id (passenger_id),
            KEY action (action),
            KEY user_id (user_id)
        ) {charset_collate};";
    }

    /**
     * Rental Items Table
     */
    public static function rental_items(): string
    {
        return "CREATE TABLE {prefix}nt_rental_items (
            id VARCHAR(50) NOT NULL,
            name VARCHAR(191) NOT NULL,
            description TEXT NULL,
            price DECIMAL(15,2) NOT NULL DEFAULT 0,
            unit VARCHAR(50) NOT NULL DEFAULT 'cái',
            category VARCHAR(50) NOT NULL DEFAULT 'accessories',
            icon VARCHAR(10) NULL,
            stock_available INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY category (category),
            KEY is_active (is_active)
        ) {charset_collate};";
    }

    /**
     * Booking Rental Items Pivot Table
     */
    public static function booking_rental_items(): string
    {
        return "CREATE TABLE {prefix}nt_booking_rental_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            booking_id BIGINT UNSIGNED NOT NULL,
            rental_item_id VARCHAR(50) NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            unit_price DECIMAL(15,2) NOT NULL DEFAULT 0,
            subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY booking_id (booking_id),
            KEY rental_item_id (rental_item_id)
        ) {charset_collate};";
    }

    /**
     * Activity Logs Table
     */
    public static function activity_logs(): string
    {
        return "CREATE TABLE {prefix}nt_activity_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            object_type VARCHAR(50) NOT NULL,
            object_id BIGINT UNSIGNED NOT NULL,
            action VARCHAR(100) NOT NULL,
            old_value LONGTEXT NULL,
            new_value LONGTEXT NULL,
            user_id BIGINT UNSIGNED NULL,
            ip_address VARCHAR(100) NULL,
            user_agent TEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY object_type_id (object_type, object_id),
            KEY action (action),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) {charset_collate};";
    }

    /**
     * API Clients Table
     */
    public static function api_clients(): string
    {
        return "CREATE TABLE {prefix}nt_api_clients (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            public_key VARCHAR(100) NOT NULL,
            secret_hash VARCHAR(191) NOT NULL,
            scopes LONGTEXT NULL,
            allowed_ips TEXT NULL,
            allowed_origins TEXT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'active',
            expires_at DATETIME NULL,
            last_used_at DATETIME NULL,
            created_by BIGINT UNSIGNED NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY public_key (public_key),
            KEY status (status),
            KEY expires_at (expires_at)
        ) {charset_collate};";
    }

    /**
     * API Request Logs Table
     */
    public static function api_request_logs(): string
    {
        return "CREATE TABLE {prefix}nt_api_request_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            api_client_id BIGINT UNSIGNED NULL,
            route VARCHAR(191) NOT NULL,
            method VARCHAR(20) NOT NULL,
            status_code INT NULL,
            error_code VARCHAR(100) NULL,
            ip_address VARCHAR(100) NULL,
            user_agent TEXT NULL,
            duration_ms INT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY api_client_id (api_client_id),
            KEY route (route),
            KEY status_code (status_code),
            KEY created_at (created_at)
        ) {charset_collate};";
    }

    /**
     * Webhook Logs Table
     */
    public static function webhook_logs(): string
    {
        return "CREATE TABLE {prefix}nt_webhook_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            provider VARCHAR(100) NULL,
            event_type VARCHAR(100) NULL,
            signature_valid TINYINT(1) NOT NULL DEFAULT 0,
            booking_code VARCHAR(50) NULL,
            amount DECIMAL(15,2) NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'received',
            error_message TEXT NULL,
            raw_payload LONGTEXT NULL,
            ip_address VARCHAR(100) NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY booking_code (booking_code),
            KEY status (status),
            KEY created_at (created_at)
        ) {charset_collate};";
    }
}
