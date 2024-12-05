<?php
namespace Actions\Functions;
/*
Usage:
use function Actions\Functions\test_print;
*/
use function Actions\MdwikiSql\fetch_query;

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

// use function Actions\TDApi\get_td_api;

function test_print($s) {
    if (print_te && gettype($s) == 'string') {
        echo "\n<br>\n$s";
    } elseif (print_te) {
        echo "\n<br>\n";
        print_r($s);
    }
}

$coordinators = fetch_query("SELECT user FROM coordinator;");
// $coordinators = get_td_api (array('get' => 'coordinator', 'select' => 'user'));

$coordinators = array_map('current', $coordinators);

// var_dump(json_encode($coordinators2, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

