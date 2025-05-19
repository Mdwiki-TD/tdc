<?php

namespace SQLorAPI\Get;

/*

Usage:

*/

// include_once __DIR__ . '/../actions/mdwiki_sql.php';
// include_once __DIR__ . '/../actions/td_api.php';

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

function super_function($api_params, $sql_params, $sql_query)
{
    global $use_td_api;
    // ---
    if ($use_td_api) {
        $data = get_td_api($api_params);
    } else {
        $data = fetch_query($sql_query, $sql_params);
    }
    // ---
    return $data;
}
