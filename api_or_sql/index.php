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
use function SQLorAPI\Get\get_process_all;
use function SQLorAPI\Get\get_users_process;
use function SQLorAPI\Get\get_pages_langs;
use function SQLorAPI\Get\get_recent_sql;
use function SQLorAPI\Get\td_or_sql_titles_infos;
use function SQLorAPI\Get\get_recent_pages_users;
use function SQLorAPI\Get\get_recent_translated;
use function SQLorAPI\Get\get_lang_in_process;
*/

include_once __DIR__ . '/../actions/mdwiki_sql.php';
include_once __DIR__ . '/../actions/td_api.php';

use function Actions\MdwikiSql\fetch_query;
use function Actions\TDApi\get_td_api;

$settings_tabe = array_column(get_td_api(['get' => 'settings']), 'value', 'title');
//---
$use_td_api  = (($settings_tabe['use_td_api'] ?? "") == "1") ? true : false;
$use_td_api  = false;   // false in tdc
// ---
$use_in_process_table  = (($settings_tabe['use_td_api'] ?? "") == "1") ? true : false;

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
        $query = "SELECT id, user FROM coordinator;";
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
            select DISTINCT p1.target, p1.title, p1.cat, p1.user, p1.pupdate, p1.lang
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
                    ROW_NUMBER() OVER (PARTITION BY p1.user ORDER BY p1.pupdate DESC) AS rn
                FROM pages p1
                WHERE p1.target != ''
            )
            SELECT target, user, pupdate, lang
            FROM RankedPages
            WHERE rn = 1
            ORDER BY pupdate DESC;
        SQL;
        //---
        $data = fetch_query($query);
    }
    // ---
    $last_user_to_tab = array();
    //---
    foreach ($data as $Key => $gg) {
        if (!in_array($gg['user'], $last_user_to_tab)) {
            $last_user_to_tab[$gg['user']] = $gg;
        }
    };
    $last_user_to_tab = $data;
    // ---
    return $data;
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
        $data = get_td_api(['get' => 'full_translators']);
    } else {
        $query = "SELECT * FROM full_translators";
        //---
        $data = fetch_query($query);
    }
    // ---
    $full_translators = $data;
    // ---
    return $data;
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
    global $use_td_api;
    // ---
    static $sql_td_qids = [];
    // ---
    if (!empty($sql_td_qids[$dis] ?? [])) return $sql_td_qids[$dis];
    // ---
    $data = [];
    // ---
    if ($use_td_api) {
        $data = get_td_api(['get' => 'qids', 'dis' => $dis]);
    } else {
        $quaries = [
            'empty' => "select id, title, qid from qids where qid = '';",
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
        $query = (in_array($dis, $quaries)) ? $quaries['all'] : $quaries[$dis];
        //---
        $data = fetch_query($query);
    }
    // ---
    $sql_td_qids[$dis] = $data;
    // ---
    return $sql_td_qids[$dis];
}

function get_td_or_sql_qids_others($dis)
{
    // ---
    global $use_td_api;
    // ---
    static $qids_result = [];
    // ---
    if (!empty($qids_result[$dis] ?? [])) return $qids_result[$dis];
    // ---
    $data = [];
    // ---
    if ($use_td_api) {
        $data = get_td_api(['get' => 'qids_others', 'dis' => $dis]);
    } else {
        $quaries = [
            'empty' => "select id, title, qid from qids_others where qid = '';",
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
        $query = (in_array($dis, $quaries)) ? $quaries['all'] : $quaries[$dis];
        //---
        $data = fetch_query($query);
    }
    // ---
    $qids_result[$dis] = $data;
    // ---
    return $qids_result[$dis];
}

function get_td_or_sql_settings()
{
    // ---
    global $use_td_api;
    // ---
    static $setting_d = [];
    // ---
    if (!empty($setting_d)) {
        return $setting_d;
    }
    // ---
    if ($use_td_api) {
        $setting_d = get_td_api(['get' => 'settings']);
    } else {
        $query = "select id, title, displayed, value, Type from settings";
        //---
        $setting_d = fetch_query($query);
    }
    // ---
    return $setting_d;
}

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
        $sql_t = "select * from pages where target = '' ORDER BY date DESC limit 100";
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
        $sql_t = 'select DISTINCT user, count(target) as count from pages where target = "" group by user order by count desc';
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
        $tab = get_td_api(['get' => 'pages', 'lang' => $lang, 'target' => 'empty']);
        $data_index[$lang] = array_column($tab, 'count', 'user');
    } else {
        // select * from pages where target = '' and lang = '$code'
        $sql_t = 'select * from pages where target = "" and lang = ?';
        $tab = fetch_query($sql_t, [$lang]);
        $data_index[$lang] = array_column($tab, 'count', 'user');
    }
    //---
    return $data_index[$lang];
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
        $data = get_td_api(['get' => 'pages', 'distinct' => "1", 'select' => 'lang']);
    } else {
        $query = "SELECT DISTINCT lang FROM pages";
        $data = fetch_query($query);
    }
    // ---
    $data = array_column($data, 'lang');
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

function get_recent_sql($lang)
{
    // ---
    global $use_td_api;
    // ---
    $lang_line = '';
    //---
    if (!empty($lang) && $lang != 'All') {
        $lang_line = "and lang = '$lang'";
    }
    //---
    $params0 = array('get' => 'pages', 'target' => 'not_empty', 'limit' => '250', 'order' => 'pupdate');
    $params1 = array('get' => 'pages', 'target' => 'not_empty', 'limit' => '250', 'order' => 'add_date');
    //---
    if (!empty($lang) && $lang != 'All') {
        $params0['lang'] = $lang;
        $params1['lang'] = $lang;
    }
    //---
    if ($use_td_api) {
        $dd0 = get_td_api($params0);
        $dd1 = get_td_api($params1);
    } else {
        $dd0 = fetch_query("select * from pages where target != '' $lang_line ORDER BY pupdate DESC limit 250");
        $dd1 = fetch_query("select * from pages where target != '' $lang_line ORDER BY add_date DESC limit 250");
    }
    // ---
    // merage the two arrays without duplicates
    $tab = array_unique(array_merge($dd0, $dd1), SORT_REGULAR);
    //---
    // sort the table by add_date
    usort($tab, function ($a, $b) {
        // return strtotime($b['add_date']) - strtotime($a['add_date']);
        return strtotime($b['pupdate']) - strtotime($a['pupdate']);
    });
    //---
    return $tab;
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

function get_recent_pages_users($lang)
{
    // ---
    global $use_td_api;
    // ---
    $lang_line = '';
    //---
    $params0 = [
        'get' => 'pages_users',
        'target' => 'not_empty',
        'order' => 'pupdate',
        // 'title_not_in_pages' => '0',
        'limit' => '100'
    ];
    //---
    if (!empty($lang) && $lang != 'All') {
        $lang_line = "and lang = '$lang'";
        $params0['lang'] = $lang;
    };
    //---
    $qua = <<<SQL
        select * #id, date, user, lang, title, cat, word, target, pupdate, add_date
        from pages_users
        where
            target != ''
        -- and title not in ( select p.title from pages p where p.lang = lang and p.target != '' )
        $lang_line
        ORDER BY pupdate DESC
        limit 100
        ;
    SQL;
    //---
    if ($use_td_api) {
        $tab = get_td_api($params0);
    } else {
        $tab = fetch_query($qua);
    }
    //---
    // sort the table by add_date
    usort($tab, function ($a, $b) {
        return strtotime($b['pupdate']) - strtotime($a['pupdate']);
    });
    //---
    return $tab;
}

function get_recent_translated($lang, $table)
{
    global $use_td_api;
    // ---
    $lang_line = '';
    //---
    $params = array('get' => $table, 'order' => 'pupdate');
    //---
    if (!empty($lang) && $lang != 'All') {
        $lang_line = "and lang = '$lang'";
        $params['lang'] = $lang;
    }
    //---
    if ($use_td_api) {
        $dd = get_td_api($params);
    } else {
        $dd = fetch_query("select * from $table where target != '' $lang_line ORDER BY pupdate DESC;");
    }
    //---
    // sort the table by add_date
    usort($dd, function ($a, $b) {
        return strtotime($b['add_date']) - strtotime($a['add_date']);
    });
    //---
    return $dd;
}
