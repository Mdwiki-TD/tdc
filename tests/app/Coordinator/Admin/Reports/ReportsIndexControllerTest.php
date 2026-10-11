<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Reports;

use App\Coordinator\Admin\Reports\ReportsIndexController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReportsIndexController::class)]
class ReportsIndexControllerTest extends TestCase
{
    public function testHandleRequest(): void
    {
        $controller = new ReportsIndexController();

        ob_start();
        $controller->handleRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Publish reports', $output);
    }
}
