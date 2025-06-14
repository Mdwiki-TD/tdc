<?php
//---
if (user_in_coord == false) {
	echo "<meta http-equiv='refresh' content='0; url=index.php'>";
	exit;
};
//---
use function Actions\Html\make_mdwiki_title;
use function Actions\Html\make_edit_icon_new;
use function SQLorAPI\Funcs\get_td_or_sql_qids;
use function SQLorAPI\Funcs\get_td_or_sql_qids_others;
use function TDWIKI\csrf\generate_csrf_token;
//---
$qid_table = $_GET['qid_table'] ?? 'qids';
//---
if ($qid_table != 'qids' && $qid_table != 'qids_others') $qid_table = 'qids';

function filter_table($data, $vav, $id)
{
	//---
	$l_list = "";
	//---
	foreach ($data as $table_name => $label) {
		$checked = ($table_name == $vav) ? "checked" : "";
		$l_list .= <<<HTML
			<div class="form-check form-check-inline">
				<input class="form-check-input"
					type="radio"
					name="$id"
					id="radio_$table_name"
					value="$table_name"
					$checked>
				<label class="form-check-label" for="radio_$table_name">$label</label>
			</div>
		HTML;
	}
	//---
	$uuu = <<<HTML
		<div class="input-group">
			<div class="form-control" style="background-color: transparent; border: none;">
				$l_list
			</div>
		</div>
	HTML;
	//---
	return $uuu;
}

function make_row($id, $title, $qid, $numb)
{
	global $qid_table;
	//---
	$edit_params = array(
		'id'   => $id,
		'qid_table'  => $qid_table,
		'title'  => $title,
		'qid'  => $qid
	);
	//---
	$edit_icon = make_edit_icon_new("qids/edit_qid", $edit_params);
	//---
	$md_title = make_mdwiki_title($title);
	//---
	return <<<HTML
	<tr>
		<th data-content="#" data-sort="$numb">
			$numb
		</th>
		<th data-content="#" data-sort="$id">
			$id
		</th>
		<td data-content="title" data-sort="$title">
			$md_title
		</td>
		<td data-content="qid" data-sort="$qid">
			<a target='_blank' href='https://wikidata.org/wiki/$qid'>$qid</a>
		</td>
		<td data-content="Edit">
			$edit_icon
		</td>
	</tr>
	HTML;
}

$testin = (($_GET['test'] ?? '') != '') ? '<input type="hidden" name="test" value="1" />' : "";
//---
$dis = $_GET['dis'] ?? 'all';
//---
if (!isset($_GET['dis']) && $GLOBALS['global_username'] == "Mr. Ibrahem") $dis = "empty";
//---
$Qids_title = ($qid_table == "qids") ? "TD Qids" : "Qids Others";
//---
if ($qid_table == "qids") {
	$qq1 = get_td_or_sql_qids($dis);
} else {
	$qq1 = get_td_or_sql_qids_others($dis);
}
// ---
$numb = 0;
//---
$done = [];
//---
$form_rows = "";
//---
foreach ($qq1 as $Key => $table) {
	$id 	= $table['id'] ?? "";
	$title 	= $table['title'] ?? "";
	$qid 	= $table['qid'] ?? "";
	//---
	if (!in_array($id, $done)) {
		$done[] = $id;
		//---
		$numb += 1;
		$form_rows .= make_row($id, $title, $qid, $numb);
	}
	//---
	if ($dis == 'duplicate') {
		$id2 	= $table['id2'] ?? "";
		$title2 = $table['title2'] ?? "";
		$qid2 	= $table['qid2'] ?? "";
		//---
		if (!in_array($id2, $done)) {
			$done[] = $id2;
			//---
			$numb += 1;
			$form_rows .= make_row($id2, $title2, $qid2, $numb);
		}
	};
	//---
};
//---
$data = [
	"qids" => 'TD Qids',
	"qids_others" => 'Qids Others',
];
//---
$filter_ta = filter_table($data, $qid_table, 'qid_table');
//---
$dis_data = [
	"empty" => 'Empty',
	"all" => 'All',
	"duplicate" => 'Duplicate',
];
//---
$filter_dis = filter_table($dis_data, $dis, 'dis');
//---
echo <<<HTML
	<div class='card-header'>
		<form class='form-inline' style='margin-block-end: 0em;' method='get' action='index.php'>
			<input name='ty' value='qids' type='hidden'/>
			<div class='row'>
				<div class='col-md-4'>
					<h4>$Qids_title: ($dis:<span>$numb</span>)</h4>
				</div>
				<div class='col-md-2'>
					$filter_ta
				</div>
				<div class='col-md-4'>
					$filter_dis
				</div>
				<div class='aligncenter col-md-2'>
					<input class='btn btn-outline-primary' type='submit' value='Filter' />
				</div>
			</div>
		</form>
	</div>
	<div class='card-body'>
		<table class='table table-striped compact table-mobile-responsive table-mobile-sided sortable2' style='width: 98%;'>
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
				$form_rows
			</tbody>
		</table>
	</div>
	</div>
HTML;
// ---
$new_row = make_edit_icon_new("qids/edit_qid", ["new" => 1, "qid_table" => $qid_table], $text = "Add one!");
//---
echo <<<HTML
	<div class='card mt-1'>
		<div class='card-body'>
			$new_row
		</div>
	</div>
HTML;

?>
</div>
