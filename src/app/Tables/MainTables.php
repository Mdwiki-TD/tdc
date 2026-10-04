<?php

namespace App\Tables;

use App\SQLorAPI\TitlesTable;

class MainTables
{
	private static bool $already_loaded = false;

	/** @var array<string, int> */
	private static array $xEnwikiPageviewsTable = [];

	/** @var array<string, int> */
	private static array $xWordsTable = [];

	/** @var array<string, int> */
	private static array $xAllWordsTable = [];

	/** @var array<string, int> */
	private static array $xAllRefsTable = [];

	/** @var array<string, int> */
	private static array $xLeadRefsTable = [];

	/** @var array<string, string|null> */
	private static array $xAssessmentsTable = [];

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

		foreach ($_titles_infos as $k => $tab) {
			$title = $tab['title'];

			self::$xEnwikiPageviewsTable[$title] = $tab['en_views'];

			self::$xWordsTable[$title] = $tab['w_lead_words'];
			self::$xAllWordsTable[$title] = $tab['w_all_words'];

			self::$xAllRefsTable[$title] = $tab['r_all_refs'];
			self::$xLeadRefsTable[$title] = $tab['r_lead_refs'];

			self::$xAssessmentsTable[$title] = $tab['importance'];
		}
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
