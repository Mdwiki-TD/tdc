<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Common;

use App\Coordinator\Admin\Common\AbstractControllerNoPost;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractControllerNoPost::class)]
class AbstractControllerNoPostTest extends TestCase
{
    public function testEchoBs5Card(): void
    {
        $controller = new class extends AbstractControllerNoPost {
            public function handleRequest(): void {}
        };

        ob_start();
        $controller->echoBs5Card('Header Title', 'Body Content', 'custom-body');
        $output = ob_get_clean();

        $this->assertStringContainsString('Header Title', $output);
        $this->assertStringContainsString('Body Content', $output);
        $this->assertStringContainsString('custom-body', $output);
    }
}
