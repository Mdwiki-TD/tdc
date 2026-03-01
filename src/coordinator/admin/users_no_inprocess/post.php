<?php
//---
use AdminPost\UserTableHandler;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
$handler = new UserTableHandler('users_no_inprocess');
$handler->handleRequest($_POST);
$handler->render();
