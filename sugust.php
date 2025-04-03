<?php
//---
header('Content-Type: application/json');
//---
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
//---
include_once __DIR__ . '/include.php';
include_once __DIR__ . '/coordinator/admin/Emails/sugust.php';
//---
use function Emails\Sugust\get_sugust;
//---
$title  = $_REQUEST['title'] ?? '';
$lang  = $_REQUEST['lang'] ?? '';
//---
$tab = get_sugust($title, $lang);
//---
echo json_encode($tab);
