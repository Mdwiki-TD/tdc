<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\FullTranslators;

use App\Coordinator\Admin\FullTranslators\FullTranslatorsPostProcessor;
use App\User\CurrentUser;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FullTranslatorsPostProcessor::class)]
class FullTranslatorsPostProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        SessionManager::ensureStarted();

        $user = $this->createMock(CurrentUser::class);
        $user->method('isCoordinator')->willReturn(true);
        $user->method('isAdmin')->willReturn(true);

        $ref = new \ReflectionClass(CurrentUser::class);
        $prop = $ref->getProperty('instance');
        $prop->setAccessible(true);
        $prop->setValue(null, $user);
    }

    public function testProcessEmptyPost(): void
    {
        $processor = new FullTranslatorsPostProcessor();

        $ref = new \ReflectionClass($processor);
        $method = $ref->getMethod('process');
        $method->setAccessible(true);
        $method->invoke($processor, []);

        $res = $processor->handle([]);
        $this->assertFalse($res['success']);
    }
}
