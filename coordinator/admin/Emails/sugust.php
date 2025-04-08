<?php
//---
namespace Emails\Sugust;
/*
Usage:
use function Emails\Sugust\get_sugust;
*/

// include_once 'infos/td_config.php';
//---
use Tables\Main\MainTables;
// use function Results\GetCats\get_in_process;
use function Results\GetResults\get_cat_exists_and_missing;
use function SQLorAPI\Process\get_lang_in_process_new;
use function SQLorAPI\Process\get_lang_in_process;
//---
function get_sugust($title, $lang)
{
    //---
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
        $items_missing = $items['missing'] ?? [];
        //---
        // $res = get_lang_in_process($lang);
        $res = get_lang_in_process_new($lang);
        //---
        $inprocess = array_intersect($res, $items_missing);
        //---
        // delete $in_process keys from $missing
        if (!empty($inprocess)) {
            $items_missing = array_diff($items_missing, $inprocess);
        }
        //---
        if (empty($items_missing)) {
            return json_encode(array('sugust' => '', 'time' => 0, 'error' => 'No suggestions available'));
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
    }
    //---
    $time_end = microtime(true);
    $time_diff = $time_end - $time_start;
    //---
    $tab = array('sugust' => $sugust, 'time' => $time_diff);
    //---
    return $tab;
}
