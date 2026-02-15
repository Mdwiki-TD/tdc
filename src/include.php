<?php

declare(strict_types=1);

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

/**
 * Ensure HOME environment variable is set
 * 
 * On Windows development systems, HOME may not be set.
 * This ensures configuration files can be located consistently.
 * 
 * @return void
 */
function ensure_home_environment(): void
{
    $home = getenv("HOME");
    
    if ($home === false || $home === '') {
        // Check for environment variable override
        $configDir = getenv('TDC_CONFIG_DIR');
        $new_home = ($configDir !== false && is_dir($configDir)) 
            ? $configDir 
            : 'I:/mdwiki/mdwiki';
        
        putenv("HOME=$new_home");
        $_ENV['HOME'] = $new_home;
    }
}

// Initialize environment
ensure_home_environment();

// Load security module first
include_once __DIR__ . '/csrf.php';

// Load configuration
include_once __DIR__ . '/backend/infos/td_config.php';

/**
 * Load all PHP files from a directory using glob
 * 
 * @param string $pattern Glob pattern for files to include
 * 
 * @return void
 */
function load_module_files(string $pattern): void
{
    foreach (glob($pattern) as $filename) {
        if (is_file($filename)) {
            include_once $filename;
        }
    }
}

// Load utility functions
load_module_files(__DIR__ . "/utils/*.php");

// Load OAuth authentication (environment-specific path)
$homeDir = getenv("HOME") ?: 'I:/mdwiki/mdwiki';
if (str_starts_with(__DIR__, 'I:')) {
    // Windows development environment
    $oauthPath = 'I:/mdwiki/auth_repo/oauth/user_infos.php';
} else {
    // Production environment
    $oauthPath = dirname(__DIR__) . '/auth/oauth/user_infos.php';
}

if (file_exists($oauthPath)) {
    include_once $oauthPath;
}

// Load API call modules
load_module_files(__DIR__ . "/backend/api_calls/*.php");

// Load API/SQL abstraction layer
load_module_files(__DIR__ . "/backend/api_or_sql/*.php");

// Load database table definitions
// Note: langcode.php is loaded last as it may depend on other tables
foreach (glob(__DIR__ . "/backend/tables/*.php") as $filename) {
    if (basename($filename) === 'langcode.php') {
        continue;
    }
    include_once $filename;
}

// Load langcode last
$langcodeFile = __DIR__ . '/backend/tables/langcode.php';
if (file_exists($langcodeFile)) {
    include_once $langcodeFile;
}

// Load results processing modules
load_module_files(__DIR__ . "/results/*.php");

// Load coordinator tools helpers
require_once __DIR__ . '/coordinator/tools/recent_helps.php';
