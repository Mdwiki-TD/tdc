<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Settings;

use App\Coordinator\Admin\Settings\SettingsPostProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SettingsPostProcessor::class)]
class SettingsPostProcessorTest extends TestCase
{
    public function testProcessEmptyPost(): void
    {
        $processor = new SettingsPostProcessor();

        $ref = new \ReflectionClass($processor);
        $method = $ref->getMethod('process');
        $method->setAccessible(true);
        $method->invoke($processor, []);

        $res = $processor->handle([]);
        $this->assertFalse($res['success']);
    }
}
