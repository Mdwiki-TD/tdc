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
 * use function App\Utils\Functions\test_print;
 *
 * // Debug output (only visible in test mode)
 * test_print("Debug message: " . $variable);
 *
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

namespace App\Utils\Functions;

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
