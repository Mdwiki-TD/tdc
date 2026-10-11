<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Translated;

use App\Coordinator\Admin\Translated\TranslatedIndexController;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TranslatedIndexController::class)]
class TranslatedIndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SessionManager::ensureStarted();
    }

    public function testHandleRequest(): void
    {
        $controller = new TranslatedIndexController();

        ob_start();
        $controller->handleRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Translated', $output);
    }
}
