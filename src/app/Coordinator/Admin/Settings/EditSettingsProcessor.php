<?php
// src/app/Coordinator/Admin/Settings/EditSettingsProcessor.php

namespace App\Coordinator\Admin\Settings;

use App\SQLorAPI\SettingsTable;
use App\Coordinator\Admin\Common\AbstractPostHandler;

/**
 * Class EditSettingsProcessor
 * Handles add/update submissions for translate_type rows (GET "cat" is
 * accepted but unused downstream, preserved for parity with legacy code).
 */
class EditSettingsProcessor extends AbstractPostHandler
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
		if (!$this->validateAdmin()) {
			$this->exit();
			return;
		}
		$this->processRows($post['rows'] ?? []);
	}

	private function update_row(string $id, mixed $displayed, mixed $value, mixed $ignored = 0): bool
	{

		$query = "UPDATE settings SET displayed = ?, value = ?, ignored = ? WHERE id = ?";
		$params = [$displayed, $value, $ignored, $id];

		$result = $this->db->executeQuery($query, $params);

		return $result;
	}

	/**
	 * Validates and inserts/updates each submitted settings row.
	 */
	private function processRows(array $rows): void
	{
		$settings = SettingsTable::getInstance();

		foreach ($rows as $key => $table) {
			// '{ "rows": { "1": { "id": "10", "title": "", "displayed": "..", "value": 1, "ignored": 0} } }'
			$id = $table['id'] ?? '';

			$data = $settings->getSettingsByid($id);
			if (empty($data)) {
				$this->addError("Invalid setting ID: $id.");
				continue;
			}

			// $title = trim($table['title'] ?? '');
			$displayed = $table['displayed'] ?? "";

			if (empty($displayed)) {
				$this->addError("displayed is required.");
				continue;
			}

			$value = $table['value'] ?? 0;
			$ignored = $table['ignored'] ?? 0;

			if ($value == $data['value'] && $displayed == $data['displayed'] && $ignored == $data['ignored']) {
				$this->addWarning("No changes made to setting ID: $id.");
				continue;
			}

			$result = $this->update_row($id, $displayed, $value, $ignored);

			if ($result === false) {
				$this->addError("Failed to update setting with id: $id.");
			} else {
				$this->addText("setting updated successfully, id: $id.");
			}
		}
	}
}
