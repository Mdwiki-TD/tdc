<?php
//---
if (user_in_coord == false) {
	echo "<meta http-equiv='refresh' content='0; url=index.php'>";
	exit;
};
//---
use function SQLorAPI\Get\get_td_or_sql_categories;
//---
if (isset($_REQUEST['test'])) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
};
//---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require __DIR__ . '/post.php';
}
//---
$cats = "";
//---
$qqq = get_td_or_sql_categories();
//---
foreach ($qqq as $Key => $ta) {
	$ca = $ta['category'] ?? "";
	$ds = $ta['campaign'] ?? "";
	if (!empty($ca)) $cats .= "<option value='$ca'>$ds</option>";
};
//---
$typies = <<<HTML
	<select name='type[]%s' id='type[]%s' class='form-select'>
		<option value='lead'>Lead</option><option value='all'>All</option>
	</select>
	HTML;
//---
$table = "";
//---
$testin = (($_REQUEST['test'] ?? '') != '') ? "<input name='test' value='1' hidden/>" : "";
//---
echo <<<HTML
	<select class='catsoptions' data-bs-theme="auto" hidden>$cats</select>
	<div class='card-header'>
		<h4>Add translations by Wikipedia url:</h4>
	</div>
	<div class='cardbody p-2'>
		<form action="index.php?ty=addnew" method="POST">
			$testin
			<input name='ty' value="addnew" hidden />
			<div class="form-group">
				<table class='table table-striped compact table-mobile-responsive table-mobile-sided' style='font-size:95%;'>
					<thead>
						<tr>
							<th>#</th>
							<th>Mdwiki Title</th>
							<th>Campaign</th>
							<th>Type</th>
							<th>User</th>
							<th>Lang</th>
							<th>Target</th>
							<th>Publication date</th>
						</tr>
					</thead>
					<tbody id='tab_data'>
						<tr></tr>
					</tbody>
				</table>
			</div>
			<button type="submit" class="btn btn-outline-primary mb-10">Save</button>
		</form>
	</div>
HTML;
//---
?>
<div class='cardbody p-3'>

	<div class='container'>
		<div id='alert' class="alert alert-warning" role="alert" style="display:none;">
			<i class="bi bi-exclamation-triangle"></i> <span id='alert_text'></span>
		</div>
	</div>
	<div class="input-group">
		<span class="input-group-text">URL</span>
		<input class="form-control mdtitles url" size='15' id='url' name='url' value='https://ar.wikipedia.org/wiki/أتولتيفيماب/مافتيفيماب/أوديسيفيماب' />
		<button class="btn btn-outline-primary mb-10" onclick="start_one_url(this)">Search</button>
	</div>
</div>

<script src='coordinator/add_by_url.js'></script>

</div>
