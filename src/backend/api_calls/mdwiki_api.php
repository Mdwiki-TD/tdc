<?php

declare(strict_types=1);

/**
 * MDWiki API Communication Module
 * 
 * Provides functions for making HTTP POST requests to the MDWiki API.
 * This module handles all direct communication with mdwiki.org's MediaWiki
 * API endpoint for wiki operations.
 * 
 * Features:
 * - cURL-based HTTP POST requests
 * - Configurable timeouts
 * - User agent identification
 * - JSON response parsing
 * - Debug logging in test mode
 * 
 * API Endpoint: https://mdwiki.org/w/api.php
 * 
 * Usage Example:
 * ```php
 * use function APICalls\MdwikiApi\get_mdwiki_url_with_params;
 * 
 * // Get page content
 * $result = get_mdwiki_url_with_params([
 *     'action' => 'query',
 *     'prop' => 'revisions',
 *     'titles' => 'Page_Title',
 *     'rvprop' => 'content',
 *     'format' => 'json'
 * ]);
 * ```
 * 
 * @package    APICalls
 * @subpackage MdwikiApi
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 * 
 * @see https://www.mediawiki.org/wiki/API:Main_page
 * @see https://mdwiki.org/w/api.php
 */

namespace APICalls\MdwikiApi;

use function Utils\Functions\test_print;

/**
 * User agent string for API requests
 * 
 * Identifies the application to the MDWiki API per MediaWiki guidelines.
 * 
 * @var string
 */
const USER_AGENT = "WikiProjectMed Translation Dashboard/2.0 (https://mdwiki.toolforge.org/; tools.mdwiki@toolforge.org)";

/**
 * Default connection timeout in seconds
 * 
 * @var int
 */
const DEFAULT_CONNECT_TIMEOUT = 5;

/**
 * Default request timeout in seconds
 * 
 * @var int
 */
const DEFAULT_REQUEST_TIMEOUT = 5;

/**
 * MDWiki API endpoint URL
 * 
 * @var string
 */
const API_ENDPOINT = 'https://mdwiki.org/w/api.php';

/**
 * Execute a POST request to the MDWiki API
 * 
 * Sends an HTTP POST request to the specified endpoint with the given
 * parameters. Handles cURL configuration, execution, and error handling.
 * 
 * @param string              $endPoint The API endpoint URL
 * @param array<string,mixed> $params   Query parameters to send
 * 
 * @return string The raw response body, or empty string on error
 * 
 * @example
 * ```php
 * $response = post_url_mdwiki('https://mdwiki.org/w/api.php', [
 *     'action' => 'query',
 *     'meta' => 'siteinfo',
 *     'format' => 'json'
 * ]);
 * ```
 */
function post_url_mdwiki(string $endPoint, array $params = []): string
{
    $ch = curl_init();
    
    if ($ch === false) {
        test_print("post_url_mdwiki: Failed to initialize cURL");
        return '';
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $endPoint,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($params, '', '&', PHP_QUERY_RFC3986),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => USER_AGENT,
        CURLOPT_CONNECTTIMEOUT => DEFAULT_CONNECT_TIMEOUT,
        CURLOPT_TIMEOUT => DEFAULT_REQUEST_TIMEOUT,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
    ]);

    $output = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);

    // Build URL for debug logging
    $url = "{$endPoint}?" . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    $url2 = str_replace('&format=json', '', $url);
    $url2 = '<a target="_blank" href="' . htmlspecialchars($url2, ENT_QUOTES, 'UTF-8') . '">' 
           . htmlspecialchars($url2, ENT_QUOTES, 'UTF-8') . '</a>';

    if ($http_code !== 200) {
        test_print('post_url_mdwiki: Error: API request failed with status code ' . $http_code);
    }

    test_print("post_url_mdwiki: (http_code: $http_code) $url2");

    if ($output === false) {
        test_print("post_url_mdwiki: cURL Error: " . ($curl_error ?: 'Unknown error'));
    }

    if (curl_errno($ch)) {
        test_print('post_url_mdwiki: Error: ' . $curl_error);
    }

    curl_close($ch);

    return is_string($output) ? $output : '';
}

/**
 * Send a request to the MDWiki API and get parsed JSON response
 * 
 * Convenience function that sends a request to the MDWiki API endpoint
 * and returns the parsed JSON response as an associative array.
 * 
 * @param array<string,mixed> $params API request parameters
 * 
 * @return array<string,mixed> Parsed JSON response, empty array on error
 * 
 * @example
 * ```php
 * // Get page info
 * $result = get_mdwiki_url_with_params([
 *     'action' => 'query',
 *     'prop' => 'info',
 *     'titles' => 'Main Page',
 *     'format' => 'json'
 * ]);
 * 
 * if (!empty($result['query']['pages'])) {
 *     foreach ($result['query']['pages'] as $page) {
 *         echo $page['title'] . ': ' . $page['pageid'];
 *     }
 * }
 * ```
 */
function get_mdwiki_url_with_params(array $params): array
{
    // Ensure JSON format is requested
    $params['format'] = 'json';
    
    $out = post_url_mdwiki(API_ENDPOINT, $params);
    
    if (empty($out)) {
        return [];
    }

    $result = json_decode($out, true);

    if (!is_array($result)) {
        test_print("post_url_mdwiki: Failed to parse JSON response");
        return [];
    }

    return $result;
}

/**
 * Get page content from MDWiki
 * 
 * Convenience function to retrieve the wikitext content of a page.
 * 
 * @param string $title The page title
 * 
 * @return string|null The page content, or null if not found
 */
function get_page_content(string $title): ?string
{
    $result = get_mdwiki_url_with_params([
        'action' => 'query',
        'prop' => 'revisions',
        'titles' => $title,
        'rvprop' => 'content',
        'rvslots' => 'main',
    ]);

    if (!isset($result['query']['pages'])) {
        return null;
    }

    foreach ($result['query']['pages'] as $page) {
        if (isset($page['revisions'][0]['slots']['main']['*'])) {
            return $page['revisions'][0]['slots']['main']['*'];
        }
        if (isset($page['revisions'][0]['*'])) {
            return $page['revisions'][0]['*'];
        }
    }

    return null;
}

/**
 * Check if a page exists on MDWiki
 * 
 * @param string $title The page title to check
 * 
 * @return bool True if the page exists
 */
function page_exists(string $title): bool
{
    $result = get_mdwiki_url_with_params([
        'action' => 'query',
        'titles' => $title,
        'format' => 'json',
    ]);

    if (!isset($result['query']['pages'])) {
        return false;
    }

    foreach ($result['query']['pages'] as $pageId => $page) {
        // MediaWiki returns negative page ID for missing pages
        return !isset($page['missing']) && $pageId > 0;
    }

    return false;
}
