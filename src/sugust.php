<?php

header('Content-Type: application/json');

include_once __DIR__ . '/include.php';
include_once __DIR__ . '/coordinator/admin/Emails/sugust.php';

use function Emails\Sugust\get_sugust;

$title  = $_GET['title'] ?? $_POST['title'] ?? '';
$lang  = $_GET['lang'] ?? $_POST['lang'] ?? '';

$tab = get_sugust($title, $lang);

echo json_encode($tab);
