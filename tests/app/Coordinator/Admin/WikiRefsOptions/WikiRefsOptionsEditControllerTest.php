<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\WikiRefsOptions;

use App\Coordinator\Admin\WikiRefsOptions\WikiRefsOptionsEditController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WikiRefsOptionsEditController::class)]
class WikiRefsOptionsEditControllerTest extends TestCase
{
    public function testGetCardTitle(): void
    {
        $controller = new WikiRefsOptionsEditController();

        $ref = new \ReflectionClass($controller);
        $method = $ref->getMethod('getCardTitle');
        $method->setAccessible(true);
        $title = $method->invoke($controller);

        $this->assertStringContainsString('language settings', $title);
    }
}
