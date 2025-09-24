<?php

namespace Utils\Functions;
/*
Usage:
use function Utils\Functions\test_print;
use function Utils\Functions\start_with;
*/

$print_t = false;

if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    $print_t = true;
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

define('print_te', $print_t);

// use function APICalls\TDApi\get_td_api;

function test_print($s)
{
    if (isset($_COOKIE['test']) && $_COOKIE['test'] == 'x') {
        return;
    }

    $print_t = (isset($_REQUEST['test']) || isset($_COOKIE['test'])) ? true : false;

    if ($print_t && gettype($s) == 'string') {
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
