<?php
// src/app/coordinator/admin/qids/index.php

namespace App\Coordinator\Admin\Qids;

use App\User\CurrentUser;

if (!CurrentUser::getInstance()->isCoordinator()) {
	header('Location: /index.php');
	exit;
};

use function App\Utils\Html\make_mdwiki_title;
use function App\Utils\Html\make_edit_icon_new;
use function App\SQLorAPI\Funcs\get_td_or_sql_qids;
use function App\SQLorAPI\Funcs\get_td_or_sql_qids_others;

$globalUsername = CurrentUser::getInstance()->getUsername();

$qidTable = $_GET['qid_table'] ?? 'qids';

if ($qidTable != 'qids' && $qidTable != 'qids_others') $qidTable = 'qids';

function filter_qids_table($data, $vav, $id)
{

	$lList = "";

	foreach ($data as $tableName => $label) {
		$checked = ($tableName == $vav) ? "checked" : "";
		$lList .= <<<HTML
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

	$uuu = <<<HTML
		<div class="input-group">
			<div class="form-control" style="background-color: transparent; border: none;">
				$lList
			</div>
		</div>
	HTML;

	return $uuu;
}

function qids_make_row($id, $title, $qid, $numb, $qidTable)
{

	$editParams = array(
		'id'   => $id,
		'qid_table'  => $qidTable,
		'title'  => $title,
		'qid'  => $qid
	);

	$editIcon = make_edit_icon_new("qids/edit_qid", $editParams);

	$mdTitle = make_mdwiki_title($title);

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

$testin = (($_GET['test'] ?? '') != '') ? '<input type="hidden" name="test" value="1" />' : "";

$dis = $_GET['dis'] ?? 'all';

if (!isset($_GET['dis']) && $globalUsername == "Mr. Ibrahem") $dis = "empty";

$QidsTitle = ($qidTable == "qids") ? "TD Qids" : "Qids Others";

if ($qidTable == "qids") {
	$qq1 = get_td_or_sql_qids($dis);
} else {
	$qq1 = get_td_or_sql_qids_others($dis);
}

$numb = 0;

$done = [];

$formRows = "";

foreach ($qq1 as $Key => $table) {
	$id 	= $table['id'] ?? "";
	$title 	= $table['title'] ?? "";
	$qid 	= $table['qid'] ?? "";

	if (!in_array($id, $done)) {
		$done[] = $id;

		$numb += 1;
		$formRows .= qids_make_row($id, $title, $qid, $numb, $qidTable);
	}

	if ($dis == 'duplicate') {
		$id2 	= $table['id2'] ?? "";
		$title2 = $table['title2'] ?? "";
		$qid2 	= $table['qid2'] ?? "";

		if (!in_array($id2, $done)) {
			$done[] = $id2;

			$numb += 1;
			$formRows .= qids_make_row($id2, $title2, $qid2, $numb, $qidTable);
		}
	};
};

$data = [
	"qids" => 'TD Qids',
	"qids_others" => 'Qids Others',
];

$filterTa = filter_qids_table($data, $qidTable, 'qid_table');

$disData = [
	"empty" => 'Empty',
	"all" => 'All',
	"duplicate" => 'Duplicate',
];

$filterDis = filter_qids_table($disData, $dis, 'dis');

echo <<<HTML
	<div class='card'>
		<div class='card-header'>
			<form class='form-inline' style='margin-block-end: 0em;' method='get' action='index.php'>
				<input name='ty' value='qids' type='hidden'/>
				<div class='row'>
					<div class='col-md-4'>
						<h4>$QidsTitle: ($dis:<span>$numb</span>)</h4>
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

$newRow = make_edit_icon_new("qids/edit_qid", ["new" => 1, "qid_table" => $qidTable], $text = "Add one!");

echo <<<HTML
	<div class='card mt-1'>
		<div class='card-body'>
			$newRow
		</div>
	</div>
HTML;

?>
</div>
