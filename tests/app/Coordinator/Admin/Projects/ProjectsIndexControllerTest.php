<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Projects;

use App\Coordinator\Admin\Projects\ProjectsIndexController;
use App\User\CurrentUser;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProjectsIndexController::class)]
class ProjectsIndexControllerTest extends TestCase
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
        $controller = new ProjectsIndexController();

        ob_start();
        $controller->handleRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Projects', $output);
    }
}
