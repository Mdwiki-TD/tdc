<?php

declare(strict_types=1);

/**
 * API/SQL Abstraction Layer - Data Functions
 * 
 * Provides a unified interface for retrieving data from either the
 * Translation Dashboard API or direct SQL database queries. This allows
 * for flexible deployment where some environments use the API while
 * others use direct database access.
 * 
 * Features:
 * - Static caching to prevent duplicate queries
 * - Automatic fallback from API to SQL
 * - Consistent return types across all functions
 * - Parameter validation and sanitization
 * 
 * Data Sources:
 * - categories: Translation categories and campaigns
 * - coordinator: Coordinator user list
 * - users_by_last_pupdate: Recent user activity
 * - count_pages: User translation counts
 * - settings: Application settings
 * - qids: Wikidata ID mappings
 * - projects: Project definitions
 * - language_settings: Per-language configuration
 * 
 * Usage Example:
 * ```php
 * use function SQLorAPI\Funcs\get_coordinator;
 * use function SQLorAPI\Funcs\get_td_or_sql_categories;
 * 
 * // Get coordinator list
 * $coordinators = get_coordinator();
 * 
 * // Get categories
 * $categories = get_td_or_sql_categories();
 * ```
 * 
 * @package    SQLorAPI
 * @subpackage Funcs
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

namespace SQLorAPI\Funcs;

use function SQLorAPI\Get\super_function;

/**
 * Get publish reports statistics
 * 
 * Returns aggregated statistics about published translation reports,
 * grouped by year, month, language, user, and result.
 * 
 * @return array<int,array<string,mixed>> Statistics records
 */
function get_publish_reports_stats(): array
{
    static $stats_data = [];
    
    if (!empty($stats_data)) {
        return $stats_data;
    }
    
    $query = <<<SQL
        SELECT DISTINCT YEAR(date) as year, MONTH(date) as month, lang, user, result
        FROM publish_reports
        GROUP BY year, month, lang, user, result
    SQL;
    
    $api_params = ['get' => 'publish_reports_stats'];
    
    $stats_data = super_function($api_params, [], $query, 'publish_reports');
    
    return $stats_data;
}

/**
 * Get all translation categories
 * 
 * Returns category definitions including campaign mappings and depth settings.
 * 
 * @return array<int,array<string,mixed>> Category records
 */
function get_td_or_sql_categories(): array
{
    static $categories = [];
    
    if (!empty($categories)) {
        return $categories;
    }
    
    $api_params = ['get' => 'categories'];
    $query = "SELECT id, category, category2, campaign, depth, def FROM categories";
    
    $categories = super_function($api_params, [], $query);
    
    return $categories;
}

/**
 * Get coordinator user list
 * 
 * Returns list of users with coordinator privileges and their active status.
 * 
 * @return array<int,array<string,mixed>> Coordinator records
 */
function get_coordinator(): array
{
    static $coordinator = [];
    
    if (!empty($coordinator)) {
        return $coordinator;
    }
    
    $api_params = ['get' => 'coordinator'];
    $query = "SELECT id, user, active FROM coordinator ORDER BY id";
    
    $coordinator = super_function($api_params, [], $query);
    
    return $coordinator;
}

/**
 * Get users grouped by their most recent translation date
 * 
 * Returns the most recent translation for each user, useful for
 * identifying active vs inactive translators.
 * 
 * @return array<string,array<string,mixed>> User => latest translation data
 */
function get_users_by_last_pupdate(): array
{
    static $last_user_to_tab = [];
    
    if (!empty($last_user_to_tab)) {
        return $last_user_to_tab;
    }
    
    $api_params = ['get' => 'users_by_last_pupdate'];
    
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
    
    $data = super_function($api_params, [], $query);
    
    foreach ($data as $gg) {
        $user = $gg['user'] ?? '';
        if (!empty($user)) {
            $last_user_to_tab[$user] = $gg;
        }
    }
    
    return $last_user_to_tab;
}

/**
 * Get translation counts per user
 * 
 * Returns count of completed translations (non-empty targets) grouped by user.
 * 
 * @return array<string,int> User => translation count
 */
function get_td_or_sql_count_pages_not_empty(): array
{
    static $count_pages = [];
    
    if (!empty($count_pages)) {
        return $count_pages;
    }
    
    $api_params = ['get' => 'count_pages', 'target' => 'not_empty'];
    $query = "SELECT DISTINCT user, COUNT(target) as count FROM pages WHERE target != '' GROUP BY user ORDER BY count DESC";
    
    $data = super_function($api_params, [], $query);
    $data = array_column($data, 'count', 'user');
    arsort($data);
    
    $count_pages = $data;
    
    return $data;
}

/**
 * Get users from pages table who are not in the users table
 * 
 * Identifies translators who have entries in the pages table
 * but no corresponding user record.
 * 
 * @return array<int,string> List of usernames
 */
function get_td_or_sql_page_user_not_in_users(): array
{
    static $users = [];
    
    if (!empty($users)) {
        return $users;
    }
    
    $api_params = ['get' => 'pages', 'distinct' => 1, 'select' => 'user'];
    $query = "SELECT DISTINCT p.user FROM pages AS p WHERE NOT EXISTS (SELECT 1 FROM users AS u WHERE p.user = u.username)";
    
    $data = super_function($api_params, [], $query);
    $users = array_column($data, 'user');
    
    return $users;
}

/**
 * Get per-language settings
 * 
 * Returns configuration options for each language code.
 * 
 * @return array<int,array<string,mixed>> Language setting records
 */
function get_td_or_sql_language_settings(): array
{
    static $data_langs = [];
    
    if (!empty($data_langs)) {
        return $data_langs;
    }
    
    $api_params = ['get' => 'language_settings'];
    $query = "SELECT * FROM language_settings ORDER BY lang_code";
    
    $data_langs = super_function($api_params, [], $query);
    
    return $data_langs;
}

/**
 * Get list of users excluded from in-process tracking
 * 
 * @return array<int,array<string,mixed>> User records with active status
 */
function get_td_or_sql_users_no_inprocess(): array
{
    static $users = [];
    
    if (!empty($users)) {
        return $users;
    }
    
    $api_params = ['get' => 'users_no_inprocess'];
    $query = "SELECT id, user, active FROM users_no_inprocess ORDER BY id";
    
    $users = super_function($api_params, [], $query);
    
    return $users;
}

/**
 * Get full translators list
 * 
 * Returns users designated as full translators with their active status.
 * 
 * @return array<int,array<string,mixed>> Full translator records
 */
function get_td_or_sql_full_translators(): array
{
    static $full_translators = [];
    
    if (!empty($full_translators)) {
        return $full_translators;
    }
    
    $api_params = ['get' => 'full_translators'];
    $query = "SELECT id, user, active FROM full_translators ORDER BY id";
    
    $full_translators = super_function($api_params, [], $query);
    
    return $full_translators;
}

/**
 * Get project definitions
 * 
 * Returns all projects with their IDs and titles.
 * 
 * @return array<int,array<string,mixed>> Project records
 */
function get_td_or_sql_projects(): array
{
    static $projects = [];
    
    if (!empty($projects)) {
        return $projects;
    }
    
    $api_params = ['get' => 'projects'];
    $query = "SELECT g_id, g_title FROM projects";
    
    $projects = super_function($api_params, [], $query);
    
    return $projects;
}

/**
 * Get Wikidata ID mappings
 * 
 * Returns title-to-QID mappings. Can filter by status.
 * 
 * @param string $dis Display type: 'empty', 'all', or 'duplicate'
 * 
 * @return array<int,array<string,mixed>> QID records
 */
function get_td_or_sql_qids(string $dis): array
{
    static $cache = [];
    
    if (!empty($cache[$dis] ?? [])) {
        return $cache[$dis];
    }
    
    $api_params = ['get' => 'qids', 'dis' => $dis];
    
    $queries = [
        'empty' => "SELECT id, title, qid FROM qids WHERE (qid = '' OR qid IS NULL);",
        'all' => "SELECT id, title, qid FROM qids;",
        'duplicate' => <<<SQL
            SELECT
                A.id AS id, A.title AS title, A.qid AS qid,
                B.id AS id2, B.title AS title2, B.qid AS qid2
            FROM qids A
            JOIN qids B ON A.qid = B.qid
            WHERE A.qid != '' AND A.title != B.title AND A.id != B.id;
        SQL
    ];
    
    $query = array_key_exists($dis, $queries) ? $queries[$dis] : $queries['all'];
    
    $data = super_function($api_params, [], $query);
    $cache[$dis] = $data;
    
    return $data;
}

/**
 * Get Wikidata ID mappings from other sources table
 * 
 * @param string $dis Display type: 'empty', 'all', or 'duplicate'
 * 
 * @return array<int,array<string,mixed>> QID records
 */
function get_td_or_sql_qids_others(string $dis): array
{
    static $cache = [];
    
    if (!empty($cache[$dis] ?? [])) {
        return $cache[$dis];
    }
    
    $api_params = ['get' => 'qids_others', 'dis' => $dis];
    
    $queries = [
        'empty' => "SELECT id, title, qid FROM qids_others WHERE (qid = '' OR qid IS NULL);",
        'all' => "SELECT id, title, qid FROM qids_others;",
        'duplicate' => <<<SQL
            SELECT
                A.id AS id, A.title AS title, A.qid AS qid,
                B.id AS id2, B.title AS title2, B.qid AS qid2
            FROM qids_others A
            JOIN qids_others B ON A.qid = B.qid
            WHERE A.qid != '' AND A.title != B.title AND A.id != B.id;
        SQL
    ];
    
    $query = array_key_exists($dis, $queries) ? $queries[$dis] : $queries['all'];
    
    $data = super_function($api_params, [], $query);
    $cache[$dis] = $data;
    
    return $data;
}

/**
 * Get application settings
 * 
 * Returns all settings from the settings table.
 * 
 * @return array<int,array<string,mixed>> Settings records
 */
function get_td_or_sql_settings(): array
{
    static $setting_d = [];
    
    if (!empty($setting_d)) {
        return $setting_d;
    }
    
    $api_params = ['get' => 'settings'];
    $query = "SELECT id, title, displayed, value, Type, ignored FROM settings";
    
    $setting_d = super_function($api_params, [], $query);
    
    return $setting_d;
}

/**
 * Get distinct languages from pages table
 * 
 * @return array<int,string> List of language codes
 */
function get_pages_langs(): array
{
    static $pages_langs = [];
    
    if (!empty($pages_langs)) {
        return $pages_langs;
    }
    
    $api_params = ['get' => 'pages', 'distinct' => "1", 'select' => 'lang', 'lang' => 'not_empty'];
    $query = "SELECT DISTINCT lang FROM pages WHERE (lang != '' AND lang IS NOT NULL)";
    
    $data = super_function($api_params, [], $query);
    $pages_langs = array_column($data, 'lang');
    
    return $pages_langs;
}

/**
 * Get distinct languages from pages_users table
 * 
 * @return array<int,string> List of language codes
 */
function get_pages_users_langs(): array
{
    static $pages_users_langs = [];
    
    if (!empty($pages_users_langs)) {
        return $pages_users_langs;
    }
    
    $api_params = ['get' => 'pages_users', 'distinct' => "1", 'select' => 'lang'];
    $query = "SELECT DISTINCT lang FROM pages_users";
    
    $data = super_function($api_params, [], $query);
    $pages_users_langs = array_column($data, 'lang');
    
    return $pages_users_langs;
}

/**
 * Get title information from titles_infos table
 * 
 * Returns metadata about titles including word counts, references, and views.
 * 
 * @param array<int,string> $titles Optional list of titles to filter (empty = all)
 * 
 * @return array<int,array<string,mixed>> Title information records
 */
function td_or_sql_titles_infos(array $titles = []): array
{
    $api_params = ['get' => 'titles', 'titles' => $titles];
    
    $query = "SELECT * FROM titles_infos";
    $sql_params = [];
    
    if (!empty($titles)) {
        $placeholders = rtrim(str_repeat('?,', count($titles)), ',');
        $query .= " WHERE title IN ({$placeholders})";
        $sql_params = $titles;
    }
    
    $data = super_function($api_params, $sql_params, $query);
    
    return $data;
}
