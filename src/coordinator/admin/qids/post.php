<?php
//---
use AdminPost\QidsHandler;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
$qid_table = $_GET["qid_table"] ?? '';
//---
$handler = new QidsHandler($qid_table);
$handler->handleRequest($_POST);
$handler->render();
