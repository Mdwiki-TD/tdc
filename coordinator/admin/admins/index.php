<?php
//---
if (user_in_coord == false) {
	echo "<meta http-equiv='refresh' content='0; url=index.php'>";
	exit;
};
//---
use function SQLorAPI\Get\get_coordinator;
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
$qq = get_coordinator();
//---
sort($qq);
//---
$numb = 0;
//---
$table_rows = "";
//---
foreach ($qq as $Key => $table) {
	$numb += 1;
	$ide	= $table['id'] ?? "";
	$usere	= $table['user'] ?? "";
	//---
	$table_rows .= <<<HTML
		<tr>
			<td data-content="id">
				<span><b>$ide</b></span>
				<input name='id[]$numb' value='$ide' hidden/>
			</td>
			<td data-content="user">
				<span><a href='/Translation_Dashboard/leaderboard.php?user=$usere'>$usere</a></span>
				<input name='user[]$numb' value='$usere' hidden/>
			</td>
			<td data-content="delete">
				<input type='checkbox' name='del[]$numb' value='$ide'/> <label> delete</label>
			</td>
		</tr>
	HTML;
};
//---
echo <<<HTML
	<div class='card-header'>
		<h4>Coordinators:</h4>
	</div>
	<div class='card-body'>
		<form action="index.php?ty=admins" method="POST">
			<input name='ty' value="admins" hidden/>
			<div class="form-group">
				<table class='table table-striped compact table-mobile-responsive' style="width:50%;">
					<thead>
						<tr>
							<th>id</th>
							<th>User</th>
							<th>Delete</th>
						</tr>
					</thead>
					<tbody id="coo_tab">
						$table_rows
					</tbody>
				</table>
			</div>
			<div class="form-group d-flex justify-content-between">
				<button type="submit" class="btn btn-outline-primary">Save</button>
				<span role='button' id="add_row" class="btn btn-outline-primary" onclick='add_row()'>New row</span>
				<span> </span>
			</div>
		</form>
	</div>
HTML;
//---
?>

<script type="text/javascript">

	function add_row() {
		var ii = $('#coo_tab >tr').length + 1;
		// ---
		var e = `
			<tr>
				<td>${ii}</td>
				<td><input class='form-control td_user_input' name='user[]${ii}'/></td>
				<td>-</td>
			</tr>
		`;
		// ---
		$('#coo_tab').append(e);
	};
</script>
</div>
