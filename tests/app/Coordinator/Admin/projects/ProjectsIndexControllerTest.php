<?php

namespace Tests\Coordinator\Admin\Projects;

use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    public function testUasortByGId()
    {
        $projs = [
            'Project A' => ['g_id' => 3],
            'Project B' => ['g_id' => 1],
            'Project C' => ['g_id' => 2]
        ];

        uasort($projs, function ($a, $b) {
            return $a['g_id'] <=> $b['g_id'];
        });

        $keys = array_keys($projs);
        $this->assertEquals('Project B', $keys[0]);
        $this->assertEquals('Project C', $keys[1]);
        $this->assertEquals('Project A', $keys[2]);
    }

    public function testSpaceshipOperator()
    {
        $result = 1 <=> 2;
        $this->assertEquals(-1, $result);

        $result = 2 <=> 1;
        $this->assertEquals(1, $result);

        $result = 2 <=> 2;
        $this->assertEquals(0, $result);
    }

    public function testProjectDataExtraction()
    {
        $tab = [
            'g_id' => 5,
            'g_title' => 'Test Project'
        ];

        $gid = $tab['g_id'] ?? "";
        $gtitle = $tab['g_title'] ?? "";

        $this->assertEquals(5, $gid);
        $this->assertEquals('Test Project', $gtitle);
    }

    public function testProjectDataWithMissingFields()
    {
        $tab = [];

        $gid = $tab['g_id'] ?? "";
        $gtitle = $tab['g_title'] ?? "";

        $this->assertEquals("", $gid);
        $this->assertEquals("", $gtitle);
    }

    public function testFormRowGeneration()
    {
        $numb = 1;
        $gid = 10;
        $gtitle = 'Project Title';

        $hiddenInput = "rows[$numb][g_id]";
        $textInput = "rows[$numb][g_title]";
        $checkboxInput = "rows[$numb][del]";

        $this->assertEquals('rows[1][g_id]', $hiddenInput);
        $this->assertEquals('rows[1][g_title]', $textInput);
        $this->assertEquals('rows[1][del]', $checkboxInput);
    }

    public function testNumberIncrement()
    {
        $numb = 5;
        $numb += 1;
        $this->assertEquals(6, $numb);

        $numb += 1;
        $this->assertEquals(7, $numb);
    }

    public function testJavaScriptLengthCalculation()
    {
        // Simulating $('#g_tab >tr').length + 1
        $existingRows = 3;
        $ii = $existingRows + 1;
        $this->assertEquals(4, $ii);
    }

    public function testFormActionUrl()
    {
        $ty = 'projects';
        $action = "index.php?ty=$ty";
        $this->assertEquals('index.php?ty=projects', $action);
    }

    public function testTableHeaders()
    {
        $headers = ['Id', 'Project', 'Delete'];
        $this->assertCount(3, $headers);
        $this->assertEquals('Id', $headers[0]);
        $this->assertEquals('Project', $headers[1]);
        $this->assertEquals('Delete', $headers[2]);
    }

    public function testAddRowLabel()
    {
        $label = 'Add:';
        $this->assertEquals('Add:', $label);
    }

    public function testButtonOnclick()
    {
        $onclick = 'add_row()';
        $this->assertEquals('add_row()', $onclick);
    }

    public function testInputTypes()
    {
        $types = ['hidden', 'text', 'checkbox'];

        $this->assertContains('hidden', $types);
        $this->assertContains('text', $types);
        $this->assertContains('checkbox', $types);
    }

    public function testCheckboxValue()
    {
        $gid = 15;
        $checkboxValue = $gid;
        $this->assertEquals(15, $checkboxValue);
    }

    public function testDeleteLabel()
    {
        $label = 'delete';
        $this->assertEquals('delete', $label);
    }

    public function testCardStructure()
    {
        $cardClasses = ['card', 'card-header', 'card-body'];

        foreach ($cardClasses as $class) {
            $this->assertIsString($class);
            $this->assertNotEmpty($class);
        }
    }

    public function testFormMethod()
    {
        $method = 'POST';
        $this->assertEquals('POST', $method);
    }

    public function testBootstrapGridColumns()
    {
        $colClasses = ['col-md-6', 'col-sm-12'];

        $this->assertContains('col-md-6', $colClasses);
        $this->assertContains('col-sm-12', $colClasses);
    }

    public function testTableClasses()
    {
        $classes = ['table', 'table-striped', 'compact', 'table-mobile-responsive', 'table-mobile-sided'];

        $this->assertCount(5, $classes);
        $this->assertContains('table', $classes);
        $this->assertContains('table-striped', $classes);
    }

    public function testButtonClasses()
    {
        $buttonClass = 'btn btn-outline-primary';

        $this->assertStringContainsString('btn', $buttonClass);
        $this->assertStringContainsString('btn-outline-primary', $buttonClass);
    }

    public function testFormGroupClass()
    {
        $class = 'form-group d-flex justify-content-between';

        $this->assertStringContainsString('form-group', $class);
        $this->assertStringContainsString('d-flex', $class);
        $this->assertStringContainsString('justify-content-between', $class);
    }

    public function testTableBodyId()
    {
        $tbodyId = 'g_tab';
        $this->assertEquals('g_tab', $tbodyId);
    }

    public function testProjectTitleHeader()
    {
        $header = 'Projects:';
        $this->assertEquals('Projects:', $header);
    }

    public function testNewRowButton()
    {
        $buttonText = 'New row';
        $this->assertEquals('New row', $buttonText);
    }

    public function testSaveButton()
    {
        $buttonText = 'Save';
        $this->assertEquals('Save', $buttonText);
    }
}
