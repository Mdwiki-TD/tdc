<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Tools;

use App\Coordinator\Tools\ProcessControllerDataTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProcessControllerDataTable::class)]
class ProcessControllerDataTableTest extends TestCase
{
    public function testHandleRequestOutputsDataTableScript(): void
    {
        $controller = new ProcessControllerDataTable();

        ob_start();
        $controller->processRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Translations in process:', $output);
        $this->assertStringContainsString('id="process_table"', $output);
        $this->assertStringContainsString('in_process&limit=200', $output);
    }
}
