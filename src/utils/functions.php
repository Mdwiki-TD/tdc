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
    if (isset($_COOKIE['test']) && $_COOKIE['test'] == 'x') {
        return;
    }

    $print_t = (isset($_REQUEST['test']) || isset($_COOKIE['test'])) ? true : false;

    if ($print_t && is_string($s)) {
        echo "\n<br>\n$s";
    } elseif ($print_t) {
        echo "\n<br>\n";
        print_r($s);
    }
}

function start_with($haystack, $needle)
{
    return strpos($haystack, $needle) === 0;
};
