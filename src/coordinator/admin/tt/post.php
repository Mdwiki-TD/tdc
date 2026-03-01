<?php
//---
use AdminPost\TranslateTypeHandler;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
$handler = new TranslateTypeHandler();
$handler->handleRequest($_POST);
$handler->render();
