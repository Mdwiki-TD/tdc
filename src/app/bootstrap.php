<?php

/**
 * Application Bootstrap and Include Module
 */

// Enable debug mode via request or cookie
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Configure secure session settings
ini_set('session.use_strict_mode', '1');

// don't use App\Settings here, Instance is not created yet
$env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'development');

if ($env === 'development' && file_exists(__DIR__ . '/load_env.php')) {
    include_once __DIR__ . '/load_env.php';
}

$vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';

if (!file_exists($vendorAutoload)) {
    $vendorAutoload = dirname(dirname(__DIR__)) . '/vendor/autoload.php';
}

if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
} else {
    die("Vendor autoload not found. Please run 'composer install' in the project root.");
}

include_once __DIR__ . '/autoload.php';

# Results27
include_once __DIR__ . '/Results27/GetResults.php';
include_once __DIR__ . '/Results27/GetCats.php';

# ApiClients
include_once __DIR__ . '/ApiClients/MdwikiApi.php';

# Coordinator
require_once __DIR__ . '/Coordinator/Helpers/RecentHelps.php';
include_once __DIR__ . '/Coordinator/Helpers/Sugust.php';
