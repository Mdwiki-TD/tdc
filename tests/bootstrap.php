<?php

declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set test environment
putenv('APP_ENV=testing');

require_once dirname(__DIR__) . '/src/app/bootstrap.php';

$vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}
