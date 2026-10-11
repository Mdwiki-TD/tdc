<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Users;

use App\Coordinator\Admin\Users\EditUserPostProcessor;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EditUserPostProcessor::class)]
class EditUserPostProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SessionManager::ensureStarted();
    }

    public function testProcessEmptyPost(): void
    {
        $processor = new EditUserPostProcessor();

        $ref = new \ReflectionClass($processor);
        $method = $ref->getMethod('process');
        $method->setAccessible(true);
        $method->invoke($processor, []);

        $res = $processor->handle([]);
        $this->assertFalse($res['success']);
    }
}
