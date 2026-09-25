<?php
// src/app/coordinator/admin/users/edit_user_post.php

namespace App\Coordinator\Admin\Users;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use function App\APICalls\MdwikiSql\sql_update_user;
use function App\APICalls\MdwikiSql\sql_add_user;
use function App\APICalls\MdwikiSql\get_user_by_username;

/**
 * Class EditUserPostProcessor
 * Handles add/update submissions of user email/wiki/project rows.
 */
class EditUserPostProcessor extends AbstractPostHandler
{

	/**
	 * Validates and processes the incoming submission.
	 */
	public function process(array $post): void
	{
		if (!isset($post['emails'])) {
			$this->addError("Invalid submission.");
			$this->returnToFormPage = true;
			return;
		}
		$this->processRows($post['emails']);
	}

	/**
	 * Validates and inserts/updates each submitted user row.
	 */
	private function processRows(array $rows): void
	{
		foreach ($rows as $key => $table) {
			// { "username": "", "email": "3", "project": "Uncategorized", "wiki": "" }
			$user    = $table['username'] ?? '';
			$email   = $table['email'] ?? '';
			$wiki    = $table['wiki'] ?? '';
			$project = $table['project'] ?? '';
			$userId  = $table['user_id'] ?? '';

			if (empty($user)) {
				$this->addError("Username is required.");
				continue;
			}

			$user = trim($user);
			$email = trim($email);

			// Validate email format if not empty
			if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
				// Handle invalid email - either log, set to empty, or return error
				$this->addError("Invalid Email format");
				$this->returnToFormPage = true;
				// $email = '';
				continue;
			}

			$wiki = trim($wiki);
			$project = trim($project);

			$ttTab = get_user_by_username($user);

			if ($ttTab) {
				$ttUsername = $ttTab['username'];
				$ttId = $ttTab['user_id'];

				if (!empty($userId) && $ttId != $userId) {
					$this->addError("User:($user) already in database with user_id:($ttId).");
					continue;
				}

				if (empty($userId) && !empty($ttUsername)) {
					$this->addError("User:($user) already in database with user_id:($ttId).");
					continue;
				}
			}

			if (empty($userId)) {
				sql_add_user($user, $email, $wiki, $project);
				$this->addText("User:($user) added successfully.");
			} else {
				sql_update_user($user, $email, $wiki, $project, $userId);
				$this->addText("User:($user) updated successfully.");
			}
		}
	}
}
