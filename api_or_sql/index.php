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
*/

use function Actions\MdwikiSql\fetch_query;
use function Actions\TDApi\get_td_api;

$data_index = [];

function isvalid($str)
{
    return !empty($str) && $str != 'All' && $str != 'all';
}

function get_td_or_sql_categories()
{
    // ---
    global $use_td_api;
    // ---
    static $categories = [];
    // ---
    if (!empty($categories ?? [])) {
        return $categories;
    }
    // ---
    if ($use_td_api) {
        $data = get_td_api(['get' => 'categories']);
    } else {
        $query = "select id, category, category2, campaign, depth, def from categories";
        //---
        $data = fetch_query($query);
    }
    // ---
    $categories = $data;
    // ---
    return $data;
}

function get_coordinator()
{
    // ---
    global $use_td_api;
    // ---
    static $coordinator = [];
    // ---
    if (!empty($coordinator ?? [])) {
        return $coordinator;
    }
    // ---
    if ($use_td_api) {
        $data = get_td_api(['get' => 'coordinator']);
    } else {
        $query = "SELECT id, user FROM coordinator order by id";
        //---
        $data = fetch_query($query);
    }
    // ---
    $coordinator = $data;
    // ---
    return $data;
}

function get_users_by_last_pupdate()
{
    // ---
    global $use_td_api;
    // ---
    static $last_user_to_tab = [];
    // ---
    if (!empty($last_user_to_tab ?? [])) {
        return $last_user_to_tab;
    }
    // ---
    $data = [];
    // ---
    if ($use_td_api) {
        $data = get_td_api(array('get' => 'users_by_last_pupdate'));
    } else {
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
        $data = fetch_query($query);
    }
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
    global $use_td_api;
    // ---
    static $count_pages = [];
    // ---
    if (!empty($count_pages ?? [])) {
        return $count_pages;
    }
    // ---
    if ($use_td_api) {
        $data = get_td_api(array('get' => 'count_pages', 'target' => 'not_empty'));
    } else {
        $query = <<<SQL
            select DISTINCT user, count(target) as count from pages where target != '' group by user order by count desc
        SQL;
        //---
        $data = fetch_query($query);
    }
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
    global $use_td_api;
    // ---
    static $users = [];
    // ---
    if (!empty($users ?? [])) {
        return $users;
    }
    // ---
    if ($use_td_api) {
        $data = get_td_api(array('get' => 'pages', 'distinct' => 1, 'select' => 'user'));
    } else {
        $query = <<<SQL
            select DISTINCT user from pages WHERE NOT EXISTS (SELECT 1 FROM users WHERE user = username)
        SQL;
        //---
        $data = fetch_query($query);
    }
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
    global $use_td_api;
    // ---
    static $users = [];
    // ---
    if (!empty($users)) return $users;
    // ---
    if ($use_td_api) {
        $users = get_td_api(['get' => 'users_no_inprocess']);
    } else {
        $query = "SELECT * FROM users_no_inprocess order by id";
        //---
        $users = fetch_query($query);
    }
    // ---
    return $users;
}

function get_td_or_sql_full_translators()
{
    // ---
    global $use_td_api;
    // ---
    static $full_translators = [];
    // ---
    if (!empty($full_translators)) return $full_translators;
    // ---
    if ($use_td_api) {
        $full_translators = get_td_api(['get' => 'full_translators']);
    } else {
        $query = "SELECT * FROM full_translators order by id";
        //---
        $full_translators = fetch_query($query);
    }
    // ---
    return $full_translators;
}

function get_td_or_sql_projects()
{
    // ---
    global $use_td_api;
    // ---
    static $projects = [];
    // ---
    if (!empty($projects ?? [])) {
        return $projects;
    }
    // ---
    if ($use_td_api) {
        $data = get_td_api(['get' => 'projects']);
    } else {
        $query = "select g_id, g_title from projects";
        //---
        $data = fetch_query($query);
    }
    // ---
    $projects = $data;
    // ---
    return $data;
}

function get_td_or_sql_qids($dis)
{
    // ---
    global $use_td_api, $data_index;
    // ---
    $key = "get_td_or_sql_qids_" . $dis;
    // ---
    if (!empty($data_index[$key] ?? [])) return $data_index[$key];
    // ---
    $data = [];
    // ---
    if ($use_td_api) {
        $data = get_td_api(['get' => 'qids', 'dis' => $dis]);
    } else {
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
        $data = fetch_query($query);
    }
    // ---
    $data_index[$key] = $data;
    // ---
    return $data;
}

function get_td_or_sql_qids_others($dis)
{
    // ---
    global $use_td_api, $data_index;
    // ---
    $key = "sql_qids_others" . $dis;
    // ---
    if (!empty($data_index[$key] ?? [])) return $data_index[$key];
    // ---
    $data = [];
    // ---
    if ($use_td_api) {
        $data = get_td_api(['get' => 'qids_others', 'dis' => $dis]);
    } else {
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
        $data = fetch_query($query);
    }
    // ---
    $data_index[$key] = $data;
    // ---
    return $data;
}

function get_td_or_sql_settings()
{
    // ---
    global $use_td_api;
    // ---
    static $setting_d = [];
    // ---
    if (!empty($setting_d)) return $setting_d;
    // ---
    if ($use_td_api) {
        $setting_d = get_td_api(['get' => 'settings']);
    } else {
        $query = "select id, title, displayed, value, Type, ignored from settings";
        //---
        $setting_d = fetch_query($query);
    }
    // ---
    return $setting_d;
}
function get_pages_langs()
{
    // ---
    global $use_td_api;
    // ---
    static $pages_langs = [];
    // ---
    if (!empty($pages_langs ?? [])) {
        return $pages_langs;
    }
    // ---
    if ($use_td_api) {
        $data = get_td_api(['get' => 'pages', 'distinct' => "1", 'select' => 'lang', 'lang' => 'not_empty']);
    } else {
        $query = "SELECT DISTINCT lang FROM pages where (lang != '' and lang IS NOT NULL)";
        $data = fetch_query($query);
    }
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
    global $use_td_api;
    // ---
    static $pages_users_langs = [];
    // ---
    if (!empty($pages_users_langs ?? [])) {
        return $pages_users_langs;
    }
    // ---
    if ($use_td_api) {
        $data = get_td_api(['get' => 'pages_users', 'distinct' => "1", 'select' => 'lang']);
    } else {
        $query = "SELECT DISTINCT lang FROM pages_users";
        $data = fetch_query($query);
    }
    // ---
    $data = array_column($data, 'lang');
    // ---
    $pages_users_langs = $data;
    // ---
    return $data;
}

function td_or_sql_titles_infos()
{
    // ---
    global $use_td_api;
    // ---
    if ($use_td_api) {
        $data = get_td_api(['get' => 'titles']);
    } else {
        $qua_old = <<<SQL
            SELECT
                ase.title,
                ase.importance,
                rc.r_lead_refs,
                rc.r_all_refs,
                ep.en_views,
                w.w_lead_words,
                w.w_all_words,
                q.qid
            FROM assessments ase
            LEFT JOIN enwiki_pageviews ep ON ase.title = ep.title
            LEFT JOIN  qids q ON q.title = ase.title
            LEFT JOIN  refs_counts rc ON rc.r_title = ase.title
            LEFT JOIN  words w ON w.w_title = ase.title
        SQL;
        // ---
        $qua = <<<SQL
            SELECT *
            FROM titles_infos
        SQL;
        // ---
        $data = fetch_query($qua);
    }
    // ---
    return $data;
}
