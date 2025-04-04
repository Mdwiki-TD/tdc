<?php
//---
if (user_in_coord == false) {
	echo "<meta http-equiv='refresh' content='0; url=index.php'>";
	exit;
};
//---
use function SQLorAPI\Get\get_td_or_sql_categories;
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
$uuux = '';
//---
$qq = get_td_or_sql_categories();
//---
$numb = 0;
//---
$table_rows = "";
//---
foreach ($qq as $Key => $table) {
	$numb += 1;
	$id 		= $table['id'] ?? "";
	$category1 	= $table['category'] ?? "";
	$category2 	= $table['category2'] ?? "";
	$campaign 	= $table['campaign'] ?? "";
	$depth		= $table['depth'] ?? "";
	//---
	$checked    = ($table['def'] == 1) ? 'checked' : '';
	//---
	$table_rows .= <<<HTML
	<tr>
		<div class='form-group'>
			<th data-content="#" style="width: 4%;">
				$numb
				<input name='id[]$numb' value='$id' hidden/>
			</th>
			<td data-content="Campaign">
				<input class="form-control" size='10' name='camp[]$numb' value='$campaign'/>
			</td>
			<td data-content="Category1">
				<input class="form-control" size='25' name='cats[]$numb' value='$category1'/>
			</td>
			<td data-content="Category2">
				<input class="form-control" size='25' name='cat2[]$numb' value='$category2'/>
			</td>
			<td data-content="Depth">
				<input class="form-control w-auto" type='number' name='dep[]$numb' value='$depth' min='0' max='10'/>
			</td>
			<td data-content="Default Cat">
				<input class="form-check-input" type='radio' id='default_cat' name='default_cat' value='$id' $checked>
			</td>
			<td data-content="Delete">
				<input type='checkbox' name='del[]$numb' value='$id'/> <label>delete</label>
			</td>
		</div>
	</tr>
	HTML;
};
//---
echo <<<HTML
	<div class='card-header'>
		<h4>Campaigns:</h4>
	</div>
	<div class='card-body'>
		<form action="index.php?ty=Campaigns" method="POST">
			<input name='ty' value="Campaigns" hidden/>
			<div class="form-group">
				<table class='table table-striped compact table-mobile-responsive table-mobile-sided'>
					<thead>
						<tr>
							<th>#</th>
							<th>Campaign</th>
							<th>Category1</th>
							<th>Category2</th>
							<th>Depth</th>
							<th>Default</th>
							<th>Delete</th>
						</tr>
					</thead>
					<tbody id="tab_logic">
						$table_rows
					</tbody>
				</table>
			</div>
			<div class="form-group d-flex justify-content-between">
				<button type="submit" class="btn btn-outline-primary">Save</button>
				<span role='button' id="add_row" class="btn btn-outline-primary" onclick='add_row()'>New row</span>
			</div>
		</form>
	</div>
HTML;
//---
?>
</div>
<script type="text/javascript">
	function add_row() {
		var ii = $('#tab_logic >tr').length + 1;
		// ---
		var e = `
			<tr>
				<td>${ii}</td>
				<td><input class='form-control' name='camp[]${ii}' placeholder='Campaign' value=''/></td>
				<td><input class='form-control' name='cats[]${ii}' placeholder='Category1' value=''/></td>
				<td><input class='form-control' name='cat2[]${ii}' placeholder='Category2' value=''/></td>
				<td><input class='form-control w-auto' type='number' name='dep[]${ii}' value='0' min='0' max='10'/></td>
				<td></td>
				<td></td>
			</tr>
		`;
		// ---
		$('#tab_logic').append(e);
		// ---
	};
</script>
</div>
