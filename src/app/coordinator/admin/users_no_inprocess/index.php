<?php
// src/app/coordinator/admin/users_no_inprocess/index.php

namespace App\Coordinator\Admin\UsersNoInprocess;

use App\User\CurrentUser;

if (!CurrentUser::getInstance()->isCoordinator()) {
	header('Location: /index.php');
	exit;
};

use function App\SQLorAPI\Funcs\get_td_or_sql_users_no_inprocess;
use function App\csrf\generate_csrf_token;



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require __DIR__ . '/post.php';
}

$qq = get_td_or_sql_users_no_inprocess();

$numb = 0;

$formText = '';

foreach ($qq as $Key => $table) {
	$numb += 1;

	$userId = $table['id'] ?? "";
	$usere	 = $table['user'] ?? "";
	$isActive	 = $table['is_active'] ?? "";

	$activeChecked = ($isActive == 1 || $isActive == "1") ? 'checked' : '';

	$formText .= <<<HTML
		<tr>
			<td data-content="id">
				<input class="form-control" size="20" name="rows[$numb][id]" value="$userId" type="hidden"/>
				<span><b>$userId</b></span>
			</td>
			<td data-content="user">
				<span><a href='/Translation_Dashboard/leaderboard.php?user=$usere'>$usere</a></span>
				<input name='rows[$numb][user]' value='$usere' type='hidden'/>
			</td>
			<td data-content="Active" data-order='$isActive'>
				<div class='form-check form-switch'>
					<input type='hidden' name='rows[$numb][active_orginal_value]' value='$isActive'>
					<input type='hidden' name='rows[$numb][is_active]' value='0'>
					<input class='form-check-input' type='checkbox' name='rows[$numb][is_active]' value='1' $activeChecked>
				</div>
			</td>
			<td data-content="delete">
				<input type='checkbox' name='rows[$numb][del]' value='$userId'/> <label> delete</label>
			</td>
		</tr>
	HTML;
};

$numb += 1;

$formTextPlus = <<<HTML
	<tr>
		<td data-content="id">
			<span><b>Add:</b></span>
		</td>
		<td data-content="User">
			<input class='form-control' name='rows[$numb][is_new]' value='yes' type='hidden'/>
			<input class='form-control td_user_input' name='rows[$numb][user]' />
		</td>
		<td data-content="Active">
			<div class="form-check form-switch">
				<input type="hidden" name="rows[$numb][is_active]" value="1">
				-
			</div>
		</td>
		<td data-content="delete">
			-
		</td>
	</tr>
HTML;

$csrfToken = generate_csrf_token(); // <input name='csrf_token' value="$csrfToken" type="hidden"/>

$tyName = "users_no_inprocess";

echo <<<HTML
    <div class='card'>
		<div class='card-header'>
			<h4>Users Not to be added to "in process" table:</h4>
		</div>
		<div class='card-body'>
			<form action="index.php?ty=$tyName" method="POST">
				<input name='csrf_token' value="$csrfToken" type="hidden"/>
				<input name='ty' value="$tyName" type="hidden"/>
				<div class="row">
					<div class="col-md-6 col-sm-12">
						<table class='table table-striped compact table-mobile-responsive table-mobile-sided table_text_left'>
							<thead>
								<tr>
									<th>ID</th>
									<th>User</th>
									<th>Active</th>
									<th>Delete</th>
								</tr>
							</thead>
							<tbody id="full_tab">
								$formText
								$formTextPlus
							</tbody>
						</table>
					</div>
				</div>
				<div class="form-group d-flex justify-content-between">
					<button type="submit" class="btn btn-outline-primary">Save</button>
					<!-- <span role='button' id="add_row" class="btn btn-outline-primary" onclick='add_row_v()'>New row</span> -->
				</div>
			</form>
		</div>
	</div>
HTML;
?>
<script type="text/javascript">
	// $(document).ready(function() {

	function add_row_v() {
		var ii = $('#full_tab >tr').length + 1;

		var e = `
			<tr>
				<td>
					<b>${ii}</b>
				</td>
				<td>
					<input class='form-control' name='rows[${ii}][is_new]' value='yes' type='hidden'/>
					<input class='form-control' name='rows[${ii}][is_active]' value='1' type='hidden'/>
					<input class='form-control td_user_input' name='rows[${ii}][user]'/>
				</td>
				<td>-</td>
			</tr>
		`;

		$('#full_tab').append(e);
	};
	// });
</script>
</div>
