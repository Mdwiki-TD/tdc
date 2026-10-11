<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Tools;

use App\Coordinator\Tools\ProcessTotalController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProcessTotalController::class)]
class ProcessTotalControllerTest extends TestCase
{
    public function testHandleRequestOutputsUserProcessCounts(): void
    {
        $controller = new ProcessTotalController();

        ob_start();
        $controller->handleRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Translations in process', $output);
        $this->assertStringContainsString('User', $output);
        $this->assertStringContainsString('Articles', $output);
    }
}
