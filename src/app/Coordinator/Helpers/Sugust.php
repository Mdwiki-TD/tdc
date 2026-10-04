<?php

namespace App\Coordinator\Helps;

use App\Tables\MainTables;
use App\SQLorAPI\InProcessTable;
use App\Results27\GetResults;

class Sugust
{
    public static function get_sugust($title, $lang): array
    {
        $title = $title ?? '';
        $lang = $lang ?? '';

        if (empty($title)) {
            return array('sugust' => '', 'time' => 0);
        }

        $timeStart = microtime(true);

        $sugust = '';

        $items = GetResults::get_cat_exists_and_missing('RTT', '1', $lang, true);

        $itemsMissing = $items['missing'] ?? [];

        $data = (InProcessTable::getInstance())->getLangInProcessByYear($lang);

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
            $dd[$key] = MainTables::getViews($key);
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
}
