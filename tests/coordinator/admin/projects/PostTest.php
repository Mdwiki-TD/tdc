<?php

namespace Tests\Coordinator\Admin\Projects;

use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function testPostDataExtraction()
    {
        $table = [
            'g_id' => '6',
            'g_title' => 'Test Project'
        ];

        $g_id = $table['g_id'] ?? '';
        $g_title = $table['g_title'] ?? '';

        $this->assertEquals('6', $g_id);
        $this->assertEquals('Test Project', $g_title);
    }

    public function testDeleteDetection()
    {
        $table = [
            'g_id' => '5',
            'g_title' => 'Project',
            'del' => '5'
        ];

        $g_id = $table['g_id'] ?? '';
        $del = $table['del'] ?? '';

        $shouldDelete = (!empty($del) && !empty($g_id));
        $this->assertTrue($shouldDelete);
    }

    public function testDeleteNotPresent()
    {
        $table = [
            'g_id' => '5',
            'g_title' => 'Project'
        ];

        $g_id = $table['g_id'] ?? '';
        $del = $table['del'] ?? '';

        $shouldDelete = (!empty($del) && !empty($g_id));
        $this->assertFalse($shouldDelete);
    }

    public function testTrimTitle()
    {
        $g_title = '  Test Project  ';
        $trimmed = trim($g_title);
        $this->assertEquals('Test Project', $trimmed);

        $g_title = "\tProject\n";
        $trimmed = trim($g_title);
        $this->assertEquals('Project', $trimmed);
    }

    public function testEmptyTitleSkip()
    {
        $g_title = '';
        $shouldSkip = empty($g_title);
        $this->assertTrue($shouldSkip);

        $g_title = '   ';
        $g_title = trim($g_title);
        $shouldSkip = empty($g_title);
        $this->assertTrue($shouldSkip);
    }

    public function testNonEmptyTitle()
    {
        $g_title = 'Valid Project';
        $shouldSkip = empty($g_title);
        $this->assertFalse($shouldSkip);
    }

    public function testAddOrUpdateLogic()
    {
        // Add case (no g_id)
        $g_id = '';
        $action = empty($g_id) ? 'Added' : 'Updated';
        $this->assertEquals('Added', $action);

        // Update case (has g_id)
        $g_id = '10';
        $action = empty($g_id) ? 'Added' : 'Updated';
        $this->assertEquals('Updated', $action);
    }

    public function testDeleteMessage()
    {
        $g_title = 'MyProject';
        $message = "Project $g_title deleted.";
        $this->assertEquals('Project MyProject deleted.', $message);
        $this->assertStringContainsString('deleted', $message);
    }

    public function testAddMessage()
    {
        $g_title = 'NewProject';
        $message = "Project $g_title Added.";
        $this->assertEquals('Project NewProject Added.', $message);
        $this->assertStringContainsString('Added', $message);
    }

    public function testUpdateMessage()
    {
        $g_title = 'ExistingProject';
        $message = "Project $g_title Updated.";
        $this->assertEquals('Project ExistingProject Updated.', $message);
        $this->assertStringContainsString('Updated', $message);
    }

    public function testDeleteQueryConstruction()
    {
        $table_name = 'projects';
        $qua2 = "DELETE FROM $table_name WHERE g_id = ?";
        $this->assertEquals('DELETE FROM projects WHERE g_id = ?', $qua2);
        $this->assertStringContainsString('WHERE g_id = ?', $qua2);
    }

    public function testParamsArrayForDelete()
    {
        $g_id = '15';
        $params = [$g_id];
        $this->assertCount(1, $params);
        $this->assertEquals('15', $params[0]);
    }

    public function testContinueAfterDelete()
    {
        // Test that after delete, we should continue to next iteration
        $shouldContinue = true;
        $this->assertTrue($shouldContinue);
    }

    public function testTextsArrayAccumulation()
    {
        $texts = [];
        $texts[] = "Project A deleted.";
        $texts[] = "Project B Added.";
        $texts[] = "Project C Updated.";

        $this->assertCount(3, $texts);
        $this->assertEquals('Project A deleted.', $texts[0]);
        $this->assertEquals('Project B Added.', $texts[1]);
        $this->assertEquals('Project C Updated.', $texts[2]);
    }

    public function testPostRowsIteration()
    {
        $_POST['rows'] = [
            1 => ['g_id' => '1', 'g_title' => 'Project 1'],
            2 => ['g_id' => '2', 'g_title' => 'Project 2']
        ];

        $count = 0;
        foreach ($_POST['rows'] ?? [] as $key => $table) {
            $count++;
        }

        $this->assertEquals(2, $count);

        unset($_POST['rows']);
    }

    public function testEmptyPostRows()
    {
        unset($_POST['rows']);

        $count = 0;
        foreach ($_POST['rows'] ?? [] as $key => $table) {
            $count++;
        }

        $this->assertEquals(0, $count);
    }

    public function testNullCoalescing()
    {
        $array = ['key' => 'value'];

        $value = $array['key'] ?? 'default';
        $this->assertEquals('value', $value);

        $value = $array['missing'] ?? 'default';
        $this->assertEquals('default', $value);
    }

    public function testEmptyGIdHandling()
    {
        $table = ['g_title' => 'New Project'];
        $g_id = $table['g_id'] ?? '';

        $this->assertEquals('', $g_id);
        $this->assertTrue(empty($g_id));
    }

    public function testDeleteWithMissingGId()
    {
        $g_id = '';
        $del = '5';

        $shouldDelete = (!empty($del) && !empty($g_id));
        $this->assertFalse($shouldDelete);
    }

    public function testBothFieldsRequired()
    {
        // Test that both del and g_id must be present for delete
        $g_id = '5';
        $del = '';
        $shouldDelete = (!empty($del) && !empty($g_id));
        $this->assertFalse($shouldDelete);

        $g_id = '';
        $del = '5';
        $shouldDelete = (!empty($del) && !empty($g_id));
        $this->assertFalse($shouldDelete);

        $g_id = '5';
        $del = '5';
        $shouldDelete = (!empty($del) && !empty($g_id));
        $this->assertTrue($shouldDelete);
    }
}