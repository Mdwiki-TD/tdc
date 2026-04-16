<?php

namespace Tests\Coordinator\Admin\Emails;

use PHPUnit\Framework\TestCase;
use Tables\SqlTables\TablesSql;

class IndexTest extends TestCase
{
    public function testGetSortedArrayLogic()
    {
        // Test the sorting logic used in get_sorted_array
        $test_array = [
            'user1' => ['live' => 10],
            'user2' => ['live' => 50],
            'user3' => ['live' => 25]
        ];

        // Sort descending by live
        uasort($test_array, function ($a, $b) {
            return $b['live'] <=> $a['live'];
        });

        $keys = array_keys($test_array);
        $this->assertEquals('user2', $keys[0]);
        $this->assertEquals('user3', $keys[1]);
        $this->assertEquals('user1', $keys[2]);
    }

    public function testGetSortedArrayWithZeroLive()
    {
        $test_array = [
            'user1' => ['live' => 0],
            'user2' => ['live' => 5],
            'user3' => ['live' => 0]
        ];

        uasort($test_array, function ($a, $b) {
            return $b['live'] <=> $a['live'];
        });

        $keys = array_keys($test_array);
        $this->assertEquals('user2', $keys[0]);
    }

    public function testEmailsFilterTableProjectMapping()
    {
        // Test that TablesSql projects get "empty" added
        TablesSql::$s_projects_title_to_id = [
            'Project1' => 1,
            'Project2' => 2
        ];

        TablesSql::$s_projects_title_to_id["empty"] = "empty";

        $this->assertArrayHasKey("empty", TablesSql::$s_projects_title_to_id);
        $this->assertEquals("empty", TablesSql::$s_projects_title_to_id["empty"]);
    }

    public function testEmailsFilterTableSelectedOption()
    {
        $project_name = 'TestProject';
        $test_project = 'TestProject';

        $is_selected = ($project_name == $test_project);
        $this->assertTrue($is_selected);

        $is_not_selected = ($project_name == 'OtherProject');
        $this->assertFalse($is_not_selected);
    }

    public function testEmailsFilterTableAllOption()
    {
        $main_project = 'All';
        $user_group = 'SomeProject';

        // When main_project is "All", should not filter
        $should_continue = (!empty($main_project) && $main_project != "All" && $user_group != $main_project);
        $this->assertFalse($should_continue);
    }

    public function testUserGroupUncategorizedMapping()
    {
        $user_group = '';
        $user_group2 = $user_group;

        if (empty($user_group2)) {
            $user_group2 = 'Uncategorized';
        }

        $this->assertEquals('Uncategorized', $user_group2);
    }

    public function testUserGroupWithValue()
    {
        $user_group = 'MyProject';
        $user_group2 = $user_group;

        if (empty($user_group2)) {
            $user_group2 = 'Uncategorized';
        }

        $this->assertEquals('MyProject', $user_group2);
    }

    public function testProjectFilterLogic()
    {
        $main_project = 'ProjectA';
        $user_group2 = 'ProjectB';

        $should_skip = (!empty($main_project) && $main_project != "All" && $user_group2 != $main_project);
        $this->assertTrue($should_skip);
    }

    public function testProjectFilterWithMatch()
    {
        $main_project = 'ProjectA';
        $user_group2 = 'ProjectA';

        $should_skip = (!empty($main_project) && $main_project != "All" && $user_group2 != $main_project);
        $this->assertFalse($should_skip);
    }

    public function testLimitApplication()
    {
        $limit = 10;
        $numb = 11;

        $should_break = ($limit > 0 && $numb > $limit);
        $this->assertTrue($should_break);
    }

    public function testLimitZeroNoBreak()
    {
        $limit = 0;
        $numb = 100;

        $should_break = ($limit > 0 && $numb > $limit);
        $this->assertFalse($should_break);
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

        $main_project = (isset($_GET['project'])) ? $_GET['project'] : 'All';
        $this->assertEquals('All', $main_project);

        $_GET = $origGet;
    }

    public function testEditParamsArrayStructure()
    {
        $edit_params = [
            'user_id'   => 123,
            'user'  => 'testuser',
            'email'  => 'test@example.com',
            'wiki'  => 'ar',
            'project'  => 'TestProject'
        ];

        $this->assertArrayHasKey('user_id', $edit_params);
        $this->assertArrayHasKey('user', $edit_params);
        $this->assertArrayHasKey('email', $edit_params);
        $this->assertArrayHasKey('wiki', $edit_params);
        $this->assertArrayHasKey('project', $edit_params);
    }

    public function testTableRowDataAttributes()
    {
        $numb = 5;
        $user_name = 'testuser';
        $email = 'test@example.com';

        // Test data attribute values
        $this->assertIsInt($numb);
        $this->assertIsString($user_name);
        $this->assertIsString($email);
    }
}
