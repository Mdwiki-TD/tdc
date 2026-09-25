<?php
// src/app/coordinator/admin/tt/edit_tt_post.php

namespace App\Coordinator\Admin\TranslateType;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use function App\APICalls\MdwikiSql\insert_to_translate_type;

/**
 * Class TtPostProcessor
 * Handles add/update submissions for translate_type rows (GET "cat" is
 * accepted but unused downstream, preserved for parity with legacy code).
 */
class TtPostProcessor extends AbstractPostHandler
{

	/**
	 * Executes authorization check and processes the POST submission.
	 */
	public function process(array $post): void
	{
		$this->processRows($post['rows'] ?? []);
	}

	/**
	 * Validates and inserts/updates each submitted translate-type row.
	 */
	private function processRows(array $rows): void
	{
		foreach ($rows as $key => $table) {
			// '{ "rows": { "1": { "add": "", "title": "111111111111", "lead": "100000", "full": "10000" } } }'
			$title = trim($table['title'] ?? '');
			$lead  = $table['lead'] ?? 0;
			$full  = $table['full'] ?? 0;
			$id    = $table['id'] ?? '';

			if (empty($title)) {
				$this->addError("Title is required.");
				continue;
			}

			$result = insert_to_translate_type($title, $lead, $full, $id);

			if ($result === false) {
				$this->addError("Failed to add translate type, title: $title.");
			} else {
				$this->addText("Translate type added successfully, title: $title.");
			}
		}
	}
}
