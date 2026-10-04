<?php
// src/app/Coordinator/Admin/tt/edit_tt_post.php

namespace App\Coordinator\Admin\TranslateType;

use App\Coordinator\Admin\Common\AbstractPostHandler;

/**
 * Class TtPostProcessor
 * Handles add/update submissions for translate_type rows (GET "cat" is
 * accepted but unused downstream, preserved for parity with legacy code).
 */
class TtPostProcessor extends AbstractPostHandler
{
    protected bool $isPopUpPage = true;


    public function __construct()
    {
        parent::__construct();
    }
	/**
	 * Executes authorization check and processes the POST submission.
	 */
	public function process(array $post): void
	{
		$this->processRows($post['rows'] ?? []);
	}

	private function insert_to_translate_type($ttTitle, $ttLead, $ttFull, $ttId = 0)
	{

		$query = "UPDATE translate_type SET tt_lead = ?, tt_full = ? WHERE tt_id = ?";
		$params = [$ttLead, $ttFull, $ttId];

		if ($ttId == 0 || $ttId == '0' || empty($ttId)) {
			$query = "INSERT INTO translate_type (tt_title, tt_lead, tt_full) SELECT ?, ?, ?";
			$params = [$ttTitle, $ttLead, $ttFull];
		};

		$result = $this->db->executeQuery($query, $params);

		return $result;
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

			$result = $this->insert_to_translate_type($title, $lead, $full, $id);

			if ($result === false) {
				$this->addError("Failed to add translate type, title: $title.");
			} else {
				$this->addText("Translate type added successfully, title: $title.");
			}
		}
	}
}
