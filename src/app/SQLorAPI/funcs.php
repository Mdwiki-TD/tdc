<?php

namespace App\SQLorAPI\Funcs;

use function App\SQLorAPI\superFunction;

function get_publish_reports_stats(): array
{

    static $statsData = [];

    if (!empty($statsData)) {
        return $statsData;
    }

    $query = <<<SQL
        SELECT DISTINCT YEAR(date) as year, MONTH(date) as month, lang, user, result
        FROM publish_reports
        GROUP BY year, month, lang, user, result
    SQL;

    $apiParams = ['get' => 'publish_reports_stats'];

    $statsData = superFunction($apiParams, [], $query);

    return $statsData;
}

function get_users_by_last_pupdate(): array
{

    static $lastUserToTab = [];

    if (!empty($lastUserToTab ?? [])) {
        return $lastUserToTab;
    }

    $data = [];

    $apiParams = array('get' => 'users_by_last_pupdate');
    $queryOld = <<<SQL
        select DISTINCT p1.target, p1.title, p1.user, p1.pupdate, p1.lang
        from pages p1
        where target != ''
        and p1.pupdate = (select p2.pupdate from pages p2 where p2.user = p1.user ORDER BY p2.pupdate DESC limit 1)
        group by p1.user
        ORDER BY p1.pupdate DESC
    SQL;

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

    $data = superFunction($apiParams, [], $query);

    foreach ($data as $key => $gg) {
        $lastUserToTab[$gg['user']] = $gg;
    }

    return $lastUserToTab;
}

function get_td_or_sql_page_user_not_in_users(): array
{

    static $users = [];

    if (!empty($users ?? [])) {
        return $users;
    }

    $sqlParams = [];
    $apiParams = array('get' => 'pages', 'distinct' => 1, 'select' => 'user');
    $query = <<<SQL
        select DISTINCT p.user from pages AS p WHERE NOT EXISTS ( SELECT 1 FROM users AS u WHERE p.user = u.username )
    SQL;

    $data = superFunction($apiParams, $sqlParams, $query);

    $data = array_column($data, 'user');

    $users = $data;

    return $data;
}

function get_td_or_sql_language_settings(): array
{

    // language_settings (lang_code, move_dots, expend, add_en_lang)
    static $dataLangs = [];

    if (!empty($dataLangs)) return $dataLangs;

    $sqlParams = [];
    $apiParams = ['get' => 'language_settings'];
    $query = "SELECT * FROM language_settings order by lang_code";

    $dataLangs = superFunction($apiParams, $sqlParams, $query);

    return $dataLangs;
}

function get_td_or_sql_projects(): array
{

    static $projects = [];

    if (!empty($projects ?? [])) {
        return $projects;
    }

    $sqlParams = [];
    $apiParams = ['get' => 'projects'];
    $query = "select g_id, g_title from projects";

    $data = superFunction($apiParams, $sqlParams, $query);

    $projects = $data;

    return $data;
}

function get_td_or_sql_qids($dis): array
{

    static $cache = [];

    if (!empty($cache[$dis] ?? [])) {
        return $cache[$dis];
    }

    $data = [];

    $sqlParams = [];
    $apiParams = ['get' => 'qids', 'dis' => $dis];
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

    $query = (array_key_exists($dis, $quaries)) ? $quaries[$dis] : $quaries['all'];

    $data = superFunction($apiParams, $sqlParams, $query);

    $cache[$dis] = $data;

    return $data;
}

function get_td_or_sql_qids_others($dis): array
{

    static $cache = [];

    if (!empty($cache[$dis] ?? [])) {
        return $cache[$dis];
    }

    $data = [];

    $sqlParams = [];
    $apiParams = ['get' => 'qids_others', 'dis' => $dis];
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

    $query = (array_key_exists($dis, $quaries)) ? $quaries[$dis] : $quaries['all'];

    $data = superFunction($apiParams, $sqlParams, $query);

    $cache[$dis] = $data;

    return $data;
}

function get_pages_langs(): array
{

    static $pagesLangs = [];

    if (!empty($pagesLangs ?? [])) {
        return $pagesLangs;
    }

    $sqlParams = [];
    $apiParams = ['get' => 'pages', 'distinct' => "1", 'select' => 'lang', 'lang' => 'not_empty'];
    $query = "SELECT DISTINCT lang FROM pages where (lang != '' and lang IS NOT NULL)";
    $data = superFunction($apiParams, $sqlParams, $query);

    $data = array_column($data, 'lang');

    // var_export(json_encode($data));

    $pagesLangs = $data;

    return $data;
}

function get_pages_users_langs(): array
{

    static $pagesUsersLangs = [];

    if (!empty($pagesUsersLangs ?? [])) {
        return $pagesUsersLangs;
    }

    $sqlParams = [];
    $apiParams = ['get' => 'pages_users', 'distinct' => "1", 'select' => 'lang'];
    $query = "SELECT DISTINCT lang FROM pages_users";
    $data = superFunction($apiParams, $sqlParams, $query);

    $data = array_column($data, 'lang');

    $pagesUsersLangs = $data;

    return $data;
}
