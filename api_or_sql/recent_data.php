<?php

namespace SQLorAPI\Recent;

/*

Usage:

use function SQLorAPI\Recent\get_recent_sql;
use function SQLorAPI\Recent\get_recent_pages_users;
use function SQLorAPI\Recent\get_recent_translated;
use function SQLorAPI\Recent\get_total_translations_count;
use function SQLorAPI\Recent\get_pages_users_to_main;
*/

use function SQLorAPI\Get\super_function;

$data_index = [];

function get_recent_sql($lang): array
{
    // ---
    $lang_line = '';
    //---
    $sql_params = [];
    //---
    $api_params = array('get' => 'pages_with_views', 'target' => 'not_empty', 'limit' => '250', 'order' => 'pupdate_or_add_date');
    //---
    if (!empty($lang) && $lang != 'All') {
        $lang_line = "and p.lang = ?";
        $sql_params[] = $lang;
        // ---
        $api_params['lang'] = $lang;
    }
    //---
    $sql_query = <<<SQL
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
    // ---
    $tab = super_function($api_params, $sql_params, $sql_query);
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

function get_recent_pages_users($lang): array
{
    // ---
    $sql_params = [];
    //---
    $api_params = [
        'get' => 'pages_users',
        'target' => 'not_empty',
        'order' => 'pupdate',
        // 'title_not_in_pages' => '0',
        'limit' => '100'
    ];
    //---
    $lang_line = '';
    //---
    if (!empty($lang) && $lang != 'All') {
        $lang_line = "and lang = ?";
        $sql_params[] = $lang;
        $api_params['lang'] = $lang;
    };
    //---
    $qua = <<<SQL
        select * #id, date, user, lang, title, cat, word, target, pupdate, add_date
        from pages_users
        where
            target != ''
        # and title not in ( select p.title from pages p where p.lang = lang and p.target != '' )
        $lang_line
        ORDER BY pupdate DESC
        limit 100
    SQL;
    //---
    $tab = super_function($api_params, $sql_params, $qua);
    // ---
    // sort the table by add_date
    usort($tab, function ($a, $b) {
        return strtotime($b['pupdate']) - strtotime($a['pupdate']);
    });
    //---
    return $tab;
}

function get_recent_translated($lang, $table, $limit, $offset): array
{
    $sql_params = [];
    $api_params = array('get' => $table, 'order' => 'pupdate', 'limit' => $limit, 'offset' => $offset);
    //---
    $query = "SELECT * FROM $table WHERE target != ''";
    //---
    if (!empty($lang) && $lang != 'All') {
        $query .= " AND lang = ?";
        $sql_params[] = $lang;
        $api_params['lang'] = $lang;
    }
    //---
    $query .= " ORDER BY pupdate DESC ";
    //---
    // add limit and offset to $sql_line
    if ($limit > 0) {
        $query .= " \n LIMIT $limit ";
        // $query .= " \n LIMIT ? ";
        // $sql_params[] = $limit;
    }
    //---
    if ($offset > 0) {
        $query .= " OFFSET $offset ";
        // $query .= " OFFSET ? ";
        // $sql_params[] = $offset;
    }
    //---
    $dd = super_function($api_params, $sql_params, $query);
    // ---
    // sort the table by add_date
    usort($dd, function ($a, $b) {
        return strtotime($b['add_date']) - strtotime($a['add_date']);
    });
    //---
    return $dd;
}

function get_total_translations_count($lang, $table): int
{
    //---
    $sql_params = [];
    $api_params = ['get' => $table, 'select' => 'COUNT(*)'];
    //---
    $query = "select COUNT(*) AS count from $table where target != ''";
    //---
    if (!empty($lang) && $lang != 'All') {
        $query .= "and lang = ?";
        $sql_params[] = $lang;
        $api_params['lang'] = $lang;
    }
    //---
    $dd = super_function($api_params, $sql_params, $query);
    // ---
    $result = (int)$dd[0]['count'] ?? 0;
    // ---
    return $result;
}

function get_pages_users_to_main($lang): array
{
    $query = "SELECT * FROM pages_users_to_main pum, pages_users pu where pum.id = pu.id";
    //---
    $sql_params = [];
    $api_params = array('get' => "pages_users_to_main");
    //---
    if (!empty($lang) && $lang != 'All') {
        $query .= "AND pu.lang = ?";
        $sql_params[] = $lang;
        $api_params['lang'] = $lang;
    }
    //---
    $dd = super_function($api_params, $sql_params, $query);
    // ---
    return $dd;
}
