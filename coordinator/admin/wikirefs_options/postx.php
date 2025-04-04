<?php

use function Infos\TdConfig\get_configs;
use function Infos\TdConfig\set_configs_all_file;

// Enable error reporting if requested
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Load configurations from file
$keysToAdd = ['move_dots', 'expend', 'add_en_lng'];
$tabes = get_configs('fixwikirefs.json');

if (isset($_POST['newlang']) && (count($_POST['newlang']) != null)) {
    for ($i = 0; $i < count($_POST['newlang']); $i++) {
        $lang1 = $_POST['newlang'][$i] ?? '';
        if ($lang1 == '') continue;
        $lang1 = strtolower($lang1);
        $tabes[$lang1] = [];
        foreach ($keysToAdd as $key) {
            $tabes[$lang1][$key] = 0;
        }
    }
}

// Handle existing languages
if (isset($_POST['lang']) && (count($_POST['lang']) != null)) {
    for ($io = 0; $io < count($_POST['lang']); $io++) {
        $lang = strtolower($_POST['lang'][$io]);
        $tabes[$lang] = [];
        foreach ($keysToAdd as $key) {
            $tabes[$lang][$key] = 0;
        }
    }
}

// Combine language processing into a single function
function addKeyFromPost($key)
{
    global $tabes;
    if (isset($_POST[$key]) && (count($_POST[$key]) != null)) {
        for ($io = 0; $io < count($_POST[$key]); $io++) {
            $vav = strtolower($_POST[$key][$io]);
            if (!isset($tabes[$vav])) $tabes[$vav] = [];
            $tabes[$vav][$key] = 1;
        }
    }
}

// Process additional keys
foreach ($keysToAdd as $key) {
    addKeyFromPost($key);
}

// Uncomment when deletion functionality is needed
if (isset($_POST['del'])) {
    for ($i = 0; $i < count($_POST['del']); $i++) {
        $key_to_del    = $_POST['del'][$i];
        if (isset($tabes[$key_to_del])) unset($tabes[$key_to_del]);
    }
}

// Save configuration if changes were made
if (isset($_POST['lang'])) {
    $tabes2 = $tabes;
    foreach ($tabes as $lang => $tab) {
        foreach ($keysToAdd as $key) {
            if (!isset($tabes2[$lang][$key])) $tabes2[$lang][$key] = 0;
        }
    }
    set_configs_all_file('fixwikirefs.json', $tabes2);
}
