<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\UsersNotInprocess;

use App\Coordinator\Admin\UsersNotInprocess\UsersNotInprocessPostProcessor;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UsersNotInprocessPostProcessor::class)]
class UsersNotInprocessPostProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SessionManager::ensureStarted();
    }

    public function testProcessEmptyPost(): void
    {
        $processor = new UsersNotInprocessPostProcessor();

        $ref = new \ReflectionClass($processor);
        $method = $ref->getMethod('process');
        $method->setAccessible(true);
        $method->invoke($processor, []);

        $res = $processor->handle([]);
        $this->assertFalse($res['success']);
    }
}
