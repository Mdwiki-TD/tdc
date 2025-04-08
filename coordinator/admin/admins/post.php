<?php
//---
use function Actions\MdwikiSql\execute_query;
//---
var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
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
		execute_query($qua2, $params = [$u_id]);
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
		execute_query($qua, $params = [$user, $user]);
	};
	//---
}
