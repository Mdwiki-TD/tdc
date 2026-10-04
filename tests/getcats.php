<?php

header('Content-Type: application/json');

include_once __DIR__ . '/../bootstrap.php';

use App\Results27\GetCats;

$cat  = $_REQUEST['cat'] ?? 'RTT';

$tab = GetCats::get_mdwiki_cat_members($cat, false);

sort($tab);

$result = [
    'len' => count($tab),
    'cat' => $cat,
    'members' => $tab
];

echo json_encode($result);
