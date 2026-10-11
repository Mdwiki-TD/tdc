<?php

declare(strict_types=1);

namespace Tests\App\SQLorAPI;

use App\SQLorAPI\ApiOrSqlService;
use App\SQLorAPI\SettingsTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SettingsTable::class)]
class SettingsTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SettingsTable::resetCache();
    }

    protected function tearDown(): void
    {
        SettingsTable::resetCache();
        parent::tearDown();
    }

    public function testGetSettingsAndEndpoint(): void
    {
        $service = $this->createMock(ApiOrSqlService::class);
        $service->expects($this->once())
            ->method('superFunction')
            ->willReturn([
                ['id' => 1, 'title' => 'use_mdwikicx', 'value' => '1']
            ]);

        $table = new SettingsTable($service);

        $settings = $table->getSettings();
        $this->assertCount(1, $settings);

        $this->assertSame('https://mdwikicx.toolforge.org/w/index.php', $table->getEndpointOld());
        $this->assertSame('https://mdwikicx.toolforge.org/w/index.php', $table->getEndpoint());
    }

    public function testGetLanguageSettings(): void
    {
        $service = $this->createMock(ApiOrSqlService::class);
        $service->expects($this->once())
            ->method('superFunction')
            ->willReturn([['lang_code' => 'ar', 'move_dots' => 1]]);

        $table = new SettingsTable($service);
        $langs = $table->getLanguageSettings();

        $this->assertSame([['lang_code' => 'ar', 'move_dots' => 1]], $langs);
    }
}
