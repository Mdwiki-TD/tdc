<?php

namespace SQLorAPI\Process;

use function SQLorAPI\Get\super_function;
use function SQLorAPI\Get\isvalid;

function get_users_process_new(): array
{
    // ---
    static $process_new = [];
    // ---
    if (!empty($process_new)) {
        return $process_new;
    }
    // ---
    // ttp://localhost:9002/api.php?get=in_process&distinct=true&limit=50&group=user&order=count&select=count
    // ---
    $api_params = ['get' => 'in_process', 'distinct' => 'true', "select" => 'user', 'group' => 'user', "order" => '2', "count" => '*'];
    // ---
    $sql_t = 'select DISTINCT user, count(*) as count from in_process group by user order by count desc';
    // ---
    $tab = super_function($api_params, [], $sql_t);
    // ---
    $process_new = array_column($tab, 'count', 'user');
    //---
    return $process_new;
}

function get_lang_in_process_new($code, $year_y = "all"): array
{
    // ---
    static $cache = [];
    // ---
    if (!empty($cache[$code] ?? [])) {
        return $cache[$code];
    }
    // ---
    /*
    SELECT * from in_process ip
        WHERE NOT EXISTS (
        SELECT p.user FROM pages p
        where p.title = ip.title
        and p.lang = ip.lang
        and p.target != ""
        )
    */
    // ---
    $query = "select * from in_process where lang = ?";
    // ---
    $api_params = ['get' => 'in_process', 'lang' => $code];
    // ---
    $params = [$code];
    // ---
    if (isvalid($year_y)) {
        $query .= " AND YEAR(add_date) = ?";
        $params[] = $year_y;
        $api_params['year'] = $year_y;
    }
    // ---
    $data = super_function($api_params, $params, $query, "in_process", true);
    // ---
    // $cache[$code] = array_column($data, 'title');
    $cache[$code] = $data;
    //---
    return $cache[$code];
}
