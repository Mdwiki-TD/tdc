<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\TranslateType;

use App\Coordinator\Admin\TranslateType\TranslateTypeIndexController;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TranslateTypeIndexController::class)]
class TranslateTypeIndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SessionManager::ensureStarted();
    }

    public function testHandleRequest(): void
    {
        $controller = new TranslateTypeIndexController();

        ob_start();
        $controller->handleRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Translate Type', $output);
    }
}
