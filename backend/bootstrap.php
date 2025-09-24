<?php
// Set the error reporting level
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the content type to JSON
header('Content-Type: application/json');

// Include the autoloader
require_once __DIR__ . '/autoloader.php';

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

// Include all results files
foreach (glob(__DIR__ . "/results/*.php") as $filename) {
    include_once $filename;
}
