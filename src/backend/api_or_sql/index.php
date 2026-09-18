<?php

namespace SQLorAPI\Get;

use function APICalls\MdwikiSql\fetch_query;
use function APICalls\TDApi\get_td_api;

function use_td_api_or_sql(): bool
{
    static $use_td_api = null;
    if ($use_td_api === null) {
        $data = get_td_api(['get' => 'settings']);

        $settings_tabe = array_column($data, 'value', 'title');

        $use_td_api  = (($settings_tabe['use_td_api'] ?? "") == "1") ? true : false;

        if (isset($_GET['use_td_api'])) {
            $use_td_api  = $_GET['use_td_api'] != "x";
        }
    }
    return $use_td_api;
}

function isvalid($str)
{
    return !empty($str) && strtolower($str) != "all";
}

function super_function(
    array $api_params,
    array $sql_params,
    string $sql_query,
    bool $no_refind = false
): array {
    $use_td_api = use_td_api_or_sql();

    $api_data = ($use_td_api) ? get_td_api($api_params) : [];

    if (empty($api_data) && !$no_refind) {
        $api_data = fetch_query($sql_query, $sql_params);
    }

    return $api_data;
}
