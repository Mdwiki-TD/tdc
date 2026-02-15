<?php

declare(strict_types=1);

/**
 * Configuration Management Module
 * 
 * Provides centralized configuration management for the Translation Dashboard.
 * Handles loading and saving configuration from INI and JSON files stored
 * in a secure configuration directory.
 * 
 * Features:
 * - INI file parsing for sensitive credentials
 * - JSON file handling for application settings
 * - Environment-aware configuration directory resolution
 * - Type-safe configuration retrieval
 * 
 * Configuration Directory Structure:
 * ```
 * ~/confs/
 * ├── db.ini           # Database credentials
 * ├── OAuthConfig.ini  # OAuth configuration
 * └── settings.json    # Application settings
 * ```
 * 
 * Security Considerations:
 * - Configuration directory should be outside web root
 * - Files should have restricted permissions (600/640)
 * - Never commit configuration files with real credentials
 * 
 * Usage Example:
 * ```php
 * use function Infos\TdConfig\Read_ini_file;
 * use function Infos\TdConfig\get_configs;
 * use function Infos\TdConfig\set_configs;
 * 
 * // Read INI configuration
 * $oauth = Read_ini_file('OAuthConfig.ini');
 * 
 * // Get JSON configuration
 * $settings = get_configs('settings.json');
 * 
 * // Update a setting
 * set_configs('settings.json', 'theme', 'dark');
 * ```
 * 
 * @package    Infos
 * @subpackage TdConfig
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

namespace Infos\TdConfig;

/**
 * Enable debug mode in development
 */
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

/**
 * Get the configuration directory path
 * 
 * Resolves the configuration directory based on environment:
 * 1. TDC_CONFIG_DIR environment variable (highest priority)
 * 2. HOME environment variable + /confs
 * 3. Development fallback
 * 
 * @return string Absolute path to configuration directory
 */
function get_config_dir(): string
{
    // Check for explicit configuration directory
    $configDir = getenv('TDC_CONFIG_DIR');
    if ($configDir !== false && is_dir($configDir)) {
        return rtrim($configDir, '/\\') . DIRECTORY_SEPARATOR;
    }
    
    // Use HOME directory
    $home = getenv("HOME");
    if ($home !== false && !empty($home)) {
        return rtrim($home, '/\\') . '/confs/';
    }
    
    // Development fallback
    return 'I:/mdwiki/mdwiki/confs/';
}

/**
 * Global configuration directory path
 * 
 * @var string
 */
$_dir = get_config_dir();

/**
 * Read and parse an INI configuration file
 * 
 * Loads an INI file from the configuration directory and returns
 * its contents as an associative array.
 * 
 * @param string $file The INI filename (relative to config directory)
 * 
 * @return array<string,mixed>|false Parsed INI data, or false on failure
 * 
 * @example
 * ```php
 * $oauth = Read_ini_file('OAuthConfig.ini');
 * if ($oauth !== false) {
 *     $clientId = $oauth['client_id'] ?? '';
 * }
 * ```
 */
function Read_ini_file(string $file): array|false
{
    global $_dir;
    
    $filepath = $_dir . $file;
    
    if (!file_exists($filepath)) {
        error_log("Configuration file not found: {$filepath}");
        return false;
    }
    
    return parse_ini_file($filepath);
}

/**
 * Read a JSON configuration file
 * 
 * Loads a JSON file from the configuration directory and returns
 * its decoded contents. Creates an empty file if it doesn't exist.
 * 
 * @param string $fileo The JSON filename (relative to config directory)
 * 
 * @return array<string,mixed> Decoded JSON data, empty array on failure
 * 
 * @example
 * ```php
 * $settings = get_configs('app_settings.json');
 * $theme = $settings['theme'] ?? 'light';
 * ```
 */
function get_configs(string $fileo): array
{
    global $_dir;
    
    $file = $_dir . $fileo;
    
    // Create empty file if it doesn't exist
    if (!is_file($file)) {
        file_put_contents($file, '{}');
    }
    
    $content = file_get_contents($file);
    
    if ($content === false) {
        error_log("Failed to read configuration file: {$file}");
        return [];
    }
    
    $data = json_decode($content, true);
    
    if (!is_array($data)) {
        return [];
    }
    
    return $data;
}

/**
 * Update a single key in a JSON configuration file
 * 
 * Reads the JSON file, updates the specified key, and saves back.
 * 
 * @param string $file  The JSON filename (relative to config directory)
 * @param string $key   The key to update
 * @param mixed  $value The value to set
 * 
 * @return bool True on success, false on failure
 * 
 * @example
 * ```php
 * set_configs('user_preferences.json', 'language', 'ar');
 * ```
 */
function set_configs(string $file, string $key, mixed $value): bool
{
    global $_dir;
    
    $filepath = $_dir . $file;
    
    $content = file_get_contents($filepath);
    if ($content === false) {
        error_log("Failed to read configuration file: {$filepath}");
        return false;
    }
    
    $data = json_decode($content, true);
    if (!is_array($data)) {
        $data = [];
    }
    
    $data[$key] = $value;
    
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($encoded === false) {
        error_log("Failed to encode configuration data");
        return false;
    }
    
    return file_put_contents($filepath, $encoded) !== false;
}

/**
 * Replace entire contents of a JSON configuration file
 * 
 * Writes the provided data to the JSON file, replacing all existing content.
 * 
 * @param string              $file    The JSON filename (relative to config directory)
 * @param array<string,mixed> $content The data to write
 * 
 * @return bool True on success, false on failure
 * 
 * @example
 * ```php
 * set_configs_all_file('cache_data.json', [
 *     'last_update' => time(),
 *     'items' => $items
 * ]);
 * ```
 */
function set_configs_all_file(string $file, array $content): bool
{
    global $_dir;
    
    $filepath = $_dir . $file;
    
    $encoded = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($encoded === false) {
        error_log("Failed to encode configuration data");
        return false;
    }
    
    return file_put_contents($filepath, $encoded) !== false;
}

/**
 * Check if a configuration file exists
 * 
 * @param string $file The filename to check
 * 
 * @return bool True if file exists
 */
function config_exists(string $file): bool
{
    global $_dir;
    return is_file($_dir . $file);
}

/**
 * Delete a configuration file
 * 
 * @param string $file The filename to delete
 * 
 * @return bool True on success
 */
function config_delete(string $file): bool
{
    global $_dir;
    $filepath = $_dir . $file;
    
    if (is_file($filepath)) {
        return unlink($filepath);
    }
    
    return true;
}
