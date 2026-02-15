<?php

declare(strict_types=1);

/**
 * SQL Tables Data Module
 * 
 * Provides centralized access to category, campaign, and project data
 * from the database. Data is loaded once and cached in static class
 * properties for efficient access throughout the application.
 * 
 * Data Tables Managed:
 * - Categories and campaigns mapping
 * - Default category/campaign selection
 * - Category depth settings
 * - Project title to ID mappings
 * 
 * Data Sources:
 * - categories table (via get_td_or_sql_categories)
 * - projects table (via get_td_or_sql_projects)
 * 
 * Usage Example:
 * ```php
 * use Tables\SqlTables\TablesSql;
 * 
 * // Get campaign name from category
 * $campaign = TablesSql::$s_cat_to_camp['RTT'] ?? 'Unknown';
 * 
 * // Get category from campaign
 * $category = TablesSql::$s_camp_to_cat['Main'] ?? '';
 * 
 * // Get project ID from title
 * $projectId = TablesSql::$s_projects_title_to_id['WPMED'] ?? 0;
 * ```
 * 
 * @package    Tables
 * @subpackage SqlTables
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

namespace Tables\SqlTables;

use function SQLorAPI\Funcs\get_td_or_sql_projects;
use function SQLorAPI\Funcs\get_td_or_sql_categories;

/**
 * Static container for SQL table data caches
 * 
 * Provides static properties for cached category, campaign, and project data.
 */
class TablesSql
{
    /**
     * Full translation tracking data
     * 
     * @var array<string,mixed>
     */
    public static array $s_full_translates = [];

    /**
     * No lead translation tracking data
     * 
     * @var array<string,mixed>
     */
    public static array $s_no_lead_translates = [];

    /**
     * Category titles list
     * 
     * @var array<int,string>
     */
    public static array $s_cat_titles = [];

    /**
     * Category to campaign mapping
     * 
     * @var array<string,string>
     */
    public static array $s_cat_to_camp = [];

    /**
     * Campaign to category mapping
     * 
     * @var array<string,string>
     */
    public static array $s_camp_to_cat = [];

    /**
     * Default category code
     * 
     * @var string
     */
    public static string $s_main_cat = '';

    /**
     * Default campaign name
     * 
     * @var string
     */
    public static string $s_main_camp = '';

    /**
     * Campaign to secondary category mapping
     * 
     * @var array<string,string>
     */
    public static array $s_camps_cat2 = [];

    /**
     * Campaign input depth settings
     * 
     * @var array<string,int>
     */
    public static array $s_camp_input_depth = [];

    /**
     * Campaign input list for forms
     * 
     * @var array<string,string>
     */
    public static array $s_campaign_input_list = [];

    /**
     * Category input list for forms
     * 
     * @var array<string,string>
     */
    public static array $s_catinput_list = [];

    /**
     * Settings storage
     * 
     * @var array<string,mixed>
     */
    public static array $s_settings = [];

    /**
     * Project title to ID mapping
     * 
     * @var array<string,int>
     */
    public static array $s_projects_title_to_id = [];

    /**
     * Clear all cached data
     * 
     * @return void
     */
    public static function clearAll(): void
    {
        self::$s_full_translates = [];
        self::$s_no_lead_translates = [];
        self::$s_cat_titles = [];
        self::$s_cat_to_camp = [];
        self::$s_camp_to_cat = [];
        self::$s_main_cat = '';
        self::$s_main_camp = '';
        self::$s_camps_cat2 = [];
        self::$s_camp_input_depth = [];
        self::$s_campaign_input_list = [];
        self::$s_catinput_list = [];
        self::$s_settings = [];
        self::$s_projects_title_to_id = [];
    }
}

// Load categories from database/API
$categories_tab = get_td_or_sql_categories();

// Process categories and populate lookup tables
foreach ($categories_tab as $tab) {
    $category = $tab['category'] ?? '';
    $campaign = $tab['campaign'] ?? '';
    
    if (empty($category) || empty($campaign)) {
        continue;
    }
    
    // Add to titles list
    TablesSql::$s_cat_titles[] = $campaign;
    
    // Secondary category mapping
    if (!empty($tab['category2'])) {
        TablesSql::$s_camps_cat2[$campaign] = $tab['category2'];
    }
    
    // Bidirectional category/campaign mapping
    TablesSql::$s_cat_to_camp[$category] = $campaign;
    TablesSql::$s_camp_to_cat[$campaign] = $category;
    
    // Form input lists
    TablesSql::$s_catinput_list[$category] = $category;
    TablesSql::$s_campaign_input_list[$campaign] = $campaign;
    
    // Category depth
    if (isset($tab['depth'])) {
        TablesSql::$s_camp_input_depth[$campaign] = (int)$tab['depth'];
    }
    
    // Set defaults based on 'def' flag
    $default = $tab['def'] ?? 0;
    if ($default == 1 || $default === '1') {
        TablesSql::$s_main_cat = $category;
        TablesSql::$s_main_camp = $campaign;
    }
}

// Load projects and create title-to-ID mapping
$projects_tab = get_td_or_sql_projects();
TablesSql::$s_projects_title_to_id = array_column($projects_tab, 'g_id', 'g_title');

/**
 * Get the campaign name for a category
 * 
 * @param string $category Category code
 * 
 * @return string Campaign name or original category if not found
 */
function get_campaign_by_category(string $category): string
{
    return TablesSql::$s_cat_to_camp[$category] ?? $category;
}

/**
 * Get the category code for a campaign
 * 
 * @param string $campaign Campaign name
 * 
 * @return string Category code or empty string if not found
 */
function get_category_by_campaign(string $campaign): string
{
    return TablesSql::$s_camp_to_cat[$campaign] ?? '';
}

/**
 * Check if a campaign is the default
 * 
 * @param string $campaign Campaign name
 * 
 * @return bool True if this is the default campaign
 */
function is_default_campaign(string $campaign): bool
{
    return TablesSql::$s_main_camp === $campaign;
}

/**
 * Get the depth setting for a campaign
 * 
 * @param string $campaign Campaign name
 * 
 * @return int Depth value, 0 if not set
 */
function get_campaign_depth(string $campaign): int
{
    return TablesSql::$s_camp_input_depth[$campaign] ?? 0;
}

/**
 * Get all campaign names
 * 
 * @return array<int,string> List of campaign names
 */
function get_all_campaigns(): array
{
    return array_values(TablesSql::$s_campaign_input_list);
}

/**
 * Get all category codes
 * 
 * @return array<int,string> List of category codes
 */
function get_all_categories(): array
{
    return array_values(TablesSql::$s_catinput_list);
}
