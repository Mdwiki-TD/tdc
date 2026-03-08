<?php

/**
 * Application Bootstrap and Include Module
 *
 * This file serves as the central bootstrap for the Translation Dashboard
 * application. It handles environment setup and includes all necessary
 * dependencies in the correct order.
 *
 * Include Order:
 * 1. CSRF protection module
 * 2. Configuration settings
 * 3. Utility functions (glob-loaded)
 * 4. OAuth authentication (environment-specific path)
 * 5. API call modules (glob-loaded)
 * 6. API/SQL abstraction layer (glob-loaded)
 * 7. Database table definitions (glob-loaded, special handling for langcode)
 * 8. Results processing modules
 * 9. Coordinator tools helpers
 *
 * Environment Setup:
 * - Sets HOME environment variable for configuration file discovery
 * - Handles Windows development environment (I:/mdwiki/mdwiki)
 * - Production uses Toolforge standard HOME path
 *
 * Usage:
 * ```php
 * // Include at the start of any entry point
 * include_once __DIR__ . '/include.php';
 * ```
 *
 * @package    Core
 * @subpackage Bootstrap
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

$vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';

if (!file_exists($vendorAutoload)) {
    $vendorAutoload = dirname(dirname(__DIR__)) . '/vendor/autoload.php';
}

if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
} else {
    die("Vendor autoload not found. Please run 'composer install' in the project root.");
}

$env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'development');

if ($env === 'development' && file_exists(__DIR__ . '/load_env.php')) {
    include_once __DIR__ . '/load_env.php';
}

// Load security module first
include_once __DIR__ . '/csrf.php';

include_once __DIR__ . '/utils/functions.php';
include_once __DIR__ . '/utils/html_side1.php';
include_once __DIR__ . '/utils/html.php';
include_once __DIR__ . '/utils/tables_dir.php';

include_once __DIR__ . '/backend/api_calls/mdwiki_api.php';
include_once __DIR__ . '/backend/api_calls/mdwiki_sql.php';
include_once __DIR__ . '/backend/api_calls/td_api.php';
include_once __DIR__ . '/backend/api_calls/wiki_api.php';

include_once __DIR__ . '/userinfos_wrap.php';

include_once __DIR__ . '/backend/api_or_sql/funcs.php';
include_once __DIR__ . '/backend/api_or_sql/index.php';
include_once __DIR__ . '/backend/api_or_sql/process_data.php';
include_once __DIR__ . '/backend/api_or_sql/recent_data.php';

include_once __DIR__ . '/backend/tables/sql_tables.php';
include_once __DIR__ . '/backend/tables/tables.php';

include_once __DIR__ . '/backend/tables/langcode.php';

include_once __DIR__ . '/results/get_results.php';
include_once __DIR__ . '/results/getcats.php';

require_once __DIR__ . '/coordinator/tools/recent_helps.php';
