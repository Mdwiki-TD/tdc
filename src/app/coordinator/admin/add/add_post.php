<?php
// src/app/coordinator/admin/add/add_post.php

namespace App\Coordinator\Admin\Add;

use App\Tables\Main\MainTables;
use App\Coordinator\Admin\Common\AbstractPostHandler;
use function App\MdwikiSql\AddHelper\add_pages_to_db;
use function App\MdwikiSql\AddHelper\checkAfterAdd;

/**
 * Class AddPostProcessor
 * Handles submission of new translation rows via add_pages_to_db().
 */
class AddPostProcessor extends AbstractPostHandler
{
	protected bool $isPopUpPage = false;


	public function process(array $post): void
	{
		$this->processRows($post['rows'] ?? []);
	}

	/**
	 * Validates and inserts each submitted translation row.
	 */
	private function processRows(array $rows): void
	{
		foreach ($rows as $key => $table) {
			$mdtitle = $table['mdtitle'] ?? '';
			$cat     = rawurldecode($table['cat'] ?? '');
			$translateType = $table['translate_type'] ?? '';
			$user    = rawurldecode($table['user'] ?? '');
			$lang    = $table['lang'] ?? '';
			$target  = $table['target'] ?? '';
			$pupdate = $table['pupdate'] ?? '';
			$word    = $table['word'] ?? '';

			$translateType = (!empty($translateType)) ? $translateType : 'lead';

			if (empty($word)) {
				$word = MainTables::getWord($mdtitle, $translateType);
			}
			if (!empty($mdtitle) && !empty($lang) && !empty($user)) {
				$add = add_pages_to_db($mdtitle, $translateType, $cat, $lang, $user, $target, $pupdate, $word);
				if ($add === false) {
					$this->addError("Failed to add translations.");
				} else {
					$this->addText("Translations added successfully.");
				}

        		$result = checkAfterAdd($mdtitle, $lang, $user, $target);

				if ($result === false) {
					$this->addError("checkAfterAdd: Failed to add translations.");
				} else {
					$this->addText("checkAfterAdd: Translations added successfully.");
				}
			} else {
				$this->addError("Failed to add translations. Missing required fields.");
			}
		}
	}
}
