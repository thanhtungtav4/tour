<?php
/**
 * ActivityLogger autoload bridge.
 *
 * The concrete ActivityLogger class currently lives in includes/Helpers/ActivityLogger.php
 * but is declared as TourBooking\ActivityLogger. Composer PSR-4 autoload therefore
 * expects this bridge file at includes/ActivityLogger.php.
 *
 * @since 0.1.0
 */

namespace TourBooking;

require_once __DIR__ . '/Helpers/ActivityLogger.php';
