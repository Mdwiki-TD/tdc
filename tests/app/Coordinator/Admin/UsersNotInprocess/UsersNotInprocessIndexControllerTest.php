<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\UsersNotInprocess;

use App\Coordinator\Admin\UsersNotInprocess\UsersNotInprocessIndexController;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UsersNotInprocessIndexController::class)]
class UsersNotInprocessIndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SessionManager::ensureStarted();
    }

    public function testHandleRequest(): void
    {
        $controller = new UsersNotInprocessIndexController();

        ob_start();
        $controller->renderFormCard();
        $output = ob_get_clean();

        $this->assertStringContainsString("Users Excluded from 'In Progress' Table:", $output);
    }
}
