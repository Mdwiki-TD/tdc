<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Add;

use App\Coordinator\Admin\Add\AddIndexController;
use App\User\CurrentUser;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddIndexController::class)]
class AddIndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        SessionManager::ensureStarted();

        $user = $this->createMock(CurrentUser::class);
        $user->method('isCoordinator')->willReturn(true);
        $user->method('isAdmin')->willReturn(true);

        $ref = new \ReflectionClass(CurrentUser::class);
        $prop = $ref->getProperty('instance');
        $prop->setAccessible(true);
        $prop->setValue(null, $user);
    }

    public function testHandleRequest(): void
    {
        $controller = new AddIndexController();

        ob_start();
        $controller->handleRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Add translations:', $output);
        $this->assertStringContainsString('name=\'csrf_token\'', $output);
    }
}
