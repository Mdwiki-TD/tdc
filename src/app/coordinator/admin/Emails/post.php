<?php
// src/app/coordinator/admin/Emails/post.php

namespace App\Coordinator\Admin\Emails;

use App\Coordinator\Admin\Common\AbstractSubPostHandler;
use function App\Utils\Html\div_alert;
use function App\APICalls\MdwikiSql\sql_update_user;
use function App\APICalls\MdwikiSql\sql_add_user;
use function App\APICalls\MdwikiSql\check_one;
use function App\csrf\verify_csrf_token;

/**
 * Class EmailsPostProcessor
 * Handles add/update submissions of user email/wiki/project rows.
 */
class EmailsPostProcessor extends AbstractSubPostHandler
{

	/**
	 * Validates and processes the incoming submission.
	 */
	public function handle(): void
	{
		$this->validateCoordinator();

		echo '</div><script>
            $("#mainnav").hide();
            $("#maindiv").hide();
        </script>';

		$closeBtn = $this->getCloseButtonHtml();

		if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['emails'])) {
			exit;
		}

		if (!verify_csrf_token()) {
			echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
			echo $closeBtn;
			return;
		}

		$this->processRows($_POST['emails']);

		echo div_alert($this->texts, 'success');
		echo div_alert($this->errors, 'danger');

		echo $closeBtn;
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
				$email = '';
			}

			$wiki = trim($wiki);
			$project = trim($project);

			$ttTab = check_one('*', 'username', $user, 'users');

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

// Instantiate and execute controller
$controller = new EmailsPostProcessor();
$controller->handle();
