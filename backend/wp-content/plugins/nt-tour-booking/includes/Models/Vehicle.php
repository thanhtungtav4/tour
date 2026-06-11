<?php
/**
 * Vehicle Model
 *
 * Represents a vehicle.
 *
 * @since 0.1.0
 */

namespace TourBooking\Models;

class Vehicle
{
    public int $id;
    public string $name;
    public ?string $plate_number;
    public string $vehicle_type;
    public int $total_seats;
    public ?int $layout_id;
    public string $status;
    public string $created_at;
    public ?string $updated_at;

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Vehicle types
     */
    const TYPE_BUS = 'bus';
    const TYPE_MINIBUS = 'minibus';
    const TYPE_CAR = 'car';
    const TYPE_LIMOUSINE = 'limousine';

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->name = $data['name'] ?? '';
        $this->plate_number = $data['plate_number'] ?? null;
        $this->vehicle_type = $data['vehicle_type'] ?? self::TYPE_BUS;
        $this->total_seats = (int) ($data['total_seats'] ?? 0);
        $this->layout_id = isset($data['layout_id']) ? (int) $data['layout_id'] : null;
        $this->status = $data['status'] ?? self::STATUS_ACTIVE;
        $this->created_at = $data['created_at'] ?? current_time('mysql');
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Check if active
     *
     * @return bool
     */
    public function is_active(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get layout object
     *
     * @return VehicleLayout|null
     */
    public function get_layout(): ?VehicleLayout
    {
        if (!$this->layout_id) {
            return null;
        }

        $repo = new \TourBooking\Repositories\VehicleLayoutRepository();
        return $repo->find($this->layout_id);
    }

    /**
     * Get status label
     *
     * @return string
     */
    public function get_status_label(): string
    {
        return $this->is_active() ? __('Active', 'nt-tour-booking') : __('Inactive', 'nt-tour-booking');
    }

    /**
     * Get vehicle type label
     *
     * @return string
     */
    public function get_type_label(): string
    {
        $labels = [
            self::TYPE_BUS => __('Xe khách', 'nt-tour-booking'),
            self::TYPE_MINIBUS => __('Xe minibus', 'nt-tour-booking'),
            self::TYPE_CAR => __('Xe ô tô', 'nt-tour-booking'),
            self::TYPE_LIMOUSINE => __('Xe limousine', 'nt-tour-booking'),
        ];

        return $labels[$this->vehicle_type] ?? $this->vehicle_type;
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
            'name' => $this->name,
            'plate_number' => $this->plate_number,
            'vehicle_type' => $this->vehicle_type,
            'vehicle_type_label' => $this->get_type_label(),
            'total_seats' => $this->total_seats,
            'layout_id' => $this->layout_id,
            'status' => $this->status,
            'status_label' => $this->get_status_label(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
