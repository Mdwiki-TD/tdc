<?php
//---
if (user_in_coord == false) {
	echo "<meta http-equiv='refresh' content='0; url=index.php'>";
	exit;
};
//---
use function Actions\Html\make_mail_icon;
use function Actions\Html\make_project_to_user;
use function Actions\MdwikiSql\fetch_query;
use function SQLorAPI\Get\get_users_by_last_pupdate;
use function SQLorAPI\Get\get_td_or_sql_count_pages_not_empty;
use function SQLorAPI\Get\get_td_or_sql_page_user_not_in_users;
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
include_once __DIR__ . '/sugust.php';
//---
function make_edit_icon($id, $user, $email, $wiki2, $proj)
{
	//---
	$edit_params = array(
		'id'   => $id,
		'nonav'  => 1,
		'user'  => $user,
		'email'  => $email,
		'wiki'  => $wiki2,
		'project'  => $proj
	);
	//---
	$edit_url = "index.php?ty=Emails/edit_user&" . http_build_query($edit_params);
	//---
	$onclick = 'pupwindow1("' . $edit_url . '")';
	//---
	return <<<HTML
    	<a class='btn btn-outline-primary btn-sm' onclick='$onclick'>Edit</a>
    HTML;
}

function get_sorted_array()
{
	$users_done = array();
	//---
	$live_pages = get_td_or_sql_count_pages_not_empty();
	//---
	$ddi = fetch_query("select user_id, username, email, wiki, user_group from users;");
	//---
	foreach ($ddi as $Key => $gk) {
		$users_done[$gk['username']] = $gk;
	};
	//---
	$der = get_td_or_sql_page_user_not_in_users();
	//---
	foreach ($der as $d => $tat) if (!array_key_exists($tat, $users_done)) {
		$users_done[$tat] = array('user_id' => 0, 'username' => $tat, 'email' => '', 'wiki' => '', 'user_group' => '');
	}
	//---
	$sorted_array = array();
	//---
	foreach ($users_done as $u => $tab) {
		$tab['live'] = $live_pages[$u] ?? 0;
		$sorted_array[$u] = $tab;
	};
	//---
	// sort $sorted_array by live
	uasort($sorted_array, function ($a, $b) {
		return $b['live'] <=> $a['live']; // للترتيب تنازليًا، عكسها ($a['live'] <=> $b['live']) للترتيب تصاعديًا
	});
	//---
	return $sorted_array;
}
//---
function filter_table($project_name)
{
	//---
	global $projects_title_to_id;
	//---
	$projects_title_to_id["empty"] = "empty";
	//---
	$l_list = <<<HTML
		<option value='Uncategorized'>Uncategorized</option>
	HTML;
	//---
	foreach ($projects_title_to_id as $p_title => $p_id) {
		$cdcdc = $project_name == $p_title ? "selected" : "";
		$l_list .= <<<HTML
			<option data-tokens='$p_title' value='$p_title' $cdcdc>$p_title</option>
		HTML;
	};
	//---
	$uuu = <<<HTML
		<div class="input-group">
			<span class="input-group-text">Project:</span>
			<select aria-label="Project"
				dir="ltr"
				class="form-select options"
				id='project'
				name='project'
				placeholder=''
				data-live-search="true"
				data-container="body"
				data-live-search-style="begins"
				data-bs-theme="auto"
				data-style='btn active'
				data-width="90%">
				$l_list
			</select>
		</div>
	HTML;
	//---
	return $uuu;
}
//---
$numb = 0;
//---
$last_user_to_tab = get_users_by_last_pupdate();
//---
$users_done = get_sorted_array();
//---
$form_rows = '';
//---
$limit = (isset($_GET['limit'])) ? $_GET['limit'] : 0;
//---
$main_project = (isset($_GET['project'])) ? $_GET['project'] : '';
//---
$project_filter = filter_table($main_project);
//---
foreach ($users_done as $user_name => $table) {
	//---
	$table = $users_done[$user_name];
	//---
	$live		= $table['live'] ?? "";
	//---
	$user_group	= $table['user_group'] ?? "";
	//---
	$user_group2 = $user_group;
	// ---
	if ($user_group2 == '') {
		$user_group2 = 'empty';
	}
	//---
	if ($main_project != "" && $user_group2 != $main_project) {
		continue;
	}
	//---
	$numb += 1;
	//---
	if ($limit > 0 && $numb > $limit) break;
	//---
	$id			= $table['user_id'] ?? "";
	$email 		= $table['email'] ?? "";
	$wiki		= $table['wiki'] ?? "";
	$user 		= $table['username'] ?? "";
	//---
	$wiki2		= $wiki . "wiki";
	//---
	// $project_line = make_project_to_user($user_group);
	//---
	$mail_icon = '';
	//---
	if (array_key_exists($user_name, $last_user_to_tab)) {
		$mail_icon = make_mail_icon($last_user_to_tab[$user_name]);
	}
	//---
	$edit_icon = make_edit_icon($id, $user, $email, $wiki, $user_group);
	//---
	$form_rows .= <<<HTML
	<tr>
		<td data-order='$numb' data-content='#'>
			$numb
		</td>
		<td data-order='$user_name' data-content='User name'>
			<span><a href='/Translation_Dashboard/leaderboard.php?user=$user_name'>$user_name</a></span>
			<input name='username[]$numb' value='$user_name' hidden/>
			<input name='id[]$numb' value='$id' hidden/>
		</td>
		<td data-order='$email' data-search='$email' data-content='Email'>
			<input class='form-control' size='25' name='email[]$numb' value='$email' readonly/>
		</td>
		<td data-content=''>
			$mail_icon
		</td>
		<td data-order='$user_group' data-search='$user_group' data-content='Project'>
			<!-- <select name='project[]$numb' class='form-select options'>$project_line</select> -->
			<input class='form-control' size='25' name='project[]$numb' value='$user_group' readonly/>
		</td>
		<td data-order='$wiki' data-search='$wiki2' data-content='Wiki'>
			<input class='form-control' size='4' name='wiki[]$numb' value='$wiki' readonly/>
		</td>
		<td data-order='$live' data-content='Live'>
			<span>$live</span>
		</td>
		<td data-content='Edit'>
			<span>$edit_icon</span>
		</td>
	</tr>
	HTML;
	//---
};
//---
echo <<<HTML
	<div class='card-header'>
		<div class='row'>
			<div class='col-md-3'>
				<span class="card-title h4" style="font-weight:bold;">
					Emails: $numb
				</span>
			</div>
			<div class='col-md-9'>
				<form method='get' action='index.php'>
					<input name='ty' value='Emails' hidden/>
					<div class='row'>
						<div class='col-md-5'>
							$project_filter
						</div>
						<div class='aligncenter col-md-3'>
							<input class='btn btn-outline-primary' type='submit' value='Filter' />
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class='card-body'>
		<div class="form-group">
			<table id='em' class='table table-striped compact table-mobile-responsive'>
				<thead>
					<tr>
						<th>#</th>
						<th>Username</th>
						<th>Email</th>
						<th></th>
						<th>Project</th>
						<th>Wiki</th>
						<th>Live</th>
						<th>Edit</th>
						<!-- <th>Delete</th> -->
					</tr>
				</thead>
				<tbody>
					$form_rows
				</tbody>
			</table>
		</div>
	</div>
	</div>
HTML;
//---
echo <<<HTML
	<div class='card'>
		<div class='card-body'>
			<form action="index.php?ty=Emails" method="POST">
				<input name='ty' value="Emails" hidden />
				<div class="form-group" id='new_tab_add' style='display: none;'>
					<table class='table table-striped compact table-mobile-responsive'>
						<thead>
							<tr>
								<th>#</th>
								<th>Username</th>
								<th>Email</th>
								<th>Project</th>
								<th>Wiki</th>
							</tr>
						</thead>
						<tbody id="tab_ma">
						</tbody>
					</table>
				</div>
				<div class="form-group d-flex justify-content-between">
					<button id="submit_bt" type="submit" class="btn btn-outline-primary" style='display: none;'>Save</button>
					<span role='button' class="btn btn-outline-primary" onclick='add_row()'>New row</span>
					<span> </span>
				</div>
			</form>
		</div>
	</div>
HTML;
?>
<script type="text/javascript">
	function pupwindow(url) {
		window.open(url, 'popupWindow', 'width=850,height=550,scrollbars=yes');
	};

	function add_row() {
		$('#submit_bt').show();
		$('#new_tab_add').show();
		var ii = $('#tab_ma >tr').length + 1;

		var options = $('.options').html();
		// find if any element has attr selected and unselect it
		options = options.replace(/selected/g, '');
		// ---
		var e = `
			<tr>
				<td>${ii}</td>
				<td><input class='form-control' name='username[]${ii}'/></td>
				<td><input class='form-control' size='25' name='email[]${ii}'/></td>
				<td><select name='project[]${ii}' class='form-select'>${options}</select></td>
				<td><input class='form-control' size='4' name='wiki[]${ii}'/></td>
			</tr>
		`;
		// ---
		$('#tab_ma').append(e);
		// ---
	};

	$(document).ready(function() {
		var t = $('#em').DataTable({
			// order: [[5	, 'desc']],
			// paging: false,
			lengthMenu: [
				[50, 100, 150],
				[50, 100, 150]
			],
			// scrollY: 800
		});
	});
</script>

</div>
