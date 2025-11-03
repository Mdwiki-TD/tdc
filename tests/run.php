<?php

declare(strict_types=1);

require __DIR__ . '/support/TestRunner.php';
require __DIR__ . '/support/assert.php';

use Tests\Support\TestRunner;

$runner = new TestRunner();

$testFiles = [
    __DIR__ . '/unit/FunctionsTest.php',
    __DIR__ . '/unit/HtmlTest.php',
];

foreach ($testFiles as $file) {
    $register = require $file;
    $register($runner);
}

$success = $runner->run();

exit($success ? 0 : 1);
