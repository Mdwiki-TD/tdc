<?php
// src/app/utils/TestPrinter.php

namespace App\Utils;

/**
 * Debug-print helper. Output only fires when ?test / test cookie is set
 * (and not disabled via cookie=x). Use directly, no inheritance needed.
 */
class TestPrinter
{
    public static function testPrint($s): void
    {
        // Suppress output when cookie is explicitly 'x'
        if (isset($_COOKIE['test']) && $_COOKIE['test'] === 'x') {
            return;
        }

        $printEnabled = isset($_REQUEST['test']) || isset($_COOKIE['test']);

        if (!$printEnabled) {
            return;
        }

        if (is_string($s)) {
            echo "\n<br>\n" . htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
            return;
        }

        echo "\n<br>\n<pre>";
        print_r($s);
        echo "</pre>";
    }
}


function test_print($s)
{
    (new TestPrinter())->testPrint($s);
}
