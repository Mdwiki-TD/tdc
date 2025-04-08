<?php
//---
use function Actions\MdwikiSql\execute_query;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
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
		execute_query($qua2, $params = [$u_id]);
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
		execute_query($qua, $params = [$user, $user]);
	};
	//---
}
// ---
if (!empty($texts)) {
	echo "<div class='container mt-3'><div class='alert alert-success' role='alert'>";
	foreach ($texts as $text) {
		echo "$text<br>";
	}
	echo "</div></div>";
}
