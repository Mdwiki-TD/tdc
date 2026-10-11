<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\WikiRefsOptions;

use App\Coordinator\Admin\WikiRefsOptions\WikiRefsOptionsIndexController;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WikiRefsOptionsIndexController::class)]
class WikiRefsOptionsIndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SessionManager::ensureStarted();
    }

    public function testHandleRequest(): void
    {
        $controller = new WikiRefsOptionsIndexController();

        ob_start();
        $controller->handleRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Language settings', $output);
    }
}
