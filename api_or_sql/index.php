<?php

namespace SQLorAPI\Get;

/*

Usage:

use function SQLorAPI\Get\isvalid;
use function SQLorAPI\Get\get_coordinator;
use function SQLorAPI\Get\get_td_or_sql_settings;
use function SQLorAPI\Get\get_td_or_sql_qids;
use function SQLorAPI\Get\get_td_or_sql_qids_others;
use function SQLorAPI\Get\get_td_or_sql_categories;
use function SQLorAPI\Get\get_users_by_last_pupdate;
use function SQLorAPI\Get\get_td_or_sql_count_pages_not_empty;
use function SQLorAPI\Get\get_td_or_sql_page_user_not_in_users;
use function SQLorAPI\Get\get_td_or_sql_full_translators;
use function SQLorAPI\Get\get_td_or_sql_projects;
use function SQLorAPI\Get\get_pages_langs;
use function SQLorAPI\Get\td_or_sql_titles_infos;
use function SQLorAPI\Get\super_function;
*/

use function Actions\MdwikiSql\fetch_query;
use function Actions\TDApi\get_td_api;

$data_index = [];

function isvalid($str)
{
    return !empty($str) && $str != 'All' && $str != 'all';
}

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

function get_td_or_sql_categories()
{
    // ---
    static $categories = [];
    // ---
    if (!empty($categories ?? [])) {
        return $categories;
    }
    // ---
    $api_params = ['get' => 'categories'];
    $query = "select id, category, category2, campaign, depth, def from categories";
    //---
    $data = super_function($api_params, [], $query);
    // ---
    $categories = $data;
    // ---
    return $categories;
}

function get_coordinator()
{
    // ---
    static $coordinator = [];
    // ---
    if (!empty($coordinator ?? [])) {
        return $coordinator;
    }
    // ---
    $api_params = ['get' => 'coordinator'];
    $query = "SELECT id, user FROM coordinator order by id";
    //---
    $data = super_function($api_params, [], $query);
    // ---
    $coordinator = $data;
    // ---
    return $data;
}

function get_users_by_last_pupdate()
{
    // ---
    static $last_user_to_tab = [];
    // ---
    if (!empty($last_user_to_tab ?? [])) {
        return $last_user_to_tab;
    }
    // ---
    $data = [];
    // ---
    $api_params = array('get' => 'users_by_last_pupdate');
    $query_old = <<<SQL
        select DISTINCT p1.target, p1.title, p1.user, p1.pupdate, p1.lang
        from pages p1
        where target != ''
        and p1.pupdate = (select p2.pupdate from pages p2 where p2.user = p1.user ORDER BY p2.pupdate DESC limit 1)
        group by p1.user
        ORDER BY p1.pupdate DESC
    SQL;
    //---
    $query = <<<SQL
        WITH RankedPages AS (
            SELECT
                p1.target,
                p1.user,
                p1.pupdate,
                p1.lang,
                p1.title,
                ROW_NUMBER() OVER (PARTITION BY p1.user ORDER BY p1.pupdate DESC) AS rn
            FROM pages p1
            WHERE p1.target != ''
        )
        SELECT target, user, pupdate, lang, title
        FROM RankedPages
        WHERE rn = 1
        ORDER BY pupdate DESC;
    SQL;
    //---
    $data = super_function($api_params, [], $query);
    // ---
    foreach ($data as $key => $gg) {
        $last_user_to_tab[$gg['user']] = $gg;
    }
    // ---
    return $last_user_to_tab;
}

function get_td_or_sql_count_pages_not_empty()
{
    // ---
    static $count_pages = [];
    // ---
    if (!empty($count_pages ?? [])) {
        return $count_pages;
    }
    // ---
    $api_params = array('get' => 'count_pages', 'target' => 'not_empty');
    $query = <<<SQL
        select DISTINCT user, count(target) as count from pages where target != '' group by user order by count desc
    SQL;
    //---
    $data = super_function($api_params, [], $query);
    // ---
    $data = array_column($data, 'count', 'user');
    // ---
    arsort($data);
    // ---
    // print_r($data);
    // ---
    $count_pages = $data;
    // ---
    return $data;
}

function get_td_or_sql_page_user_not_in_users()
{
    // ---
    static $users = [];
    // ---
    if (!empty($users ?? [])) {
        return $users;
    }
    // ---
    $sql_params = [];
    $api_params = array('get' => 'pages', 'distinct' => 1, 'select' => 'user');
    $query = <<<SQL
        select DISTINCT user from pages WHERE NOT EXISTS (SELECT 1 FROM users WHERE user = username)
    SQL;
    //---
    $data = super_function($api_params, $sql_params, $query);
    // ---
    $data = array_column($data, 'user');
    // ---
    $users = $data;
    // ---
    return $data;
}

function get_td_or_sql_users_no_inprocess()
{
    // ---
    static $users = [];
    // ---
    if (!empty($users)) return $users;
    // ---
    $sql_params = [];
    $api_params = ['get' => 'users_no_inprocess'];
    $query = "SELECT * FROM users_no_inprocess order by id";
    //---
    $users = super_function($api_params, $sql_params, $query);
    // ---
    return $users;
}

function get_td_or_sql_full_translators()
{
    // ---
    static $full_translators = [];
    // ---
    if (!empty($full_translators)) return $full_translators;
    // ---
    $sql_params = [];
    $api_params = ['get' => 'full_translators'];
    $query = "SELECT * FROM full_translators order by id";
    //---
    $full_translators = super_function($api_params, $sql_params, $query);
    // ---
    return $full_translators;
}

function get_td_or_sql_projects()
{
    // ---
    static $projects = [];
    // ---
    if (!empty($projects ?? [])) {
        return $projects;
    }
    // ---
    $sql_params = [];
    $api_params = ['get' => 'projects'];
    $query = "select g_id, g_title from projects";
    //---
    $data = super_function($api_params, $sql_params, $query);
    // ---
    $projects = $data;
    // ---
    return $data;
}

function get_td_or_sql_qids($dis)
{
    // ---
    global $data_index;
    // ---
    $key = "get_td_or_sql_qids_" . $dis;
    // ---
    if (!empty($data_index[$key] ?? [])) return $data_index[$key];
    // ---
    $data = [];
    // ---
    $sql_params = [];
    $api_params = ['get' => 'qids', 'dis' => $dis];
    $quaries = [
        'empty' => "select id, title, qid from qids where (qid = '' OR qid IS NULL);",
        'all' => "select id, title, qid from qids;",
        'duplicate' => <<<SQL
            SELECT
            A.id AS id, A.title AS title, A.qid AS qid,
            B.id AS id2, B.title AS title2, B.qid AS qid2
        FROM
            qids A
        JOIN
            qids B ON A.qid = B.qid
        WHERE
            A.qid != '' AND A.title != B.title AND A.id != B.id;
        SQL
    ];
    //---
    $query = (array_key_exists($dis, $quaries)) ? $quaries[$dis] : $quaries['all'];
    //---
    $data = super_function($api_params, $sql_params, $query);
    // ---
    $data_index[$key] = $data;
    // ---
    return $data;
}

function get_td_or_sql_qids_others($dis)
{
    // ---
    global $data_index;
    // ---
    $key = "sql_qids_others" . $dis;
    // ---
    if (!empty($data_index[$key] ?? [])) return $data_index[$key];
    // ---
    $data = [];
    // ---
    $sql_params = [];
    $api_params = ['get' => 'qids_others', 'dis' => $dis];
    $quaries = [
        'empty' => "select id, title, qid from qids_others where (qid = '' OR qid IS NULL);",
        'all' => "select id, title, qid from qids_others;",
        'duplicate' => <<<SQL
            SELECT
            A.id AS id, A.title AS title, A.qid AS qid,
            B.id AS id2, B.title AS title2, B.qid AS qid2
        FROM
            qids_others A
        JOIN
            qids_others B ON A.qid = B.qid
        WHERE
            A.qid != '' AND A.title != B.title AND A.id != B.id;
        SQL
    ];
    //---
    $query = (array_key_exists($dis, $quaries)) ? $quaries[$dis] : $quaries['all'];
    //---
    $data = super_function($api_params, $sql_params, $query);
    // ---
    $data_index[$key] = $data;
    // ---
    return $data;
}

function get_td_or_sql_settings()
{
    // ---
    static $setting_d = [];
    // ---
    if (!empty($setting_d)) return $setting_d;
    // ---
    $sql_params = [];
    $api_params = ['get' => 'settings'];
    $query = "select id, title, displayed, value, Type, ignored from settings";
    //---
    $setting_d = super_function($api_params, $sql_params, $query);
    // ---
    return $setting_d;
}
function get_pages_langs()
{
    // ---
    static $pages_langs = [];
    // ---
    if (!empty($pages_langs ?? [])) {
        return $pages_langs;
    }
    // ---
    $sql_params = [];
    $api_params = ['get' => 'pages', 'distinct' => "1", 'select' => 'lang', 'lang' => 'not_empty'];
    $query = "SELECT DISTINCT lang FROM pages where (lang != '' and lang IS NOT NULL)";
    $data = super_function($api_params, $sql_params, $query);
    // ---
    $data = array_column($data, 'lang');
    // ---
    // var_export(json_encode($data));
    // ---
    $pages_langs = $data;
    // ---
    return $data;
}

function get_pages_users_langs()
{
    // ---
    static $pages_users_langs = [];
    // ---
    if (!empty($pages_users_langs ?? [])) {
        return $pages_users_langs;
    }
    // ---
    $sql_params = [];
    $api_params = ['get' => 'pages_users', 'distinct' => "1", 'select' => 'lang'];
    $query = "SELECT DISTINCT lang FROM pages_users";
    $data = super_function($api_params, $sql_params, $query);
    // ---
    $data = array_column($data, 'lang');
    // ---
    $pages_users_langs = $data;
    // ---
    return $data;
}

function td_or_sql_titles_infos($titles = [])
{
    // ---
    // Ensure $titles is an array
    if (!is_array($titles)) {
        $titles = [];
    }
    // ---
    $sql_params = [];
    $api_params = ['get' => 'titles'];
    // ---
    if (!empty($titles)) {
        $api_params['titles'] = $titles;
    }
    // ---
    $qua = <<<SQL
        SELECT *
        FROM titles_infos
    SQL;
    // ---
    if (!empty($titles)) {
        $titles = implode("','", $titles);
        $qua .= " WHERE title IN ('$titles')";
    }
    // ---
    $data = super_function($api_params, $sql_params, $qua);
    // ---
    return $data;
}
