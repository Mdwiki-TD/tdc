<?php

header('Content-Type: application/json');

include_once __DIR__ . '/../include.php';

use function Results\GetCats\get_mdwiki_cat_members;

$cat  = $_REQUEST['cat'] ?? 'RTT';

$tab = get_mdwiki_cat_members($cat, false);

sort($tab);

$result = [
    'len' => count($tab),
    'cat' => $cat,
    'members' => $tab
];

echo json_encode($result);
