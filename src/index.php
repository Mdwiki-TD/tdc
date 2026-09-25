<?php
// src/index.php

use App\Templates\PageHeader;
use App\Templates\PageFooter;
use App\AppRouter;

include_once __DIR__ . '/app/include_all.php';
include_once __DIR__ . '/app/index.php'; // AppRouter

require_once __DIR__ . '/templates/PageHead.php';
include_once __DIR__ . '/templates/PageHeader.php';
include_once __DIR__ . '/templates/PageFooter.php';

$pageHeader = new PageHeader();
$pageHeader->render();

// Instantiate and execute application router
$router = new AppRouter();
$router->handleRequest();

$timeStart = $pageHeader->getLoadStartTime();

$pageFooter = new PageFooter($timeStart);
$pageFooter->render();
