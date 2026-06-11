<?php
/**
 * Seat Model
 *
 * Represents a seat on a departure.
 *
 * @since 0.1.0
 */

namespace TourBooking\Models;

class Seat
{
    public int $id;
    public int $tour_departure_id;
    public int $departure_vehicle_id;
    public string $seat_code;
    public string $status;
    public ?int $booking_id;
    public ?int $passenger_id;
    public ?string $hold_expires_at;
    public ?string $booked_at;
    public ?string $checked_in_at;
    public string $created_at;
    public ?string $updated_at;

    /**
     * Status constants
     */
    const STATUS_AVAILABLE = 'available';
    const STATUS_HOLDING = 'holding';
    const STATUS_BOOKED = 'booked';
    const STATUS_CHECKED_IN = 'checked_in';
    const STATUS_BLOCKED = 'blocked';

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->tour_departure_id = (int) ($data['tour_departure_id'] ?? 0);
        $this->departure_vehicle_id = (int) ($data['departure_vehicle_id'] ?? 0);
        $this->seat_code = $data['seat_code'] ?? '';
        $this->status = $data['status'] ?? self::STATUS_AVAILABLE;
        $this->booking_id = isset($data['booking_id']) ? (int) $data['booking_id'] : null;
        $this->passenger_id = isset($data['passenger_id']) ? (int) $data['passenger_id'] : null;
        $this->hold_expires_at = $data['hold_expires_at'] ?? null;
        $this->booked_at = $data['booked_at'] ?? null;
        $this->checked_in_at = $data['checked_in_at'] ?? null;
        $this->created_at = $data['created_at'] ?? current_time('mysql');
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Check if available
     *
     * @return bool
     */
    public function is_available(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    /**
     * Check if holding
     *
     * @return bool
     */
    public function is_holding(): bool
    {
        return $this->status === self::STATUS_HOLDING;
    }

    /**
     * Check if booked
     *
     * @return bool
     */
    public function is_booked(): bool
    {
        return in_array($this->status, [self::STATUS_BOOKED, self::STATUS_CHECKED_IN]);
    }

    /**
     * Check if hold is expired
     *
     * @return bool
     */
    public function is_hold_expired(): bool
    {
        if (!$this->hold_expires_at) {
            return false;
        }

        return strtotime($this->hold_expires_at) < time();
    }

    /**
     * Check if hold is about to expire (within 5 minutes)
     *
     * @return bool
     */
    public function is_hold_about_to_expire(): bool
    {
        if (!$this->hold_expires_at) {
            return false;
        }

        $expires = strtotime($this->hold_expires_at);
        $five_minutes = 5 * MINUTE_IN_SECONDS;

        return $expires > time() && $expires < (time() + $five_minutes);
    }

    /**
     * Get status label
     *
     * @return string
     */
    public function get_status_label(): string
    {
        $labels = [
            self::STATUS_AVAILABLE => __('Available', 'nt-tour-booking'),
            self::STATUS_HOLDING => __('Holding', 'nt-tour-booking'),
            self::STATUS_BOOKED => __('Booked', 'nt-tour-booking'),
            self::STATUS_CHECKED_IN => __('Checked In', 'nt-tour-booking'),
            self::STATUS_BLOCKED => __('Blocked', 'nt-tour-booking'),
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get time remaining for hold
     *
     * @return int Seconds remaining
     */
    public function get_hold_time_remaining(): int
    {
        if (!$this->hold_expires_at) {
            return 0;
        }

        $expires = strtotime($this->hold_expires_at);
        $remaining = $expires - time();

        return max(0, $remaining);
    }

    /**
     * Get formatted hold time remaining
     *
     * @return string
     */
    public function get_hold_time_remaining_formatted(): string
    {
        $seconds = $this->get_hold_time_remaining();

        if ($seconds <= 0) {
            return 'Expired';
        }

        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;

        return "{$minutes}m {$secs}s";
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
            'tour_departure_id' => $this->tour_departure_id,
            'departure_vehicle_id' => $this->departure_vehicle_id,
            'seat_code' => $this->seat_code,
            'status' => $this->status,
            'status_label' => $this->get_status_label(),
            'booking_id' => $this->booking_id,
            'passenger_id' => $this->passenger_id,
            'hold_expires_at' => $this->hold_expires_at,
            'hold_time_remaining' => $this->get_hold_time_remaining(),
            'hold_time_remaining_formatted' => $this->get_hold_time_remaining_formatted(),
            'booked_at' => $this->booked_at,
            'checked_in_at' => $this->checked_in_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Convert to safe array (for public API)
     *
     * @return array
     */
    public function to_safe_array(): array
    {
        return [
            'seat_code' => $this->seat_code,
            'status' => $this->status,
        ];
    }
}
