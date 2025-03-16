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
