<?php
/**
 * Departure Model
 *
 * Represents a tour departure schedule.
 *
 * @since 0.1.0
 */

namespace TourBooking\Models;

class Departure
{
    public int $id;
    public int $tour_id;
    public string $departure_code;
    public string $start_date;
    public ?string $end_date;
    public ?string $departure_time;
    public float $adult_price;
    public float $child_price;
    public float $infant_price;
    public int $capacity;
    public string $status;
    public string $created_at;
    public ?string $updated_at;

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_OPEN = 'open';
    const STATUS_FULL = 'full';
    const STATUS_CLOSED = 'closed';
    const STATUS_DEPARTED = 'departed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    /**
     * Constructor
     *
     * @param array $data Departure data
     */
    public function __construct(array $data = [])
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->tour_id = (int) ($data['tour_id'] ?? 0);
        $this->departure_code = $data['departure_code'] ?? '';
        $this->start_date = $data['start_date'] ?? '';
        $this->end_date = $data['end_date'] ?? null;
        $this->departure_time = $data['departure_time'] ?? null;
        $this->adult_price = (float) ($data['adult_price'] ?? 0);
        $this->child_price = (float) ($data['child_price'] ?? 0);
        $this->infant_price = (float) ($data['infant_price'] ?? 0);
        $this->capacity = (int) ($data['capacity'] ?? 0);
        $this->status = $data['status'] ?? self::STATUS_OPEN;
        $this->created_at = $data['created_at'] ?? current_time('mysql');
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Get Tour object
     *
     * @return Tour|null
     */
    public function get_tour(): ?Tour
    {
        return Tour::find($this->tour_id);
    }

    /**
     * Get formatted start date
     *
     * @param string $format Date format
     * @return string
     */
    public function get_start_date_formatted(string $format = 'd/m/Y'): string
    {
        if (empty($this->start_date)) {
            return '';
        }
        return date_i18n($format, strtotime($this->start_date));
    }

    /**
     * Get formatted end date
     *
     * @param string $format Date format
     * @return string
     */
    public function get_end_date_formatted(string $format = 'd/m/Y'): string
    {
        if (empty($this->end_date)) {
            return '';
        }
        return date_i18n($format, strtotime($this->end_date));
    }

    /**
     * Get formatted departure time
     *
     * @param string $format Time format
     * @return string
     */
    public function get_departure_time_formatted(string $format = 'H:i'): string
    {
        if (empty($this->departure_time)) {
            return '';
        }
        return date_i18n($format, strtotime($this->departure_time));
    }

    /**
     * Check if departure is open for booking
     *
     * @return bool
     */
    public function is_open(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Check if departure is available
     *
     * @return bool
     */
    public function is_available(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_FULL]);
    }

    /**
     * Check if departure has started
     *
     * @return bool
     */
    public function has_started(): bool
    {
        if (empty($this->start_date)) {
            return false;
        }
        return strtotime($this->start_date) <= time();
    }

    /**
     * Check if departure is in the past
     *
     * @return bool
     */
    public function is_past(): bool
    {
        if (empty($this->start_date)) {
            return false;
        }
        return strtotime($this->start_date) < strtotime('today');
    }

    /**
     * Get available seats count
     *
     * @return int
     */
    public function get_available_seats(): int
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nt_departure_seats';
        $departure_vehicles_table = $wpdb->prefix . 'nt_departure_vehicles';

        // Get total capacity from vehicles
        $total_capacity = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(dv.capacity) 
                FROM {$departure_vehicles_table} dv 
                WHERE dv.tour_departure_id = %d AND dv.status = 'active'",
                $this->id
            )
        );

        if (!$total_capacity) {
            $total_capacity = $this->capacity;
        }

        // Get booked seats count
        $booked_seats = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) 
                FROM {$table_name} 
                WHERE tour_departure_id = %d AND status IN ('booked', 'checked_in')",
                $this->id
            )
        );

        return max(0, (int) $total_capacity - (int) $booked_seats);
    }

    /**
     * Get pickup points for this departure
     *
     * @return array
     */
    public function get_pickup_points(): array
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nt_departure_pickup_points';
        $pickup_points_table = $wpdb->prefix . 'nt_pickup_points';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT pp.*, dpp.pickup_time, dpp.note as departure_note, dpp.sort_order as departure_sort_order
                FROM {$table_name} dpp
                JOIN {$pickup_points_table} pp ON dpp.pickup_point_id = pp.id
                WHERE dpp.tour_departure_id = %d AND dpp.status = 'active'
                ORDER BY dpp.sort_order ASC",
                $this->id
            ),
            ARRAY_A
        );

        return $results ?: [];
    }

    /**
     * Get vehicles assigned to this departure
     *
     * @return array
     */
    public function get_vehicles(): array
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nt_departure_vehicles';
        $vehicles_table = $wpdb->prefix . 'nt_vehicles';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT v.*, dv.capacity as assigned_capacity, dv.id as departure_vehicle_id
                FROM {$table_name} dv
                JOIN {$vehicles_table} v ON dv.vehicle_id = v.id
                WHERE dv.tour_departure_id = %d AND dv.status = 'active'",
                $this->id
            ),
            ARRAY_A
        );

        return $results ?: [];
    }

    /**
     * Get status label
     *
     * @return string
     */
    public function get_status_label(): string
    {
        $labels = [
            self::STATUS_DRAFT => __('Draft', 'nt-tour-booking'),
            self::STATUS_OPEN => __('Open', 'nt-tour-booking'),
            self::STATUS_FULL => __('Full', 'nt-tour-booking'),
            self::STATUS_CLOSED => __('Closed', 'nt-tour-booking'),
            self::STATUS_DEPARTED => __('Departed', 'nt-tour-booking'),
            self::STATUS_CANCELLED => __('Cancelled', 'nt-tour-booking'),
            self::STATUS_COMPLETED => __('Completed', 'nt-tour-booking'),
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function to_array(): array
    {
        return [
            'id' => $this->id,
            'tour_id' => $this->tour_id,
            'departure_code' => $this->departure_code,
            'start_date' => $this->start_date,
            'start_date_formatted' => $this->get_start_date_formatted(),
            'end_date' => $this->end_date,
            'end_date_formatted' => $this->get_end_date_formatted(),
            'departure_time' => $this->departure_time,
            'departure_time_formatted' => $this->get_departure_time_formatted(),
            'adult_price' => $this->adult_price,
            'child_price' => $this->child_price,
            'infant_price' => $this->infant_price,
            'capacity' => $this->capacity,
            'available_seats' => $this->get_available_seats(),
            'status' => $this->status,
            'status_label' => $this->get_status_label(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
