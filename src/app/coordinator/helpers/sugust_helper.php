<?php
// src/app/coordinator/helpers/sugust_helper.php

namespace App\Emails\Sugust;

use App\Tables\Main\MainTables;
use function App\Results\GetResults\get_cat_exists_and_missing;
use function App\SQLorAPI\Process\get_lang_in_process_new;

function get_sugust($title, $lang)
{

    $title  = $title ?? '';
    $lang  = $lang ?? '';

    if (empty($title)) {
        return array('sugust' => '', 'time' => 0);
    };

    $timeStart = microtime(true);

    $sugust = '';

    $items = get_cat_exists_and_missing('RTT', '1', $lang, $useCache = true);

    $itemsMissing = $items['missing'] ?? [];

    $data = get_lang_in_process_new($lang);

    $res = array_column($data, 'title');

    $inprocess = array_intersect($res, $itemsMissing);

    // delete $inProcess keys from $missing
    if (!empty($inprocess)) {
        $itemsMissing = array_diff($itemsMissing, $inprocess);
    }

    if (empty($itemsMissing)) {
        return array('sugust' => '', 'time' => 0, 'error' => 'No suggestions available');
    }

    $dd = [];
    foreach ($itemsMissing as $t) {
        $key = str_replace('_', ' ', $t);
        $dd[$key] = MainTables::$xEnwikiPageviewsTable[$key] ?? 0;
    }

    arsort($dd);

    // $sugust = array_rand($itemsMissing);

    foreach ($dd as $v => $gt) {
        if ($v != $title) {
            $sugust = $v;
            break;
        }
    }

    $tab = array('sugust' => $sugust, 'time' => microtime(true) - $timeStart);

    return $tab;
}
