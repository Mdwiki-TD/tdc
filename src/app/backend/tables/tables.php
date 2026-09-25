<?php

namespace App\Tables\Main;


/*
(\$)(enwiki_pageviews_table|Words_table|All_Words_table|All_Refs_table|Lead_Refs_table|Assessments_table|Langs_table)\b

MainTables::$1x_$2

use App\Tables\Main\MainTables;

*/



use function App\SQLorAPI\Funcs\td_or_sql_titles_infos;

class MainTables
{
	public static $xEnwikiPageviewsTable = [];
	public static $xWordsTable = [];
	public static $xAllWordsTable = [];
	public static $xAllRefsTable = [];
	public static $xLeadRefsTable = [];
	public static $xAssessmentsTable = [];
	public static $xLangsTable = [];

	/**
	 * Get the ref count based on mdtitle and translateType.
	 *
	 * @param string $mdtitle
	 * @param string $translateType
	 * @return int
	 */
	public static function getRefCount(string $mdtitle, string $translateType = 'lead'): int
	{
		if ($translateType === 'all') {
			return self::$xAllRefsTable[$mdtitle] ?? 0;
		}

		return self::$xLeadRefsTable[$mdtitle] ?? 0;
	}
	/**
	 * Get the word count based on mdtitle and translateType.
	 *
	 * @param string $mdtitle
	 * @param string $translateType
	 * @return int
	 */
	public static function getWord(string $mdtitle, string $translateType = 'lead'): int
	{
		if ($translateType === 'all') {
			return self::$xAllWordsTable[$mdtitle] ?? 0;
		}

		return self::$xWordsTable[$mdtitle] ?? 0;
	}
}

$_titles_infos = td_or_sql_titles_infos();

// var_dump(json_encode($_titles_infos, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
// [{ "title": "11p deletion syndrome", "importance": "", "r_lead_refs": 5, "r_all_refs": 14, "en_views": 1592, "w_lead_words": 221, "w_all_words": 547, "qid": "Q1892153" }, ...]

foreach ($_titles_infos as $k => $tab) {
	$title = $tab['title'];

	MainTables::$xEnwikiPageviewsTable[$title] = $tab['en_views'];

	MainTables::$xWordsTable[$title] = $tab['w_lead_words'];
	MainTables::$xAllWordsTable[$title] = $tab['w_all_words'];

	MainTables::$xAllRefsTable[$title] = $tab['r_all_refs'];
	MainTables::$xLeadRefsTable[$title] = $tab['r_lead_refs'];

	MainTables::$xAssessmentsTable[$title] = $tab['importance'];
};

if (file_exists(__DIR__ . '/lang_names.json')) {
	$contents = file_get_contents(__DIR__ . '/lang_names.json');
	if ($contents === false) {
		error_log('Failed to read lang_names.json');
	} else {
		$data = json_decode($contents, true);
		if (is_array($data)) {
			MainTables::$xLangsTable = $data;
			ksort(MainTables::$xLangsTable);
		}
	}
}
