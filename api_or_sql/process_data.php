<?php

namespace SQLorAPI\Process;

/*

Usage:

use function SQLorAPI\Process\get_process_all_new;
use function SQLorAPI\Process\get_users_process_new;
use function SQLorAPI\Process\get_lang_in_process_new;
*/

use function SQLorAPI\Get\super_function;

$data_index = [];

function get_process_all_new()
{
    // ---
    static $process_all = [];
    // ---
    if (!empty($process_all)) {
        return $process_all;
    }
    // ---
    $api_params = ['get' => 'in_process', 'limit' => "100", 'order' => 'add_date'];
    $sql_t = "select * from in_process ORDER BY add_date DESC limit 100";
    //---
    $process_all = super_function($api_params, [], $sql_t);
    // ---
    return $process_all;
}

function get_users_process_new()
{
    // ---
    static $process_new = [];
    // ---
    if (!empty($process_new)) {
        return $process_new;
    }
    // ---
    // ttp://localhost:9002/api.php?get=in_process&distinct=true&limit=50&group=user&order=count&select=count
    $api_params = ['get' => 'in_process', 'distinct' => 'true', 'group' => 'user', 'order' => 'count', 'select' => 'count'];
    // ---
    $sql_t = 'select DISTINCT user, count(*) as count from in_process group by user order by count desc';
    // ---
    $tab = super_function($api_params, [], $sql_t);
    // ---
    $process_new = array_column($tab, 'count', 'user');
    //---
    return $process_new;
}

function get_lang_in_process_new($lang)
{
    // ---
    global $data_index;
    // ---
    if (!empty($data_index[$lang] ?? [])) {
        return $data_index[$lang];
    }
    // ---
    $sql_t = 'select * from in_process where lang = ?';
    // ---
    $tab = super_function(['get' => 'in_process', 'lang' => $lang], [$lang], $sql_t);
    // ---
    $data_index[$lang] = array_column($tab, 'title');
    //---
    return $data_index[$lang];
}
