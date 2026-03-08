<?php
//---
use function APICalls\MdwikiSql\insert_to_projects;
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
