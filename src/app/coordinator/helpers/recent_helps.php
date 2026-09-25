<?php
// src/app/coordinator/helpers/recent_helps.php

namespace App\Coordinator\Helps\RecentHelps;

use App\Tables\Langs\LangsTables;
use function App\Utils\test_print;

function filter_recent2($lang, $result)
{

    ksort($result);

    $langList = "<option data-tokens='All' value='All'>All</option>";

    foreach ($result as $langCode) {
        $langeee = LangsTables::$LCodeToLang[$langCode] ?? '';
        $selected = ($langCode == $lang) ? 'selected' : '';
        if (empty($langCode)) continue;
        $langList .= <<<HTML
            <option data-tokens='$langCode' value='$langCode' $selected>$langeee</option>
            HTML;
    };

    return $langList;
}

function do_add_date($results)
{

    foreach ($results as $tat => $tabe) {
        $pupdate  = $tabe['pupdate'] ?? ''; // 2025-01-30
        $addDate = $tabe['add_date'] ?? ''; // 2025-01-29

        if (strpos($pupdate, ':') !== false) $pupdate = explode(' ', $pupdate)[0];
        if (strpos($addDate, ':') !== false) $addDate = explode(' ', $addDate)[0];

        $addDate = str_replace("-", "", $addDate);
        $pupdate  = str_replace("-", "", $pupdate);

        // change str to number
        $addAdd = intval($addDate);
        $addPup = intval($pupdate);

        if ($addAdd > $addPup) {
            test_print("add_add($addAdd) > add_pup($addPup)");
            return true;
        }

    };

    return false;
}

function filter_table($data, $vav, $id)
{

    $lList = "";

    foreach ($data as $tableName => $label) {
        $checked = ($tableName == $vav) ? "checked" : "";
        $lList .= <<<HTML
			<div class="form-check form-check-inline">
				<input class="form-check-input"
					type="radio"
					name="$id"
					id="radio_$tableName"
					value="$tableName"
					$checked>
				<label class="form-check-label" for="radio_$tableName">$label</label>
			</div>
		HTML;
    }
    return $lList;
}
