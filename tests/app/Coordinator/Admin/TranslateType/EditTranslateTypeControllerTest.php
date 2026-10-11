<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\TranslateType;

use App\Coordinator\Admin\TranslateType\EditTranslateTypeController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EditTranslateTypeController::class)]
class EditTranslateTypeControllerTest extends TestCase
{
    public function testGetCardTitle(): void
    {
        $controller = new EditTranslateTypeController();

        $ref = new \ReflectionClass($controller);
        $method = $ref->getMethod('getCardTitle');
        $method->setAccessible(true);
        $title = $method->invoke($controller);

        $this->assertStringContainsString('Translate type', $title);
    }
}
