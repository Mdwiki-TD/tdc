<?php
//---
use AdminPost\CampaignsHandler;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
$handler = new CampaignsHandler();
$handler->handleRequest($_POST);
$handler->render();
