<?php

declare(strict_types=1);

/**
 * Wikipedia API Communication Module
 * 
 * Provides functions for interacting with Wikimedia/Wikipedia APIs,
 * including pageviews statistics and general API queries.
 * 
 * Features:
 * - cURL-based HTTP requests
 * - Pageview statistics retrieval from Wikimedia Pageviews API
 * - HTML link generation for pageview display
 * - Configurable date ranges for statistics
 * 
 * API Endpoints:
 * - Pageviews: https://wikimedia.org/api/rest_v1/metrics/pageviews/
 * - Pageviews UI: https://pageviews.wmcloud.org/
 * 
 * Usage Example:
 * ```php
 * use function APICalls\WikiApi\get_views;
 * use function APICalls\WikiApi\make_view_by_number;
 * 
 * // Get total views for a page
 * $views = get_views('Article_Title', 'en', '2024-01-01');
 * 
 * // Generate clickable view count link
 * $link = make_view_by_number('Article_Title', 1000, 'en', '2024-01-01');
 * ```
 * 
 * @package    APICalls
 * @subpackage WikiApi
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 * 
 * @see https://wikitech.wikimedia.org/wiki/Analytics/AQS/Pageviews
 * @see https://pageviews.wmcloud.org/
 */

namespace APICalls\WikiApi;

/**
 * User agent string for API requests
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
 * Default start date for pageview queries
 * 
 * @var string
 */
const DEFAULT_START_DATE = '2019-01-01';

/**
 * End date for pageview queries (far future)
 * 
 * @var string
 */
const QUERY_END_DATE = '20300101';

/**
 * Execute a GET request to any URL
 * 
 * Generic cURL wrapper for making HTTP GET requests.
 * 
 * @param string $url The full URL to request
 * 
 * @return string The response body, or empty string on error
 * 
 * @example
 * ```php
 * $content = get_url_result_curl('https://example.com/api/data');
 * ```
 */
function get_url_result_curl(string $url): string
{
    $ch = curl_init($url);
    
    if ($ch === false) {
        echo "<br>cURL Error: Failed to initialize<br>" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        return '';
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => USER_AGENT,
        CURLOPT_CONNECTTIMEOUT => DEFAULT_CONNECT_TIMEOUT,
        CURLOPT_TIMEOUT => DEFAULT_REQUEST_TIMEOUT,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
    ]);

    $output = curl_exec($ch);
    $curl_error = curl_error($ch);

    if ($output === false) {
        echo "<br>cURL Error: " . htmlspecialchars($curl_error ?: 'Unknown error', ENT_QUOTES, 'UTF-8') 
           . "<br>" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }

    curl_close($ch);

    return is_string($output) ? $output : '';
}

/**
 * Generate an HTML link for displaying pageview counts
 * 
 * Creates a clickable link to the Pageviews tool with the view count
 * as the link text. If the count is zero or unknown, the link includes
 * a data attribute for lazy-loading via JavaScript.
 * 
 * @param string      $target  The page title on Wikipedia
 * @param int|string  $numb    The view count (0 or '?' for unknown)
 * @param string      $lang    Wikipedia language code (e.g., 'en', 'ar')
 * @param string|null $pupdate Publication date (YYYY-MM-DD format)
 * 
 * @return string HTML anchor element with pageview link
 * 
 * @example
 * ```php
 * // With known view count
 * echo make_view_by_number('Medicine', 15420, 'en', '2023-06-15');
 * // Output: <a target='_blank' href='...'>15,420</a>
 * 
 * // With unknown count (will be loaded via JS)
 * echo make_view_by_number('Medicine', 0, 'en', '2023-06-15');
 * // Output: <a target='_blank' name='toget' data-json-url='...' href='...'>?</a>
 * ```
 */
function make_view_by_number(string $target, int|string $numb, string $lang, ?string $pupdate): string
{
    $target = trim($target);
    $numb2 = (!empty($numb)) ? $numb : "?";
    $start = !empty($pupdate) ? $pupdate : DEFAULT_START_DATE;
    $end = date("Y-m-d", strtotime("yesterday"));

    // Build Pageviews UI URL
    $url = 'https://pageviews.wmcloud.org/?' . http_build_query([
        'project' => "{$lang}.wikipedia.org",
        'platform' => 'all-access',
        'agent' => 'all-agents',
        'start' => $start,
        'end' => $end,
        'redirects' => '0',
        'pages' => $target,
    ], '', '&', PHP_QUERY_RFC3986);

    // Format number for display
    $numb3 = (is_numeric($numb2)) ? number_format((int)$numb2) : $numb2;
    
    // Build HTML link
    $escaped_url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $escaped_numb = htmlspecialchars((string)$numb3, ENT_QUOTES, 'UTF-8');
    $link = "<a target='_blank' href='{$escaped_url}'>{$escaped_numb}</a>";

    // Return simple link if we have valid count
    if (is_numeric($numb2) && (int)$numb2 > 0) {
        return $link;
    }

    // For unknown counts, add data attribute for lazy loading
    $start2 = !empty($pupdate) ? str_replace('-', '', $pupdate) : '20190101';
    $api_url = 'https://wikimedia.org/api/rest_v1/metrics/pageviews/per-article/' 
             . rawurlencode($lang) . '.wikipedia/all-access/all-agents/' 
             . rawurlencode($target) . '/daily/' . $start2 . '/' . QUERY_END_DATE;

    $escaped_api_url = htmlspecialchars($api_url, ENT_QUOTES, 'UTF-8');
    
    return "<a target='_blank' name='toget' data-json-url='{$escaped_api_url}' href='{$escaped_url}'>{$escaped_numb}</a>";
}

/**
 * Get total pageviews for a Wikipedia article
 * 
 * Queries the Wikimedia Pageviews API to get the total number of views
 * for a specific article from the given start date to present.
 * 
 * @param string      $target  The page title on Wikipedia
 * @param string      $lang    Wikipedia language code (e.g., 'en', 'ar')
 * @param string|null $pupdate Start date (YYYY-MM-DD format)
 * 
 * @return int Total view count, 0 on error or no data
 * 
 * @example
 * ```php
 * // Get views for English Wikipedia article since 2023
 * $views = get_views('COVID-19_pandemic', 'en', '2023-01-01');
 * echo "Total views: " . number_format($views);
 * ```
 */
function get_views(string $target, string $lang, ?string $pupdate): int
{
    if (empty($target)) {
        return 0;
    }

    $start2 = !empty($pupdate) ? str_replace('-', '', $pupdate) : '20190101';
    
    $url = 'https://wikimedia.org/api/rest_v1/metrics/pageviews/per-article/' 
         . rawurlencode($lang) . '.wikipedia/all-access/all-agents/' 
         . rawurlencode($target) . '/daily/' . $start2 . '/' . QUERY_END_DATE;

    $output = get_url_result_curl($url);

    if (empty($output)) {
        return 0;
    }

    $result = json_decode($output, true);

    if (!is_array($result)) {
        return 0;
    }

    // Sum all daily view counts
    if (isset($result['items']) && is_array($result['items'])) {
        return array_sum(array_column($result['items'], 'views'));
    }

    return 0;
}
