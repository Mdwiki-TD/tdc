<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Add;

use App\Coordinator\Admin\Add\AddPostProcessor;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddPostProcessor::class)]
class AddPostProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SessionManager::ensureStarted();
    }

    public function testProcessEmptyPost(): void
    {
        $processor = new AddPostProcessor();

        $ref = new \ReflectionClass($processor);
        $method = $ref->getMethod('process');
        $method->setAccessible(true);
        $method->invoke($processor, []);

        $res = $processor->handle([]);
        $this->assertFalse($res['success']);
    }
}
