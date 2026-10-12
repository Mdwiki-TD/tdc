<?php

declare(strict_types=1);

namespace Tests\App\MdwikiSql;

use App\MdwikiSql\AddHelper;
use App\MdwikiSql\Database;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddHelper::class)]
class AddHelperTest extends TestCase
{
    public function testInsertToPagesNewRecord(): void
    {
        $db = $this->createMock(Database::class);

        // First fetchQuery returns empty -> record does not exist
        $db->expects($this->once())
            ->method('fetchQuery')
            ->willReturn([]);

        // executeQuery executes INSERT
        $db->expects($this->once())
            ->method('executeQuery')
            ->with(
                $this->stringContains('INSERT INTO pages'),
                $this->equalTo(['Test Title', 100, 'lead', 'RTT', 'en', 'user1', '2023-01-01', 'Target Page'])
            )
            ->willReturn(true);

        $helper = new AddHelper($db);

        $data = [
            'user' => 'user1',
            'title' => 'Test_Title',
            'lang' => 'en',
            'target' => 'Target_Page',
            'pupdate' => '2023-01-01',
            'cat' => 'RTT',
            'translate_type' => 'lead',
            'word' => 100
        ];

        $result = $helper->insertToPages($data);
        $this->assertTrue($result);
    }

    public function testInsertToPagesExistingRecordUpdateAllowed(): void
    {
        $db = $this->createMock(Database::class);

        // Record exists with empty target
        $db->expects($this->once())
            ->method('fetchQuery')
            ->willReturn([
                ['id' => 42, 'target' => '']
            ]);

        $db->expects($this->once())
            ->method('executeQuery')
            ->with(
                $this->stringContains('UPDATE pages'),
                $this->equalTo(['Target Page', '2023-01-01', 100, 42])
            )
            ->willReturn(true);

        $helper = new AddHelper($db);

        $data = [
            'user' => 'user1',
            'title' => 'Test_Title',
            'lang' => 'en',
            'target' => 'Target_Page',
            'pupdate' => '2023-01-01',
            'cat' => 'RTT',
            'translate_type' => 'lead',
            'word' => 100
        ];

        $result = $helper->insertToPages($data);
        $this->assertTrue($result);
    }

    public function testInsertToPagesExistingRecordUpdateDeniedWithoutOverwrite(): void
    {
        $db = $this->createMock(Database::class);

        // Record exists with existing target
        $db->expects($this->once())
            ->method('fetchQuery')
            ->willReturn([
                ['id' => 42, 'target' => 'Other Target']
            ]);

        $db->expects($this->never())
            ->method('executeQuery');

        $helper = new AddHelper($db);

        $data = [
            'user' => 'user1',
            'title' => 'Test Title',
            'lang' => 'en',
            'target' => 'New Target',
            'pupdate' => '2023-01-01',
            'cat' => 'RTT',
            'translate_type' => 'lead',
            'word' => 100
        ];

        $result = $helper->insertToPages($data, false);
        $this->assertFalse($result);
    }

    public function testAddPagesToDbDefaultsCatToRtt(): void
    {
        $db = $this->createMock(Database::class);

        $db->expects($this->once())
            ->method('fetchQuery')
            ->willReturn([]);

        $db->expects($this->once())
            ->method('executeQuery')
            ->with(
                $this->anything(),
                $this->callback(function ($params) {
                    // Check cat param index 3 is RTT
                    return $params[3] === 'RTT';
                })
            )
            ->willReturn(true);

        $helper = new AddHelper($db);
        $res = $helper->addPagesToDb('Title', 'lead', '', 'en', 'user', 'target', '2023-01-01');
        $this->assertTrue($res);
    }

    public function testCheckAfterAdd(): void
    {
        $db = $this->createMock(Database::class);

        $db->expects($this->once())
            ->method('fetchQuery')
            ->with(
                $this->stringContains('SELECT 1 FROM pages'),
                ['Title', 'en', 'user', 'target']
            )
            ->willReturn([['1' => 1]]);

        $helper = new AddHelper($db);
        $this->assertTrue($helper->checkAfterAdd('Title', 'en', 'user', 'target'));
    }
}
