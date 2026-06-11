<?php
/**
 * AccessToken Repository
 *
 * Data access for booking access tokens (magic links).
 *
 * @since 0.1.0
 */

namespace TourBooking\Repositories;

class AccessTokenRepository
{
    protected string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'nt_booking_access_tokens';
    }

    /**
     * Find token by hash
     *
     * @param string $token_hash
     * @return array|null
     */
    public function find_by_hash(string $token_hash): ?array
    {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE token_hash = %s",
                $token_hash
            ),
            ARRAY_A
        );
    }

    /**
     * Find token by booking ID and purpose
     *
     * @param int $booking_id
     * @param string $purpose
     * @return array|null
     */
    public function find_by_booking_purpose(int $booking_id, string $purpose): ?array
    {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE booking_id = %d AND purpose = %s",
                $booking_id,
                $purpose
            ),
            ARRAY_A
        );
    }

    /**
     * Create a new token
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        global $wpdb;

        $data['created_at'] = current_time('mysql');

        $result = $wpdb->insert($this->table, $data, [
            '%d', '%s', '%s', '%s', '%s', '%s', '%s',
        ]);

        if ($result === false) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update last accessed time
     *
     * @param int $id
     * @return bool
     */
    public function touch(int $id): bool
    {
        global $wpdb;

        $result = $wpdb->update(
            $this->table,
            ['last_accessed_at' => current_time('mysql')],
            ['id' => $id],
            ['%s'],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Delete token
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        global $wpdb;

        $result = $wpdb->delete($this->table, ['id' => $id], ['%d']);

        return $result !== false;
    }

    /**
     * Delete by booking
     *
     * @param int $booking_id
     * @return bool
     */
    public function delete_by_booking(int $booking_id): bool
    {
        global $wpdb;

        $result = $wpdb->delete($this->table, ['booking_id' => $booking_id], ['%d']);

        return $result !== false;
    }

    /**
     * Check if token is valid (exists, not expired)
     *
     * @param string $token_hash
     * @return bool
     */
    public function is_valid(string $token_hash): bool
    {
        $token = $this->find_by_hash($token_hash);

        if (!$token) {
            return false;
        }

        // Check expiry
        if ($token['expires_at'] && strtotime($token['expires_at']) < time()) {
            return false;
        }

        return true;
    }

    /**
     * Get expiry date based on mode
     *
     * @param string $departure_date Departure date
     * @param string $mode 'departure_plus_1_day' or custom
     * @return string
     */
    public static function calculate_expiry(string $departure_date, string $mode = 'departure_plus_1_day'): string
    {
        switch ($mode) {
            case 'departure_plus_1_day':
                return date('Y-m-d 23:59:59', strtotime($departure_date) + DAY_IN_SECONDS);
            default:
                return date('Y-m-d 23:59:59', strtotime($departure_date) + DAY_IN_SECONDS);
        }
    }
}
