<?php
// src/app/coordinator/admin/projects/post.php

namespace App\Coordinator\Admin\Projects;

use function App\APICalls\MdwikiSql\insert_to_projects;
use function App\APICalls\MdwikiSql\execute_query;
use function App\Utils\Html\div_alert;
use function App\csrf\verify_csrf_token;
use App\User\CurrentUser;

if (!CurrentUser::getInstance()->isCoordinator()) {
	header('Location: /index.php');
	exit;
};

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
$texts = [];

foreach ($_POST['rows'] ?? [] as $key => $table) {
	// { "rows": { "1": { "g_id": "6", "g_title": "Benevity" } }
	// { "g_id": "5", "g_title": "Wiki", "del": "5" }

	$gId  	= $table['g_id'] ?? '';
	$del  	= $table['del'] ?? '';

	$gTitle  	= $table['g_title'] ?? '';

	if (!empty($del) && !empty($gId)) {
		$qua2 = "DELETE FROM projects WHERE g_id = ?";

		execute_query($qua2, $params = [$gId]);

		$texts[] = "Project $gTitle deleted.";

		continue;
	};

	$gTitle = trim($gTitle);

	if (empty($gTitle)) {
		continue;
	}

	insert_to_projects($gTitle, $gId);

	if (empty($gId)) {
		$texts[] = "Project $gTitle Added.";
	} else {
		$texts[] = "Project $gTitle Updated.";
	}

}

echo div_alert($texts, 'success');
