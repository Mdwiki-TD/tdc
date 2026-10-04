<?php

namespace App\ApiClients;

use App\Logger;

class MdwikiApi
{
    public const USER_AGENT = "WikiProjectMed Translation Dashboard/1.0 (https://mdwiki.toolforge.org/; tools.mdwiki@toolforge.org)";
    public const API_ENDPOINT = 'https://mdwiki.org/w/api.php';

    public static function post_url_mdwiki(string $endPoint, array $params = []): string
    {
        $ch = curl_init();

        if ($ch === false) {
            Logger::debug("post_url_mdwiki: Failed to initialize cURL");
            return '';
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $endPoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params, '', '&', PHP_QUERY_RFC3986),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => self::USER_AGENT,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 5,
        ]);

        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        $url = "{$endPoint}?" . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $url2 = str_replace('&format=json', '', $url);
        $url2 = '<a target="_blank" href="' . htmlspecialchars($url2, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($url2, ENT_QUOTES, 'UTF-8') . '</a>';

        if ($httpCode !== 200) {
            Logger::debug('post_url_mdwiki: Error: API request failed with status code ' . $httpCode);
        }

        Logger::debug("post_url_mdwiki: (http_code: $httpCode) $url2");

        if ($output === false) {
            Logger::debug("post_url_mdwiki: cURL Error: " . ($curlError ?: 'Unknown error'));
        }

        if (curl_errno($ch)) {
            Logger::debug('post_url_mdwiki: Error: ' . $curlError);
        }

        return $output === false ? '' : $output;
    }

    public static function get_mdwiki_url_with_params(array $params): array
    {
        $params['format'] = 'json';

        $out = self::post_url_mdwiki(self::API_ENDPOINT, $params);

        if (empty($out)) {
            return [];
        }

        $result = json_decode($out, true);

        if (!is_array($result)) {
            Logger::debug("post_url_mdwiki: Failed to parse JSON response");
            return [];
        }

        return $result;
    }

    public static function get_page_content(string $title): ?string
    {
        $result = self::get_mdwiki_url_with_params([
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

    public static function page_exists(string $title): bool
    {
        $result = self::get_mdwiki_url_with_params([
            'action' => 'query',
            'titles' => $title,
            'format' => 'json',
        ]);

        if (!isset($result['query']['pages'])) {
            return false;
        }

        foreach ($result['query']['pages'] as $pageId => $page) {
            return !isset($page['missing']) && $pageId > 0;
        }

        return false;
    }
}
