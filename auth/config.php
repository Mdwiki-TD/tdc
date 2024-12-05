<?php
//---
include_once __DIR__ . '/../vendor_load.php';
//---
use Defuse\Crypto\Key;
//---
// get the root path from __FILE__ , split before public_html
// split the file path on the public_html directory
$pathParts = explode('public_html', __FILE__);
// the root path is the first part of the split file path
$ROOT_PATH = $pathParts[0];
//---
// if root path find (I:\) then $ROOT_PATH = ""
if (strpos($ROOT_PATH, "I:\\") !== false) {
    $ROOT_PATH = "I:/mdwiki/mdwiki/";
}
//---
$inifile = $ROOT_PATH . '/confs/OAuthConfig.ini';
//---
$ini = parse_ini_file($inifile);
//---
if ($ini === false) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "The ini file:($inifile) could not be read";
    exit(0);
}
if (
    !isset($ini['agent']) ||
    !isset($ini['consumerKey']) ||
    !isset($ini['consumerSecret'])
) {
    header("HTTP/1.1 500 Internal Server Error");
    echo 'Required configuration directives not found in ini file';
    exit(0);
}

$domain = $_SERVER['SERVER_NAME'] ?? 'localhost';

$cookie_key     = $ini['cookie_key'] ?? '';
$cookie_key = Key::loadFromAsciiSafeString($cookie_key);
