<?php

namespace App\Tables\Main;

use App\SQLorAPI\TitlesTable;

class MainTables
{
	private static $already_loaded = false;
	private static $xEnwikiPageviewsTable = [];
	private static $xWordsTable = [];
	private static $xAllWordsTable = [];
	private static $xAllRefsTable = [];
	private static $xLeadRefsTable = [];
	private static $xAssessmentsTable = [];

	/**
	 * Get the ref count based on mdtitle and translateType.
	 *
	 * @param string $mdtitle
	 * @param string $translateType
	 * @return int
	 */
	public static function getRefCount(string $mdtitle, string $translateType = 'lead'): int
	{
		self::load();

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
		self::load();

		if ($translateType === 'all') {
			return self::$xAllWordsTable[$mdtitle] ?? 0;
		}

		return self::$xWordsTable[$mdtitle] ?? 0;
	}

	private static function load(): void
	{
		if (self::$already_loaded) {
			return;
		}
		self::$already_loaded = true;

		$_titles_infos = (TitlesTable::getInstance())->getTitlesInfos();

		// var_dump(json_encode($_titles_infos, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		// [{ "title": "11p deletion syndrome", "importance": "", "r_lead_refs": 5, "r_all_refs": 14, "en_views": 1592, "w_lead_words": 221, "w_all_words": 547, "qid": "Q1892153" }, ...]

		foreach ($_titles_infos as $k => $tab) {
			$title = $tab['title'];

			self::$xEnwikiPageviewsTable[$title] = $tab['en_views'];

			self::$xWordsTable[$title] = $tab['w_lead_words'];
			self::$xAllWordsTable[$title] = $tab['w_all_words'];

			self::$xAllRefsTable[$title] = $tab['r_all_refs'];
			self::$xLeadRefsTable[$title] = $tab['r_lead_refs'];

			self::$xAssessmentsTable[$title] = $tab['importance'];
		};
	}
	public static function getViews(string $title): int
	{
		self::load();
		return self::$xEnwikiPageviewsTable[$title] ?? 0;
	}
	public static function getAssessments(string $title): string|null
	{
		self::load();
		return self::$xAssessmentsTable[$title] ?? null;
	}
}
