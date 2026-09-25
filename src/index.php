<?php

include_once __DIR__ . '/app/include_all.php';
include_once __DIR__ . '/templates/header.php';

use App\Templates\PageHeader;

$pageHeader = new PageHeader();
$pageHeader->render();

echo <<<HTML
	<script>$("#coord").addClass("active");</script>
HTML;

include_once __DIR__ . '/app/index.php';

$timeStart = $pageHeader->getLoadStartTime();

include_once __DIR__ . '/templates/footer.php';
