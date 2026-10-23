<?php

namespace App\Coordinator\Helps;

use App\Tables\MainTables;
use App\SQLorAPI\InProcessTable;
use App\Results27\GetResults;

class Sugust
{
    public static function get_sugust(?string $title, ?string $lang): array
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

        $data1 = (InProcessTable::getInstance())->getLangInProcessByYear($lang);

        $res = array_column($data1, 'title');

        $inprocess = array_intersect($res, $itemsMissing);

        // delete $inProcess keys from $missing
        if (!empty($inprocess)) {
            $itemsMissing = array_diff($itemsMissing, $inprocess);
        }

        if (empty($itemsMissing)) {
            return array('sugust' => '', 'time' => 0, 'error' => 'No suggestions available');
        }

        $data = [];
        foreach ($itemsMissing as $t) {
            $key = str_replace('_', ' ', $t);
            $data[$key] = MainTables::getViews($key);
        }

        arsort($data);

        // $sugust = array_rand($itemsMissing);

        foreach ($data as $v => $gt) {
            if ($v != $title) {
                $sugust = $v;
                break;
            }
        }

        $tab = array('sugust' => $sugust, 'time' => microtime(true) - $timeStart);

        return $tab;
    }
}
