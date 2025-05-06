<?php
//---
use function Actions\Html\div_alert; // echo div_alert($texts, 'success');
use function Actions\MdwikiSql\insert_to_translate_type;
use function TDWIKI\csrf\verify_csrf_token;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
$cat = $_GET['cat'] ?? '';
//---
if (verify_csrf_token()) {
	$texts = [];
	$errors = [];
	//---
	foreach ($_POST['rows'] ?? [] as $key => $table) {
		// '{ "ty": "tt/post", "rows": { "1": { "add": "", "title": "111111111111", "lead": "100000", "full": "10000" } } }'
		// ---
		$title 	= trim($table['title'] ?? '');
		$lead 	= $table['lead'] ?? 0;
		$full 	= $table['full'] ?? 0;
		$id  	= $table['id'] ?? "";
		//---
		if (empty($title)) {
			$errors[] = "Title is required.";
			continue;
		}
		//---
		$result = insert_to_translate_type($title, $lead, $full, $tt_id = $id);
		//---
		if ($result === false) {
			$errors[] = "Failed to add translate type, title: $title.";
		} else {
			$texts[] = "Translate type added successfully, title: $title.";
		}
	}
	//---
	echo div_alert($texts, 'success');
	echo div_alert($errors, 'danger');
};
echo <<<HTML
	<div class="aligncenter">
		<a class="btn btn-outline-primary" onclick="window.close()">Close</a>
	</div>
HTML;
