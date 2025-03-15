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

/**
 * Checks whether the provided value is a valid string.
 *
 * A string is considered valid if it is not empty and does not exactly equal "All" or "all".
 *
 * @param mixed $str The value to validate.
 * @return bool True if the string is valid, false otherwise.
 */
function isvalid($str)
{
    return !empty($str) && $str != 'All' && $str != 'all';
}

/**
 * Retrieves categories from either the TD API or a SQL database.
 *
 * This function checks a global configuration to determine the source for the categories data.
 * It caches the retrieved data in a static variable on the first call and returns the cached
 * result on subsequent calls.
 *
 * @return array The categories data, which may include fields such as id, category, category2, campaign, depth, and def.
 */
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

/**
 * Retrieves coordinator data from the configured data source.
 *
 * This function fetches coordinator details using either the TD API or a SQL query, depending on the global configuration.
 * The result is cached to optimize subsequent calls.
 *
 * @return array The coordinator data, including coordinator IDs and corresponding user information.
 */
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

/**
 * Retrieves the most recent page update for each user.
 *
 * This function fetches user records representing the latest page update for every user,
 * using either the TD API or a SQL query based on the configuration flag. The results,
 * which typically include details such as the user identifier, target page, update timestamp,
 * and language, are cached to avoid repeated data retrievals within the same request.
 *
 * @return array Array of user records with the latest page update information.
 */
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

/**
 * Retrieves the count of non-empty pages per user.
 *
 * Depending on the global configuration, this function fetches data from the TD API or executes a SQL query to count pages
 * where the target field is not empty. It returns an associative array mapping each user to their count of non-empty pages,
 * sorted in descending order. The results are cached to avoid redundant data retrieval.
 *
 * @return array Associative array of user counts keyed by user identifier.
 */
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

/**
 * Retrieves distinct page users that are not present in the users table.
 *
 * Depending on the configuration, this function fetches unique user identifiers from page records using either the TD API or a SQL query.
 * The results are cached during the request for efficiency.
 *
 * @return array List of unique user identifiers.
 */
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

/**
 * Retrieves full translator data from either the TD API or a SQL database.
 *
 * This function checks the global configuration to determine whether to use the TD API or execute a SQL query to
 * fetch translator records. The retrieved data is cached in a static variable to prevent redundant queries during
 * the same request.
 *
 * @return array Array containing full translator data.
 */
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

/**
 * Retrieves project data from either the TD API or a SQL database.
 *
 * Depending on the global configuration ($use_td_api), this function fetches projects by
 * either calling the TD API with the 'projects' parameter or executing a SQL query. The
 * result, consisting of project IDs and titles, is cached statically to avoid redundant queries.
 *
 * @return array The list of projects with their IDs and titles.
 */
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

/**
 * Retrieves QID records based on the specified discriminator from either the TD API or the SQL database.
 *
 * If available, cached results for the given discriminator are returned immediately. When the global API
 * flag is enabled, the function calls the TD API using the provided discriminator. Otherwise, it selects
 * a SQL query based on the discriminator (typically one of 'empty', 'all', or 'duplicate') and executes it.
 * The results are then cached for future calls.
 *
 * @param string $dis A discriminator that determines which subset of QIDs to retrieve.
 * @return array The QID data retrieved from the designated data source.
 */
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

/**
 * Retrieves QIDs data from the "qids_others" resource based on a given discriminator.
 *
 * Depending on configuration, the function either calls the TD API or executes a SQL query
 * to obtain QIDs data. It supports fetching records with empty QIDs, all records, or duplicate QIDs
 * (where duplicate entries have differing titles). Results are cached per discriminator to avoid
 * redundant data retrieval.
 *
 * @param mixed $dis A discriminator indicating the subset of QIDs data to retrieve (e.g., 'empty', 'all', 'duplicate').
 * @return mixed The retrieved QIDs data from the API or database.
 */
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

/**
 * Retrieves settings data from either the TD API or a SQL database.
 *
 * This function caches the settings in a static variable to prevent multiple queries. It
 * determines the data source based on the global flag $use_td_api: if true, it fetches the
 * settings via the TD API; otherwise, it executes a SQL query to retrieve the settings.
 *
 * @return array The settings data including id, title, displayed, value, and type.
 */
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

/**
 * Retrieves all process pages with an empty target field.
 *
 * Fetches up to 100 page entries sorted by date in descending order where the page's target is empty.
 * Depending on the configuration, data is obtained using the TD API or a SQL query.
 * Results are cached to optimize subsequent calls.
 *
 * @return array The collection of process page entries.
 */
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

/**
 * Retrieves the count of in-process entries grouped by user.
 *
 * Depending on the configuration, the function fetches the data either from the TD API
 * or a SQL database. It returns an associative array where each key is a user identifier
 * and its value is the number of process entries for that user. Results are cached to avoid
 * redundant data retrieval within the same execution.
 *
 * @return array Associative array mapping user identifiers to their process count.
 */
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

/**
 * Retrieves the number of pages in process for each user.
 *
 * This function returns an associative array mapping users to the count of pages that have an empty 'target' field,
 * indicating that they are currently in process. It selects the data source (TD API or SQL database) based on configuration
 * and caches the results to prevent redundant queries.
 *
 * @return array Associative array with user identifiers as keys and the count of in-process pages as values.
 */
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

/**
 * Retrieves an array of distinct language codes from pages.
 *
 * Depending on the configuration, this function sources the language data via the TD API
 * or a direct SQL query. The result is cached to optimize subsequent calls.
 *
 * @return array List of unique language codes.
 */
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

/**
 * Retrieves distinct language codes from the pages_users dataset.
 *
 * Depending on the global configuration, this function fetches the data via the TD API or a SQL query.
 * The result is cached to avoid redundant lookups during the request lifecycle.
 *
 * @return array List of unique language codes.
 */
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

/**
 * Retrieves a merged list of recent non-empty pages filtered by language.
 *
 * This function fetches two sets of page records from either the TD API or a SQL database—one ordered by the page update timestamp and another by the addition date. It then combines these results, removes any duplicates, and sorts the final list in descending order by the 'pupdate' field.
 *
 * @param string $lang The language code to filter pages by. Use an empty string or "All" for no language filtering.
 *
 * @return array An array of pages sorted by the most recent update time.
 */
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

/**
 * Retrieves title information from either the TD API or SQL database.
 *
 * Depending on the global configuration defined by $use_td_api, this function
 * fetches titles information by either calling the TD API with a 'titles' request
 * or executing a SQL query on the titles_infos table. It returns the retrieved data.
 *
 * @return mixed The retrieved titles information.
 */
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

/**
 * Retrieves recent page user records, optionally filtered by language.
 *
 * This function fetches up to 100 entries from the pages_users source using either the TD API or 
 * a SQL query based on configuration settings. If a specific language (other than "All") is provided, 
 * the results are filtered to include only records matching that language. The returned records are 
 * sorted in descending order by the 'pupdate' field.
 *
 * @param string $lang The language code to filter records by, or "All" to include all languages.
 * @return array An array of page user records sorted by update date.
 */
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

/**
 * Retrieves recent translated records from the specified table.
 *
 * Fetches translation entries from either an API or a SQL database depending on configuration. If a language filter is provided 
 * (i.e., not empty and not "All"), only entries matching that language are retrieved. The resulting records are then sorted in 
 * descending order by their 'add_date' to emphasize the most recent translations.
 *
 * @param string $lang Language filter for the translation records; when empty or "All", no filtering is applied.
 * @param string $table The name of the table from which the translation records are fetched.
 *
 * @return array Sorted list of translation records.
 */
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
