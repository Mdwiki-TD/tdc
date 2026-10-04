<?php
// src/app/Coordinator/Admin/add/add_post.php

namespace App\Coordinator\Admin\Add;

use App\MdwikiSql\AddHelper;
use App\Tables\Main\MainTables;
use App\Coordinator\Admin\Common\AbstractPostHandler;

/**
 * Class AddPostProcessor
 * Handles submission of new translation rows via addPagesToDb().
 */
class AddPostProcessor extends AbstractPostHandler
{
	protected bool $isPopUpPage = false;

    private AddHelper $addHelper;

    public function __construct()
    {
        $this->addHelper = new AddHelper();
    }

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
				$add = $this->addHelper->addPagesToDb(
					$mdtitle,
					$translateType,
					$cat,
					$lang,
					$user,
					$target,
					$pupdate,
					$word,
				);
				if ($add === false) {
					$this->addError("Failed to add translations.");
				} else {
					$this->addText("Translations added successfully.");
				}

        		$result = $this->addHelper->checkAfterAdd($mdtitle, $lang, $user, $target);

				if ($result === false) {
					$this->addError("checkAfterAdd: Failed to add translations.");
				}
			} else {
				$this->addError("Failed to add translations. Missing required fields.");
			}
		}
	}
}
