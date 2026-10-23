<?php
// src/app/Coordinator/Admin/full_translators/full_translators_post.php

namespace App\Coordinator\Admin\FullTranslators;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use App\SQLorAPI\UsersTable;
/**
 * Class FullTranslatorsPostProcessor
 * Handles add/update/delete of "full article translator" users.
 */
class FullTranslatorsPostProcessor extends AbstractPostHandler
{
	protected bool $isPopUpPage = false;

	private const DB_TABLE_NAME = 'full_translators';
	private UsersTable $usersTable;

	public function __construct()
	{
		parent::__construct();
		$this->usersTable = UsersTable::getInstance();
	}

	public function process(array $post): void
	{
		$this->processRows($post['rows'] ?? []);
		if (isset($post['new'])) {
			$this->processNewRows($post['new']);
		}
	}
	private function processNewRows(array $newRows): void
	{
		foreach ($newRows as $key => $table) {
			// { "id": "1", "user": "" }
			$rawUsername = $table['user'] ?? '';
			$username = trim($rawUsername);

			if (empty($username)) {
				if ($rawUsername !== '') {
					$this->addWarning("Username is required.");
				}
				continue;
			}

			$isActive = 1;
			$tableName = self::DB_TABLE_NAME;

			$qua = <<<SQL
                    INSERT INTO $tableName (user, is_active)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE
                        is_active = VALUES(is_active)
                SQL;

			$result = $this->db->executeQuery($qua, [$username, $isActive]);
			$this->verifyInsertResult($result, $username, 'Username');
		}
	}
	/**
	 * Processes each submitted user row: delete, add, or update.
	 */
	private function processRows(array $rows): void
	{
		$records = $this->usersTable->getFullTranslators();
		$recordsById = array_column($records, null, 'id');

		foreach ($rows as $key => $table) {
			// { "id": "1", "user": "" }
			// { "id": "4", "user": "Dr3939", "del": "4" }
			$id = $table['id'] ?? '';
			$del = $table['del'] ?? '';

			if (empty($id)) {
				continue;
			}
			$dbData = $recordsById[$id] ?? [];
			if (empty($dbData)) {
				$this->addWarning("User with id $id does not exist.");
				continue;
			}

			if ($del === $id) {
				$this->deleteRow($id, self::DB_TABLE_NAME, "User");
				continue;
			}
			$username = $dbData['user'];

			$isActive = $this->boolInt($table['is_active'] ?? 0);

			if ($isActive == $dbData['is_active']) {
				continue;
			}

			$tableName = self::DB_TABLE_NAME;

			$qua = "UPDATE $tableName SET is_active = ? WHERE id = ?";

			$result = $this->db->executeQuery($qua, [$isActive, $id]);

			$this->verifyUpdateResult($result, $username, 'Username');
		}
	}
}
