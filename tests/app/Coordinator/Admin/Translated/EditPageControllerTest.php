<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Translated;

use App\Coordinator\Admin\Translated\EditPageController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EditPageController::class)]
class EditPageControllerTest extends TestCase
{
    public function testGetCardTitle(): void
    {
        $controller = new EditPageController();

        $ref = new \ReflectionClass($controller);
        $method = $ref->getMethod('getCardTitle');
        $method->setAccessible(true);
        $title = $method->invoke($controller);

        $this->assertStringContainsString('Edit Page', $title);
    }
}
