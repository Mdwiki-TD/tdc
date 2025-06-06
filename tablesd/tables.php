<?PHP

namespace Tables\Main;

//---
/*
(\$)(enwiki_pageviews_table|Words_table|All_Words_table|All_Refs_table|Lead_Refs_table|Assessments_table|Langs_table)\b

MainTables::$1x_$2

use Tables\Main\MainTables;

*/

if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
};
//---
use Tables\Langs\LangsTables;
use function SQLorAPI\Funcs\td_or_sql_titles_infos;
use function Tables\TablesDir\open_td_Tables_file;
//---
class MainTables
{
	public static $x_enwiki_pageviews_table = [];
	public static $x_Words_table = [];
	public static $x_All_Words_table = [];
	public static $x_All_Refs_table = [];
	public static $x_Lead_Refs_table = [];
	public static $x_Assessments_table = [];
	public static $x_Langs_table = [];
}
// ---
$tables_d = array(
	// 'enwiki_pageviews' => &MainTables::$x_enwiki_pageviews_table,
	// 'words' => &MainTables::$x_Words_table,
	// 'allwords' => &MainTables::$x_All_Words_table,
	// 'all_refcount' => &MainTables::$x_All_Refs_table,
	// 'lead_refcount' => &MainTables::$x_Lead_Refs_table,
	// 'assessments' => &MainTables::$x_Assessments_table,
	'langs_tables' => &MainTables::$x_Langs_table,
);
//---
foreach ($tables_d as $key => &$value) {
	$file = "jsons/{$key}.json";
	$value = open_td_Tables_file($file);
}
//---
$titles_infos = td_or_sql_titles_infos();

// var_dump(json_encode($titles_infos, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
// [{ "title": "11p deletion syndrome", "importance": "", "r_lead_refs": 5, "r_all_refs": 14, "en_views": 1592, "w_lead_words": 221, "w_all_words": 547, "qid": "Q1892153" }, ...]
// ---
foreach ($titles_infos as $k => $tab) {
	$title = $tab['title'];
	// ---
	MainTables::$x_enwiki_pageviews_table[$title] = $tab['en_views'];
	// ---
	MainTables::$x_Words_table[$title] = $tab['w_lead_words'];
	MainTables::$x_All_Words_table[$title] = $tab['w_all_words'];
	// ---
	MainTables::$x_All_Refs_table[$title] = $tab['r_all_refs'];
	MainTables::$x_Lead_Refs_table[$title] = $tab['r_lead_refs'];
	// ---
	MainTables::$x_Assessments_table[$title] = $tab['importance'];
};
