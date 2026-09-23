<?php
// src/app/coordinator/admin/full_translators/post.php

namespace App\Coordinator\Admin\FullTranslators;

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

$tableName = "full_translators";

foreach ($_POST['rows'] ?? [] as $key => $table) {
	// { "id": "1", "user": "" }
	// { "id": "4", "user": "Dr3939", "del": "4" }

	$uId  	= $table['id'] ?? '';
	$del  	= $table['del'] ?? '';

	$user  	= $table['user'] ?? '';

	if (!empty($del) && !empty($uId)) {
		$qua2 = "DELETE FROM $tableName WHERE id = ?";

		$result = execute_query($qua2, $params = [$uId]);

		if ($result === false) {
			$errors[] = "Failed to delete user $user.";
			continue;
		}

		$texts[] = "User $user deleted.";

		continue;
	};

	// $isNew = $table['is_new'] ?? '';

	$user = trim($user);

	$isActive = $table['is_active'] ?? '';
	$activeOrginalValue = $table['active_orginal_value'] ?? '';

	if ($isActive == $activeOrginalValue && !empty($uId)) {
		continue;
	};

	if (!empty($user)) { // && empty($uId) && $isNew == 'yes'

		// $qua = "INSERT INTO $tableName (user) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM $tableName WHERE user = ?)";

		$qua = <<<SQL
			INSERT INTO $tableName (user, is_active)
			VALUES (?, ?)
			ON DUPLICATE KEY UPDATE
				is_active = VALUES(is_active)
		SQL;

		$result = execute_query($qua, $params = [$user, $isActive]);

		if ($result === false) {
			$errors[] = "Failed to add user $user.";
		} else {
			$texts[] = (empty($uId)) ? "User $user Added." : "User $user Updated.";
		}
	};

}

echo div_alert($texts, 'success');
echo div_alert($errors, 'danger');
