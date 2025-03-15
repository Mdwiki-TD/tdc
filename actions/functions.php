<?php

namespace Actions\Functions;
/*
Usage:
use function Actions\Functions\test_print;
*/

$print_t = false;

if (isset($_REQUEST['test'])) {
    $print_t = true;
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

define('print_te', $print_t);

include_once __DIR__ . '/html.php';
include_once __DIR__ . '/wiki_api.php';
include_once __DIR__ . '/mdwiki_api.php';
include_once __DIR__ . '/mdwiki_sql.php';
include_once __DIR__ . '/td_api.php';
include_once __DIR__ . '/../api_or_sql/index.php';

/**
 * Conditionally prints the given value if debugging output is enabled.
 *
 * When the constant `print_te` is true, the function outputs the provided data. If the value is a string,
 * it is echoed with HTML line breaks. For non-string values, the function outputs a formatted representation
 * using print_r, preceded by an HTML line break.
 *
 * @param mixed $s The value to be printed.
 */

function test_print($s)
{
    if (print_te && gettype($s) == 'string') {
        echo "\n<br>\n$s";
    } elseif (print_te) {
        echo "\n<br>\n";
        print_r($s);
    }
}
