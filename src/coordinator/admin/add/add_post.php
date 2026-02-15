<?php

declare(strict_types=1);

/**
 * Add Translations to Database Module
 * 
 * Provides functions for inserting and updating translation records
 * in the pages table. Handles deduplication and word count lookups.
 * 
 * Features:
 * - Insert new translation records
 * - Update existing records with completion data
 * - Automatic word count from cached data
 * - Duplicate prevention
 * 
 * @package    Add
 * @subpackage AddPost
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

namespace Add\AddPost;

use Tables\Main\MainTables;
use function APICalls\MdwikiSql\execute_query;
use function APICalls\MdwikiSql\fetch_query;

/**
 * Insert or update a translation record in the pages table
 * 
 * This function handles both:
 * 1. Updating existing incomplete records with target/pupdate/word
 * 2. Inserting new records if no matching record exists
 * 
 * Deduplication is based on: title + lang + user
 * 
 * @param array<string,mixed> $t Translation data array with keys:
 *        - user: Translator username
 *        - lang: Target language code
 *        - title: Source article title
 *        - target: Translated article title
 *        - pupdate: Publication/update date
 *        - cat: Category/campaign
 *        - translate_type: 'lead' or 'all'
 *        - word: Word count (0 to auto-lookup)
 * 
 * @return array<int,array<string,mixed>>|false Query result or false on error
 */
function insert_to_pages(array $t): array|false
{
    // Normalize underscores to spaces in all string values
    foreach ($t as $key => $value) {
        if (is_string($value)) {
            $t[$key] = str_replace('_', ' ', $value);
        }
    }
    
    // Query 1: Update existing incomplete record
    $query1 = <<<SQL
        UPDATE pages
        SET target = ?, pupdate = ?, word = ?
        WHERE user = ? AND title = ? AND lang = ? AND (target = '' OR target IS NULL)
    SQL;
    
    $params1 = [
        $t['target'] ?? '',
        $t['pupdate'] ?? '',
        $t['word'] ?? 0,
        $t['user'] ?? '',
        $t['title'] ?? '',
        $t['lang'] ?? ''
    ];
    
    execute_query($query1, $params1);
    
    // Query 2: Insert new record if not exists
    $query2 = <<<SQL
        INSERT INTO pages (title, word, translate_type, cat, lang, date, user, pupdate, target, add_date)
        SELECT ?, ?, ?, ?, ?, DATE(NOW()), ?, ?, ?, NOW()
        WHERE NOT EXISTS (SELECT 1 FROM pages WHERE title = ? AND lang = ? AND user = ?)
    SQL;
    
    $params2 = [
        $t['title'] ?? '',
        $t['word'] ?? 0,
        $t['translate_type'] ?? 'lead',
        $t['cat'] ?? '',
        $t['lang'] ?? '',
        $t['user'] ?? '',
        $t['pupdate'] ?? '',
        $t['target'] ?? '',
        $t['title'] ?? '',
        $t['lang'] ?? '',
        $t['user'] ?? ''
    ];
    
    if (isset($_REQUEST['test'])) {
        error_log("insert_to_pages query1: {$query1}");
        error_log("insert_to_pages query2: {$query2}");
    }
    
    return execute_query($query2, $params2);
}

/**
 * Add a translation record to the database
 * 
 * High-level function that prepares translation data and inserts it.
 * Automatically looks up word count if not provided.
 * 
 * @param string      $title          Source article title (MDWiki)
 * @param string      $translate_type Translation type ('lead' or 'all')
 * @param string      $cat            Category/campaign code
 * @param string      $lang           Target language code
 * @param string      $user           Translator username
 * @param string      $target         Translated article title
 * @param string      $pupdate        Publication date
 * @param int|string  $word           Word count (0 for auto-lookup)
 * 
 * @return bool True if insert/update was successful
 * 
 * @example
 * ```php
 * $success = add_pages_to_db(
 *     'COVID-19 pandemic',  // title
 *     'lead',               // translate_type
 *     'RTT',                // category
 *     'ar',                 // language
 *     'JohnDoe',            // user
 *     'جائحة فيروس كورونا', // target
 *     '2024-01-15',         // pupdate
 *     0                     // word (auto-lookup)
 * );
 * ```
 */
function add_pages_to_db(
    string $title,
    string $translate_type,
    string $cat,
    string $lang,
    string $user,
    string $target,
    string $pupdate,
    int|string $word
): bool {
    // Set defaults
    $translate_type = (!empty($translate_type)) ? $translate_type : 'lead';
    $cat = (!empty($cat)) ? $cat : 'RTT';
    
    // Auto-lookup word count if not provided
    if (empty($word)) {
        if ($translate_type === 'all') {
            $word = MainTables::$x_All_Words_table[$title] ?? 0;
        } else {
            $word = MainTables::$x_Words_table[$title] ?? 0;
        }
    }
    
    // Prepare data array
    $t = [
        'user' => trim($user),
        'lang' => trim($lang),
        'title' => trim($title),
        'target' => trim($target),
        'pupdate' => trim($pupdate),
        'cat' => trim($cat),
        'translate_type' => trim($translate_type),
        'word' => (int)$word
    ];
    
    // Execute insert/update
    insert_to_pages($t);
    
    // Verify the record was created/updated
    $find_query = "SELECT * FROM pages WHERE title = ? AND lang = ? AND user = ? AND target = ?";
    $find_params = [trim($title), trim($lang), trim($user), trim($target)];
    
    $find_it = fetch_query($find_query, $find_params);
    
    return !empty($find_it);
}

/**
 * Batch add multiple translation records
 * 
 * @param array<int,array<string,mixed>> $records Array of translation data
 * 
 * @return array{success:int,failed:int,errors:array<int,string>} Results summary
 */
function batch_add_pages(array $records): array
{
    $results = [
        'success' => 0,
        'failed' => 0,
        'errors' => []
    ];
    
    foreach ($records as $index => $record) {
        $required = ['title', 'lang', 'user'];
        $missing = [];
        
        foreach ($required as $field) {
            if (empty($record[$field] ?? '')) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $results['failed']++;
            $results['errors'][] = "Record {$index}: Missing required fields: " . implode(', ', $missing);
            continue;
        }
        
        $success = add_pages_to_db(
            $record['title'] ?? '',
            $record['translate_type'] ?? 'lead',
            $record['cat'] ?? 'RTT',
            $record['lang'] ?? '',
            $record['user'] ?? '',
            $record['target'] ?? '',
            $record['pupdate'] ?? '',
            $record['word'] ?? 0
        );
        
        if ($success) {
            $results['success']++;
        } else {
            $results['failed']++;
            $results['errors'][] = "Record {$index}: Failed to insert/update";
        }
    }
    
    return $results;
}
