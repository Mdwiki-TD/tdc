<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Reports;

use App\Coordinator\Admin\Reports\ReportsIndex2Controller;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReportsIndex2Controller::class)]
class ReportsIndex2ControllerTest extends TestCase
{
    public function testHandleRequest(): void
    {
        $controller = new ReportsIndex2Controller();

        ob_start();
        $controller->handleRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Publish reports', $output);
    }
}
