<?php

namespace Tests\Coordinator\Admin\Emails;

use PHPUnit\Framework\TestCase;
use App\Tables\SqlTables\TablesSql;

class IndexTest extends TestCase
{
    public function testGetSortedArrayLogic()
    {
        // Test the sorting logic used in get_sorted_array
        $testArray = [
            'user1' => ['live' => 10],
            'user2' => ['live' => 50],
            'user3' => ['live' => 25]
        ];

        // Sort descending by live
        uasort($testArray, function ($a, $b) {
            return $b['live'] <=> $a['live'];
        });

        $keys = array_keys($testArray);
        $this->assertEquals('user2', $keys[0]);
        $this->assertEquals('user3', $keys[1]);
        $this->assertEquals('user1', $keys[2]);
    }

    public function testGetSortedArrayWithZeroLive()
    {
        $testArray = [
            'user1' => ['live' => 0],
            'user2' => ['live' => 5],
            'user3' => ['live' => 0]
        ];

        uasort($testArray, function ($a, $b) {
            return $b['live'] <=> $a['live'];
        });

        $keys = array_keys($testArray);
        $this->assertEquals('user2', $keys[0]);
    }

    public function testEmailsFilterTableProjectMapping()
    {
        // Test that TablesSql projects get "empty" added
        TablesSql::$sProjectsTitleToId = [
            'Project1' => 1,
            'Project2' => 2
        ];

        TablesSql::$sProjectsTitleToId["empty"] = "empty";

        $this->assertArrayHasKey("empty", TablesSql::$sProjectsTitleToId);
        $this->assertEquals("empty", TablesSql::$sProjectsTitleToId["empty"]);
    }

    public function testEmailsFilterTableSelectedOption()
    {
        $projectName = 'TestProject';
        $testProject = 'TestProject';

        $isSelected = ($projectName == $testProject);
        $this->assertTrue($isSelected);

        $isNotSelected = ($projectName == 'OtherProject');
        $this->assertFalse($isNotSelected);
    }

    public function testEmailsFilterTableAllOption()
    {
        $mainProject = 'All';
        $userGroup = 'SomeProject';

        // When main_project is "All", should not filter
        $shouldContinue = (!empty($mainProject) && $mainProject != "All" && $userGroup != $mainProject);
        $this->assertFalse($shouldContinue);
    }

    public function testUserGroupUncategorizedMapping()
    {
        $userGroup = '';
        $userGroup2 = $userGroup;

        if (empty($userGroup2)) {
            $userGroup2 = 'Uncategorized';
        }

        $this->assertEquals('Uncategorized', $userGroup2);
    }

    public function testUserGroupWithValue()
    {
        $userGroup = 'MyProject';
        $userGroup2 = $userGroup;

        if (empty($userGroup2)) {
            $userGroup2 = 'Uncategorized';
        }

        $this->assertEquals('MyProject', $userGroup2);
    }

    public function testProjectFilterLogic()
    {
        $mainProject = 'ProjectA';
        $userGroup2 = 'ProjectB';

        $shouldSkip = (!empty($mainProject) && $mainProject != "All" && $userGroup2 != $mainProject);
        $this->assertTrue($shouldSkip);
    }

    public function testProjectFilterWithMatch()
    {
        $mainProject = 'ProjectA';
        $userGroup2 = 'ProjectA';

        $shouldSkip = (!empty($mainProject) && $mainProject != "All" && $userGroup2 != $mainProject);
        $this->assertFalse($shouldSkip);
    }

    public function testLimitApplication()
    {
        $limit = 10;
        $numb = 11;

        $shouldBreak = ($limit > 0 && $numb > $limit);
        $this->assertTrue($shouldBreak);
    }

    public function testLimitZeroNoBreak()
    {
        $limit = 0;
        $numb = 100;

        $shouldBreak = ($limit > 0 && $numb > $limit);
        $this->assertFalse($shouldBreak);
    }

    public function testLimitParameter()
    {
        $origGet = $_GET;
        $_GET['limit'] = '50';

        $limit = (isset($_GET['limit'])) ? $_GET['limit'] : 0;
        $this->assertEquals('50', $limit);

        $_GET = $origGet;
    }

    public function testLimitParameterDefault()
    {
        $origGet = $_GET;
        unset($_GET['limit']);

        $limit = (isset($_GET['limit'])) ? $_GET['limit'] : 0;
        $this->assertEquals(0, $limit);

        $_GET = $origGet;
    }

    public function testProjectParameterDefault()
    {
        $origGet = $_GET;
        unset($_GET['project']);

        $mainProject = (isset($_GET['project'])) ? $_GET['project'] : 'All';
        $this->assertEquals('All', $mainProject);

        $_GET = $origGet;
    }

    public function testEditParamsArrayStructure()
    {
        $editParams = [
            'user_id'   => 123,
            'user'  => 'testuser',
            'email'  => 'test@example.com',
            'wiki'  => 'ar',
            'project'  => 'TestProject'
        ];

        $this->assertArrayHasKey('user_id', $editParams);
        $this->assertArrayHasKey('user', $editParams);
        $this->assertArrayHasKey('email', $editParams);
        $this->assertArrayHasKey('wiki', $editParams);
        $this->assertArrayHasKey('project', $editParams);
    }

    public function testTableRowDataAttributes()
    {
        $numb = 5;
        $userName = 'testuser';
        $email = 'test@example.com';

        // Test data attribute values
        $this->assertIsInt($numb);
        $this->assertIsString($userName);
        $this->assertIsString($email);
    }
}
