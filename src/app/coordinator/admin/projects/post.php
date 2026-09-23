<?php
// src/app/coordinator/admin/projects/post.php

namespace App\Coordinator\Admin\Projects;

use App\User\CurrentUser;
use App\Coordinator\Admin\Common\AbstractSubPostHandler;
use function App\APICalls\MdwikiSql\insert_to_projects;
use function App\APICalls\MdwikiSql\execute_query;
use function App\Utils\Html\div_alert;
use function App\csrf\verify_csrf_token;

/**
 * Class ProjectsPostProcessor
 * Handles add/update/delete submissions of project rows.
 * Can run standalone (direct request) or be delegated to from
 * ProjectsIndexController when the index form is submitted.
 */
class ProjectsPostProcessor extends AbstractSubPostHandler
{

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

		echo div_alert($this->texts, 'success');
	}

	/**
	 * Processes each submitted project row: delete, add, or update.
	 */
	private function processRows(array $rows): void
	{
		foreach ($rows as $key => $table) {
			$gId  = $table['g_id'] ?? '';
			$del  = $table['del'] ?? '';
			$gTitle = $table['g_title'] ?? '';

			if (!empty($del) && !empty($gId)) {
				$qua2 = "DELETE FROM projects WHERE g_id = ?";
				execute_query($qua2, [$gId]);

				$this->addText("Project $gTitle deleted.");
				continue;
			}

			$gTitle = trim($gTitle);

			if (empty($gTitle)) {
				continue;
			}

			insert_to_projects($gTitle, $gId);

			if (empty($gId)) {
				$this->addText("Project $gTitle Added.");
			} else {
				$this->addText("Project $gTitle Updated.");
			}
		}
	}

}
