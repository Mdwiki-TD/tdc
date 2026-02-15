<?php

declare(strict_types=1);

/**
 * API/SQL Abstraction Layer - Super Function
 * 
 * Provides the core abstraction function that routes data requests
 * to either the Translation Dashboard API or direct SQL queries
 * based on configuration and availability.
 * 
 * Architecture:
 * - Primary source: TD API (when enabled and available)
 * - Fallback: Direct SQL queries
 * - Result caching is handled by calling functions
 * 
 * Configuration:
 * - use_td_api setting controls API vs SQL preference
 * - use_td_api=x GET parameter disables API
 * - use_td_api GET parameter enables API
 * 
 * Usage Example:
 * ```php
 * use function SQLorAPI\Get\super_function;
 * 
 * // Get data via API or SQL
 * $data = super_function(
 *     ['get' => 'categories'],           // API parameters
 *     [],                                 // SQL parameters
 *     'SELECT * FROM categories'          // SQL query
 * );
 * ```
 * 
 * @package    SQLorAPI
 * @subpackage Get
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

namespace SQLorAPI\Get;

use function APICalls\MdwikiSql\fetch_query;
use function APICalls\TDApi\get_td_api;

// Load settings to determine API usage preference
$settings_table = array_column(get_td_api(['get' => 'settings']), 'value', 'title');

// Default to SQL (API disabled) for reliability
$use_td_api = (($settings_table['use_td_api'] ?? "") === "1");

// Allow runtime override via GET parameter
if (isset($_GET['use_td_api'])) {
    $use_td_api = $_GET['use_td_api'] !== "x";
}

/**
 * Check if a string value is valid (non-empty and not 'all')
 * 
 * Used for filtering parameters where 'all' means no filter.
 * 
 * @param string|null $str The string to validate
 * 
 * @return bool True if the string is valid for filtering
 */
function isvalid(?string $str): bool
{
    return !empty($str) && strtolower($str) !== "all";
}

/**
 * Universal data retrieval function
 * 
 * Attempts to retrieve data from the API first (if enabled),
 * falling back to direct SQL query if API fails or returns empty.
 * 
 * @param array<string,mixed>     $api_params  Parameters for API request
 * @param array<int,mixed>        $sql_params  Parameters for SQL prepared statement
 * @param string                  $sql_query   SQL query to execute as fallback
 * @param string|null             $table_name  Table name for database selection
 * @param bool                    $no_refind   If true, don't fallback to SQL on empty API result
 * 
 * @return array<int,array<string,mixed>> Data records
 * 
 * @example
 * ```php
 * // Get categories
 * $categories = super_function(
 *     ['get' => 'categories'],
 *     [],
 *     'SELECT * FROM categories'
 * );
 * 
 * // Get user's translations with parameters
 * $translations = super_function(
 *     ['get' => 'pages', 'user' => 'JohnDoe'],
 *     ['JohnDoe'],
 *     'SELECT * FROM pages WHERE user = ?'
 * );
 * ```
 */
function super_function(
    array $api_params,
    array $sql_params,
    string $sql_query,
    ?string $table_name = null,
    bool $no_refind = false
): array {
    global $use_td_api;
    
    $data = [];
    
    // Try API first if enabled
    if ($use_td_api) {
        $data = get_td_api($api_params);
    }
    
    // Fallback to SQL if API returned empty
    if (empty($data) && !$no_refind) {
        $data = fetch_query($sql_query, $sql_params, $table_name);
    }
    
    // Ensure we always return an array
    if (!is_array($data)) {
        $data = [];
    }
    
    return $data;
}

/**
 * Check if API mode is currently enabled
 * 
 * @return bool True if API should be used for data retrieval
 */
function is_api_enabled(): bool
{
    global $use_td_api;
    return $use_td_api;
}

/**
 * Force API mode for current request
 * 
 * @param bool $enabled Whether to enable API mode
 * 
 * @return void
 */
function set_api_mode(bool $enabled): void
{
    global $use_td_api;
    $use_td_api = $enabled;
}
