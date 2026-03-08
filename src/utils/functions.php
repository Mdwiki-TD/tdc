<?php

/**
 * Utility Functions Module
 *
 * Provides common utility functions used throughout the Translation Dashboard
 * application. These functions handle debugging, string manipulation, and
 * other general-purpose operations.
 *
 * Features:
 * - Conditional debug printing based on test mode
 * - String prefix checking utilities
 * - Test mode detection and configuration
 *
 * Usage Example:
 * ```php
 * use function Utils\Functions\test_print;
 * use function Utils\Functions\start_with;
 *
 * // Debug output (only visible in test mode)
 * test_print("Debug message: " . $variable);
 *
 * // Check if string starts with prefix
 * if (start_with($url, 'https://')) {
 *     // Handle secure URL
 * }
 * ```
 *
 * Environment Variables:
 * - test (REQUEST/COOKIE): Enables debug mode when set
 *
 * @package    Utils
 * @subpackage Functions
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

namespace Utils\Functions;

/**
 * @var bool $print_t Global flag indicating if debug printing is enabled
 * @global
 */
$print_t = false;

// Initialize debug mode based on request parameters or cookies
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    $print_t = true;
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

function test_print($s)
{
    // Suppress output when cookie is explicitly 'x'
    if (isset($_COOKIE['test']) && $_COOKIE['test'] === 'x') {
        return;
    }

    $print_t = (isset($_REQUEST['test']) || isset($_COOKIE['test']));

    if ($print_t && is_string($s)) {
        echo "\n<br>\n" . htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    } elseif ($print_t) {
        echo "\n<br>\n<pre>";
        print_r($s);
        echo "</pre>";
    }
}

function start_with($haystack, $needle)
{
    return strpos($haystack, $needle) === 0;
};

/**
 * Check if current environment is development/localhost
 *
 * @return bool True if running in development environment
 */
function is_development(): bool
{
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    return $serverName === 'localhost'
        || str_starts_with($serverName, '127.')
        || str_starts_with($serverName, '192.168.');
}
