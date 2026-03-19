<?PHP

namespace Tables\Langs;

/*
Usage:
use Tables\Langs\LangsTables;

(\$)(skip_codes|change_codes|code_to_wikiname|lang_to_code|code_to_lang)\b
LangsTables::$1L_$2

*/
//---
/*
"gsw": "als",
"gswsgs": "bat-smg",
"fiu-vro": "vro",
"roa-rup": "rup",
"lzh": "zh-classical",
"nan": "zh-min-nan",
"yue": "zh-yue",
"be-x-old": "be-tarask",
s
https://ar.wikipedia.org/w/api.php?action=query&format=json&meta=siteinfo&utf8=1&formatversion=2&siprop=languages&sifilteriw=local&sishowalldb=1

https://cxserver.wikimedia.org/v2/list/languagepairs

https://www.wikidata.org/w/api.php?action=query&format=json&meta=languageinfo&utf8=1&formatversion=2&liprop=code%7Cname%7Cfallbacks%7Cvariantnames%7Cvariants%7Cdir%7Cbcp47%7Cautonym

https://db-names.toolforge.org/

"als" : "gsw"
"bat-smg" : "sgs"
"be-x-old" : "be-tarask"
"cbk-zam" : "cbk-x-zam"
"fiu-vro" : "vro"
"map-bms" : "jv-x-bms"
"roa-rup" : "rup"
"roa-tara" : "nap-x-tara"
"nds-nl" : "nds-NL"
"zh-classical" : "lzh"
"zh-min-nan" : "nan"
"zh-yue" : "yue"
*/

use Tables\Main\MainTables;

class LangsTables
{
    public static $L_change_codes = [];
    public static $L_code_to_lang = [];
}

LangsTables::$L_change_codes = [
    "nb"    =>    "no",
    "bat_smg"    =>    "bat-smg",
    "be-x-old"    =>    "be-tarask",
    "be_x_old"    =>    "be-tarask",
    "cbk_zam"    =>    "cbk-zam",
    "fiu_vro"    =>    "fiu-vro",
    "map_bms"    =>    "map-bms",
    "nds_nl"    =>    "nds-nl",
    "roa_rup"    =>    "roa-rup",
    "zh_classical"    =>    "zh-classical",
    "zh_min_nan"    =>    "zh-min-nan",
    "zh_yue"    =>    "zh-yue",
];

foreach (MainTables::$x_Langs_table as $_ => $lang_tab) {
    $lang_code = $lang_tab['code'] ?? "";
    $lang_name = $lang_tab['autonym'] ?? "";

    if (isset(LangsTables::$L_change_codes[$lang_code]) && isset(LangsTables::$L_code_to_lang[LangsTables::$L_change_codes[$lang_code]])) {
        continue;
    }

    $lang_title = "($lang_code) $lang_name";

    LangsTables::$L_code_to_lang[$lang_code] = $lang_title;
};
