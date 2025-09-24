<?php
//---
use function Utils\Html\div_alert; // echo div_alert($texts, 'success');
use function APICalls\MdwikiSql\sql_update_user;
use function APICalls\MdwikiSql\sql_add_user;
use function APICalls\MdwikiSql\check_one;
use function TDWIKI\csrf\verify_csrf_token;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
echo '</div><script>
$("#mainnav").hide();
$("#maindiv").hide();
</script>';
// ---
if (isset($_POST['emails']) && verify_csrf_token()) {
	$texts = [];
	$errors = [];
	//---
	$new_q = "INSERT INTO users (username, email, wiki, user_group) SELECT DISTINCT user, '', '', '' from pages
		WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = user)";
	//---
	// '{ "ty": "Emails", "emails": { "1": { "username": "x", "email": "x", "project": "TWB/WikiMed (Arabic)", "wiki": "ar" } } }'
	// ---
	foreach ($_POST['emails'] as $key => $table) {
		// { "username": "", "email": "3", "project": "Uncategorized", "wiki": "" }
		//---
		$user    = $table['username'] ?? '';
		$email 	 = $table['email'] ?? '';
		$wiki 	 = $table['wiki'] ?? '';
		$project = $table['project'] ?? '';
		$user_id = $table['user_id'] ?? '';
		//---
		if (empty($user)) {
			$errors[] = "Username is required.";
			continue;
		};
		//---
		$user = trim($user);
		$email = trim($email);
		//---
		// Validate email format if not empty
		if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// Handle invalid email - either log, set to empty, or return error
			$errors[] = "Invalid Email format";
			$email = '';
		}
		//---
		$wiki      = trim($wiki);
		$project   = trim($project);
		//---
		$tt_tab = check_one($select = "*", $where = "username", $value = $user, $table = "users");
		// ---
		if ($tt_tab) {
			$tt_username = $tt_tab['username'];
			$tt_id = $tt_tab['user_id'];
			// ---
			if (!empty($user_id) && $tt_id != $user_id) {
				$errors[] = "User:($user) already in database with user_id:($tt_id).";
				continue;
			}
			// ---
			if (empty($user_id) && !empty($tt_username)) {
				$errors[] = "User:($user) already in database with user_id:($tt_id).";
				continue;
			}
		}
		// ---
		if (empty($user_id)) {
			sql_add_user($user, $email, $wiki, $project);
			$texts[] = "User:($user) added successfully.";
		} else {
			sql_update_user($user, $email, $wiki, $project, $user_id);
			$texts[] = "User:($user) updated successfully.";
		}
	}
	//---
	echo div_alert($texts, 'success');
	echo div_alert($errors, 'danger');
}
echo <<<HTML
	<div class="aligncenter">
		<a class="btn btn-outline-primary" onclick="window.close()">Close</a>
	</div>
HTML;
