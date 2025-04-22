<?php
//---
use function Actions\MdwikiSql\execute_query;
use function Actions\Html\div_alert; // echo div_alert($texts, 'success');
use function TDWIKI\csrf\verify_csrf_token;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
if (verify_csrf_token()) {
	$errors = [];
	$texts = [];
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
			$qua2 = "DELETE FROM full_translators WHERE id = ?";
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
		$is_new = $table['is_new'] ?? '';
		//---
		$user = trim($user);
		//---
		if (!empty($user) && empty($u_id) && $is_new == 'yes') {
			$qua = "INSERT INTO full_translators (user) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM full_translators WHERE user = ?)";
			//---
			$texts[] = "User $user Added.";
			//---
			$result = execute_query($qua, $params = [$user, $user]);
			//---
			if ($result === false) {
				$errors[] = "Failed to add user $user.";
			}
		};
	}
	// ---
	echo div_alert($texts, 'success');
	echo div_alert($errors, 'danger');
}
