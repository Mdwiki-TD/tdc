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
//---
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
        $in_process = get_in_process($items_missing, $lang);
        //---
        // delete $in_process keys from $missing
        if (!empty($in_process)) {
            $items_missing = array_diff($items_missing, array_keys($in_process));
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
