<?php
//---
if (user_in_coord == false) {
	echo "<meta http-equiv='refresh' content='0; url=index.php'>";
	exit;
};
//---
use function Actions\Html\make_mdwiki_title;
use function SQLorAPI\Get\get_td_or_sql_qids;
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
echo <<<HTML
	<div class='card-header'>
		<div class='row'>
			<div class='col-md-5'>
				<h4>Qids: ($dis:<span id="qidscount"></span>)</h4>
			</div>
			<div class='col-md-3'>
				<!-- only display empty qids -->
				<a class='btn btn-outline-secondary' href="index.php?ty=qids&dis=empty">Only Empty</a>
			</div>
			<div class='col-md-2'>
				<a class='btn btn-outline-secondary' href="index.php?ty=qids&dis=all">All</a>
			</div>
			<div class='col-md-2'>
				<!-- only display empty qids -->
				<a class='btn btn-outline-secondary' href="index.php?ty=qids&dis=duplicate">Duplicate</a>
			</div>
		</div>
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
HTML;
//---
$uuux = '';

$qq = get_td_or_sql_qids($dis);

$numb = 0;
//---
$done = [];
//---
foreach ($qq as $Key => $table) {
	$id 	= $table['id'] ?? "";
	$title 	= $table['title'] ?? "";
	$qid 	= $table['qid'] ?? "";
	//---
	if (!in_array($id, $done)) {
		$done[] = $id;
		//---
		$numb += 1;
		echo make_row($id, $title, $qid, $numb);
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
			echo make_row($id2, $title2, $qid2, $numb);
		}
	};
	//---
};
//---
echo <<<HTML
	</tbody>
	</table>
<script>$("#qidscount").text("{$numb}");</script>

<form action="index.php?ty=qids/post" method="POST">
	$testin
	<input name='ty' value="qids/post" hidden/>
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
	<span role='button' id="add_row" class="btn btn-outline-primary" style="position: absolute; right: 130px;" onclick='add_row()'>New row</span>
	<button id="submit_bt" type="submit" class="btn btn-outline-primary" style='display: none;'>Save</button>
</form>
HTML;
?>
<script type="text/javascript">
	var i = 1;

	function add_row() {
		$('#submit_bt').show();
		$('#qidstab').show();
		var ii = $('#tab_new >tr').length + 1;
		var e = "<tr>";
		e = e + "<td>" + ii + "</td>";
		e = e + "<td><input class='form-control' name='add_qids[]" + ii + "' placeholder='title" + ii + "'/></td>";
		e = e + "<td><input class='form-control' name='qid[]" + ii + "' placeholder='qid" + ii + "'/></td>";
		e = e + "</tr>";
		$('#tab_new').append(e);
		i++;
	};
</script>
</div>
