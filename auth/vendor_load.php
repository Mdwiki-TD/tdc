<?php
/*
usage:

include_once __DIR__ . '/vendor_load.php';
*/

if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
};
require __DIR__ . '/../../auth/vendor/autoload.php'; // TD

// include_once(__DIR__ . '/../../vendor/autoload.php');
