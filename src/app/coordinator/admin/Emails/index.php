<?php
// src/app/coordinator/admin/Emails/index.php

namespace App\Coordinator\Admin\Emails;


use App\User\CurrentUser;

if (!CurrentUser::getInstance()->isCoordinator()) {
	header('Location: /index.php');
	exit;
};

use App\Tables\SqlTables\TablesSql;
use function App\Utils\Html\make_mail_icon_new;
use function App\Utils\Html\make_edit_icon_new;
use function App\APICalls\MdwikiSql\fetch_query;
use function App\SQLorAPI\Funcs\get_users_by_last_pupdate;
use function App\SQLorAPI\Funcs\get_td_or_sql_count_pages_not_empty;
use function App\SQLorAPI\Funcs\get_td_or_sql_page_user_not_in_users;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require __DIR__ . '/post.php';
}

function get_sorted_array()
{
	$usersDone = [];

	$livePages = get_td_or_sql_count_pages_not_empty();

	$ddi = fetch_query("select user_id, username, email, wiki, user_group from users;");

	foreach ($ddi as $Key => $gk) {
		$usersDone[$gk['username']] = $gk;
	};

	$der = get_td_or_sql_page_user_not_in_users();

	foreach ($der as $d => $tat) if (!array_key_exists($tat, $usersDone)) {
		$usersDone[$tat] = array('user_id' => 0, 'username' => $tat, 'email' => '', 'wiki' => '', 'user_group' => '');
	}

	$sortedArray = [];

	foreach ($usersDone as $u => $tab) {
		$tab['live'] = $livePages[$u] ?? 0;
		$sortedArray[$u] = $tab;
	};

	// sort $sortedArray by live
	uasort($sortedArray, function ($a, $b) {
		return $b['live'] <=> $a['live']; // للترتيب تنازليًا، عكسها ($a['live'] <=> $b['live']) للترتيب تصاعديًا
	});

	return $sortedArray;
}

function emails_filter_table($projectName)
{

	TablesSql::$sProjectsTitleToId["empty"] = "empty";

	$lList = <<<HTML
		<option data-tokens='all' value='All'>All</option>
	HTML;

	foreach (TablesSql::$sProjectsTitleToId as $pTitle => $pId) {
		if (empty($pTitle)) continue;
		$cdcdc = $projectName == $pTitle ? "selected" : "";
		$lList .= <<<HTML
			<option data-tokens='$pTitle' value='$pTitle' $cdcdc>$pTitle</option>
		HTML;
	};

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
				$lList
			</select>
		</div>
	HTML;

	return $uuu;
}

$numb = 0;

$lastUserToTab = get_users_by_last_pupdate();

$usersDone = get_sorted_array();

$formRows = '';

$limit = (isset($_GET['limit'])) ? $_GET['limit'] : 0;

$mainProject = (isset($_GET['project'])) ? $_GET['project'] : 'All';

// if ($mainProject == 'empty') { $mainProject = 'Uncategorized'; }

$projectFilter = emails_filter_table($mainProject);

foreach ($usersDone as $userName => $table) {

	$table = $usersDone[$userName];

	$live		= $table['live'] ?? "";

	$userGroup	= $table['user_group'] ?? "";

	$userGroup2 = $userGroup;

	if (empty($userGroup2)) $userGroup2 = 'Uncategorized';

	if (!empty($mainProject) && $mainProject != "All" && $userGroup2 != $mainProject) {
		continue;
	}

	$numb += 1;

	if ($limit > 0 && $numb > $limit) break;

	$userId    = $table['user_id'] ?? "";
	$email 		= $table['email'] ?? "";
	$wiki		= $table['wiki'] ?? "";
	$user 		= $table['username'] ?? "";

	$mailIcon = '';

	if (array_key_exists($userName, $lastUserToTab)) {
		$mailIcon = make_mail_icon_new($lastUserToTab[$userName], 'pup_window_email');
	}

	$editParams = array(
		'user_id'   => $userId,
		'user'  => $user,
		'email'  => $email,
		'wiki'  => $wiki,
		'project'  => $userGroup
	);

	$editIcon = make_edit_icon_new("Emails/edit_user", $editParams);

	$formRows .= <<<HTML
	<tr>
		<td data-order='$numb' data-content='#'>
			$numb
		</td>
		<td data-order='$userName' data-content='User name'>
			<span><a href='/Translation_Dashboard/leaderboard.php?user=$userName'>$userName</a></span>
		</td>
		<td data-order='$email' data-search='$email' data-content='Email'>
			$email
		</td>
		<td data-content='Send Email'>
			$mailIcon
		</td>
		<td data-order='$userGroup2' data-search='$userGroup2' data-content='Project'>
			$userGroup2
		</td>
		<td data-order='$wiki' data-search='$wiki' data-content='Wiki'>
			$wiki
		</td>
		<td data-order='$live' data-content='Live'>
			<span>$live</span>
		</td>
		<td data-content='Edit'>
			<span>$editIcon</span>
		</td>
	</tr>
	HTML;

};

echo <<<HTML
	<div class='card'>
		<div class='card-header'>
			<div class='row'>
				<div class='col-md-3'>
					<span class="card-title h4" style="font-weight:bold;">
						Users: $numb
					</span>
				</div>
				<div class='col-md-9'>
					<form method='get' action='index.php'>
						<input name='ty' value='Emails' type='hidden'/>
						<div class='row'>
							<div class='col-md-5'>
								$projectFilter
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
				<table id='em' class='table table-striped compact table-mobile-responsive table-mobile-sided table_text_left'>
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
						</tr>
					</thead>
					<tbody>
						$formRows
					</tbody>
				</table>
			</div>
		</div>
	</div>
HTML;

$newRow = make_edit_icon_new("Emails/edit_user", ["new" => 1], $text = "Add one!");

echo <<<HTML
	<div class='card mt-1'>
		<div class='card-body'>
			$newRow
		</div>
	</div>
HTML;

$hoste = ($_SERVER["SERVER_NAME"] == "localhost")
	? "https://cdnjs.cloudflare.com"
	: "https://tools-static.wmflabs.org/cdnjs";

echo <<<HTML
	<link href="$hoste/ajax/libs/datatables.net-buttons-dt/3.2.5/buttons.dataTables.min.css" rel='stylesheet'/>
	<script src="$hoste/ajax/libs/datatables-buttons/3.2.5/js/dataTables.buttons.min.js"></script>
	<script src="$hoste/ajax/libs/datatables.net-buttons-dt/3.2.5/buttons.dataTables.js"></script>
	<script src="$hoste/ajax/libs/datatables-buttons/3.2.5/js/buttons.html5.js"></script>
HTML;

?>
<script type="text/javascript">
	$(document).ready(function() {
		var t = $('#em').DataTable({
			layout: {
				topStart: {
					buttons: ['copy', 'csv']
				}
			},
			stateSave: true,
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
