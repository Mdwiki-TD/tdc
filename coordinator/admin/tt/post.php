<?php
//---
use function Actions\MdwikiSql\insert_to_translate_type;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
foreach ($_POST['rows'] ?? [] as $key => $table) {
	// '{ "ty": "tt/post", "rows": { "1": { "add": "", "title": "111111111111", "lead": "100000", "full": "10000" } } }'
	// ---
	$title 	= $table['title'] ?? '';
	$lead 	= $table['lead'] ?? 0;
	$full 	= $table['full'] ?? 0;
	//---
	if (empty($title)) continue;
	//---
	insert_to_translate_type($title, $lead, $full);
}
//---
$cat = $_GET['cat'] ?? '';
//---
echo <<<HTML
	<div class='alert alert-success' role='alert'>Translate Type Saved...<br>
		return to Translate Type page in 2 seconds
	</div>
	<meta http-equiv='refresh' content='2; url=index.php?ty=tt&cat=$cat'>
HTML;
//---
