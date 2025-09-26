<?php
//---
use function APICalls\MdwikiSql\execute_query;
use function Utils\Html\div_alert; // echo div_alert($texts, 'success');
use function TDWIKI\csrf\verify_csrf_token;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
if (verify_csrf_token()) {
	$errors = [];
	$texts = [];
	//---
	$table_name = "users_no_inprocess";
	//---
	foreach ($_POST['rows'] ?? [] as $key => $table) {
		// { "id": "1", "user": "" }
		// { "id": "4", "user": "Dr3939", "del": "4" }
		//---
		$u_id  	= $table['id'] ?? '';
		$del  	= $table['del'] ?? '';
		//---
		$user  	= $table['user'] ?? '';
		//---
		if (!empty($del) && !empty($u_id)) {
			$qua2 = "DELETE FROM $table_name WHERE id = ?";
			// ---
			$result = execute_query($qua2, $params = [$u_id]);
			// ---
			if ($result === false) {
				$errors[] = "Failed to delete user $user.";
				continue;
			}
			// ---
			$texts[] = "User $user deleted.";
			// ---
			continue;
		};
		//---
		// $is_new = $table['is_new'] ?? '';
		//---
		$user = trim($user);
		//---
		$active = $table['active'] ?? '';
		$active_orginal_value = $table['active_orginal_value'] ?? '';
		//---
		if ($active == $active_orginal_value && !empty($u_id)) {
			continue;
		};
		//---
		if (!empty($user)) { // && empty($u_id) && $is_new == 'yes'
			//---
			// $qua = "INSERT INTO $table_name (user) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM $table_name WHERE user = ?)";
			//---
			$qua = <<<SQL
				INSERT INTO $table_name (user, active)
				VALUES (?, ?)
				ON DUPLICATE KEY UPDATE
					active = VALUES(active)
			SQL;
			//---
			$result = execute_query($qua, $params = [$user, $active]);
			//---
			if ($result === false) {
				$errors[] = "Failed to add user $user.";
			} else {
				$texts[] = (empty($u_id)) ? "User $user Added." : "User $user Updated.";
			}
		};
		//---
	}
	// ---
	echo div_alert($texts, 'success');
	echo div_alert($errors, 'danger');
}
