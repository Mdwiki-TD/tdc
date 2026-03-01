<?php
//---
use AdminPost\ProjectsHandler;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
$handler = new ProjectsHandler();
$handler->handleRequest($_POST);
$handler->render();
