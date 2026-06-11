<?php
/**
 * PickupPoint Model
 *
 * Represents a pickup point location.
 *
 * @since 0.1.0
 */

namespace TourBooking\Models;

class PickupPoint
{
    public int $id;
    public string $name;
    public ?string $address;
    public ?string $map_url;
    public ?string $note;
    public string $status;
    public int $sort_order;
    public string $created_at;
    public ?string $updated_at;

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Constructor
     *
     * @param array $data Pickup point data
     */
    public function __construct(array $data = [])
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->name = $data['name'] ?? '';
        $this->address = $data['address'] ?? null;
        $this->map_url = $data['map_url'] ?? null;
        $this->note = $data['note'] ?? null;
        $this->status = $data['status'] ?? self::STATUS_ACTIVE;
        $this->sort_order = (int) ($data['sort_order'] ?? 0);
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
     * Get status label
     *
     * @return string
     */
    public function get_status_label(): string
    {
        return $this->is_active() ? __('Active', 'nt-tour-booking') : __('Inactive', 'nt-tour-booking');
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
            'address' => $this->address,
            'map_url' => $this->map_url,
            'note' => $this->note,
            'status' => $this->status,
            'status_label' => $this->get_status_label(),
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
