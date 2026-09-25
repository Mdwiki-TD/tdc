<?php
// src/app/coordinator/admin/qids/index.php

namespace App\Coordinator\Admin\Qids;

use App\User\CurrentUser;
use App\Coordinator\Admin\Common\AbstractControllerNoPost;
use function App\Utils\Html\make_mdwiki_title;
use function App\Utils\Html\make_edit_icon_new;
use function App\SQLorAPI\Funcs\get_td_or_sql_qids;
use function App\SQLorAPI\Funcs\get_td_or_sql_qids_others;

/**
 * Class QidsIndexController
 * Lists TD Qids / Qids Others records with duplicate/empty filtering
 * and per-row edit access.
 */
class QidsIndexController extends AbstractControllerNoPost
{
	private CurrentUser $currentUser;
	private string $qidTable;
	private string $dis;

	public function __construct()
	{
		$this->currentUser = CurrentUser::getInstance();

		$this->qidTable = $_GET['qid_table'] ?? 'qids';
		if ($this->qidTable !== 'qids' && $this->qidTable !== 'qids_others') {
			$this->qidTable = 'qids';
		}

		$this->dis = $_GET['dis'] ?? 'all';
		if (!isset($_GET['dis']) && $this->currentUser->getUsername() === 'Mr. Ibrahem') {
			$this->dis = 'empty';
		}
	}

	/**
	 * Handles authentication and executes controller output.
	 */
	public function handleRequest(): void
	{
        $this->validateCoordinator();

		$qidsTitle = ($this->qidTable === 'qids') ? 'TD Qids' : 'Qids Others';

		$rows = ($this->qidTable === 'qids')
			? get_td_or_sql_qids($this->dis)
			: get_td_or_sql_qids_others($this->dis);

		[$formRows, $numb] = $this->buildFormRows($rows);

		$this->renderListCard($qidsTitle, $numb, $formRows);
		$this->renderAddNewCard();
	}

	/**
	 * Builds all HTML table rows, de-duplicating by id, including the
	 * secondary duplicate-pair row when the "duplicate" filter is active.
	 */
	private function buildFormRows(array $rows): array
	{
		$numb = 0;
		$done = [];
		$formRows = '';

		foreach ($rows as $key => $table) {
			$id    = $table['id'] ?? '';
			$title = $table['title'] ?? '';
			$qid   = $table['qid'] ?? '';

			if (!in_array($id, $done)) {
				$done[] = $id;

				$numb++;
				$formRows .= $this->renderRow($id, $title, $qid, $numb);
			}

			if ($this->dis === 'duplicate') {
				$id2    = $table['id2'] ?? '';
				$title2 = $table['title2'] ?? '';
				$qid2   = $table['qid2'] ?? '';

				if (!in_array($id2, $done)) {
					$done[] = $id2;

					$numb++;
					$formRows .= $this->renderRow($id2, $title2, $qid2, $numb);
				}
			}
		}

		return [$formRows, $numb];
	}

	/**
	 * Renders a single qid table row.
	 */
	private function renderRow(string $id, string $title, string $qid, int $numb): string
	{
		$editParams = [
			'id'        => $id,
			'qid_table' => $this->qidTable,
			'title'     => $title,
			'qid'       => $qid,
		];

		$editIcon = make_edit_icon_new('qids/edit_qid', $editParams);
		$mdTitle  = make_mdwiki_title($title);

		return <<<HTML
            <tr>
                <th data-content="#" data-sort="$numb">
                    $numb
                </th>
                <th data-content="#" data-sort="$id">
                    $id
                </th>
                <td data-content="title" data-sort="$title">
                    $mdTitle
                </td>
                <td data-content="qid" data-sort="$qid">
                    <a target='_blank' href='https://wikidata.org/wiki/$qid'>$qid</a>
                </td>
                <td data-content="Edit">
                    $editIcon
                </td>
            </tr>
        HTML;
	}

	/**
	 * Renders a set of radio-button filter controls for the given options.
	 */
	private function renderFilterRadios(array $data, string $selected, string $id): string
	{
		$list = '';

		foreach ($data as $tableName => $label) {
			$checked = ($tableName === $selected) ? 'checked' : '';
			$list .= <<<HTML
                <div class="form-check form-check-inline">
                    <input class="form-check-input"
                        type="radio"
                        name="$id"
                        id="radio_$tableName"
                        value="$tableName"
                        $checked>
                    <label class="form-check-label" for="radio_$tableName">$label</label>
                </div>
            HTML;
		}

		return <<<HTML
            <div class="input-group">
                <div class="form-control" style="background-color: transparent; border: none;">
                    $list
                </div>
            </div>
        HTML;
	}

	/**
	 * Renders the main filter card with the results table.
	 */
	private function renderListCard(string $qidsTitle, int $numb, string $formRows): void
	{
		$data = [
			'qids'        => 'TD Qids',
			'qids_others' => 'Qids Others',
		];
		$filterTa = $this->renderFilterRadios($data, $this->qidTable, 'qid_table');

		$disData = [
			'empty'     => 'Empty',
			'all'       => 'All',
			'duplicate' => 'Duplicate',
		];
		$filterDis = $this->renderFilterRadios($disData, $this->dis, 'dis');

		$dis = $this->dis;

		echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <form class='form-inline' style='margin-block-end: 0em;' method='get' action='index.php'>
                        <input name='ty' value='qids' type='hidden'/>
                        <div class='row'>
                            <div class='col-md-4'>
                                <h4>$qidsTitle: ($dis:<span>$numb</span>)</h4>
                            </div>
                            <div class='col-md-2'>
                                $filterTa
                            </div>
                            <div class='col-md-4'>
                                $filterDis
                            </div>
                            <div class='aligncenter col-md-2'>
                                <input class='btn btn-outline-primary' type='submit' value='Filter' />
                            </div>
                        </div>
                    </form>
                </div>
                <div class='card-body'>
                    <table class='table table-striped compact table-mobile-responsive table-mobile-sided sortable2 table_text_left' style='width: 98%;'>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>id</th>
                                <th>Title</th>
                                <th>Qid</th>
                                <th>Edit</th>
                            </tr>
                        </thead>
                        <tbody id="tab_logic">
                            $formRows
                        </tbody>
                    </table>
                </div>
            </div>
        HTML;
	}

	/**
	 * Renders the "Add one!" shortcut card below the results table.
	 */
	private function renderAddNewCard(): void
	{
		$newRow = make_edit_icon_new('qids/edit_qid', ['new' => 1, 'qid_table' => $this->qidTable], 'Add one!');

		echo <<<HTML
            <div class='card mt-1'>
                <div class='card-body'>
                    $newRow
                </div>
            </div>
        HTML;
	}
}


