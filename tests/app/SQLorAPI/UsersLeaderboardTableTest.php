<?php

declare(strict_types=1);

namespace Tests\App\SQLorAPI;

use App\SQLorAPI\ApiOrSqlService;
use App\SQLorAPI\UsersLeaderboardTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UsersLeaderboardTable::class)]
class UsersLeaderboardTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        UsersLeaderboardTable::resetCache();
    }

    protected function tearDown(): void
    {
        UsersLeaderboardTable::resetCache();
        parent::tearDown();
    }

    public function testGetUserYearsLangsCampsAndFilterData(): void
    {
        $service = $this->createMock(ApiOrSqlService::class);
        $service->expects($this->exactly(3))
            ->method('superFunction')
            ->willReturnOnConsecutiveCalls(
                [['year' => '2023'], ['year' => '2022']],
                [['lang' => 'ar'], ['lang' => 'en']],
                [['campaign' => 'RTT']]
            );

        $table = new UsersLeaderboardTable($service);

        $filterData = $table->getUserFilterData('user1');

        $this->assertSame(['2023', '2022'], $filterData['years']);
        $this->assertSame(['ar', 'en'], $filterData['langs']);
        $this->assertSame(['RTT'], $filterData['camps']);
    }

    public function testGetUserNewFilterData(): void
    {
        $service = $this->createMock(ApiOrSqlService::class);
        $service->expects($this->once())
            ->method('superFunction')
            ->with(['get' => 'user_data_status', 'user' => 'user1'], [], "")
            ->willReturn(['years' => ['2023' => 5], 'langs' => ['ar' => 5], 'camps' => ['RTT' => 5]]);

        $table = new UsersLeaderboardTable($service);
        $data = $table->getUserNewFilterData('user1');

        $this->assertSame(5, $data['years']['2023']);
    }
}
