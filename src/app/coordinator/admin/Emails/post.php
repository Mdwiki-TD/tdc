<?php
// src/app/coordinator/admin/Emails/post.php

namespace App\Coordinator\Admin\Emails;

use App\User\CurrentUser;
use function App\Utils\Html\div_alert;
use function App\APICalls\MdwikiSql\sql_update_user;
use function App\APICalls\MdwikiSql\sql_add_user;
use function App\APICalls\MdwikiSql\check_one;
use function App\csrf\verify_csrf_token;

if (!CurrentUser::getInstance()->isCoordinator()) {
	header('Location: /index.php');
	exit;
};
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo '</div><script>
$("#mainnav").hide();
$("#maindiv").hide();
</script>';

$closeBtn = <<<HTML
	<div class="aligncenter">
		<a class="btn btn-outline-primary" onclick="window.close()">Close</a>
	</div>
HTML;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['emails'])) {
	exit;
}

if (!verify_csrf_token()) {
	echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
	echo $closeBtn;
	return;
}

$texts = [];
$errors = [];

$new_q = "INSERT INTO users (username, email, wiki, user_group) SELECT DISTINCT user, '', '', '' from pages
	WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = user)";

// '{ "ty": "Emails", "emails": { "1": { "username": "x", "email": "x", "project": "TWB/WikiMed (Arabic)", "wiki": "ar" } } }'

foreach ($_POST['emails'] as $key => $table) {
	// { "username": "", "email": "3", "project": "Uncategorized", "wiki": "" }

	$user    = $table['username'] ?? '';
	$email 	 = $table['email'] ?? '';
	$wiki 	 = $table['wiki'] ?? '';
	$project = $table['project'] ?? '';
	$userId = $table['user_id'] ?? '';

	if (empty($user)) {
		$errors[] = "Username is required.";
		continue;
	};

	$user = trim($user);
	$email = trim($email);

	// Validate email format if not empty
	if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		// Handle invalid email - either log, set to empty, or return error
		$errors[] = "Invalid Email format";
		$email = '';
	}

	$wiki      = trim($wiki);
	$project   = trim($project);

	$ttTab = check_one($select = "*", $where = "username", $value = $user, $table = "users");

	if ($ttTab) {
		$ttUsername = $ttTab['username'];
		$ttId = $ttTab['user_id'];

		if (!empty($userId) && $ttId != $userId) {
			$errors[] = "User:($user) already in database with user_id:($ttId).";
			continue;
		}

		if (empty($userId) && !empty($ttUsername)) {
			$errors[] = "User:($user) already in database with user_id:($ttId).";
			continue;
		}
	}

	if (empty($userId)) {
		sql_add_user($user, $email, $wiki, $project);
		$texts[] = "User:($user) added successfully.";
	} else {
		sql_update_user($user, $email, $wiki, $project, $userId);
		$texts[] = "User:($user) updated successfully.";
	}
}

echo div_alert($texts, 'success');
echo div_alert($errors, 'danger');

echo $closeBtn;
