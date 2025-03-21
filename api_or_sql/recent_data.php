<?php

namespace SQLorAPI\Recent;

/*

Usage:

use function SQLorAPI\Recent\get_recent_sql;
use function SQLorAPI\Recent\get_recent_pages_users;
use function SQLorAPI\Recent\get_recent_translated;
*/

use function Actions\MdwikiSql\fetch_query;
use function Actions\TDApi\get_td_api;

$data_index = [];

function get_recent_sql($lang)
{
    // ---
    global $use_td_api;
    // ---
    $lang_line = '';
    //---
    $sql_params = [];
    //---
    $params0 = array('get' => 'pages_with_views', 'target' => 'not_empty', 'limit' => '250', 'order' => 'pupdate_or_add_date');
    //---
    if (!empty($lang) && $lang != 'All') {
        $lang_line = "and p.lang = ?";
        $sql_params[] = $lang;
        // ---
        $params0['lang'] = $lang;
    }
    //---
    if ($use_td_api) {
        $tab = get_td_api($params0);
    } else {
        $qua = <<<SQL
            select distinct
                p.id, p.title, p.word, p.translate_type, p.cat,
                p.lang, p.user, p.target, p.date, p.pupdate, p.add_date, p.deleted, p.target, p.lang,
                (select v.views from views_new_all v where p.target = v.target AND p.lang = v.lang LIMIT 1) as views
            from pages p
            where p.target != ''
            $lang_line
            ORDER BY GREATEST(UNIX_TIMESTAMP(p.pupdate), UNIX_TIMESTAMP(p.add_date)) DESC
            limit 250
        SQL;
        $tab = fetch_query($qua, $sql_params);
    }
    // ---
    // merage the two arrays without duplicates
    // $tab = array_unique(array_merge($dd0, $dd1), SORT_REGULAR);
    //---
    // sort the table by add_date
    // usort($tab, function ($a, $b) {
    //     return strtotime($b['pupdate']) - strtotime($a['pupdate']);
    // });
    //---
    return $tab;
}

function get_recent_pages_users($lang)
{
    // ---
    global $use_td_api;
    // ---
    $lang_line = '';
    //---
    $sql_params = [];
    $params0 = [
        'get' => 'pages_users',
        'target' => 'not_empty',
        'order' => 'pupdate',
        // 'title_not_in_pages' => '0',
        'limit' => '100'
    ];
    //---
    if (!empty($lang) && $lang != 'All') {
        $lang_line = "and lang = ?";
        $sql_params[] = $lang;
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
        $tab = fetch_query($qua, $sql_params);
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
    $sql_params = [];
    $params = array('get' => $table, 'order' => 'pupdate');
    //---
    if (!empty($lang) && $lang != 'All') {
        $lang_line = "and lang = ?";
        $sql_params[] = $lang;
        $params['lang'] = $lang;
    }
    //---
    if ($use_td_api) {
        $dd = get_td_api($params);
    } else {
        $dd = fetch_query("select * from $table where target != '' $lang_line ORDER BY pupdate DESC;", $sql_params);
    }
    //---
    // sort the table by add_date
    usort($dd, function ($a, $b) {
        return strtotime($b['add_date']) - strtotime($a['add_date']);
    });
    //---
    return $dd;
}
