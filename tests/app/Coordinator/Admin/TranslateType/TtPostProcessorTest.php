<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\TranslateType;

use App\Coordinator\Admin\TranslateType\TtPostProcessor;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TtPostProcessor::class)]
class TtPostProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SessionManager::ensureStarted();
    }

    public function testProcessEmptyPost(): void
    {
        $processor = new TtPostProcessor();

        $ref = new \ReflectionClass($processor);
        $method = $ref->getMethod('process');
        $method->setAccessible(true);
        $method->invoke($processor, []);

        $res = $processor->handle([]);
        $this->assertFalse($res['success']);
    }
}
