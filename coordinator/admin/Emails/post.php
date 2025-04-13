<?php
//---
use function Actions\MdwikiSql\sql_add_user;
use function Actions\MdwikiSql\execute_query;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
$new_q = "INSERT INTO users (username, email, wiki, user_group) SELECT DISTINCT user, '', '', '' from pages
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = user)";
//---
if (isset($_POST['emails'])) {
	// '{ "ty": "Emails", "emails": { "1": { "username": "x", "email": "x", "project": "TWB/WikiMed (Arabic)", "wiki": "ar" } } }'
	// ---
	foreach ($_POST['emails'] as $key => $table) {
		// { "username": "", "email": "3", "project": "Uncategorized", "wiki": "" }
		//---
		$user    = $table['username'] ?? '';
		$email 	 = $table['email'] ?? '';
		$wiki 	 = $table['wiki'] ?? '';
		$project = $table['project'] ?? '';
		//---
		if (!empty($user)) {
			//---
			// var_export(json_encode($table, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
			//---
			$user = trim($user);
			$email     = trim($email);
			$wiki      = trim($wiki);
			$project   = trim($project);
			//---
			sql_add_user($user, $email, $wiki, $project);
			//---
		};
	};
};
