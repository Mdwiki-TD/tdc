<?php
// src/app/coordinator/admin/tt/post.php

namespace App\Coordinator\Admin\TranslateType;

use App\User\CurrentUser;
use App\Coordinator\Admin\Common\AbstractSubPostHandler;
use function App\Utils\Html\div_alert;
use function App\APICalls\MdwikiSql\insert_to_translate_type;
use function App\csrf\verify_csrf_token;

/**
 * Class TtPostController
 * Handles add/update submissions for translate_type rows (GET "cat" is
 * accepted but unused downstream, preserved for parity with legacy code).
 */
class TtPostController extends AbstractSubPostHandler
{


	/**
	 * Executes authorization check and processes the POST submission.
	 */
	public function handleRequest(): void
	{
		// Check user authorization
		if (!CurrentUser::getInstance()->isCoordinator()) {
			header('Location: /index.php');
			exit;
		}

		$this->renderHeaderScripts();

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
	 * Renders UI scripts to isolate the modal/page layout.
	 */
	private function renderHeaderScripts(): void
	{
		echo '</div><script>
            $("#mainnav").hide();
            $("#maindiv").hide();
        </script>';
	}

	/**
	 * Validates and inserts/updates each submitted translate-type row.
	 */
	private function processRows(array $rows): void
	{
		foreach ($rows as $key => $table) {
			// '{ "ty": "tt/post", "rows": { "1": { "add": "", "title": "111111111111", "lead": "100000", "full": "10000" } } }'
			$title = trim($table['title'] ?? '');
			$lead  = $table['lead'] ?? 0;
			$full  = $table['full'] ?? 0;
			$id    = $table['id'] ?? '';

			if (empty($title)) {
				$this->addError("Title is required.");
				continue;
			}

			$result = insert_to_translate_type($title, $lead, $full, $id);

			if ($result === false) {
				$this->addError("Failed to add translate type, title: $title.");
			} else {
				$this->addText("Translate type added successfully, title: $title.");
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
$controller = new TtPostController();
$controller->handleRequest();
