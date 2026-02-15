<?php

declare(strict_types=1);

/**
 * Translation Dashboard API Communication Module
 * 
 * Provides functions for communicating with the Translation Dashboard's
 * internal API service. This API provides aggregated data and statistics
 * from the translation database.
 * 
 * Features:
 * - Environment-aware endpoint selection (localhost vs production)
 * - cURL-based HTTP GET requests
 * - JSON response parsing with error handling
 * - Debug comparison utilities
 * 
 * API Endpoints:
 * - Production: https://mdwiki.toolforge.org/api.php
 * - Development: http://localhost:9001/api.php
 * 
 * Usage Example:
 * ```php
 * use function APICalls\TDApi\get_td_api;
 * 
 * // Get translation categories
 * $categories = get_td_api(['get' => 'categories']);
 * 
 * // Get user statistics
 * $stats = get_td_api(['get' => 'users_by_last_pupdate']);
 * ```
 * 
 * @package    APICalls
 * @subpackage TDApi
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

namespace APICalls\TDApi;

/**
 * User agent string for API requests
 * 
 * @var string
 */
const USER_AGENT = "WikiProjectMed Translation Dashboard/2.0 (https://mdwiki.toolforge.org/; tools.mdwiki@toolforge.org)";

/**
 * Connection timeout in seconds
 * 
 * @var int
 */
const CONNECT_TIMEOUT = 10;

/**
 * Request timeout in seconds
 * 
 * @var int
 */
const REQUEST_TIMEOUT = 10;

/**
 * Print debug information when test mode is enabled
 * 
 * Internal debug function for this module.
 * 
 * @param mixed $s Value to print
 * 
 * @return void
 */
function test_print_o(mixed $s): void
{
    if (isset($_COOKIE['test']) && $_COOKIE['test'] === 'x') {
        return;
    }
    
    $print_t = (isset($_REQUEST['test']) || isset($_COOKIE['test']));

    if ($print_t && is_string($s)) {
        echo "\n<br>\n" . htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    } elseif ($print_t) {
        echo "\n<br>\n<pre>";
        print_r($s);
        echo "</pre>";
    }
}

/**
 * Compare two data structures for debugging
 * 
 * Outputs a side-by-side comparison of two arrays/objects in JSON format.
 * Useful for comparing API responses with database query results.
 * 
 * @param mixed $t1 First data structure to compare
 * @param mixed $t2 Second data structure to compare
 * 
 * @return void Output is printed directly
 * 
 * @example
 * ```php
 * // Compare API response with database query
 * $api_result = get_td_api(['get' => 'categories']);
 * $db_result = fetch_query("SELECT * FROM categories");
 * compare_it($api_result, $db_result);
 * ```
 */
function compare_it(mixed $t1, mixed $t2): void
{
    echo "<br>fetch _query:<br>";
    var_dump(json_encode($t1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "<br>get_td_api:<br>";
    var_dump(json_encode($t2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Execute a GET request to the Translation Dashboard API
 * 
 * Sends an HTTP GET request to the TD API endpoint with the given
 * parameters. Automatically selects the correct endpoint based on
 * the current server environment.
 * 
 * @param string              $endPoint The API endpoint URL
 * @param array<string,mixed> $params   Query parameters
 * 
 * @return string The raw response body
 * 
 * @example
 * ```php
 * $response = post_url('https://mdwiki.toolforge.org/api.php', ['get' => 'settings']);
 * ```
 */
function post_url(string $endPoint, array $params = []): string
{
    $ch = curl_init();
    
    if ($ch === false) {
        test_print_o("post_url: Failed to initialize cURL");
        return '';
    }

    $url = $endPoint . "?" . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => USER_AGENT,
        CURLOPT_CONNECTTIMEOUT => CONNECT_TIMEOUT,
        CURLOPT_TIMEOUT => REQUEST_TIMEOUT,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
    ]);

    $output = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);

    // Build clickable URL for debug output
    $url2 = str_replace('&format=json', '', $url);
    $url2 = '<a target="_blank" href="' . htmlspecialchars($url2, ENT_QUOTES, 'UTF-8') . '">' 
           . htmlspecialchars($url2, ENT_QUOTES, 'UTF-8') . '</a>';

    if ($http_code !== 200) {
        test_print_o('post_url: Error: API request failed with status code ' . $http_code);
    }

    test_print_o("post_url: (http_code: $http_code) $url2");

    if ($output === false) {
        test_print_o("post_url: cURL Error: " . ($curl_error ?: 'Unknown error'));
    }

    if (curl_errno($ch)) {
        test_print_o('post_url: Error: ' . $curl_error);
    }

    curl_close($ch);

    return is_string($output) ? $output : '';
}

/**
 * Get the appropriate API endpoint URL based on environment
 * 
 * @return string The API endpoint URL
 */
function get_api_endpoint(): string
{
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    $base_url = ($server_name === 'localhost') 
        ? 'http://localhost:9001' 
        : 'https://mdwiki.toolforge.org';
    
    return $base_url . '/api.php';
}

/**
 * Query the Translation Dashboard API and return parsed results
 * 
 * Sends a request to the TD API and returns the 'results' portion
 * of the response. Handles JSON parsing and error conditions.
 * 
 * @param array<string,mixed> $params API request parameters
 * 
 * @return array<string,mixed> Parsed results array, empty on error
 * 
 * @example
 * ```php
 * // Get coordinator list
 * $coordinators = get_td_api(['get' => 'coordinator']);
 * 
 * // Get user's in-process translations
 * $process = get_td_api([
 *     'get' => 'in_process',
 *     'user' => 'username'
 * ]);
 * 
 * // Get categories with campaign info
 * $categories = get_td_api(['get' => 'categories']);
 * ```
 */
function get_td_api(array $params): array
{
    $endPoint = get_api_endpoint();
    $out = post_url($endPoint, $params);

    if (empty($out)) {
        return [];
    }

    $results = json_decode($out, true);

    if (!is_array($results)) {
        test_print_o('get_td_api: Failed to parse JSON response');
        return [];
    }

    $result = $results['results'] ?? [];

    if (isset($result['error'])) {
        test_print_o('Error: ' . json_encode($result['error'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return [];
    }

    return $result;
}
