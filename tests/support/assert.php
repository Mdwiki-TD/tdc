<?php

declare(strict_types=1);

namespace Tests\Support;

use AssertionError;

/**
 * @template T
 * @param T $expected
 * @param T $actual
 */
function assertSame($expected, $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        $description = $message !== '' ? $message : sprintf('Failed asserting that %s matches expected %s.', var_export($actual, true), var_export($expected, true));
        throw new AssertionError($description);
    }
}

function assertTrue(bool $value, string $message = ''): void
{
    if ($value !== true) {
        throw new AssertionError($message !== '' ? $message : 'Failed asserting that value is true.');
    }
}

function assertFalse(bool $value, string $message = ''): void
{
    if ($value !== false) {
        throw new AssertionError($message !== '' ? $message : 'Failed asserting that value is false.');
    }
}

function assertStringContains(string $needle, string $haystack, string $message = ''): void
{
    if (strpos($haystack, $needle) === false) {
        $description = $message !== '' ? $message : sprintf('Failed asserting that "%s" contains "%s".', $haystack, $needle);
        throw new AssertionError($description);
    }
}

function assertMatchesPattern(string $pattern, string $value, string $message = ''): void
{
    if (@preg_match($pattern, $value) !== 1) {
        $description = $message !== '' ? $message : sprintf('Failed asserting that "%s" matches pattern %s.', $value, $pattern);
        throw new AssertionError($description);
    }
}
