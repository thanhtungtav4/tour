<?php
/**
 * Tour Model
 *
 * Wrapper class for WordPress tour posts.
 *
 * @since 0.1.0
 */

namespace TourBooking\Models;

class Tour
{
    public \WP_Post $post;

    public function __construct($post)
    {
        if (is_int($post)) {
            $post = get_post($post);
        }
        $this->post = $post;
    }

    public function get_id(): int
    {
        return $this->post->ID;
    }

    public function get_title(): string
    {
        return $this->post->post_title;
    }

    public function get_content(): string
    {
        return apply_filters('the_content', $this->post->post_content);
    }

    public function get_excerpt(): string
    {
        return get_the_excerpt($this->post);
    }

    public function get_slug(): string
    {
        return $this->post->post_name;
    }

    public function get_tour_code(): string
    {
        return get_post_meta($this->post->ID, 'tour_code', true) ?: '';
    }

    public function get_destination(): string
    {
        return get_post_meta($this->post->ID, 'destination', true) ?: '';
    }

    public function get_duration_days(): int
    {
        return (int) get_post_meta($this->post->ID, 'duration_days', true);
    }

    public function get_duration_nights(): int
    {
        return (int) get_post_meta($this->post->ID, 'duration_nights', true);
    }

    public function get_duration_string(): string
    {
        $days = $this->get_duration_days();
        $nights = $this->get_duration_nights();
        if ($days > 0 && $nights > 0) {
            return "{$days} ngày {$nights} đêm";
        } elseif ($days > 0) {
            return "{$days} ngày";
        }
        return '';
    }

    public function get_difficulty(): string
    {
        $terms = wp_get_post_terms($this->post->ID, 'nt_difficulty');
        return !empty($terms) ? $terms[0]->slug : 'medium';
    }

    public function get_price(): float
    {
        return (float) get_post_meta($this->post->ID, 'base_price', true);
    }

    public function get_price_formatted(): string
    {
        return number_format($this->get_price(), 0, '.', ',') . 'đ';
    }

    public function get_featured_image(string $size = 'large'): ?string
    {
        if (has_post_thumbnail($this->post->ID)) {
            return get_the_post_thumbnail_url($this->post->ID, $size);
        }
        return null;
    }

    public function get_image_filename(): string
    {
        $thumb_id = get_post_thumbnail_id($this->post->ID);
        if ($thumb_id) {
            $meta = wp_get_attachment_metadata($thumb_id);
            return $meta['file'] ?? basename(get_attached_file($thumb_id));
        }
        return '';
    }

    public function get_gallery(): array
    {
        $gallery_ids = get_post_meta($this->post->ID, 'gallery', true);
        $urls = [];
        if ($gallery_ids) {
            foreach (explode(',', $gallery_ids) as $id) {
                $url = wp_get_attachment_url((int) $id);
                if ($url) {
                    $urls[] = $url;
                }
            }
        }
        if (empty($urls)) {
            $featured = $this->get_featured_image();
            if ($featured) {
                $urls[] = $featured;
            }
        }
        return $urls;
    }

    public function get_departure_times(): array
    {
        $times = get_post_meta($this->post->ID, 'departure_times', true);
        if (is_array($times)) {
            return $times;
        }
        return $times ? explode(',', $times) : ['Sáng'];
    }

    public function get_highlights(): array
    {
        $json = get_post_meta($this->post->ID, 'highlights', true);
        if ($json) {
            $decoded = json_decode($json, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    public function get_itinerary(): array
    {
        $json = get_post_meta($this->post->ID, 'itinerary_json', true);
        if ($json) {
            $decoded = json_decode($json, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    public function get_included(): array
    {
        $raw = get_post_meta($this->post->ID, 'included', true);
        return $raw ? array_map('trim', explode("\n", $raw)) : [];
    }

    public function get_excluded(): array
    {
        $raw = get_post_meta($this->post->ID, 'excluded', true);
        return $raw ? array_map('trim', explode("\n", $raw)) : [];
    }

    public function get_notes(): string
    {
        return get_post_meta($this->post->ID, 'notes', true) ?: '';
    }

    public function get_services(): array
    {
        $json = get_post_meta($this->post->ID, 'services', true);
        if ($json) {
            $decoded = json_decode($json, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    public function get_next_departure_date(): ?string
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nt_tour_departures';
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT start_date FROM {$table} WHERE tour_id = %d AND status = 'open' AND start_date >= %s ORDER BY start_date ASC LIMIT 1",
            $this->post->ID,
            date('Y-m-d')
        ));
        return $result ?: null;
    }

    public function get_total_departures(): int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nt_tour_departures';
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE tour_id = %d AND status IN ('open', 'departed', 'completed')",
            $this->post->ID
        ));
    }

    public function get_available_spots(): int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nt_tour_departures';
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT SUM(capacity) as total_capacity, 
             (SELECT COUNT(*) FROM {$wpdb->prefix}nt_bookings b WHERE b.tour_departure_id = d.id AND b.booking_status IN ('pending_payment', 'confirmed')) as booked
             FROM {$table} d WHERE d.tour_id = %d AND d.status = 'open' AND d.start_date >= %s",
            $this->post->ID,
            date('Y-m-d')
        ), ARRAY_A);

        if (!$result || !$result['total_capacity']) {
            return 0;
        }
        return max(0, (int) $result['total_capacity'] - (int) $result['booked']);
    }

    public function get_rating(): float
    {
        return (float) get_post_meta($this->post->ID, 'rating', true) ?: 5.0;
    }

    public function get_review_count(): int
    {
        return (int) get_post_meta($this->post->ID, 'review_count', true) ?: 0;
    }

    public function get_departure_dates(): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nt_tour_departures';
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT id, start_date, capacity, status,
             (SELECT COUNT(*) FROM {$wpdb->prefix}nt_bookings b WHERE b.tour_departure_id = d.id AND b.booking_status IN ('pending_payment', 'confirmed')) as booked
             FROM {$table} d WHERE d.tour_id = %d AND d.status = 'open' AND d.start_date >= %s ORDER BY d.start_date ASC LIMIT 30",
            $this->post->ID,
            date('Y-m-d')
        ), ARRAY_A);

        $dates = [];
        foreach ($results as $row) {
            $dates[] = [
                'date' => $row['start_date'],
                'available_spots' => max(0, (int) $row['capacity'] - (int) $row['booked']),
                'total_spots' => (int) $row['capacity'],
                'status' => $row['status'] === 'open' ? 'available' : 'full',
            ];
        }
        return $dates;
    }

    public function get_pickup_points(): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nt_pickup_points';
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT id, name, address FROM {$table} WHERE status = 'active' ORDER BY sort_order ASC"
        ), ARRAY_A);

        return $results ?: [];
    }

    /**
     * Convert to array for API response (public tour list)
     */
    public function to_list_array(): array
    {
        return [
            'id' => $this->get_id(),
            'slug' => $this->get_slug(),
            'name' => $this->get_title(),
            'description' => $this->get_excerpt(),
            'thumbnail' => $this->get_featured_image(),
            'gallery' => $this->get_gallery(),
            'image_filename' => $this->get_image_filename(),
            'price' => $this->get_price(),
            'price_formatted' => $this->get_price_formatted(),
            'difficulty' => $this->get_difficulty(),
            'duration' => $this->get_duration_string(),
            'available_spots' => $this->get_available_spots(),
            'departure_times' => $this->get_departure_times(),
            'highlights' => $this->get_highlights(),
            'next_departure_date' => $this->get_next_departure_date(),
            'total_departures' => $this->get_total_departures(),
            'rating' => $this->get_rating(),
            'review_count' => $this->get_review_count(),
        ];
    }

    /**
     * Convert to array for API response (tour detail)
     */
    public function to_array(): array
    {
        return [
            'id' => $this->get_id(),
            'slug' => $this->get_slug(),
            'name' => $this->get_title(),
            'description' => $this->get_excerpt(),
            'content' => $this->get_content(),
            'thumbnail' => $this->get_featured_image(),
            'gallery' => $this->get_gallery(),
            'image_filename' => $this->get_image_filename(),
            'price' => $this->get_price(),
            'price_formatted' => $this->get_price_formatted(),
            'difficulty' => $this->get_difficulty(),
            'duration' => $this->get_duration_string(),
            'departure_times' => $this->get_departure_times(),
            'highlights' => $this->get_highlights(),
            'itinerary' => $this->get_itinerary(),
            'included' => $this->get_included(),
            'excluded' => $this->get_excluded(),
            'notes' => $this->get_notes(),
            'services' => $this->get_services(),
            'departure_dates' => $this->get_departure_dates(),
            'pickup_points' => $this->get_pickup_points(),
            'rating' => $this->get_rating(),
            'review_count' => $this->get_review_count(),
        ];
    }

    public static function all(array $args = []): array
    {
        $defaults = [
            'post_type' => 'nt_tour',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ];
        $args = wp_parse_args($args, $defaults);
        $posts = get_posts($args);
        return array_map(fn($post) => new self($post), $posts);
    }

    public static function find(int $id): ?self
    {
        $post = get_post($id);
        if (!$post || $post->post_type !== 'nt_tour') {
            return null;
        }
        return new self($post);
    }

    public static function find_by_slug(string $slug): ?self
    {
        $args = [
            'name' => $slug,
            'post_type' => 'nt_tour',
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ];
        $posts = get_posts($args);
        if (empty($posts)) {
            return null;
        }
        return new self($posts[0]);
    }
}