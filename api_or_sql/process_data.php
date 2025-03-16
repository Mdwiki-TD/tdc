<?php

namespace SQLorAPI\Process;

/*

Usage:

use function SQLorAPI\Process\get_process_all;
use function SQLorAPI\Process\get_users_process;
use function SQLorAPI\Process\get_users_process_new;
use function SQLorAPI\Process\get_lang_in_process;
*/

use function Actions\MdwikiSql\fetch_query;
use function Actions\TDApi\get_td_api;

$data_index = [];

function get_process_all()
{
    // ---
    global $use_td_api;
    // ---
    static $process_all = [];
    // ---
    if (!empty($process_all)) {
        return $process_all;
    }
    // ---
    if ($use_td_api) {
        $process_all = get_td_api(['get' => 'pages', 'order' => 'date', 'target' => 'empty', 'limit' => "100"]);
    } else {
        $sql_t = "select * from pages where (target = '' OR target IS NULL) ORDER BY date DESC limit 100";
        $process_all = fetch_query($sql_t);
    }
    //---
    return $process_all;
}

function get_users_process_new()
{
    // ---
    global $use_td_api;
    // ---
    static $process_new = [];
    // ---
    if (!empty($process_new)) {
        return $process_new;
    }
    // ---
    if ($use_td_api) {
        $res = get_td_api(['get' => 'in_process']);
        $result = [];
        foreach ($res as $t) {
            $user = $t['user'] ?? "";
            if (isset($result[$user])) {
                $result[$user] += 1;
            } else {
                $result[$user] = 1;
            };
        }
        $process_new = $result;
    } else {
        $sql_t = 'select DISTINCT user, count(*) as count from in_process group by user order by count desc';
        $tab = fetch_query($sql_t);
        $process_new = array_column($tab, 'count', 'user');
    }
    //---
    return $process_new;
}

function get_users_process()
{
    // ---
    global $use_td_api;
    // ---
    static $users_process = [];
    // ---
    if (!empty($users_process)) {
        return $users_process;
    }
    // ---
    if ($use_td_api) {
        $tab = get_td_api(['get' => 'count_pages', 'distinct' => 1, 'target' => 'empty']);
        $users_process = array_column($tab, 'count', 'user');
    } else {
        $sql_t = 'select DISTINCT user, count(target) as count from pages where (target = "" OR target IS NULL) group by user order by count desc';
        $tab = fetch_query($sql_t);
        $users_process = array_column($tab, 'count', 'user');
    }
    //---
    return $users_process;
}

function get_lang_in_process($lang)
{
    // ---
    global $use_td_api, $data_index;
    // ---
    if (!empty($data_index[$lang] ?? [])) {
        return $data_index[$lang];
    }
    // ---
    if ($use_td_api) {
        $tab = get_td_api(['get' => 'pages', 'lang' => $lang, 'target' => 'empty', 'select' => "title"]);
    } else {
        // select * from pages where (target = '' OR target IS NULL) and lang = '$code'
        $sql_t = 'select * from pages where (target = "" OR target IS NULL) and lang = ?';
        $tab = fetch_query($sql_t, [$lang]);
    }
    //---
    $data_index[$lang] = array_column($tab, 'title');
    //---
    return $data_index[$lang];
}
