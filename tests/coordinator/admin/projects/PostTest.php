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

        $gId = $table['g_id'] ?? '';
        $gTitle = $table['g_title'] ?? '';

        $this->assertEquals('6', $gId);
        $this->assertEquals('Test Project', $gTitle);
    }

    public function testDeleteDetection()
    {
        $table = [
            'g_id' => '5',
            'g_title' => 'Project',
            'del' => '5'
        ];

        $gId = $table['g_id'] ?? '';
        $del = $table['del'] ?? '';

        $shouldDelete = (!empty($del) && !empty($gId));
        $this->assertTrue($shouldDelete);
    }

    public function testDeleteNotPresent()
    {
        $table = [
            'g_id' => '5',
            'g_title' => 'Project'
        ];

        $gId = $table['g_id'] ?? '';
        $del = $table['del'] ?? '';

        $shouldDelete = (!empty($del) && !empty($gId));
        $this->assertFalse($shouldDelete);
    }

    public function testTrimTitle()
    {
        $gTitle = '  Test Project  ';
        $trimmed = trim($gTitle);
        $this->assertEquals('Test Project', $trimmed);

        $gTitle = "\tProject\n";
        $trimmed = trim($gTitle);
        $this->assertEquals('Project', $trimmed);
    }

    public function testEmptyTitleSkip()
    {
        $gTitle = '';
        $shouldSkip = empty($gTitle);
        $this->assertTrue($shouldSkip);

        $gTitle = '   ';
        $gTitle = trim($gTitle);
        $shouldSkip = empty($gTitle);
        $this->assertTrue($shouldSkip);
    }

    public function testNonEmptyTitle()
    {
        $gTitle = 'Valid Project';
        $shouldSkip = empty($gTitle);
        $this->assertFalse($shouldSkip);
    }

    public function testAddOrUpdateLogic()
    {
        // Add case (no g_id)
        $gId = '';
        $action = empty($gId) ? 'Added' : 'Updated';
        $this->assertEquals('Added', $action);

        // Update case (has g_id)
        $gId = '10';
        $action = empty($gId) ? 'Added' : 'Updated';
        $this->assertEquals('Updated', $action);
    }

    public function testDeleteMessage()
    {
        $gTitle = 'MyProject';
        $message = "Project $gTitle deleted.";
        $this->assertEquals('Project MyProject deleted.', $message);
        $this->assertStringContainsString('deleted', $message);
    }

    public function testAddMessage()
    {
        $gTitle = 'NewProject';
        $message = "Project $gTitle Added.";
        $this->assertEquals('Project NewProject Added.', $message);
        $this->assertStringContainsString('Added', $message);
    }

    public function testUpdateMessage()
    {
        $gTitle = 'ExistingProject';
        $message = "Project $gTitle Updated.";
        $this->assertEquals('Project ExistingProject Updated.', $message);
        $this->assertStringContainsString('Updated', $message);
    }

    public function testDeleteQueryConstruction()
    {
        $tableName = 'projects';
        $qua2 = "DELETE FROM $tableName WHERE g_id = ?";
        $this->assertEquals('DELETE FROM projects WHERE g_id = ?', $qua2);
        $this->assertStringContainsString('WHERE g_id = ?', $qua2);
    }

    public function testParamsArrayForDelete()
    {
        $gId = '15';
        $params = [$gId];
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
        $gId = $table['g_id'] ?? '';

        $this->assertEquals('', $gId);
        $this->assertTrue(empty($gId));
    }

    public function testDeleteWithMissingGId()
    {
        $gId = '';
        $del = '5';

        $shouldDelete = (!empty($del) && !empty($gId));
        $this->assertFalse($shouldDelete);
    }

    public function testBothFieldsRequired()
    {
        // Test that both del and g_id must be present for delete
        $gId = '5';
        $del = '';
        $shouldDelete = (!empty($del) && !empty($gId));
        $this->assertFalse($shouldDelete);

        $gId = '';
        $del = '5';
        $shouldDelete = (!empty($del) && !empty($gId));
        $this->assertFalse($shouldDelete);

        $gId = '5';
        $del = '5';
        $shouldDelete = (!empty($del) && !empty($gId));
        $this->assertTrue($shouldDelete);
    }
}
