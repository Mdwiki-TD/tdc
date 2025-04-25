<?php
//---
require_once __DIR__ . '/add_post.php';
//---
use function Actions\Html\div_alert; // echo div_alert($texts, 'success');
use function TDWIKI\csrf\verify_csrf_token;
use function Add\AddPost\add_pages_to_db;

//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
if (verify_csrf_token()) {
	$texts = [];
	$errors = [];
	//---
	foreach ($_POST['rows'] ?? [] as $key => $table) {
		// { "id": "1", "camp": "Main", "cat1": "RTT", "cat2": "", "dep": "1" }
		//---
		$mdtitle	= $table['mdtitle'] ?? '';
		$cat		= rawurldecode($table['cat']) ?? '';
		$type		= $table['type'] ?? '';
		$user		= rawurldecode($table['user']) ?? '';
		$lang		= $table['lang'] ?? '';
		$target		= $table['target'] ?? '';
		$pupdate	= $table['pupdate'] ?? '';
		$word     	= $table['word'] ?? '';
		//---
		if (!empty($mdtitle) && !empty($lang) && !empty($user)) { // && !empty($target)
			//---
			$result = add_pages_to_db($mdtitle, $type, $cat, $lang, $user, $target, $pupdate, $word);
			//---
			if ($result === false) {
				$errors[] = "Failed to add translations.";
			} else {
				$texts[] = "Translations added successfully.";
			}
			// ---
		} else {
			$errors[] = "Failed to add translations. Missing required fields.";
		}
	}
	// ---
	echo div_alert($texts, 'success');
	echo div_alert($errors, 'danger');
}
