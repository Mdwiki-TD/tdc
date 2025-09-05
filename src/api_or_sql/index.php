<?php

namespace SQLorAPI\Get;

/*

Usage:

*/

use function Actions\MdwikiSql\fetch_query;
use function Actions\TDApi\get_td_api;

$settings_tabe = array_column(get_td_api(['get' => 'settings']), 'value', 'title');
//---
$use_td_api  = (($settings_tabe['use_td_api'] ?? "") == "1") ? true : false;
// ---
$use_td_api  = false;
// ---
if (isset($_GET['use_td_api'])) {
    $use_td_api  = $_GET['use_td_api'] != "x";
}

include_once __DIR__ . '/process_data.php';
include_once __DIR__ . '/recent_data.php';
include_once __DIR__ . '/funcs.php';

function super_function(array $api_params, array $sql_params, string $sql_query): array
{
    global $use_td_api;
    // ---
    $data = ($use_td_api) ? get_td_api($api_params) : [];
    // ---
    if (empty($data)) {
        $data = fetch_query($sql_query, $sql_params);
    }
    // ---
    return $data;
}

function super_function_new(array $api_params, array $sql_params, string $sql_query, string $table_name): array
{
    global $use_td_api;
    // ---
    $data = ($use_td_api) ? get_td_api($api_params) : [];
    // ---
    if (empty($data)) {
        $data = fetch_query($sql_query, $sql_params, $table_name);
    }
    // ---
    return $data;
}
