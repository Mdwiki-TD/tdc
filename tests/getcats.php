<?php
// This is an adapted test script for the command line.
// Usage: php tests/getcats.php <category>

if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

// Set error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Bootstrap the application
require_once __DIR__ . '/../backend/bootstrap.php';

use function Results\GetCats\get_mdwiki_cat_members;

// Get category from command line arguments
$cat = $argv[1] ?? 'RTT';

echo "Testing with category: $cat\n";

$members = get_mdwiki_cat_members($cat, false);
sort($members);

$result = [
    'category' => $cat,
    'member_count' => count($members),
    'members' => $members
];

echo "Test Result:\n";
print_r($result);
