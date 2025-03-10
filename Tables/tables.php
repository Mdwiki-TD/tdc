<?PHP
//---
if (isset($_REQUEST['test'])) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
};

use function Actions\MdwikiSql\fetch_query;
use function Actions\TDApi\get_td_api;

function td_or_sql_titles_infos()
{
	// ---
	$from_api = false;
	// ---
	if ($from_api) {
		$data = get_td_api(['get' => 'titles']);
	} else {
		$qua_old = <<<SQL
            SELECT
                ase.title,
                ase.importance,
                rc.r_lead_refs,
                rc.r_all_refs,
                ep.en_views,
                w.w_lead_words,
                w.w_all_words,
                q.qid
            FROM assessments ase
            LEFT JOIN enwiki_pageviews ep ON ase.title = ep.title
            LEFT JOIN  qids q ON q.title = ase.title
            LEFT JOIN  refs_counts rc ON rc.r_title = ase.title
            LEFT JOIN  words w ON w.w_title = ase.title
        SQL;
		// ---
        $qua = <<<SQL
            SELECT *
            FROM titles_infos
        SQL;
        // ---
		$data = fetch_query($qua);
	}
	// ---
	return $data;
}
//---
$Assessments_fff = array(
	'Top' => 1,
	'High' => 2,
	'Mid' => 3,
	'Low' => 4,
	'Unknown' => 5,
	'' => 5
);
//---
$tables = array(
	// 'enwiki_pageviews' => &$enwiki_pageviews_table,
	// 'words' => &$Words_table,
	// 'allwords' => &$All_Words_table,
	// 'all_refcount' => &$All_Refs_table,
	// 'lead_refcount' => &$Lead_Refs_table,
	// 'assessments' => &$Assessments_table,
	'langs_tables' => &$Langs_table,
);
//---
$tables_dir = isset($GLOBALS['tables_dir']) ? $GLOBALS['tables_dir'] : __DIR__ . '/../../td/Tables';
//---
if (substr($tables_dir, 0, 2) == 'I:') {
	$tables_dir = 'I:/mdwiki/mdwiki/public_html/td/Tables';
}
//---
foreach ($tables as $key => &$value) {
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
