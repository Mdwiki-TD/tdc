<?PHP

namespace Tools\RecentHelps;

/*
require_once __DIR__ . '/recent_helps.php';

use function Tools\RecentHelps\filter_recent;
use function Tools\RecentHelps\do_add_date;

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
    $langse = <<<HTML
        <select aria-label="Language code"
            class="selectpicker"
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
    HTML;
    //---
    $uuu = <<<HTML
        <div class="input-group">
            $langse
        </div>
    HTML;
    //---
    return $uuu;
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
