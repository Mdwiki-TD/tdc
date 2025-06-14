<?php
//---
/*
name=(["'])(\w+)\[\](\$\{*\w+\}*)["']
name=$1rows[$3][$2]$1
*/
//---
if (user_in_coord == false) {
	echo "<meta http-equiv='refresh' content='0; url=index.php'>";
	exit;
};
//---
use function SQLorAPI\Funcs\get_td_or_sql_categories;
use function TDWIKI\csrf\generate_csrf_token;
//---
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
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
	<select name='rows[%s][type]' id='rows[%s][type]' class='form-select w-100' data-bs-theme="auto">
		<option value='lead'>Lead</option><option value='all'>All</option>
	</select>
	HTML;
//---
$table = "";
//---
foreach (range(1, 1) as $numb) {
	//---
	$cats_line = <<<HTML
		<select class='form-select catsoptions' name='rows[$numb][cat]' data-bs-theme="auto">
			$cats
		</select>
	HTML;
	//---
	$type_line = sprintf($typies, $numb, $numb);
	//---
	$table .= <<<HTML
	<tr id="row_$numb">
		<td data-order='$numb' data-content='#'>
			$numb
		</td>
		<td data-content='Mdwiki Title'>
			<input class="form-control mdtitles" size='15' name='rows[$numb][mdtitle]' required/>
		</td>
		<td data-content='Campaign'>
			$cats_line
		</td>
		<td data-content='Type'>
			$type_line
		</td>
		<td data-content='User'>
			<input class="form-control td_user_input" size='10' name='rows[$numb][user]' required/>
		</td>
		<td data-content='Lang.'>
			<input class="form-control lang_input" size='2' name='rows[$numb][lang]' required/>
		</td>
		<td data-content='Target'>
			<input class="form-control" size='20' name='rows[$numb][target]'/>
		</td>
		<td data-content='Publication date'>
			<input class="form-control" size='10' name='rows[$numb][pupdate]' placeholder='YYYY-MM-DD'/>
		</td>
		<td data-content="Delete">
			<div class="">
				<button type="button" class="btn btn-danger btn-sm" onclick="delete_row($numb)">Delete</button>
			</div>
		</td>
	</tr>
	HTML;
};
//---
$testin = (($_GET['test'] ?? '') != '') ? '<input type="hidden" name="test" value="1" />' : "";
//---
$csrf_token = generate_csrf_token(); // <input name='csrf_token' value="$csrf_token" hidden />
//---
echo <<<HTML
	<select class='catsoptions' data-bs-theme="auto" hidden>$cats</select>
	<div class='card-header'>
		<h4>Add translations:</h4>
	</div>
	<div class='cardbody p-2'>
		<form action="index.php?ty=add" method="POST">
			<input name='csrf_token' value="$csrf_token" hidden />
			$testin
			<input name='ty' value="add" hidden />
			<div class="form-group">
				<table class='table table-striped compact table-mobile-responsive table-mobile-sided' style='font-size:95%;'>
					<thead>
						<tr>
							<th>#</th>
							<th>Mdwiki Title</th>
							<th>Campaign</th>
							<th>Type</th>
							<th>User</th>
							<th>Lang.</th>
							<th>Target</th>
							<th>Publication date</th>
						</tr>
					</thead>
					<tbody id='tab_data'>
						$table
					</tbody>
				</table>
			</div>
			<div class="form-group d-flex justify-content-between">
				<button type="submit" class="btn btn-outline-primary mb-10">Save</button>
				<span role='button' id="add_new_row" class="btn btn-outline-primary" onclick='add_new_row()'>New row</span>
			</div>
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

<script src='/tdc/js/add_by_url.js'></script>

</div>
