<?php

declare(strict_types=1);

/**
 * In-Process Translation Data Functions
 * 
 * Provides functions for retrieving and managing translations that are
 * currently in progress. These translations are tracked in the in_process
 * table and represent work that has been started but not yet completed.
 * 
 * Features:
 * - Get all in-process translations
 * - Filter by user
 * - Filter by language
 * - Static caching for performance
 * 
 * Usage Example:
 * ```php
 * use function SQLorAPI\Process\get_process_all_new;
 * use function SQLorAPI\Process\get_user_process_new;
 * 
 * // Get all recent in-process items
 * $all = get_process_all_new();
 * 
 * // Get specific user's in-process items
 * $userItems = get_user_process_new('JohnDoe', '2024');
 * ```
 * 
 * @package    SQLorAPI
 * @subpackage Process
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

namespace SQLorAPI\Process;

use function SQLorAPI\Get\super_function;
use function SQLorAPI\Get\isvalid;

/**
 * Get all in-process translations (limited to recent 100)
 * 
 * Returns the most recent 100 translations currently being worked on.
 * Results are cached statically for the request lifetime.
 * 
 * @return array<int,array<string,mixed>> In-process translation records
 */
function get_process_all_new(): array
{
    static $process_all = [];
    
    if (!empty($process_all)) {
        return $process_all;
    }
    
    $api_params = ['get' => 'in_process', 'limit' => "100", "order" => 'add_date'];
    $sql = "SELECT * FROM in_process ORDER BY add_date DESC LIMIT 100";
    
    $process_all = super_function($api_params, [], $sql);
    
    return $process_all;
}

/**
 * Get in-process translations for a specific user
 * 
 * Returns all in-process items for the given user, optionally filtered
 * by year. Results are cached per user.
 * 
 * @param string $user   Username to filter by
 * @param string $year_y Year to filter by ('all' for no filter)
 * 
 * @return array<int,array<string,mixed>> In-process records for the user
 * 
 * @example
 * ```php
 * // Get all in-process for user
 * $items = get_user_process_new('JohnDoe');
 * 
 * // Get 2024 items only
 * $items = get_user_process_new('JohnDoe', '2024');
 * ```
 */
function get_user_process_new(string $user, string $year_y = "all"): array
{
    static $cache = [];
    
    $cache_key = $user . '_' . $year_y;
    
    if (!empty($cache[$cache_key] ?? [])) {
        return $cache[$cache_key];
    }
    
    $api_params = ['get' => 'in_process', 'user' => $user];
    $query = "SELECT * FROM in_process WHERE user = ?";
    $params = [$user];
    
    if (isvalid($year_y)) {
        $query .= " AND YEAR(add_date) = ?";
        $params[] = $year_y;
        $api_params['year'] = $year_y;
    }
    
    $data = super_function($api_params, $params, $query, "in_process", true);
    
    $cache[$cache_key] = $data;
    
    return $data;
}

/**
 * Get count of in-process translations per user
 * 
 * Returns aggregated counts of in-process items grouped by user.
 * Useful for displaying user workloads.
 * 
 * @return array<string,int> User => count mapping
 */
function get_users_process_new(): array
{
    static $process_new = [];
    
    if (!empty($process_new)) {
        return $process_new;
    }
    
    $api_params = [
        'get' => 'in_process',
        'distinct' => 'true',
        'select' => 'user',
        'group' => 'user',
        'order' => '2',
        'count' => '*'
    ];
    
    $sql = 'SELECT DISTINCT user, COUNT(*) as count FROM in_process GROUP BY user ORDER BY count DESC';
    
    $tab = super_function($api_params, [], $sql);
    $process_new = array_column($tab, 'count', 'user');
    
    return $process_new;
}

/**
 * Get in-process translations for a specific language
 * 
 * Returns all in-process items for the given language code,
 * optionally filtered by year. Results are cached per language.
 * 
 * @param string $code   Language code to filter by
 * @param string $year_y Year to filter by ('all' for no filter)
 * 
 * @return array<int,array<string,mixed>> In-process records for the language
 * 
 * @example
 * ```php
 * // Get Arabic in-process items
 * $arItems = get_lang_in_process_new('ar');
 * 
 * // Get 2024 Spanish items
 * $esItems = get_lang_in_process_new('es', '2024');
 * ```
 */
function get_lang_in_process_new(string $code, string $year_y = "all"): array
{
    static $cache = [];
    
    $cache_key = $code . '_' . $year_y;
    
    if (!empty($cache[$cache_key] ?? [])) {
        return $cache[$cache_key];
    }
    
    $query = "SELECT * FROM in_process WHERE lang = ?";
    $api_params = ['get' => 'in_process', 'lang' => $code];
    $params = [$code];
    
    if (isvalid($year_y)) {
        $query .= " AND YEAR(add_date) = ?";
        $params[] = $year_y;
        $api_params['year'] = $year_y;
    }
    
    $data = super_function($api_params, $params, $query, "in_process", true);
    
    $cache[$cache_key] = $data;
    
    return $cache[$cache_key];
}

/**
 * Get total count of in-process translations
 * 
 * @return int Total number of in-process items
 */
function get_process_count(): int
{
    static $count = null;
    
    if ($count !== null) {
        return $count;
    }
    
    $api_params = ['get' => 'in_process', 'count' => '1'];
    $sql = "SELECT COUNT(*) as count FROM in_process";
    
    $result = super_function($api_params, [], $sql);
    
    $count = (int)($result[0]['count'] ?? 0);
    
    return $count;
}

/**
 * Clear all in-process caches
 * 
 * Forces fresh data retrieval on next call.
 * Useful after data modifications.
 * 
 * @return void
 */
function clear_process_cache(): void
{
    // Note: PHP static variables cannot be cleared externally
    // This function is a placeholder for future cache implementation
    // Currently, caches are request-scoped and clear automatically
}
