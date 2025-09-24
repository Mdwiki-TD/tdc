<?php
// Set the error reporting level
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the content type to JSON
header('Content-Type: application/json');

// Include the CSRF token and configuration files
include_once __DIR__ . '/csrf.php';
include_once __DIR__ . '/infos/td_config.php';

// Include all utility functions
foreach (glob(__DIR__ . "/utils/*.php") as $filename) {
    include_once $filename;
}

// Include all API call and SQL-related files
foreach (glob(__DIR__ . "/api_calls/*.php") as $filename) {
    include_once $filename;
}
foreach (glob(__DIR__ . "/api_or_sql/*.php") as $filename) {
    include_once $filename;
}

// Include all table definition files
foreach (glob(__DIR__ . "/tablesd/*.php") as $filename) {
    if (basename($filename) == 'langcode.php') continue;
    include_once $filename;
}
include_once __DIR__ . '/tablesd/langcode.php';

// ---
// Authentication-related files will be handled separately
// if (substr(__DIR__, 0, 2) == 'I:') {
//     include_once 'I:/mdwiki/auth_repo/oauth/user_infos.php';
// } else {
//     include_once __DIR__ . '/../auth/oauth/user_infos.php';
// }
// ---
