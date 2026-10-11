<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Admins;

use App\Coordinator\Admin\Admins\AdminsPostProcessor;
use App\User\CurrentUser;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdminsPostProcessor::class)]
class AdminsPostProcessorTest extends TestCase
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
        $processor = new AdminsPostProcessor();

        $ref = new \ReflectionClass($processor);
        $method = $ref->getMethod('process');
        $method->setAccessible(true);
        $method->invoke($processor, []);

        $res = $processor->handle([]);
        $this->assertFalse($res['success']);
    }
}
