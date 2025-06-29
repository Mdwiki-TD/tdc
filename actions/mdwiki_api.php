<?php

namespace Actions\MdwikiApi;
/*
Usage:
use function Actions\MdwikiApi\get_mdwiki_url_with_params;
*/

use function Actions\Functions\test_print;

function post_url_mdwiki(string $endPoint, array $params = []): string
{
    $usr_agent = "WikiProjectMed Translation Dashboard/1.0 (https://mdwiki.toolforge.org/; tools.mdwiki@toolforge.org)";

    $ch = curl_init();
    // ---
    curl_setopt_array($ch, [
        CURLOPT_URL => $endPoint,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => $usr_agent,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 5,
        // لاستخدام ملفات الكوكيز عند الحاجة:
        // CURLOPT_COOKIEJAR => "cookie.txt",
        // CURLOPT_COOKIEFILE => "cookie.txt",
    ]);
    // ---
    $output = curl_exec($ch);
    // ---
    $url = "{$endPoint}?" . http_build_query($params);
    // ---
    // remove "&format=json" from $url then make it link <a href="$url2">
    $url2 = str_replace('&format=json', '', $url);
    $url2 = "<a target='_blank' href='$url2'>$url2</a>";
    //---
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //---
    if ($http_code !== 200) {
        test_print('post_url_mdwiki: Error: API request failed with status code ' . $http_code);
    }
    //---
    test_print("post_url_mdwiki: (http_code: $http_code) $url2");
    // ---
    if ($output === FALSE) {
        test_print("post_url_mdwiki: cURL Error: " . curl_error($ch));
    }

    if (curl_errno($ch)) {
        test_print('post_url_mdwiki: Error:' . curl_error($ch));
    }


    curl_close($ch);
    return $output;
}

function get_mdwiki_url_with_params(array $params): array
{
    $endPoint = 'https://mdwiki.org/w/api.php';
    //---
    $out = post_url_mdwiki($endPoint, $params);
    //---
    $result = json_decode($out, true);
    //---
    if (!is_array($result)) {
        $result = [];
    }
    //---
    return $result;
}
