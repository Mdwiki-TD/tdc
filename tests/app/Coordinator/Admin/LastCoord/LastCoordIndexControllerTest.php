<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\LastCoord;

use App\Coordinator\Admin\LastCoord\LastCoordIndexController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LastCoordIndexController::class)]
class LastCoordIndexControllerTest extends TestCase
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

    public function testHandleRequestOutputsView(): void
    {
        $_GET['lang'] = 'All';

        $controller = new LastCoordIndexController();

        ob_start();
        $controller->processRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Recent translations:', $output);
    }
}
