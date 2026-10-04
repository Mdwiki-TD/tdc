<?php

declare(strict_types=1);

namespace Tests\App;

use App\Logger;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LoggerTest extends TestCase
{
    private array $originalCookie;
    private array $originalRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalCookie = $_COOKIE;
        $this->originalRequest = $_REQUEST;
        $this->resetLoggerDebugState();
    }

    protected function tearDown(): void
    {
        $_COOKIE = $this->originalCookie;
        $_REQUEST = $this->originalRequest;
        $this->resetLoggerDebugState();
        parent::tearDown();
    }

    private function resetLoggerDebugState(): void
    {
        $reflection = new ReflectionClass(Logger::class);
        $property = $reflection->getProperty('isDebugEnabled');
        $property->setValue(null, null);
    }

    public function testDebugOutputsStringWhenTestQueryParamIsSet(): void
    {
        $_REQUEST['test'] = '1';

        ob_start();
        Logger::debug('Hello Debug');
        $output = ob_get_clean();

        $this->assertSame("\n<br>\nHello Debug", $output);
    }

    public function testDebugOutputsArrayWhenTestQueryParamIsSet(): void
    {
        $_REQUEST['test'] = '1';

        ob_start();
        Logger::debug(['key' => 'value']);
        $output = ob_get_clean();

        $this->assertStringContainsString("\n<br>\n", $output);
        $this->assertStringContainsString('[key] => value', $output);
    }

    public function testDebugIsDisabledWhenCookieTestIsX(): void
    {
        $_COOKIE['test'] = 'x';
        $_REQUEST['test'] = '1';

        ob_start();
        Logger::debug('Should not output');
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }

    public function testDebugDoesNotOutputWhenNoTestParamOrCookie(): void
    {
        unset($_COOKIE['test'], $_REQUEST['test']);

        ob_start();
        Logger::debug('No output');
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }
}
