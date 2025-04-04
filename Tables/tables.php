<?PHP
//---
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
};

use function SQLorAPI\Get\td_or_sql_titles_infos;
//---
$tables_dir = isset($GLOBALS['tables_dir']) ? $GLOBALS['tables_dir'] : __DIR__ . '/../../td/Tables';
//---
if (substr($tables_dir, 0, 2) == 'I:') {
	$tables_dir = 'I:/mdwiki/mdwiki/public_html/td/Tables';
}
//---
$tables_d = array(
	// 'enwiki_pageviews' => &$enwiki_pageviews_table,
	// 'words' => &$Words_table,
	// 'allwords' => &$All_Words_table,
	// 'all_refcount' => &$All_Refs_table,
	// 'lead_refcount' => &$Lead_Refs_table,
	// 'assessments' => &$Assessments_table,
	'langs_tables' => &$Langs_table,
);
//---
foreach ($tables_d as $key => &$value) {
	$file = file_get_contents($tables_dir . "/jsons/{$key}.json");
	$value = json_decode($file, true);
}
//---
$enwiki_pageviews_table = [];
$Words_table = [];
$All_Words_table = [];
$All_Refs_table = [];
$Lead_Refs_table = [];
$Assessments_table = [];
//---
$titles_infos = td_or_sql_titles_infos();

// var_dump(json_encode($titles_infos, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
// [{ "title": "11p deletion syndrome", "importance": "", "r_lead_refs": 5, "r_all_refs": 14, "en_views": 1592, "w_lead_words": 221, "w_all_words": 547, "qid": "Q1892153" }, ...]
// ---
foreach ($titles_infos as $k => $tab) {
	$title = $tab['title'];
	// ---
	$enwiki_pageviews_table[$title] = $tab['en_views'];
	// ---
	$Words_table[$title] = $tab['w_lead_words'];
	$All_Words_table[$title] = $tab['w_all_words'];
	// ---
	$All_Refs_table[$title] = $tab['r_all_refs'];
	$Lead_Refs_table[$title] = $tab['r_lead_refs'];
	// ---
	$Assessments_table[$title] = $tab['importance'];
};
