<?php
// src/app/coordinator/admin/full_translators/post.php

namespace App\Coordinator\Admin\FullTranslators;

use App\User\CurrentUser;
use App\Coordinator\Admin\Common\AbstractSubPostHandler;
use function App\APICalls\MdwikiSql\execute_query;
use function App\Utils\Html\div_alert;
use function App\csrf\verify_csrf_token;

/**
 * Class FullTranslatorsPostProcessor
 * Handles add/update/delete of "full article translator" users.
 */
class FullTranslatorsPostProcessor extends AbstractSubPostHandler
{
	private const TABLE_NAME = 'full_translators';


	/**
	 * Validates and processes the incoming submission.
	 */
	public function handle(): void
	{
		$this->validateCoordinator();

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			exit;
		}

		$closeBtn = $this->getCloseButtonHtml();

		if (!verify_csrf_token()) {
			$this->DisplayCsrfAlert();
			echo $closeBtn;
			return;
		}

		$this->processRows($_POST['rows'] ?? []);

		$this->divAlerts();
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
