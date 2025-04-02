<?php
//---
if (user_in_coord == false) {
	echo "<meta http-equiv='refresh' content='0; url=index.php'>";
	exit;
};
//---
use function Actions\Html\make_mdwiki_title;
use function SQLorAPI\Get\get_td_or_sql_qids_others;
//---
function make_edit_icon($id, $title, $qid)
{
	//---
	$edit_params = array(
		'id'   => $id,
		'title'  => $title,
		'nonav'  => 1,
		'qid'  => $qid
	);
	//---
	$edit_url = "index.php?ty=qids_others/edit_qid&" . http_build_query($edit_params);
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

$qq1 = get_td_or_sql_qids_others($dis);

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
echo <<<HTML
	<div class='card-header'>
		<div class='row'>
			<div class='col-md-5'>
				<h4>Other Qids: ($dis:<span>$numb</span>)</h4>
			</div>
			<div class='col-md-3'>
				<!-- only display empty qids_others -->
				<a class='btn btn-outline-secondary' href="index.php?ty=qids_others&dis=empty">Only Empty</a>
			</div>
			<div class='col-md-2'>
				<a class='btn btn-outline-secondary' href="index.php?ty=qids_others&dis=all">All</a>
			</div>
			<div class='col-md-2'>
				<!-- only display empty qids_others -->
				<a class='btn btn-outline-secondary' href="index.php?ty=qids_others&dis=duplicate">Duplicate</a>
			</div>
		</div>
	</div>
	<div class='card-body'>
		<table class='table table-striped compact table-mobile-responsive sortable2' style='width: 90%;'>
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
echo <<<HTML
	<div class='card'>
		<div class='card-body'>
			<form action="index.php?ty=qids_others/post" method="POST">
				$testin
				<input name='ty' value="qids_others/post" hidden/>
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
	function add_row() {
		$('#submit_bt').show();
		$('#qidstab').show();
		// ---
		var ii = $('#tab_new >tr').length + 1;

		var e = `
			<tr>
				<td>${ii}</td>
				<td><input class='form-control' name='add_qids[]${ii}' placeholder='title${ii}'/></td>
				<td><input class='form-control' name='qid[]${ii}' placeholder='qid${ii}'/></td>
			</tr>
		`;

		$('#tab_new').append(e);
	};
</script>
</div>
