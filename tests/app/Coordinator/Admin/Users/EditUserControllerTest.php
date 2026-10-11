<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Users;

use App\Coordinator\Admin\Users\EditUserController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EditUserController::class)]
class EditUserControllerTest extends TestCase
{
    public function testGetCardTitle(): void
    {
        $controller = new EditUserController();

        $ref = new \ReflectionClass($controller);
        $method = $ref->getMethod('getCardTitle');
        $method->setAccessible(true);
        $title = $method->invoke($controller);

        $this->assertStringContainsString('User', $title);
    }
}
