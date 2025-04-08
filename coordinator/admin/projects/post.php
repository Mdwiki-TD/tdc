<?php
//---
use function Actions\MdwikiSql\insert_to_projects;
use function Actions\MdwikiSql\execute_query;
//---
if (isset($_POST['del'])) {
	// ---
	for($i = 0; $i < count($_POST['del']); $i++ ) {
		$del	= $_POST['del'][$i];
		//---
		if (!empty($del)) {
			$qua2 = "DELETE FROM projects WHERE g_id = ?";
			execute_query($qua2, $params=[$del]);
		};
	};
};
//---
if (isset($_POST['g_title'])) {
	//---
	// var_dump(json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
	//---
	for($i = 0; $i < count($_POST['g_title']); $i++ ) {
		//---
		$g_title	= $_POST['g_title'][$i];
		//---
		if (empty($g_title)) continue;
		//---
		$g_id  		= $_POST['g_id'][$i] ?? "";
		//---
		insert_to_projects($g_title, $g_id);
	};
};
//---
?>
