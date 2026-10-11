<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Common;

use App\Coordinator\Admin\Common\AbstractEditController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractEditController::class)]
class AbstractEditControllerTest extends TestCase
{
    public function testRenderHeaderScripts(): void
    {
        $controller = new class extends AbstractEditController {
            protected function buildForm(): string { return ''; }
            protected function getCardTitle(): string { return 'Title'; }
            protected function createPostProcessor(): object { return new \stdClass(); }
        };

        ob_start();
        $controller->renderHeaderScripts();
        $output = ob_get_clean();

        $this->assertStringContainsString('<div class="container-fluid">', $output);
    }
}
