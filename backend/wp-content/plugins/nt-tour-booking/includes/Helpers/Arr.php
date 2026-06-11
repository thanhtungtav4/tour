<?php
/**
 * Array Helper Utilities
 *
 * Common array manipulation functions.
 *
 * @since 0.1.0
 */

namespace TourBooking\Helpers;

class Arr
{
    /**
     * Get a value from array using dot notation
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(array $array, string $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        // Check for dot notation
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = $array;

            foreach ($keys as $k) {
                if (!is_array($value) || !isset($value[$k])) {
                    return $default;
                }
                $value = $value[$k];
            }

            return $value;
        }

        return $default;
    }

    /**
     * Set a value in array using dot notation
     *
     * @param array&$array
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(array &$array, string $key, $value): void
    {
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $ref = &$array;

            foreach ($keys as $k) {
                if (!isset($ref[$k]) || !is_array($ref[$k])) {
                    $ref[$k] = [];
                }
                $ref =&$ref[$k];
            }

            $ref = $value;
        } else {
            $array[$key] = $value;
        }
    }

    /**
     * Check if key exists in array using dot notation
     *
     * @param array $array
     * @param string $key
     * @return bool
     */
    public static function has(array $array, string $key): bool
    {
        if (isset($array[$key])) {
            return true;
        }

        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = $array;

            foreach ($keys as $k) {
                if (!is_array($value) || !isset($value[$k])) {
                    return false;
                }
                $value = $value[$k];
            }

            return true;
        }

        return false;
    }

    /**
     * Only return specified keys from array
     *
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function only(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Remove specified keys from array
     *
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function except(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * Pluck values from array of arrays/objects
     *
     * @param array $array
     * @param string $key
     * @return array
     */
    public static function pluck(array $array, string $key): array
    {
        $results = [];

        foreach ($array as $item) {
            if (is_array($item) && isset($item[$key])) {
                $results[] = $item[$key];
            } elseif (is_object($item) && isset($item->$key)) {
                $results[] = $item->$key;
            }
        }

        return $results;
    }

    /**
     * Group array by key
     *
     * @param array $array
     * @param string $key
     * @return array
     */
    public static function group_by(array $array, string $key): array
    {
        $results = [];

        foreach ($array as $item) {
            $value = is_array($item) ? ($item[$key] ?? null) : ($item->$key ?? null);

            if ($value !== null) {
                $results[$value][] = $item;
            }
        }

        return $results;
    }

    /**
     * Filter array by callback
     *
     * @param array $array
     * @param callable $callback
     * @return array
     */
    public static function filter(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Map array with callback
     *
     * @param array $array
     * @param callable $callback
     * @return array
     */
    public static function map(array $array, callable $callback): array
    {
        return array_map($callback, $array);
    }
}
