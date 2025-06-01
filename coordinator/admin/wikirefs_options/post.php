<?php

use function Infos\TdConfig\get_configs;
use function Infos\TdConfig\set_configs_all_file;
use function TDWIKI\csrf\verify_csrf_token;

// Enable error reporting if requested
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Function to initialize language keys
function initializeLanguageKeys($tabes, $languages, $keysToAdd)
{
    foreach ($languages as $lang) {
        $lang = strtolower($lang);
        $tabes[$lang] = array_fill_keys($keysToAdd, 0);
    }
    return $tabes;
}

// Function to add keys from POST data
function addKeysFromPost($tabes, $keysToAdd)
{
    foreach ($keysToAdd as $key) {
        if (isset($_POST[$key])) {
            foreach ($_POST[$key] as $value) {
                $value = strtolower($value);
                if (!isset($tabes[$value])) {
                    $tabes[$value] = array_fill_keys($keysToAdd, 0);
                }
                $tabes[$value][$key] = 1;
            }
        }
    }
    return $tabes;
}

// Function to delete keys
function deleteKeys($tbes)
{
    if (isset($_POST['del'])) {
        for ($i = 0; $i < count($_POST['del']); $i++) {
            $key_to_del    = $_POST['del'][$i];
            if (isset($tbes[$key_to_del])) unset($tbes[$key_to_del]);
        }
    }
    return $tbes;
}

if (verify_csrf_token()) {
    // Load configurations from file
    $keysToAdd = ['move_dots', 'expend', 'add_en_lang'];
    $tabes = get_configs('fixwikirefs.json');

    // Initialize and process languages
    $languagesToAdd = array_filter(array_merge($_POST['newlang'] ?? [], $_POST['lang'] ?? []));
    $tabes = initializeLanguageKeys($tabes, $languagesToAdd, $keysToAdd);
    $tabes = addKeysFromPost($tabes, $keysToAdd);
    $tabes = deleteKeys($tabes);

    // Save configuration
    set_configs_all_file('fixwikirefs.json', $tabes);
}
