<?php
//---
use function Actions\MdwikiSql\execute_query;
use function Actions\Html\div_alert; // echo div_alert($texts, 'success');
use function TDWIKI\csrf\verify_csrf_token;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
if (verify_csrf_token()) {
	$texts = [];
	$errors = [];
	//---
	foreach ($_POST['rows'] ?? [] as $key => $table) {
		// '{ "id": "11", "user": "Ifteebd10", "del": "11" }'
		// '{ "id": "11", "user": "Ifteebd10", "is_new": "yes" }'
		//---
		$u_id  	= $table['id'] ?? '';
		$del  	= $table['del'] ?? '';
		//---
		if (!empty($del)) {
			$qua2 = "DELETE FROM coordinator WHERE id = ?";
			// ---
			$result = execute_query($qua2, $params = [$u_id]);
			// ---
			if ($result === false) {
				$errors[] = "Failed to delete admin with ID $u_id.";
			} else {
				$texts[] = "Admin with ID $u_id deleted successfully.";
			}
			// ---
			continue;
		};
		//---
		$user  	= $table['user'] ?? '';
		$is_new = $table['is_new'] ?? '';
		//---
		$user = trim($user);
		//---
		if (!empty($user) && empty($u_id) && $is_new == 'yes') {
			$qua = "INSERT INTO coordinator (user) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM coordinator WHERE user = ?)";
			//---
			$result = execute_query($qua, $params = [$user, $user]);
			//---
			if ($result === false) {
				$errors[] = "Failed to add admin user $user.";
			} else {
				$texts[] = "Admin user $user added successfully.";
			}
		};
		//---
	}
	echo div_alert($texts, 'success');
	echo div_alert($errors, 'danger');
}
