<?php

declare(strict_types=1);

use Tests\Support\TestRunner;
use function Tests\Support\assertFalse;
use function Tests\Support\assertTrue;
use function Utils\Functions\start_with;

require_once __DIR__ . '/../../src/utils/functions.php';

return static function (TestRunner $runner): void {
    $runner->add('start_with returns true when haystack begins with needle', static function (): void {
        assertTrue(start_with('translation', 'tran'));
    });

    $runner->add('start_with returns false for non matching prefix', static function (): void {
        assertFalse(start_with('translation', 'dash'));
    });

    $runner->add('start_with treats empty needle as true', static function (): void {
        assertTrue(start_with('dashboard', ''));
    });

    $runner->add('start_with is case sensitive', static function (): void {
        assertFalse(start_with('Wiki', 'wi'));
    });

    $runner->add('start_with only matches at beginning of string', static function (): void {
        assertFalse(start_with('coordinator', 'din'));
    });
};
