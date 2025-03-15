<?php

namespace Actions\Functions;
/*
Usage:
use function Actions\Functions\test_print;
use function Actions\Functions\open_json_file;
use function Actions\Functions\start_with;
*/

$print_t = false;

if (isset($_REQUEST['test'])) {
    $print_t = true;
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

define('print_te', $print_t);

// include_once __DIR__ . '/wiki_api.php';
// include_once __DIR__ . '/mdwiki_api.php';
// include_once __DIR__ . '/mdwiki_sql.php';
// include_once __DIR__ . '/td_api.php';
// include_once __DIR__ . '/../api_or_sql/include.php';

// use function Actions\TDApi\get_td_api;

function test_print($s)
{
    $print_t = (isset($_REQUEST['test']) || isset($_COOKIE['test'])) ? true : false;

    if ($print_t && gettype($s) == 'string') {
        echo "\n<br>\n$s";
    } elseif ($print_t) {
        echo "\n<br>\n";
        print_r($s);
    }
}
function open_json_file($file_path)
{
    $new_list = array();
    // Check if the file exists
    if (!is_file($file_path)) {
        // Handle the case when the file does not exist
        test_print("$file_path does not exist");
        return $new_list; // Return an empty list
    }

    // Attempt to read the file contents
    $text = file_get_contents($file_path);

    // Check if file_get_contents was successful
    if ($text === false) {
        // Handle the case when file_get_contents fails
        test_print("Failed to read file contents from $file_path");
        return $new_list; // Return an empty list
    }

    // Attempt to decode JSON
    $data = json_decode($text, true);

    // Check if json_decode was successful
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        // Handle the case when json_decode fails
        test_print("Failed to decode JSON from $file_path");
        return $new_list; // Return an empty list
    }

    // Return the decoded data
    // test_print("Successfully decoded JSON from $file_path. " . count($data) . " ");
    return $data;
}

function start_with($haystack, $needle)
{
    return strpos($haystack, $needle) === 0;
};
