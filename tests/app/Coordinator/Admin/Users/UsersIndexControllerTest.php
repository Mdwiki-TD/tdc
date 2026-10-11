<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Users;

use App\Coordinator\Admin\Users\UsersIndexController;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UsersIndexController::class)]
class UsersIndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SessionManager::ensureStarted();
    }

    public function testHandleRequest(): void
    {
        $controller = new UsersIndexController();

        ob_start();
        $controller->processRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Users', $output);
    }
}
