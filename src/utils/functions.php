<?php

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
