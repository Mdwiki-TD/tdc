<?php

declare(strict_types=1);

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

/**
 * Global constant indicating if test mode is enabled
 * 
 * Use this constant to conditionally execute debug code:
 * ```php
 * if (print_te) {
 *     // Debug-only code
 * }
 * ```
 * 
 * @var bool
 */
define('print_te', $print_t);

/**
 * Print debug information when test mode is enabled
 * 
 * Outputs debug information to the response when test mode is active.
 * The output is automatically formatted based on the input type:
 * - Strings are printed directly with line breaks
 * - Arrays and objects are printed using print_r()
 * 
 * Security Note: Debug output should never be enabled in production.
 * The 'x' cookie value suppresses output even in test mode.
 * 
 * @param mixed $s The value to print (string, array, object, etc.)
 * 
 * @return void Output is printed directly, nothing is returned
 * 
 * @example
 * ```php
 * // Print a debug message
 * test_print("Processing user: " . $username);
 * 
 * // Debug an array structure
 * test_print($userData);
 * 
 * // Debug with context
 * test_print("API Response: " . json_encode($response));
 * ```
 */
function test_print(mixed $s): void
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

/**
 * Check if a string starts with a given prefix
 * 
 * Performs a case-sensitive check to determine if the haystack string
 * begins with the needle string.
 * 
 * @param string $haystack The string to search in
 * @param string $needle   The prefix to search for
 * 
 * @return bool True if haystack starts with needle, false otherwise
 * 
 * @example
 * ```php
 * // Check URL protocol
 * if (start_with($url, 'https://')) {
 *     echo "Secure connection";
 * }
 * 
 * // Check file extension
 * if (start_with($filename, 'backup_')) {
 *     echo "This is a backup file";
 * }
 * 
 * // Validate input format
 * $isValid = start_with($code, 'Q');  // Wikidata QID check
 * ```
 */
function start_with(string $haystack, string $needle): bool
{
    return str_starts_with($haystack, $needle);
}

/**
 * Safely get a value from an array with a default
 * 
 * Retrieves a value from an array using a key, returning a default
 * if the key doesn't exist or the value is null.
 * 
 * @template T
 * @param array<string|int, T> $array   The array to search
 * @param string|int           $key     The key to look up
 * @param T|null               $default The default value if key not found
 * 
 * @return T|null The value if found, default otherwise
 * 
 * @example
 * ```php
 * $name = array_get($_POST, 'username', 'Anonymous');
 * $page = array_get($_GET, 'page', 1);
 * ```
 */
function array_get(array $array, string|int $key, mixed $default = null): mixed
{
    return array_key_exists($key, $array) ? $array[$key] : $default;
}

/**
 * Escape HTML special characters safely
 * 
 * Convenience wrapper around htmlspecialchars with secure defaults.
 * 
 * @param string $string The string to escape
 * 
 * @return string The escaped string
 */
function html_escape(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

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
