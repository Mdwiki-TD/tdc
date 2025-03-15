<?PHP
//---
header('Content-Type: application/json');
//---
if (isset($_REQUEST['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
//---
include_once __DIR__ . '/../include.php';
//---
use function Results\GetCats\get_mdwiki_cat_members;
//---
$cat  = $_REQUEST['cat'] ?? 'RTT';
//---
$tab = get_mdwiki_cat_members($cat, false);
//---
sort($tab);
//---
$result = [
    'len' => count($tab),
    'cat' => $cat,
    'members' => $tab
];
//---
echo json_encode($result);
