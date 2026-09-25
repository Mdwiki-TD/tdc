<?php
// src/app/coordinator/admin/add/add_post.php

namespace App\Coordinator\Admin\Add;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use function App\Coordinator\Helps\AddHelper\add_pages_to_db;

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
			$type    = $table['type'] ?? '';
			$user    = rawurldecode($table['user'] ?? '');
			$lang    = $table['lang'] ?? '';
			$target  = $table['target'] ?? '';
			$pupdate = $table['pupdate'] ?? '';
			$word    = $table['word'] ?? '';

			if (!empty($mdtitle) && !empty($lang) && !empty($user)) {
				$result = add_pages_to_db($mdtitle, $type, $cat, $lang, $user, $target, $pupdate, $word);

				if ($result === false) {
					$this->addError("Failed to add translations.");
				} else {
					$this->addText("Translations added successfully.");
				}
			} else {
				$this->addError("Failed to add translations. Missing required fields.");
			}
		}
	}

}
