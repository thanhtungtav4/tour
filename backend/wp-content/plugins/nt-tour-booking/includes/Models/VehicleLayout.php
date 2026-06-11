<?php
/**
 * VehicleLayout Model
 *
 * Represents a vehicle seat layout.
 *
 * @since 0.1.0
 */

namespace TourBooking\Models;

class VehicleLayout
{
    public int $id;
    public string $name;
    public string $vehicle_type;
    public int $total_seats;
    public string $layout_json;
    public string $created_at;
    public ?string $updated_at;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->name = $data['name'] ?? '';
        $this->vehicle_type = $data['vehicle_type'] ?? Vehicle::TYPE_BUS;
        $this->total_seats = (int) ($data['total_seats'] ?? 0);
        $this->layout_json = $data['layout_json'] ?? '';
        $this->created_at = $data['created_at'] ?? current_time('mysql');
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Get layout as array
     *
     * @return array
     */
    public function get_layout(): array
    {
        if (empty($this->layout_json)) {
            return [];
        }

        $decoded = json_decode($this->layout_json, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get seat codes from layout
     *
     * @return array
     */
    public function get_seat_codes(): array
    {
        $layout = $this->get_layout();
        $codes = [];

        foreach ($layout as $row) {
            if (!is_array($row)) {
                continue;
            }

            foreach ($row as $seat) {
                if ($seat && $seat !== 'driver' && $seat !== 'aisle' && $seat !== 'toilet') {
                    $codes[] = $seat;
                }
            }
        }

        return $codes;
    }

    /**
     * Get layout as visual grid
     *
     * @return array
     */
    public function get_grid(): array
    {
        return $this->get_layout();
    }

    /**
     * Validate layout JSON
     *
     * @param string $json
     * @return bool
     */
    public static function validate_layout_json(string $json): bool
    {
        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            return false;
        }

        // Check if it's a 2D array with valid seat codes
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                return false;
            }
        }

        return true;
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
            'vehicle_type' => $this->vehicle_type,
            'total_seats' => $this->total_seats,
            'layout_json' => $this->layout_json,
            'layout' => $this->get_layout(),
            'seat_codes' => $this->get_seat_codes(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Default 45-seat bus layout
     *
     * @return string JSON
     */
    public static function default_45_seat_layout(): string
    {
        $layout = [
            ['driver', null, 'A01', 'A02', 'A03', 'A04'],
            ['A05', 'A06', 'A07', 'A08', 'A09', 'A10'],
            ['A11', 'A12', 'A13', 'A14', 'A15', 'A16'],
            ['A17', 'A18', 'A19', 'A20', 'A21', 'A22'],
            ['A23', 'A24', 'A25', 'A26', 'A27', 'A28'],
            ['A29', 'A30', 'A31', 'A32', 'A33', 'A34'],
            ['A35', 'A36', 'A37', 'A38', 'A39', 'A40'],
            ['A41', 'A42', 'A43', 'A44', 'A45', null],
        ];

        return json_encode($layout);
    }

    /**
     * Default 29-seat bus layout
     *
     * @return string JSON
     */
    public static function default_29_seat_layout(): string
    {
        $layout = [
            ['driver', null, 'A01', 'A02', 'A03', 'A04'],
            ['A05', 'A06', 'A07', 'A08', 'A09', 'A10'],
            ['A11', 'A12', 'A13', 'A14', 'A15', 'A16'],
            ['A17', 'A18', 'A19', 'A20', 'A21', 'A22'],
            ['A23', 'A24', 'A25', 'A26', 'A27', 'A28'],
            ['A29', null, null, null, null, null],
        ];

        return json_encode($layout);
    }
}
