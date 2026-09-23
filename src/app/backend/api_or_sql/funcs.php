<?php

namespace App\SQLorAPI\Funcs;


use function App\SQLorAPI\Get\super_function;

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

    $statsData = super_function($apiParams, [], $query);

    return $statsData;
}

function get_td_or_sql_categories(): array
{

    static $categories = [];

    if (!empty($categories ?? [])) {
        return $categories;
    }

    $apiParams = ['get' => 'categories'];
    $query = "select id, category, category2, campaign, depth, is_default from categories";

    $data = super_function($apiParams, [], $query);

    $categories = $data;

    return $categories;
}

function get_coordinators(): array
{

    static $coordinators = [];

    if (!empty($coordinators ?? [])) {
        return $coordinators;
    }

    $apiParams = ['get' => 'coordinators'];
    $query = "SELECT id, username, is_active FROM coordinators order by id";

    $data = super_function($apiParams, [], $query);

    $coordinators = $data;

    return $coordinators;
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

    $data = super_function($apiParams, [], $query);

    foreach ($data as $key => $gg) {
        $lastUserToTab[$gg['user']] = $gg;
    }

    return $lastUserToTab;
}

function get_td_or_sql_count_pages_not_empty(): array
{

    static $countPages = [];

    if (!empty($countPages ?? [])) {
        return $countPages;
    }

    $apiParams = array('get' => 'count_pages', 'target' => 'not_empty');
    $query = <<<SQL
        select DISTINCT user, count(target) as count from pages where target != '' group by user order by count desc
    SQL;

    $data = super_function($apiParams, [], $query);

    $data = array_column($data, 'count', 'user');

    arsort($data);

    // print_r($data);

    $countPages = $data;

    return $data;
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

    $data = super_function($apiParams, $sqlParams, $query);

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

    $dataLangs = super_function($apiParams, $sqlParams, $query);

    return $dataLangs;
}

function get_td_or_sql_users_no_inprocess(): array
{

    static $users = [];

    if (!empty($users)) return $users;

    $sqlParams = [];
    $apiParams = ['get' => 'users_no_inprocess'];
    $query = "SELECT id, user, is_active FROM users_no_inprocess order by id";

    $users = super_function($apiParams, $sqlParams, $query);

    return $users;
}

function get_td_or_sql_full_translators(): array
{

    static $fullTranslators = [];

    if (!empty($fullTranslators)) return $fullTranslators;

    $sqlParams = [];
    $apiParams = ['get' => 'full_translators'];
    $query = "SELECT id, user, is_active FROM full_translators order by id";

    $fullTranslators = super_function($apiParams, $sqlParams, $query);

    return $fullTranslators;
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

    $data = super_function($apiParams, $sqlParams, $query);

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

    $data = super_function($apiParams, $sqlParams, $query);

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

    $data = super_function($apiParams, $sqlParams, $query);

    $cache[$dis] = $data;

    return $data;
}

function get_td_or_sql_settings(): array
{

    static $setting_d = [];

    if (!empty($setting_d)) return $setting_d;

    $sqlParams = [];
    $apiParams = ['get' => 'settings'];
    $query = "select id, title, displayed, value, Type, ignored from settings";

    $setting_d = super_function($apiParams, $sqlParams, $query);

    return $setting_d;
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
    $data = super_function($apiParams, $sqlParams, $query);

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
    $data = super_function($apiParams, $sqlParams, $query);

    $data = array_column($data, 'lang');

    $pagesUsersLangs = $data;

    return $data;
}

function td_or_sql_titles_infos($titles = []): array
{

    // Ensure $titles is an array
    if (!is_array($titles)) {
        $titles = [];
    }

    $apiParams = ['get' => 'titles', 'titles' => $titles];

    $qua = <<<SQL
        select
            ase.title AS title,
            ase.importance AS importance,
            rc.r_lead_refs AS r_lead_refs,
            rc.r_all_refs AS r_all_refs,
            ep.en_views AS en_views,
            w.w_lead_words AS w_lead_words,
            w.w_all_words AS w_all_words,
            q.qid AS qid
        from
            assessments ase
            left join enwiki_pageviews ep on ase.title = ep.title
            left join qids q on q.title = ase.title
            left join refs_counts rc on rc.r_title = ase.title
            left join words w on w.w_title = ase.title
    SQL;

    $sqlParams = [];

    if (!empty($titles)) {
        $placeholders = rtrim(str_repeat('?,', count($titles)), ',');
        $qua .= " WHERE ase.title IN ($placeholders)";
        $sqlParams = $titles;              // pass to super_function
    }

    $data = super_function($apiParams, $sqlParams, $qua);

    return $data;
}
