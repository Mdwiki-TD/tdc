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
use function SQLorAPI\Funcs\get_td_or_sql_projects;
use function TDWIKI\csrf\generate_csrf_token;
//---
$numb = 0;
//---
$projs = get_td_or_sql_projects();
//---
// sort $projs by g_id
uasort($projs, function ($a, $b) {
	return $a['g_id'] <=> $b['g_id'];
});
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
			<span><b>$gid</b></span>
			<input name='rows[$numb][g_id]' value='$gid' type='hidden'/>
		</td>
	  	<td data-content='Project'>
	  		<input class='form-control' name='rows[$numb][g_title]' value='$gtitle'/>
		</td>
	  	<td data-content='Delete'>
	  		<input type='checkbox' name='rows[$numb][del]' value='$gid'/> <label> delete</label>
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
			<input class='form-control td_user_input' name='rows[$numb][g_title]' />
		</td>
		<td data-content="delete">-
		</td>
	</tr>
HTML;
//---
$csrf_token = generate_csrf_token(); // <input name='csrf_token' value="$csrf_token" type="hidden"/>
//---
echo <<<HTML
	<div class='card-header'>
		<h4>Projects:</h4>
	</div>
	<div class='card-body'>
		<form action="index.php?ty=projects" method="POST">
			<input name='csrf_token' value="$csrf_token" type="hidden"/>
			<input name='ty' value="projects" type="hidden"/>
			<div class="row">
				<div class="col-md-6 col-sm-12">
					<table class='table table-striped compact table-mobile-responsive table-mobile-sided'>
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
				<td>
					<b>${ii}</b>
				</td>
				<td>
					<input class='form-control' name='rows[${ii}][g_title]' value=''/>
				</td>
				<td>-</td>
			</tr>
		`;
		// ---
		$('#g_tab').append(e);
	};
</script>
</div>
