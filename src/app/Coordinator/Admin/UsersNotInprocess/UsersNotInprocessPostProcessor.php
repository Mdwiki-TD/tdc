<?php
// src/app/Coordinator/Admin/users_not_inprocess/users_not_inprocess_post.php

namespace App\Coordinator\Admin\UsersNotInprocess;

use App\Coordinator\Admin\Common\AbstractPostHandler;

/**
 * Class UsersNotInprocessPostProcessor
 * Handles add/update/delete of users excluded from the "in process" table.
 */
class UsersNotInprocessPostProcessor extends AbstractPostHandler
{
	protected bool $isPopUpPage = false;

	private const DB_TABLE_NAME = 'users_no_inprocess';

	public function __construct()
	{
		parent::__construct();
	}
	public function process(array $post): void
	{
		$this->processRows($post['rows'] ?? []);
	}
	private function processNewRows(array $newRows): void
	{
		/*
		if (isset($post['new'])) {
			$this->processNewRows($post['new']);
		}
		*/
		foreach ($newRows as $key => $table) {
		}
	}
	/**
	 * Processes each submitted user row: delete, add, or update.
	 */
	private function processRows(array $rows): void
	{
		foreach ($rows as $key => $table) {
			// { "id": "1", "user": "" }
			// { "id": "4", "user": "Dr3939", "del": "4" }
			$id = $table['id'] ?? '';
			$del = $table['del'] ?? '';
			$user = $table['user'] ?? '';

			if ($del === $id) {
				$qua2 = "DELETE FROM " . self::DB_TABLE_NAME . " WHERE id = ?";

				$result = $this->db->executeQuery($qua2, [$id]);
				$this->verifyDeleteResult($result, $id, 'User');
				continue;
			}

			$user = trim($user);

			$isActive = $this->boolInt($table['is_active'] ?? 0);
			$activeOriginalValue = $table['active_orginal_value'] ?? '';

			if ($isActive == $activeOriginalValue && !empty($id)) {
				continue;
			}

			if (!empty($user)) { // && empty($id) && $isNew == 'yes'
				$tableName = self::DB_TABLE_NAME;
				// $qua = "INSERT INTO $tableName (user) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM $tableName WHERE user = ?)";

				$qua = <<<SQL
                    INSERT INTO $tableName (user, is_active)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE
                        is_active = VALUES(is_active)
                SQL;

				$result = $this->db->executeQuery($qua, [$user, $isActive]);

				if ($result === false) {
					$this->addError("Failed to add user $user.");
				} else {
					$this->addText((empty($id)) ? "User $user Added." : "User $user Updated.");
				}
			}
		}
	}
}
