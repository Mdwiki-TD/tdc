<?php
//---
namespace Emails\Sugust;
/*
Usage:
use function Emails\Sugust\get_sugust;
*/

//---
use Tables\Main\MainTables;
use function Results\GetResults\get_cat_exists_and_missing;
use function SQLorAPI\Process\get_lang_in_process_new;
//---
function get_sugust($title, $lang)
{
    //---
    $title  = $title ?? '';
    $lang  = $lang ?? '';
    //---
    if (empty($title)) {
        return array('sugust' => '', 'time' => 0);
    };
    // ---
    $time_start = microtime(true);
    //---
    $sugust = '';
    //---
    $items = get_cat_exists_and_missing('RTT', '1', $lang, $use_cache = true);
    //---
    $items_missing = $items['missing'] ?? [];
    //---
    $data = get_lang_in_process_new($lang);
    //---
    $res = array_column($data, 'title');
    //---
    $inprocess = array_intersect($res, $items_missing);
    //---
    // delete $in_process keys from $missing
    if (!empty($inprocess)) {
        $items_missing = array_diff($items_missing, $inprocess);
    }
    //---
    if (empty($items_missing)) {
        return array('sugust' => '', 'time' => 0, 'error' => 'No suggestions available');
    }
    //---
    $dd = [];
    foreach ($items_missing as $t) {
        $key = str_replace('_', ' ', $t);
        $dd[$key] = MainTables::$x_enwiki_pageviews_table[$key] ?? 0;
    }
    //---
    arsort($dd);
    //---
    // $sugust = array_rand($items_missing);
    //---
    foreach ($dd as $v => $gt) {
        if ($v != $title) {
            $sugust = $v;
            break;
        }
    }
    //---
    $tab = array('sugust' => $sugust, 'time' => microtime(true) - $time_start);
    //---
    return $tab;
}
