<?php
// src/app/coordinator/admin/full_translators/post.php

namespace App\Coordinator\Admin\FullTranslators;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use function App\APICalls\MdwikiSql\execute_query;

/**
 * Class FullTranslatorsPostProcessor
 * Handles add/update/delete of "full article translator" users.
 */
class FullTranslatorsPostProcessor extends AbstractPostHandler
{
	private const TABLE_NAME = 'full_translators';


	public function process(array $post): void
	{
		$this->processRows($post['rows'] ?? []);
	}

	/**
	 * Processes each submitted user row: delete, add, or update.
	 */
	private function processRows(array $rows): void
	{
		foreach ($rows as $key => $table) {
			// { "id": "1", "user": "" }
			// { "id": "4", "user": "Dr3939", "del": "4" }
			$uId = $table['id'] ?? '';
			$del = $table['del'] ?? '';
			$user = $table['user'] ?? '';

			if (!empty($del) && !empty($uId)) {
				$qua2 = "DELETE FROM " . self::TABLE_NAME . " WHERE id = ?";

				$result = execute_query($qua2, [$uId]);

				if ($result === false) {
					$this->addError("Failed to delete user $user.");
					continue;
				}

				$this->addText("User $user deleted.");
				continue;
			}

			$user = trim($user);

			$isActive = $table['is_active'] ?? '';
			$activeOriginalValue = $table['active_orginal_value'] ?? '';

			if ($isActive == $activeOriginalValue && !empty($uId)) {
				continue;
			}

			if (!empty($user)) {
				// $qua = "INSERT INTO $tableName (user) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM $tableName WHERE user = ?)";
				$tableName = self::TABLE_NAME;

				$qua = <<<SQL
                    INSERT INTO $tableName (user, is_active)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE
                        is_active = VALUES(is_active)
                SQL;

				$result = execute_query($qua, [$user, $isActive]);

				if ($result === false) {
					$this->addError("Failed to add user $user.");
				} else {
					$this->addText((empty($uId)) ? "User $user Added." : "User $user Updated.");
				}
			}
		}
	}
}
