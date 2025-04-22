<?php
//---
if (user_in_coord == false) {
	echo "<meta http-equiv='refresh' content='0; url=index.php'>";
	exit;
};
//---
use function Actions\Html\make_mdwiki_title;
use function SQLorAPI\Get\get_td_or_sql_qids;
use function SQLorAPI\Get\get_td_or_sql_qids_others;
use function TDWIKI\csrf\generate_csrf_token;
//---
$qid_table = $_GET['qid_table'] ?? 'qids';

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

function make_edit_icon($id, $title, $qid)
{
	global $qid_table;
	//---
	$edit_params = array(
		'id'   => $id,
		'qid_table'  => $qid_table,
		'title'  => $title,
		'nonav'  => 1,
		'qid'  => $qid
	);
	//---
	$edit_url = "index.php?ty=qids/edit_qid&" . http_build_query($edit_params);
	//---
	$onclick = 'pupwindow1("' . $edit_url . '")';
	//---
	return <<<HTML
    	<a class='btn btn-outline-primary btn-sm' onclick='$onclick'>Edit</a>
    HTML;
}
//---
function make_row($id, $title, $qid, $numb)
{
	$edit_icon = make_edit_icon($id, $title, $qid);
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

$testin = (($_REQUEST['test'] ?? '') != '') ? "<input name='test' value='1' hidden/>" : "";
//---
$dis = $_GET['dis'] ?? 'all';
//---
if (!isset($_GET['dis']) && global_username == "Mr. Ibrahem") $dis = "empty";
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
			<input name='ty' value='qids' hidden/>
			<div class='row'>
				<div class='col-md-3'>
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
		<table class='table table-striped compact table-mobile-responsive table-mobile-sided sortable2' style='width: 90%;'>
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
$csrf_token = generate_csrf_token(); // <input name='csrf_token' value="$csrf_token" hidden />
//---
echo <<<HTML
	<div class='card'>
		<div class='card-body'>
			<form action="index.php?ty=$qid_table/post&dis=$dis&qid_table=$qid_table" method="POST">
				<input name='csrf_token' value="$csrf_token" hidden />
				$testin
				<input name='ty' value="qids/post" hidden/>
				<input name='qid_table' value="$qid_table" hidden/>
				<div id='qidstab' style='display: none;'>
					<table class='table table-striped compact table-mobile-responsive table-mobile-sided' style='width: 90%;'>
						<thead>
							<tr>
								<th>#</th>
								<th>Title</th>
								<th>Qid</th>
							</tr>
						</thead>
						<tbody id="tab_new">
						</tbody>
					</table>
				</div>
				<div class="form-group d-flex justify-content-between">
					<button id="submit_bt" type="submit" class="btn btn-outline-primary" style='display: none;'>Save</button>
					<span role='button' id="add_row" class="btn btn-outline-primary" onclick='add_row()'>New row</span>
					<span> </span>
				</div>
			</form>
		</div>
	</div>
HTML;

?>
<script type="text/javascript">
	var ii = 0;
	// ---
	function add_row() {
		// ---
		ii += 1;
		// ---
		$('#submit_bt').show();
		$('#qidstab').show();
		// ---
		var e = `
			<tr>
				<td>${ii}</td>
				<td><input class='form-control' name='rows[${ii}][title]' placeholder='title${ii}'/></td>
				<td><input class='form-control' name='rows[${ii}][qid]' placeholder='qid${ii}'/></td>
			</tr>
		`;
		// ---
		$('#tab_new').append(e);
	};
</script>
</div>
