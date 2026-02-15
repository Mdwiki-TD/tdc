<?php

declare(strict_types=1);

/**
 * Main Tables Data Module
 * 
 * Provides centralized access to translation statistics and metadata
 * from the titles_infos table. Data is loaded once and cached in
 * static class properties for efficient access throughout the application.
 * 
 * Data Tables Managed:
 * - Page views from English Wikipedia
 * - Word counts (lead and full article)
 * - Reference counts (lead and all)
 * - Article importance assessments
 * - Language name mappings
 * 
 * Data Sources:
 * - titles_infos table (via API or direct SQL)
 * - lang_names.json (local file)
 * 
 * Usage Example:
 * ```php
 * use Tables\Main\MainTables;
 * 
 * // Get word count for lead section
 * $lead_words = MainTables::$x_Words_table['COVID-19'] ?? 0;
 * 
 * // Get total views from English Wikipedia
 * $views = MainTables::$x_enwiki_pageviews_table['COVID-19'] ?? 0;
 * ```
 * 
 * @package    Tables
 * @subpackage Main
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

namespace Tables\Main;

use function SQLorAPI\Funcs\td_or_sql_titles_infos;
use function Utils\TablesDir\open_td_Tables_file;

// Enable debug mode
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

/**
 * Static container for cached table data
 * 
 * This class provides static properties that hold cached data loaded
 * from the database or API. Data is loaded once at module initialization
 * and accessed via static properties throughout the application.
 * 
 * Property Naming Convention:
 * - $x_enwiki_pageviews_table: Article => monthly views from enwiki
 * - $x_Words_table: Article => lead section word count
 * - $x_All_Words_table: Article => full article word count
 * - $x_All_Refs_table: Article => total reference count
 * - $x_Lead_Refs_table: Article => lead section reference count
 * - $x_Assessments_table: Article => importance assessment
 * - $x_Langs_table: Language code => language name
 */
class MainTables
{
    /**
     * English Wikipedia monthly page views
     * 
     * @var array<string,int>
     */
    public static array $x_enwiki_pageviews_table = [];

    /**
     * Lead section word counts
     * 
     * @var array<string,int>
     */
    public static array $x_Words_table = [];

    /**
     * Full article word counts
     * 
     * @var array<string,int>
     */
    public static array $x_All_Words_table = [];

    /**
     * Total reference counts
     * 
     * @var array<string,int>
     */
    public static array $x_All_Refs_table = [];

    /**
     * Lead section reference counts
     * 
     * @var array<string,int>
     */
    public static array $x_Lead_Refs_table = [];

    /**
     * Article importance assessments
     * 
     * @var array<string,string>
     */
    public static array $x_Assessments_table = [];

    /**
     * Language code to name mappings
     * 
     * @var array<string,string>
     */
    public static array $x_Langs_table = [];

    /**
     * Clear all cached data
     * 
     * Useful for testing or forcing data refresh.
     * 
     * @return void
     */
    public static function clearAll(): void
    {
        self::$x_enwiki_pageviews_table = [];
        self::$x_Words_table = [];
        self::$x_All_Words_table = [];
        self::$x_All_Refs_table = [];
        self::$x_Lead_Refs_table = [];
        self::$x_Assessments_table = [];
        self::$x_Langs_table = [];
    }
}

// Load titles information from API or database
$titles_infos = td_or_sql_titles_infos();

// Populate static tables from titles_infos data
foreach ($titles_infos as $tab) {
    $title = $tab['title'] ?? '';
    
    if (empty($title)) {
        continue;
    }
    
    // Page views from English Wikipedia
    MainTables::$x_enwiki_pageviews_table[$title] = (int)($tab['en_views'] ?? 0);
    
    // Word counts
    MainTables::$x_Words_table[$title] = (int)($tab['w_lead_words'] ?? 0);
    MainTables::$x_All_Words_table[$title] = (int)($tab['w_all_words'] ?? 0);
    
    // Reference counts
    MainTables::$x_All_Refs_table[$title] = (int)($tab['r_all_refs'] ?? 0);
    MainTables::$x_Lead_Refs_table[$title] = (int)($tab['r_lead_refs'] ?? 0);
    
    // Importance assessment
    MainTables::$x_Assessments_table[$title] = (string)($tab['importance'] ?? '');
}

// Load language name mappings from JSON file
$langNamesFile = __DIR__ . '/lang_names.json';
if (file_exists($langNamesFile)) {
    $contents = file_get_contents($langNamesFile);
    
    if ($contents !== false) {
        $data = json_decode($contents, true);
        
        if (is_array($data)) {
            MainTables::$x_Langs_table = $data;
            ksort(MainTables::$x_Langs_table);
        }
    } else {
        error_log('Failed to read lang_names.json');
    }
}

/**
 * Get word count for an article
 * 
 * Convenience function that returns the appropriate word count
 * based on translation type.
 * 
 * @param string $title     Article title
 * @param string $tr_type   Translation type ('lead' or 'all')
 * 
 * @return int Word count, 0 if not found
 */
function get_word_count(string $title, string $tr_type = 'lead'): int
{
    if ($tr_type === 'all') {
        return MainTables::$x_All_Words_table[$title] ?? 0;
    }
    return MainTables::$x_Words_table[$title] ?? 0;
}

/**
 * Get reference count for an article
 * 
 * @param string $title   Article title
 * @param string $section 'lead' for lead section, 'all' for entire article
 * 
 * @return int Reference count, 0 if not found
 */
function get_ref_count(string $title, string $section = 'all'): int
{
    if ($section === 'lead') {
        return MainTables::$x_Lead_Refs_table[$title] ?? 0;
    }
    return MainTables::$x_All_Refs_table[$title] ?? 0;
}

/**
 * Check if an article exists in the tables
 * 
 * @param string $title Article title
 * 
 * @return bool True if article has data
 */
function article_exists_in_tables(string $title): bool
{
    return isset(MainTables::$x_Words_table[$title]) 
        || isset(MainTables::$x_All_Words_table[$title]);
}
