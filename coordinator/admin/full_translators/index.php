<?php
//---
if (user_in_coord == false) {
	echo "<meta http-equiv='refresh' content='0; url=index.php'>";
	exit;
};
//---
use function SQLorAPI\Get\get_td_or_sql_full_translators;
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
$qq = get_td_or_sql_full_translators();
//---
$numb = 0;
//---
$form_text = '';
//---
foreach ($qq as $Key => $table) {
	$numb += 1;
	//---
	$user_id	= $table['id'] ?? "";
	$usere	= $table['user'] ?? "";
	//---
	$form_text .= <<<HTML
		<tr>
			<td data-content="id">
				<input class="form-control" size="20" name="rows[$numb][id]" value="$user_id" hidden/>
				<span><b>$user_id</b></span>
			</td>
			<td data-content="user">
				<span><a href='/Translation_Dashboard/leaderboard.php?user=$usere'>$usere</a></span>
				<input name='rows[$numb][user]' value='$usere' hidden/>
			</td>
			<td data-content="delete">
				<input type='checkbox' name='rows[$numb][del]' value='$user_id'/> <label> delete</label>
			</td>
		</tr>
	HTML;
};
//---
$numb += 1;
//---
$form_text_plus = <<<HTML
	<tr>
		<td data-content="id">
			<span><b>Add:</b></span>
		</td>
		<td data-content="user">
			<input class='form-control' name='rows[$numb][is_new]' value='yes' hidden/>
			<input class='form-control td_user_input' name='rows[$numb][user]' />
		</td>
		<td data-content="delete">
			-
		</td>
	</tr>
HTML;
//---
echo <<<HTML
	<div class='card-header'>
		<h4>Full article translators:</h4>
	</div>
	<div class='card-body'>
		<form action="index.php?ty=full_translators" method="POST">
			<input name='ty' value="full_translators" hidden />
			<div class="form-group">
				<table class='table table-striped compact table-mobile-responsive table-mobile-sided' style="width:50%;">
					<thead>
						<tr>
							<th>ID</th>
							<th>User</th>
							<th>Delete</th>
						</tr>
					</thead>
					<tbody id="full_tab">
						$form_text
						$form_text_plus
					</tbody>
				</table>
			</div>
			<div class="form-group d-flex justify-content-between">
				<button type="submit" class="btn btn-outline-primary">Save</button>
				<!-- <span role='button' id="add_row" class="btn btn-outline-primary" onclick='add_row_v()'>New row</span> -->
			</div>
		</form>
	</div>
HTML;
?>
<script type="text/javascript">
	// $(document).ready(function() {

	function add_row_v() {
		var ii = $('#full_tab >tr').length + 1;
		// ---
		var e = `
			<tr>
				<td>
					<b>${ii}</b>
				</td>
				<td>
					<input class='form-control' name='rows[${ii}][is_new]' value='yes' hidden/>
					<input class='form-control td_user_input' name='rows[${ii}][user]'/>
				</td>
				<td>-</td>
			</tr>
		`;
		// ---
		$('#full_tab').append(e);
	};
	// });
</script>
</div>
