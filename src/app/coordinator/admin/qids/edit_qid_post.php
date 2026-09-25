<?php
// src/app/coordinator/admin/qids/edit_qid_post.php

namespace App\Coordinator\Admin\Qids;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use function App\MdwikiSql\execute_query;
use function App\MdwikiSql\get_qid_row;
use function App\MdwikiSql\get_qid_by_title;

/**
 * Class QidsPostProcessor
 * Handles bulk add/update submissions of title/qid rows, validating
 * uniqueness constraints against both the qid and title columns.
 */
class QidsPostProcessor extends AbstractPostHandler
{
    protected bool $isPopUpPage = true;

	private string $qidTable;

	public function __construct(string $qidTable = 'qids')
	{
		$this->qidTable = $qidTable;

		if ($this->qidTable !== 'qids' && $this->qidTable !== 'qids_others') {
			$this->qidTable = 'qids';
		}
	}

	/**
	 * Executes authorization check and processes the POST submission.
	 */
    public function process(array $post): void
	{
		$this->validateCoordinator();

		$this->processRows($post['rows'] ?? []);

		if (!empty($this->texts)) {
			$this->addText("table:({$this->qidTable})");
		} elseif (!empty($this->errors)) {
			$this->addError("table:({$this->qidTable})");
		}
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

			$txTab = get_qid_row('qid', $qid, $this->qidTable);

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

			$ttTab = get_qid_row('title', $title, $this->qidTable);

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

		$qidOfTitle = get_qid_by_title($title, $this->qidTable);

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

		$qidOfTitle = get_qid_by_title($title, $this->qidTable);

		if (!empty($qidOfTitle) && $qidOfTitle == $qid) {
			$this->addText("Qid added successfully for title: $title.");
		} else {
			$this->addError("Failed to add Qid for title: $title. qid_of_title:$qidOfTitle");
		}
	}
}


