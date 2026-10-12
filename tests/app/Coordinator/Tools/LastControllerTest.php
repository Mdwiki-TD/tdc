<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Tools;

use App\Coordinator\Tools\LastController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LastController::class)]
class LastControllerTest extends TestCase
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

    public function testHandleRequestOutputsMainView(): void
    {
        $_GET['lang'] = 'ar';
        $_GET['last_table'] = 'pages';

        $controller = new LastController();

        ob_start();
        $controller->handleRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('Recent translations:', $output);
        $this->assertStringContainsString('id="last_table"', $output);
    }

    public function testCreateLastTableDataFormatsRow(): void
    {
        $controller = new LastController();
        $row = [
            'user' => 'LongUsernameForTestingDisplay',
            'lang' => 'en',
            'title' => 'Sample Title',
            'target' => 'Sample Target',
            'pupdate' => '2023-01-01',
            'add_date' => '2023-01-01 12:00:00',
            'campaign' => 'RTT',
            'views' => 100
        ];

        $html = $controller->createLastTableData($row, 1);

        $this->assertStringContainsString('LongUsernameForTestingDisplay', $html);
        $this->assertStringContainsString('Sample Title', $html);
        $this->assertStringContainsString('Sample Target', $html);
    }
}
