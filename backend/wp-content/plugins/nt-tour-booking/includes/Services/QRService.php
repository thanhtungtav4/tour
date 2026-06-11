<?php
/**
 * QR Service
 *
 * Generates QR codes for passenger check-in.
 *
 * @since 0.1.0
 */

namespace TourBooking\Services;

use TourBooking\Helpers\TokenGenerator;
use TourBooking\Repositories\PassengerRepository;

class QRService
{
    protected PassengerRepository $passenger_repo;

    public function __construct()
    {
        $this->passenger_repo = new PassengerRepository();
    }

    /**
     * Generate QR token for a passenger
     *
     * @param int $passenger_id
     * @return string Token
     */
    public function generate_token(int $passenger_id): string
    {
        $raw_token = TokenGenerator::generate_short();
        $token_hash = TokenGenerator::hash($raw_token);

        // Save hash to passenger
        $this->passenger_repo->set_qr_token($passenger_id, $token_hash);

        // Log
        \TourBooking\ActivityLogger::log_qr_generated($passenger_id);

        return $raw_token;
    }

    /**
     * Get QR URL for a passenger
     *
     * @param int $passenger_id
     * @return string|null
     */
    public function get_qr_url(int $passenger_id): ?string
    {
        $passenger = $this->passenger_repo->find($passenger_id);

        if (!$passenger || !$passenger->has_qr()) {
            return null;
        }

        // We can't reverse the hash, so we need a different approach
        // Store the raw token encrypted or use a different method
        // For now, we'll regenerate a new token
        return null;
    }

    /**
     * Generate and get QR data URL
     *
     * @param int $passenger_id
     * @return array ['token' => string, 'qr_url' => string]|null
     */
    public function generate_and_get_qr(int $passenger_id): ?array
    {
        $passenger = $this->passenger_repo->find($passenger_id);

        if (!$passenger || !$passenger->can_generate_qr()) {
            return null;
        }

        $token = $this->generate_token($passenger_id);
        $qr_url = TokenGenerator::generate_qr_url($token);

        return [
            'token' => $token,
            'qr_url' => $qr_url,
        ];
    }

    /**
     * Generate QR code as base64 image
     *
     * @param string $data
     * @param int $size
     * @return string Base64 PNG image
     */
    public function generate_qr_image(string $data, int $size = 200): string
    {
        // Using Google Chart API for simplicity (no external library needed)
        // For production, consider using a proper QR library
        $encoded = urlencode($data);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encoded}";
    }

    /**
     * Generate QR code for all passengers in a booking
     *
     * @param int $booking_id
     * @return array Array of passenger QR data
     */
    public function generate_for_booking(int $booking_id): array
    {
        $passengers = $this->passenger_repo->get_by_booking($booking_id);
        $results = [];

        foreach ($passengers as $passenger) {
            if ($passenger->can_generate_qr()) {
                $qr_data = $this->generate_and_get_qr($passenger->id);
                if ($qr_data) {
                    $qr_data['passenger_id'] = $passenger->id;
                    $qr_data['passenger_name'] = $passenger->name;
                    $qr_data['seat_code'] = $passenger->seat_code;
                    $results[] = $qr_data;
                }
            }
        }

        return $results;
    }

    /**
     * Validate QR token
     *
     * @param string $token
     * @return array|null Passenger data if valid
     */
    public function validate_token(string $token): ?array
    {
        $token_hash = TokenGenerator::hash($token);

        global $wpdb;
        $table = $wpdb->prefix . 'nt_booking_passengers';

        $passenger = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE qr_token_hash = %s",
                $token_hash
            ),
            ARRAY_A
        );

        if (!$passenger) {
            return null;
        }

        return $passenger;
    }
}
