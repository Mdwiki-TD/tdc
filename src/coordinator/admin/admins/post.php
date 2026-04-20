<?php
//---
use function APICalls\MdwikiSql\execute_query;
use function Utils\Html\div_alert;
use function TDWIKI\csrf\verify_csrf_token;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	exit;
}

$close_btn = <<<HTML
	<div class="aligncenter">
		<a class="btn btn-outline-primary" onclick="window.close()">Close</a>
	</div>
HTML;

if (!verify_csrf_token()) {
	echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
	echo $close_btn;
	return;
}
$errors = [];
$texts = [];
//---
$table_name = "coordinators";
//---
foreach ($_POST['rows'] ?? [] as $key => $table) {
	// '{ "id": "11", "username": "Ifteebd10", "del": "11" }'
	// '{ "id": "11", "username": "Ifteebd10", "is_new": "yes" }'
	//---
	$u_id  	= $table['id'] ?? '';
	$del  	= $table['del'] ?? '';
	//---
	$username  	= $table['username'] ?? '';
	//---
	if (!empty($del) && !empty($u_id)) {
		$qua2 = "DELETE FROM $table_name WHERE id = ?";
		// ---
		$result = execute_query($qua2, $params = [$u_id]);
		// ---
		if ($result === false) {
			$errors[] = "Failed to delete user $username.";
			continue;
		}
		// ---
		$texts[] = "User $username deleted.";
		// ---
		continue;
	};
	//---
	// $is_new = $table['is_new'] ?? '';
	//---
	$username = trim($username);
	//---
	$is_active = $table['is_active'] ?? '';
	$active_orginal_value = $table['active_orginal_value'] ?? '';
	//---
	if ($is_active == $active_orginal_value && !empty($u_id)) {
		continue;
	};
	//---
	if (!empty($username)) { // && empty($u_id) && $is_new == 'yes'
		//---
		// $qua = "INSERT INTO $table_name (username) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM $table_name WHERE username = ?)";
		//---
		$qua = <<<SQL
			INSERT INTO $table_name (username, is_active)
			VALUES (?, ?)
			ON DUPLICATE KEY UPDATE
				is_active = VALUES(is_active)
		SQL;
		//---
		$result = execute_query($qua, $params = [$username, $is_active]);
		//---
		if ($result === false) {
			$errors[] = "Failed to add user $username.";
		} else {
			$texts[] = (empty($u_id)) ? "User $username Added." : "User $username Updated.";
		}
	};
	//---
}
// ---
echo div_alert($texts, 'success');
echo div_alert($errors, 'danger');

echo $close_btn;
