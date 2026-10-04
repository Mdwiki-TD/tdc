<?php

header('Content-Type: application/json');

include_once __DIR__ . '/bootstrap.php';

use App\Coordinator\Helps\Sugust;

$title  = $_GET['title'] ?? $_POST['title'] ?? '';
$lang  = $_GET['lang'] ?? $_POST['lang'] ?? '';

$tab = Sugust::get_sugust($title, $lang);

echo json_encode($tab);
