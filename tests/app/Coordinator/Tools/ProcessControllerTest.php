<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Tools;

use App\Coordinator\Tools\ProcessController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProcessController::class)]
class ProcessControllerTest extends TestCase
{
    public function testHandleRequestOutputsTableAndScript(): void
    {
        $controller = new ProcessController();

        ob_start();
        $controller->handleRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Translations in process:', $output);
        $this->assertStringContainsString('id="process_table"', $output);
    }
}
