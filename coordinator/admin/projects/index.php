<?php
//---
if (user_in_coord == false) {
	echo "<meta http-equiv='refresh' content='0; url=index.php'>";
	exit;
};
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
use function SQLorAPI\Get\get_td_or_sql_projects;
//---
$numb = 0;
//---
$projs = get_td_or_sql_projects();
//---
$form_text = '';
//---
foreach ($projs as $gtitle => $tab) {
	$numb += 1;
	//---
	$gid = $tab['g_id'] ?? "";
	$gtitle = $tab['g_title'] ?? "";
	//---
	$form_text .= <<<HTML
	<tr>
		<td data-content='id'>
			<span><b>$numb</b></span>
			<input name='g_id[]$numb' value='$gid' hidden/>
		</td>
	  	<td data-content='Project'>
	  		<input class='form-control' name='g_title[]$numb' value='$gtitle'/>
		</td>
	  	<td data-content='Delete'>
	  		<input type='checkbox' name='del[]$numb' value='$gid'/> <label> delete</label>
	  	</td>
	</tr>
	HTML;
};
//---
$form_text_plus = <<<HTML
	<tr>
		<td data-content="id">
			<span><b>Add:</b></span>
		</td>
		<td data-content="user">
			<input class='form-control td_user_input' name='g_title[]$numb' />
		</td>
		<td data-content="delete">

		</td>
	</tr>
HTML;
//---
echo <<<HTML
	<div class='card-header'>
		<h4>Projects:</h4>
	</div>
	<div class='card-body'>
		<form action="index.php?ty=projects" method="POST">
			<input name='ty' value="projects" hidden />
			<div class="form-group">
				<table class='table table-striped compact table-mobile-responsive table-mobile-sided' style="width:50%;">
					<thead>
						<tr>
							<th>Id</th>
							<th>Project</th>
							<th>Delete</th>
						</tr>
					</thead>
					<tbody id="g_tab">
						$form_text
						$form_text_plus
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
?>
<script type="text/javascript">

	function add_row() {
		var ii = $('#g_tab >tr').length + 1;
		// ---
		var e = `
			<tr>
				<td><b>${ii}</b><input name='g_id[]' value='0' hidden/></td>
				<td><input class='form-control' name='g_title[]${ii}' value=''/></td>
				<td>-</td>
			</tr>
		`;
		// ---
		$('#g_tab').append(e);
	};
</script>
</div>
