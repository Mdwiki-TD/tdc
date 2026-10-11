<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\PagesUsersToMain;

use App\Coordinator\Admin\PagesUsersToMain\FixItController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FixItController::class)]
class FixItControllerTest extends TestCase
{
    public function testGetCardTitle(): void
    {
        $controller = new FixItController();

        $ref = new \ReflectionClass($controller);
        $method = $ref->getMethod('getCardTitle');
        $method->setAccessible(true);
        $title = $method->invoke($controller);

        $this->assertStringContainsString('Edit Page', $title);
    }
}
