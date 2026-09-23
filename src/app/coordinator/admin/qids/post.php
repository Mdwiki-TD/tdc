<?php
// src/app/coordinator/admin/qids/post.php

namespace App\Coordinator\Admin\Qids;

use App\User\CurrentUser;
use App\Coordinator\Admin\Common\AbstractSubPostHandler;
use function App\Utils\Html\div_alert;
use function App\APICalls\MdwikiSql\execute_query;
use function App\APICalls\MdwikiSql\check_one;
use function App\csrf\verify_csrf_token;

/**
 * Class QidsPostController
 * Handles bulk add/update submissions of title/qid rows, validating
 * uniqueness constraints against both the qid and title columns.
 */
class QidsPostController extends AbstractSubPostHandler
{
	private string $qidTable;

	public function __construct()
	{
		$this->qidTable = $_GET['qid_table'] ?? '';

		if ($this->qidTable !== 'qids' && $this->qidTable !== 'qids_others') {
			$this->qidTable = 'qids';
		}
	}

	/**
	 * Executes authorization check and processes the POST submission.
	 */
	public function handleRequest(): void
	{
		$this->validateCoordinator();

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

		if (!empty($this->texts)) {
			$this->addText("table:({$this->qidTable})");
		} elseif (!empty($this->errors)) {
			$this->addError("table:({$this->qidTable})");
		}

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
	 * Validates and processes every submitted row.
	 */
	private function processRows(array $rows): void
	{
		foreach ($rows as $key => $table) {
			$title = trim($table['title'] ?? '');
			$qid   = trim($table['qid'] ?? '');
			$id    = $table['id'] ?? '';

			if (empty($title)) {
				$this->addError("Title is required. qid=($qid)");
				continue;
			}

			if (empty($qid)) {
				$this->addError("Qid is required. title=($title)");
				continue;
			}

			$txTab = check_one('*', 'qid', $qid, $this->qidTable);

			if ($txTab) {
				$txId = $txTab['id'];
				$titleOfQid = $txTab['title'];

				if (!empty($id) && $txId != $id) {
					$this->addError("Qid:($qid) already used in database with with id:($txId).");
					continue;
				}

				if (!empty($titleOfQid) && empty($id) && $titleOfQid != $title) {
					$this->addError("Qid:($qid) already used in database with title:($titleOfQid).");
					continue;
				}
			}

			$ttTab = check_one('*', 'title', $title, $this->qidTable);

			if ($ttTab) {
				$qidOfTitle5 = $ttTab['qid'];
				$ttId = $ttTab['id'];

				if (!empty($id) && $ttId != $id) {
					$this->addError("Title:($title) already used in database with qid:($qidOfTitle5), new qid:($qid)");
					continue;
				}

				if (empty($id) && !empty($qidOfTitle5) && $qidOfTitle5 != $qid) {
					$this->addError("Title:($title) already used in database with qid:($qidOfTitle5), new qid:($qid)");
					continue;
				}
			}

			if (empty($id)) {
				$this->addNewRow($qid, $title);
			} else {
				$this->updateRow($qid, $id, $title);
			}
		}
	}

	/**
	 * Inserts or updates a title/qid pair in the database.
	 */
	private function addIt(string $id, string $title, string $qid): void
	{
		$qua = "INSERT INTO {$this->qidTable} (title, qid) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM {$this->qidTable} WHERE (title = ? OR qid = ?))";
		$params = [$title, $qid, $title, $qid];

		if (!empty($id)) {
			$qua = "UPDATE {$this->qidTable} SET title = ?, qid = ? WHERE id = ? ";
			$params = [$title, $qid, $id];
		}

		execute_query($qua, $params);

		if (!empty($qid)) {
			$qua2 = <<<SQL
                UPDATE {$this->qidTable} SET qid = ?
                WHERE title = ? and (qid = '' OR qid IS NULL);
            SQL;

			execute_query($qua2, [$qid, $title]);
		}
	}

	/**
	 * Updates an existing row and verifies the change took effect.
	 */
	private function updateRow(string $qid, string $id, string $title): void
	{
		$this->addIt($id, $title, $qid);

		$qidOfTitle = check_one('qid', 'title', $title, $this->qidTable);

		if (!empty($qidOfTitle) && $qidOfTitle == $qid) {
			$this->addText("Data Changes successfully of title: $title, Qid: $qid");
		} else {
			$this->addError("Failed to chanhe data of title: $title, Qid: $qid. Found: qid in db:$qidOfTitle");
		}
	}

	/**
	 * Inserts a brand-new row and verifies the insert took effect.
	 */
	private function addNewRow(string $qid, string $title): void
	{
		$this->addIt('', $title, $qid);

		$qidOfTitle = check_one('qid', 'title', $title, $this->qidTable);

		if (!empty($qidOfTitle) && $qidOfTitle == $qid) {
			$this->addText("Qid added successfully for title: $title.");
		} else {
			$this->addError("Failed to add Qid for title: $title. qid_of_title:$qidOfTitle");
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
$controller = new QidsPostController();
$controller->handleRequest();
