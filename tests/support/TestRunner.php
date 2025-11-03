<?php

declare(strict_types=1);

namespace Tests\Support;

use Throwable;

/**
 * Minimal test runner for executing simple assertion-based tests without external dependencies.
 */
final class TestRunner
{
    /**
     * @var array<int, array{name: string, test: callable}>
     */
    private array $tests = [];

    public function add(string $name, callable $test): void
    {
        $this->tests[] = ['name' => $name, 'test' => $test];
    }

    public function run(): bool
    {
        $failures = 0;
        $total = count($this->tests);

        foreach ($this->tests as $index => $test) {
            $number = $index + 1;
            try {
                ($test['test'])();
                echo sprintf("%d) PASS %s\n", $number, $test['name']);
            } catch (Throwable $throwable) {
                $failures++;
                $message = $throwable->getMessage();
                if ($message === '') {
                    $message = sprintf('%s thrown', $throwable::class);
                }
                echo sprintf("%d) FAIL %s\n    %s\n", $number, $test['name'], $message);
            }
        }

        if ($failures > 0) {
            echo sprintf("\nFAILURES! Tests: %d, Failures: %d\n", $total, $failures);
            return false;
        }

        echo sprintf("\nOK (%d tests)\n", $total);
        return true;
    }
}
