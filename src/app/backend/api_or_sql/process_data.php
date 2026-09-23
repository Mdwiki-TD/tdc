<?php

namespace App\SQLorAPI\Process;

use function App\SQLorAPI\Get\super_function;
use function App\SQLorAPI\Get\isvalid;

function get_users_process_new(): array
{

    static $processNew = [];

    if (!empty($processNew)) {
        return $processNew;
    }

    // ttp://localhost:9002/api.php?get=in_process&distinct=true&limit=50&group=user&order=count&select=count

    $apiParams = ['get' => 'in_process', 'distinct' => 'true', "select" => 'user', 'group' => 'user', "order" => '2', "count" => '*'];

    $sql_t = 'select DISTINCT user, count(*) as count from in_process group by user order by count desc';

    $tab = super_function($apiParams, [], $sql_t);

    $processNew = array_column($tab, 'count', 'user');

    return $processNew;
}

function get_lang_in_process_new($code, $year_y = "all"): array
{

    static $cache = [];

    if (!empty($cache[$code] ?? [])) {
        return $cache[$code];
    }

    /*
    SELECT * from in_process ip
        WHERE NOT EXISTS (
        SELECT p.user FROM pages p
        where p.title = ip.title
        and p.lang = ip.lang
        and p.target != ""
        )
    */

    $query = "select * from in_process where lang = ?";

    $apiParams = ['get' => 'in_process', 'lang' => $code];

    $params = [$code];

    if (isvalid($year_y)) {
        $query .= " AND YEAR(add_date) = ?";
        $params[] = $year_y;
        $apiParams['year'] = $year_y;
    }

    $data = super_function($apiParams, $params, $query, true);

    // $cache[$code] = array_column($data, 'title');
    $cache[$code] = $data;

    return $cache[$code];
}
