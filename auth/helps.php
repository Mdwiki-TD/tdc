<?php

namespace OAuth\Helps;
/*
Usage:
use function OAuth\Helps\get_from_cookie;
use function OAuth\Helps\decode_value;
use function OAuth\Helps\encode_value;
*/

include_once __DIR__ . '/vendor_load.php';
include_once __DIR__ . '/config.php';

use Defuse\Crypto\Crypto;

function decode_value($value)
{
    global $cookie_key;
    try {
        $value = Crypto::decrypt($value, $cookie_key);
    } catch (\Exception $e) {
        $value = $value;
    }
    return $value;
}

function encode_value($value)
{
    global $cookie_key;
    try {
        $value = Crypto::encrypt($value, $cookie_key);
    } catch (\Exception $e) {
        $value = $value;
    };
    return $value;
}

function get_from_cookie($key)
{
    if (isset($_COOKIE[$key])) {
        $value = decode_value($_COOKIE[$key]);
    } else {
        // echo "key: $key<br>";
        $value = "";
    };
    if ($key == "username") {
        $value = str_replace("+", " ", $value);
    };
    return $value;
}
