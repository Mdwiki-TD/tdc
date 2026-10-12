<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Tools;

use App\Coordinator\Tools\CategoriesController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CategoriesController::class)]
class CategoriesControllerTest extends TestCase
{
    public function testHandleRequestOutputsCardAndDataTableScript(): void
    {
        $controller = new CategoriesController();

        ob_start();
        $controller->handleRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Translations Categories', $output);
        $this->assertStringContainsString('id="categories_table"', $output);
        $this->assertStringContainsString('Q107014860', $output);
    }
}
