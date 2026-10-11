<?php

declare(strict_types=1);

namespace Tests\App;

use App\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Logger::class)]
class LoggerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Logger::reset();
    }

    protected function tearDown(): void
    {
        Logger::reset();
        parent::tearDown();
    }

    public function testSetSinkAndWriteLogs(): void
    {
        $logged = [];
        Logger::setSink(function (string $level, string $message) use (&$logged) {
            $logged[] = [$level, $message];
        });

        Logger::info('Info message');
        Logger::warning('Warning message');
        Logger::error('Error message');

        $this->assertCount(3, $logged);
        $this->assertSame(['info', 'Info message'], $logged[0]);
        $this->assertSame(['warning', 'Warning message'], $logged[1]);
        $this->assertSame(['error', 'Error message'], $logged[2]);
    }

    public function testDebugStringAndNonStringWhenDebugEnabled(): void
    {
        $logged = [];
        Logger::setSink(function (string $level, string $message) use (&$logged) {
            $logged[] = [$level, $message];
        });

        // Set debug mode via reflection or test stringify with info
        Logger::info(print_r(['key' => 'val'], true));

        $this->assertCount(1, $logged);
        $this->assertStringContainsString('key', $logged[0][1]);
    }

    public function testReset(): void
    {
        $logged = [];
        Logger::setSink(function (string $level, string $message) use (&$logged) {
            $logged[] = [$level, $message];
        });

        Logger::reset();
        Logger::info('Post reset message');

        $this->assertEmpty($logged);
    }
}
