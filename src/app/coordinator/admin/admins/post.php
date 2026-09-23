<?php
// src/app/coordinator/admin/admins/post.php

namespace App\Coordinator\Admin\Admins;

use function App\APICalls\MdwikiSql\execute_query;
use function App\Utils\Html\div_alert;
use function App\csrf\verify_csrf_token;
use App\User\CurrentUser;

if (!CurrentUser::getInstance()->isCoordinator()) {
	header('Location: /index.php');
	exit;
};
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	exit;
}

$closeBtn = <<<HTML
	<div class="aligncenter">
		<a class="btn btn-outline-primary" onclick="window.close()">Close</a>
	</div>
HTML;

if (!verify_csrf_token()) {
	echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
	echo $closeBtn;
	return;
}
$errors = [];
$texts = [];

$tableName = "coordinators";

foreach ($_POST['rows'] ?? [] as $key => $table) {
	// '{ "id": "11", "username": "Ifteebd10", "del": "11" }'
	// '{ "id": "11", "username": "Ifteebd10", "is_new": "yes" }'

	$uId  	= $table['id'] ?? '';
	$del  	= $table['del'] ?? '';

	$username  	= $table['username'] ?? '';

	if (!empty($del) && !empty($uId)) {
		$qua2 = "DELETE FROM $tableName WHERE id = ?";

		$result = execute_query($qua2, $params = [$uId]);

		if ($result === false) {
			$errors[] = "Failed to delete user $username.";
			continue;
		}

		$texts[] = "User $username deleted.";

		continue;
	};

	// $isNew = $table['is_new'] ?? '';

	$username = trim($username);

	$isActive = $table['is_active'] ?? '';
	$activeOrginalValue = $table['active_orginal_value'] ?? '';

	if ($isActive == $activeOrginalValue && !empty($uId)) {
		continue;
	};

	if (!empty($username)) { // && empty($uId) && $isNew == 'yes'

		// $qua = "INSERT INTO $tableName (username) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM $tableName WHERE username = ?)";

		$qua = <<<SQL
			INSERT INTO $tableName (username, is_active)
			VALUES (?, ?)
			ON DUPLICATE KEY UPDATE
				is_active = VALUES(is_active)
		SQL;

		$result = execute_query($qua, $params = [$username, $isActive]);

		if ($result === false) {
			$errors[] = "Failed to add user $username.";
		} else {
			$texts[] = (empty($uId)) ? "User $username Added." : "User $username Updated.";
		}
	};

}

echo div_alert($texts, 'success');
echo div_alert($errors, 'danger');

echo $closeBtn;
