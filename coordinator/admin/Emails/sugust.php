<?php
//---
namespace Emails\Sugust;
/*
Usage:
use function Emails\Sugust\get_sugust;
*/

include_once 'Tables/tables.php';
include_once 'actions/functions.php';
include_once 'results/get_results.php';
include_once 'results/getcats.php';
include_once 'infos/td_config.php';
//---
use function Results\GetCats\get_in_process;
use function Results\GetResults\get_cat_exists_and_missing;
/**
 * Generates a suggestion based on the provided title and language.
 *
 * This function retrieves missing category items, filters out those that are already in process,
 * and selects the most popular suggestion (based on pageview statistics) that differs from the given title.
 * It also measures the execution time for generating the suggestion.
 *
 * If no valid suggestion can be determined, the function returns a JSON-encoded error response with a time of 0.
 *
 * @param string $title The title used for comparison to avoid suggesting the same value.
 * @param string $lang  The language code to filter and localize the suggestion.
 *
 * @return array|string Associative array with keys "sugust" (the suggested value) and "time" (execution duration),
 *                      or a JSON-encoded error message if no suggestion is available.
 */
function get_sugust($title, $lang)
{

    $title  = $title ?? '';
    $lang  = $lang ?? '';
    //---
    $time_start = microtime(true);
    //---
    $sugust = '';
    //---
    if (!empty($title)) {
        $items = get_cat_exists_and_missing('RTT', '', '1', $lang, $use_cache = true);
        //---
        $items_missing = $items['missing'] ?? array();
        //---
        $inprocess = get_in_process($items_missing, $lang);
        //---
        // delete $in_process keys from $missing
        if (!empty($inprocess)) {
            $items_missing = array_diff($items_missing, array_keys($inprocess));
        };
        //---
        if (empty($items_missing)) {
            return json_encode(array('sugust' => '', 'time' => 0, 'error' => 'No suggestions available'));
        }
        //---
        $dd = array();
        //---
        foreach ($items_missing as $t) {
            $t = str_replace('_', ' ', $t);
            $kry = $enwiki_pageviews_table[$t] ?? 0;
            $dd[$t] = $kry;
        };
        //---
        arsort($dd);
        //---
        // $sugust = array_rand($items_missing);
        foreach ($dd as $v => $gt) {
            if ($v != $title) {
                $sugust = $v;
                break;
            };
        };
    };
    //---
    $time_end = microtime(true);
    $time_diff = $time_end - $time_start;
    //---
    $tab = array('sugust' => $sugust, 'time' => $time_diff);
    //---
    return $tab;
}
