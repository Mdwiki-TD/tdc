<?php
//---
require_once __DIR__ . '/../../../backend/api_calls/AdminPostHandler.php';
//---
use AdminPost\ProjectsHandler;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
$handler = new ProjectsHandler();
$handler->handleRequest($_POST);
$handler->render();
