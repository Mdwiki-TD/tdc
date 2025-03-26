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
foreach (range(1, 1) as $numb) {
	//---
	$cats_line = <<<HTML
		<select class='form-select catsoptions' name='cat[]$numb' data-bs-theme="auto">
			$cats
		</select>
	HTML;
	//---
	$type_line = sprintf($typies, $numb, $numb);
	//---
	$table .= <<<HTML
	<tr>
		<td data-order='$numb' data-content='#'>
			$numb
		</td>
		<td data-content='Mdwiki Title'>
			<input class="form-control mdtitles" size='15' name='mdtitle[]$numb' required/>
		</td>
		<td data-content='Campaign'>
			$cats_line
		</td>
		<td data-content='Type'>
			$type_line
		</td>
		<td data-content='User'>
			<input class="form-control td_user_input" size='10' name='user[]$numb' required/>
		</td>
		<td data-content='Lang.'>
			<input class="form-control lang_input" size='2' name='lang[]$numb' required/>
		</td>
		<td data-content='Target'>
			<input class="form-control" size='20' name='target[]$numb'/>
		</td>
		<td data-content='Publication date'>
			<input class="form-control" size='10' name='pupdate[]$numb' placeholder='YYYY-MM-DD'/>
		</td>
	</tr>
	HTML;
};
//---
$testin = (($_REQUEST['test'] ?? '') != '') ? "<input name='test' value='1' hidden/>" : "";
//---
echo <<<HTML
	<style>
		.ui-menuxx {
			height: 200px;
		}
	</style>
	<div class='card-header'>
		<h4>Add translations:</h4>
	</div>
	<div class='cardbody p-2'>
		<form action="index.php?ty=add" method="POST">
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
					<tbody id='g_tab'>
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
</div>

<script type="text/javascript">
	function add_new_row() {
		const options = $('.catsoptions').html();
		const ii = $('#g_tab > tr').length + 1;

		const row = `
			<tr>
				<td data-content="#">${ii}</td>
				<td data-content="mdwiki title"><input class="form-control mdtitles" size="15" name="mdtitle[]${ii}" required /></td>
				<td data-content="Campaign"><select class="form-select" name="cat[]${ii}">${options}</select></td>
				<td data-content="Type">
					<select name="type[]${ii}" class="form-select">
						<option value="lead">Lead</option>
						<option value="all">All</option>
					</select>
				</td>
				<td data-content="User"><input class="form-control td_user_input" size="10" name="user[]${ii}" required /></td>
				<td data-content="Language"><input class="form-control lang_input" size="2" name="lang[]${ii}" required /></td>
				<td data-content="Wiki title"><input class="form-control" size="20" name="target[]${ii}" required /></td>
				<td data-content="Date"><input class="form-control" size="10" name="pupdate[]${ii}" required /></td>
			</tr>
		`;

		$('#g_tab').append(row);
	}
</script>

</div>
