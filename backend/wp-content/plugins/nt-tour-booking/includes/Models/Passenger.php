<?php
/**
 * Passenger Model
 *
 * Represents a passenger in a booking.
 *
 * @since 0.1.0
 */

namespace TourBooking\Models;

class Passenger
{
    public int $id;
    public int $booking_id;
    public int $tour_departure_id;
    public ?string $name;
    public ?string $phone;
    public ?string $email;
    public ?string $gender;
    public ?string $date_of_birth;
    public ?string $id_number;
    public ?string $id_issue_date;
    public ?string $id_issue_place;
    public ?int $id_front_attachment_id;
    public ?int $id_back_attachment_id;
    public ?string $address;
    public ?string $emergency_contact;
    public ?string $health_notes;
    public ?string $dietary_requirements;
    public string $passenger_type;
    public ?int $pickup_point_id;
    public ?string $seat_code;
    public bool $is_placeholder;
    public string $profile_status;
    public ?string $qr_token_hash;
    public ?string $qr_generated_at;
    public string $checkin_status;
    public ?string $checked_in_at;
    public ?int $checked_in_by;
    public ?string $note;
    public string $created_at;
    public ?string $updated_at;

    /**
     * Passenger type constants
     */
    const TYPE_ADULT = 'adult';
    const TYPE_CHILD = 'child';
    const TYPE_INFANT = 'infant';

    /**
     * Profile status constants
     */
    const PROFILE_MISSING = 'missing';
    const PROFILE_PARTIAL = 'partial';
    const PROFILE_COMPLETED = 'completed';
    const PROFILE_ADMIN_FILLED = 'admin_filled';

    /**
     * Check-in status constants
     */
    const CHECKIN_NOT_CHECKED_IN = 'not_checked_in';
    const CHECKIN_CHECKED_IN = 'checked_in';
    const CHECKIN_NO_SHOW = 'no_show';
    const CHECKIN_CANCELLED = 'cancelled';

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->booking_id = (int) ($data['booking_id'] ?? 0);
        $this->tour_departure_id = (int) ($data['tour_departure_id'] ?? 0);
        $this->name = $data['name'] ?? null;
        $this->phone = $data['phone'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->gender = $data['gender'] ?? null;
        $this->date_of_birth = $data['date_of_birth'] ?? null;
        $this->id_number = $data['id_number'] ?? null;
        $this->id_issue_date = $data['id_issue_date'] ?? null;
        $this->id_issue_place = $data['id_issue_place'] ?? null;
        $this->id_front_attachment_id = isset($data['id_front_attachment_id']) ? (int) $data['id_front_attachment_id'] : null;
        $this->id_back_attachment_id = isset($data['id_back_attachment_id']) ? (int) $data['id_back_attachment_id'] : null;
        $this->address = $data['address'] ?? null;
        $this->emergency_contact = $data['emergency_contact'] ?? null;
        $this->health_notes = $data['health_notes'] ?? null;
        $this->dietary_requirements = $data['dietary_requirements'] ?? null;
        $this->passenger_type = $data['passenger_type'] ?? self::TYPE_ADULT;
        $this->pickup_point_id = isset($data['pickup_point_id']) ? (int) $data['pickup_point_id'] : null;
        $this->seat_code = $data['seat_code'] ?? null;
        $this->is_placeholder = (bool) ($data['is_placeholder'] ?? true);
        $this->profile_status = $data['profile_status'] ?? self::PROFILE_MISSING;
        $this->qr_token_hash = $data['qr_token_hash'] ?? null;
        $this->qr_generated_at = $data['qr_generated_at'] ?? null;
        $this->checkin_status = $data['checkin_status'] ?? self::CHECKIN_NOT_CHECKED_IN;
        $this->checked_in_at = $data['checked_in_at'] ?? null;
        $this->checked_in_by = isset($data['checked_in_by']) ? (int) $data['checked_in_by'] : null;
        $this->note = $data['note'] ?? null;
        $this->created_at = $data['created_at'] ?? current_time('mysql');
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Check if profile is complete
     *
     * @return bool
     */
    public function is_profile_complete(): bool
    {
        return in_array($this->profile_status, [self::PROFILE_COMPLETED, self::PROFILE_ADMIN_FILLED]);
    }

    /**
     * Check if profile is missing
     *
     * @return bool
     */
    public function is_profile_missing(): bool
    {
        return $this->profile_status === self::PROFILE_MISSING;
    }

    /**
     * Check if is placeholder
     *
     * @return bool
     */
    public function is_placeholder(): bool
    {
        return $this->is_placeholder;
    }

    /**
     * Check if checked in
     *
     * @return bool
     */
    public function is_checked_in(): bool
    {
        return $this->checkin_status === self::CHECKIN_CHECKED_IN;
    }

    /**
     * Check if QR is generated
     *
     * @return bool
     */
    public function has_qr(): bool
    {
        return !empty($this->qr_token_hash);
    }

    /**
     * Can generate QR
     *
     * @return bool
     */
    public function can_generate_qr(): bool
    {
        return !empty($this->name) && !$this->has_qr();
    }

    /**
     * Get profile status label
     *
     * @return string
     */
    public function get_profile_status_label(): string
    {
        $labels = [
            self::PROFILE_MISSING => __('Thiếu thông tin', 'nt-tour-booking'),
            self::PROFILE_PARTIAL => __('Chưa đủ thông tin', 'nt-tour-booking'),
            self::PROFILE_COMPLETED => __('Hoàn thành', 'nt-tour-booking'),
            self::PROFILE_ADMIN_FILLED => __('Admin điền', 'nt-tour-booking'),
        ];

        return $labels[$this->profile_status] ?? $this->profile_status;
    }

    /**
     * Get passenger type label
     *
     * @return string
     */
    public function get_type_label(): string
    {
        $labels = [
            self::TYPE_ADULT => __('Người lớn', 'nt-tour-booking'),
            self::TYPE_CHILD => __('Trẻ em', 'nt-tour-booking'),
            self::TYPE_INFANT => __('Em bé', 'nt-tour-booking'),
        ];

        return $labels[$this->passenger_type] ?? $this->passenger_type;
    }

    /**
     * Get check-in status label
     *
     * @return string
     */
    public function get_checkin_status_label(): string
    {
        $labels = [
            self::CHECKIN_NOT_CHECKED_IN => __('Chưa check-in', 'nt-tour-booking'),
            self::CHECKIN_CHECKED_IN => __('Đã check-in', 'nt-tour-booking'),
            self::CHECKIN_NO_SHOW => __('No-show', 'nt-tour-booking'),
            self::CHECKIN_CANCELLED => __('Đã hủy', 'nt-tour-booking'),
        ];

        return $labels[$this->checkin_status] ?? $this->checkin_status;
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
            'booking_id' => $this->booking_id,
            'tour_departure_id' => $this->tour_departure_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'id_number' => $this->id_number,
            'id_issue_date' => $this->id_issue_date,
            'id_issue_place' => $this->id_issue_place,
            'id_front_attachment_id' => $this->id_front_attachment_id,
            'id_back_attachment_id' => $this->id_back_attachment_id,
            'address' => $this->address,
            'emergency_contact' => $this->emergency_contact,
            'health_notes' => $this->health_notes,
            'dietary_requirements' => $this->dietary_requirements,
            'passenger_type' => $this->passenger_type,
            'passenger_type_label' => $this->get_type_label(),
            'pickup_point_id' => $this->pickup_point_id,
            'seat_code' => $this->seat_code,
            'is_placeholder' => $this->is_placeholder,
            'profile_status' => $this->profile_status,
            'profile_status_label' => $this->get_profile_status_label(),
            'has_qr' => $this->has_qr(),
            'checkin_status' => $this->checkin_status,
            'checkin_status_label' => $this->get_checkin_status_label(),
            'checked_in_at' => $this->checked_in_at,
            'checked_in_by' => $this->checked_in_by,
            'note' => $this->note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
