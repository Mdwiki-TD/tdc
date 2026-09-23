<?php

namespace App\SQLorAPI\Recent;

use function App\SQLorAPI\Get\super_function;

function get_recent_sql($lang): array
{

    static $cache = [];

    if (!empty($cache[$lang] ?? [])) {
        return $cache[$lang];
    }

    $langLine = '';

    $sqlParams = [];

    $apiParams = [
        'get' => 'pages_with_views',
        'target' => 'not_empty',
        "order" => 'pupdate_or_add_date',
        'limit' => '250',
    ];

    if (!empty($lang) && $lang != 'All') {
        $langLine = "and p.lang = ?";
        $sqlParams[] = $lang;

        $apiParams['lang'] = $lang;
    }

    $sqlQuery = <<<SQL
        select distinct
            p.id, p.title, p.word, p.translate_type, p.cat,
            p.lang, p.user, p.target, p.date, p.pupdate,
            p.add_date, p.deleted, p.mdwiki_revid,
            (select v.views from views_new_all v where p.target = v.target AND p.lang = v.lang LIMIT 1) as views
        from pages p
        where p.target != ''
        $langLine
        ORDER BY GREATEST(UNIX_TIMESTAMP(p.pupdate), UNIX_TIMESTAMP(p.add_date)) DESC
        limit 250
    SQL;

    $tab = super_function($apiParams, $sqlParams, $sqlQuery);

    // merage the two arrays without duplicates
    // $tab = array_unique(array_merge($dd0, $dd1), SORT_REGULAR);

    // sort the table by add_date
    // usort($tab, function ($a, $b) {
    //     return strtotime($b['pupdate']) - strtotime($a['pupdate']);
    // });

    $cache[$lang] = $tab;

    return $tab;
}

function get_recent_pages_users($lang): array
{

    static $cache = [];

    if (!empty($cache[$lang] ?? [])) {
        return $cache[$lang];
    }

    $sqlParams = [];

    $apiParams = [
        'get' => 'pages_users',
        'target' => 'not_empty',
        "order" => 'pupdate',
        'limit' => '100'
    ];

    $langLine = '';

    if (!empty($lang) && $lang != 'All') {
        $langLine = "and lang = ?";
        $sqlParams[] = $lang;
        $apiParams['lang'] = $lang;
    };

    $qua = <<<SQL
        select * #id, date, user, lang, title, cat, word, target, pupdate, add_date
        from pages_users
        where
            target != ''
        # and title not in ( select p.title from pages p where p.lang = lang and p.target != '' )
        $langLine
        ORDER BY pupdate DESC
        limit 100
    SQL;

    $tab = super_function($apiParams, $sqlParams, $qua);

    // sort the table by add_date
    usort($tab, function ($a, $b) {
        return strtotime($b['pupdate']) - strtotime($a['pupdate']);
    });

    $cache[$lang] = $tab;

    return $tab;
}

function get_recent_translated($lang, $table, $limit, $offset): array
{

    $sqlParams = [];
    $apiParams = array('get' => $table, "order" => 'pupdate', 'limit' => $limit, 'offset' => $offset);

    $query = "SELECT * FROM $table WHERE target != ''";

    if (!empty($lang) && $lang != 'All') {
        $query .= " AND lang = ?";
        $sqlParams[] = $lang;
        $apiParams['lang'] = $lang;
    }

    $query .= " ORDER BY pupdate DESC ";

    // add limit and offset to $sqlLine
    if ($limit > 0) {
        $query .= " \n LIMIT $limit ";
        // $query .= " \n LIMIT ? ";
        // $sqlParams[] = $limit;
    }

    if ($offset > 0) {
        $query .= " OFFSET $offset ";
        // $query .= " OFFSET ? ";
        // $sqlParams[] = $offset;
    }

    $dd = super_function($apiParams, $sqlParams, $query);

    // sort the table by add_date
    usort($dd, function ($a, $b) {
        return strtotime($b['add_date']) - strtotime($a['add_date']);
    });

    return $dd;
}

function get_total_translations_count($lang, $table): int
{

    $sqlParams = [];
    $apiParams = ['get' => $table, 'select' => 'count(*)'];

    $query = "select COUNT(*) AS count from $table where target != ''";

    if (!empty($lang) && $lang != 'All') {
        $query .= " AND lang = ?";
        $sqlParams[] = $lang;
        $apiParams['lang'] = $lang;
    }

    $dd = super_function($apiParams, $sqlParams, $query);

    $result = (int)($dd[0]['count'] ?? 0);

    return $result;
}

function get_pages_users_to_main($lang): array
{
    static $cache = [];

    if (!empty($cache[$lang] ?? [])) {
        return $cache[$lang];
    }

    $query = "SELECT * FROM pages_users_to_main pum, pages_users pu where pum.id = pu.id";

    $sqlParams = [];
    $apiParams = array('get' => "pages_users_to_main");

    if (!empty($lang) && $lang != 'All') {
        $query .= " AND pu.lang = ?";
        $sqlParams[] = $lang;
        $apiParams['lang'] = $lang;
    }

    $dd = super_function($apiParams, $sqlParams, $query);

    $cache[$lang] = $dd;

    return $dd;
}
