<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Tools;

use App\Coordinator\Tools\StatController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StatController::class)]
class StatControllerTest extends TestCase
{
    private array $origGet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->origGet = $_GET;
    }

    protected function tearDown(): void
    {
        $_GET = $this->origGet;
        parent::tearDown();
    }

    public function testHandleRequestOutputsStatusAndFilter(): void
    {
        $_GET['cat'] = 'RTT';

        $controller = new StatController();

        ob_start();
        $controller->handleRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Status:', $output);
        $this->assertStringContainsString('Category:', $output);
        $this->assertStringContainsString('enwiki views', $output);
    }
}
