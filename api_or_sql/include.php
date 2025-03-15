<?php

// include_once __DIR__ . '/../actions/mdwiki_sql.php';
// include_once __DIR__ . '/../actions/td_api.php';

use function Actions\MdwikiSql\fetch_query;
use function Actions\TDApi\get_td_api;

$settings_tabe = array_column(get_td_api(['get' => 'settings']), 'value', 'title');
//---
$use_td_api  = (($settings_tabe['use_td_api'] ?? "") == "1") ? true : false;
$use_td_api  = false;   // false in tdc
// ---
$use_in_process_table  = (($settings_tabe['use_td_api'] ?? "") == "1") ? true : false;

include_once __DIR__ . '/index.php';
include_once __DIR__ . '/process_data.php';
include_once __DIR__ . '/recent_data.php';
