<?PHP

namespace Tools\RecentHelps;

/*
require_once __DIR__ . '/recent_helps.php';

use function Tools\RecentHelps\filter_recent;
use function Tools\RecentHelps\do_add_date;
use function Tools\RecentHelps\filter_table;

*/

use Tables\Langs\LangsTables;
use function Actions\Functions\test_print;

function filter_recent($lang, $result)
{
    //---
    ksort($result);
    //---
    $lang_list = "<option data-tokens='All' value='All'>All</option>";
    //---
    foreach ($result as $codr) {
        $langeee = LangsTables::$L_code_to_lang[$codr] ?? '';
        $selected = ($codr == $lang) ? 'selected' : '';
        $lang_list .= <<<HTML
            <option data-tokens='$codr' value='$codr' $selected>$langeee</option>
            HTML;
    };
    //---
    $result = <<<HTML
        <div class="input-group">
            <!-- <span class="input-group-text">Lang:</span> -->
            <select aria-label="Language code"
                class="selectpicker bg-white"
                id='lang'
                name='lang'
                placeholder='two letter code'
                data-live-search="true"
                data-container="body"
                data-live-search-style="begins"
                data-bs-theme="auto"
                data-style='btn active'
                data-width="90%"
                >
                $lang_list
            </select>
        </div>
    HTML;
    //---
    return $result;
}

function do_add_date($results)
{
    //---
    foreach ($results as $tat => $tabe) {
        $pupdate  = $tabe['pupdate'] ?? ''; // 2025-01-30
        $add_date = $tabe['add_date'] ?? ''; // 2025-01-29
        //---
        if (strpos($pupdate, ':') !== false) $pupdate = explode(' ', $pupdate)[0];
        if (strpos($add_date, ':') !== false) $add_date = explode(' ', $add_date)[0];
        //---
        $add_date = str_replace("-", "", $add_date);
        $pupdate  = str_replace("-", "", $pupdate);
        //---
        // change str to number
        $add_add = intval($add_date);
        $add_pup = intval($pupdate);
        //---
        if ($add_add > $add_pup) {
            test_print("add_add($add_add) > add_pup($add_pup)");
            return true;
        }
        //---
    };
    //---
    return false;
}

function filter_table($data, $vav, $id)
{
    //---
    $l_list = "";
    //---
    foreach ($data as $table_name => $label) {
        $checked = ($table_name == $vav) ? "checked" : "";
        $l_list .= <<<HTML
			<div class="form-check form-check-inline">
				<input class="form-check-input"
					type="radio"
					name="$id"
					id="radio_$table_name"
					value="$table_name"
					$checked>
				<label class="form-check-label" for="radio_$table_name">$label</label>
			</div>
		HTML;
    }
    //---
    $uuu = <<<HTML
		<div class="input-group">
			<span class="input-group-text">Namespace:</span>
			<div class="form-control">
				$l_list
			</div>
		</div>
	HTML;
    //---
    return $uuu;
}
