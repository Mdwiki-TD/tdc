<?php
// src/app/coordinator/admin/add/post.php

namespace App\Coordinator\Admin\Add;

use App\User\CurrentUser;
use function App\Utils\Html\div_alert;
use function App\csrf\verify_csrf_token;
use function Add\AddPost\add_pages_to_db;

/**
 * Class AddPostProcessor
 * Handles submission of new translation rows via add_pages_to_db().
 */
class AddPostProcessor
{
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
	}

	/**
	 * Validates and inserts each submitted translation row.
	 */
	private function processRows(array $rows): void
	{
		foreach ($rows as $key => $table) {
			$mdtitle = $table['mdtitle'] ?? '';
			$cat     = rawurldecode($table['cat'] ?? '');
			$type    = $table['type'] ?? '';
			$user    = rawurldecode($table['user'] ?? '');
			$lang    = $table['lang'] ?? '';
			$target  = $table['target'] ?? '';
			$pupdate = $table['pupdate'] ?? '';
			$word    = $table['word'] ?? '';

			if (!empty($mdtitle) && !empty($lang) && !empty($user)) {
				$result = add_pages_to_db($mdtitle, $type, $cat, $lang, $user, $target, $pupdate, $word);

				if ($result === false) {
					$this->errors[] = "Failed to add translations.";
				} else {
					$this->texts[] = "Translations added successfully.";
				}
			} else {
				$this->errors[] = "Failed to add translations. Missing required fields.";
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
