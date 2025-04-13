<?php
//---
use function Actions\MdwikiSql\insert_to_projects;
use function Actions\MdwikiSql\execute_query;
use function Actions\Html\div_alert; // echo div_alert($texts, 'success');
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
$texts = [];
//---
foreach ($_POST['rows'] ?? [] as $key => $table) {
	// { "rows": { "1": { "g_id": "6", "g_title": "Benevity" } }
	// { "g_id": "5", "g_title": "Wiki", "del": "5" }
	//---
	$g_id  	= $table['g_id'] ?? '';
	$del  	= $table['del'] ?? '';
	//---
	$g_title  	= $table['g_title'] ?? '';
	//---
	if (!empty($del) && !empty($g_id)) {
		$qua2 = "DELETE FROM projects WHERE g_id = ?";
		// ---
		execute_query($qua2, $params = [$g_id]);
		// ---
		$texts[] = "Project $g_title deleted.";
		// ---
		continue;
	};
	//---
	$g_title = trim($g_title);
	//---
	if (empty($g_title)) {
		continue;
	}
	//---
	insert_to_projects($g_title, $g_id);
	//---
	if (empty($g_id)) {
		$texts[] = "Project $g_title Added.";
	} else {
		$texts[] = "Project $g_title Updated.";
	}
	//---
}
// ---
echo div_alert($texts, 'success');
