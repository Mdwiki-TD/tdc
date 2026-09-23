<?php
// src/app/coordinator/admin/admins/post.php

namespace App\Coordinator\Admin\Admins;

use App\User\CurrentUser;
use function App\APICalls\MdwikiSql\execute_query;
use function App\Utils\Html\div_alert;
use function App\csrf\verify_csrf_token;

/**
 * Class AdminsPostProcessor
 * Handles add/update/delete of coordinator (admin) users.
 */
class AdminsPostProcessor
{
	private const TABLE_NAME = 'coordinators';

	private array $texts = [];
	private array $errors = [];

	/**
	 * Validates and processes the incoming submission.
	 */
	public function handle(): void
	{
		// Check user authorization
		if (!CurrentUser::getInstance()->isCoordinator()) {
			header('Location: /index.php');
			exit;
		}

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			exit;
		}

		$closeBtn = $this->getCloseButtonHtml();

		if (!verify_csrf_token()) {
			echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
			echo $closeBtn;
			return;
		}

		$this->processRows($_POST['rows'] ?? []);

		echo div_alert($this->texts, 'success');
		echo div_alert($this->errors, 'danger');

		echo $closeBtn;
	}

	/**
	 * Processes each submitted coordinator row: delete, add, or update.
	 */
	private function processRows(array $rows): void
	{
		foreach ($rows as $key => $table) {
			// '{ "id": "11", "username": "Ifteebd10", "del": "11" }'
			// '{ "id": "11", "username": "Ifteebd10", "is_new": "yes" }
			$uId = $table['id'] ?? '';
			$del = $table['del'] ?? '';
			$username = $table['username'] ?? '';

			if (!empty($del) && !empty($uId)) {
				$qua2 = "DELETE FROM " . self::TABLE_NAME . " WHERE id = ?";

				$result = execute_query($qua2, [$uId]);

				if ($result === false) {
					$this->errors[] = "Failed to delete user $username.";
					continue;
				}

				$this->texts[] = "User $username deleted.";
				continue;
			}

			$username = trim($username);

			$isActive = $table['is_active'] ?? '';
			$activeOriginalValue = $table['active_orginal_value'] ?? '';

			if ($isActive == $activeOriginalValue && !empty($uId)) {
				continue;
			}

			if (!empty($username)) {
				$tableName = self::TABLE_NAME;

				$qua = <<<SQL
                    INSERT INTO $tableName (username, is_active)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE
                        is_active = VALUES(is_active)
                SQL;

				$result = execute_query($qua, [$username, $isActive]);

				if ($result === false) {
					$this->errors[] = "Failed to add user $username.";
				} else {
					$this->texts[] = (empty($uId)) ? "User $username Added." : "User $username Updated.";
				}
			}
		}
	}

	/**
	 * Generates a close button HTML block.
	 */
	private function getCloseButtonHtml(): string
	{
		return <<<HTML
            <div class="aligncenter">
                <a class="btn btn-outline-primary" onclick="window.close()">Close</a>
            </div>
        HTML;
	}
}
