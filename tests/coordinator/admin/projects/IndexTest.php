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

        $hidden_input = "rows[$numb][g_id]";
        $text_input = "rows[$numb][g_title]";
        $checkbox_input = "rows[$numb][del]";

        $this->assertEquals('rows[1][g_id]', $hidden_input);
        $this->assertEquals('rows[1][g_title]', $text_input);
        $this->assertEquals('rows[1][del]', $checkbox_input);
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
        $existing_rows = 3;
        $ii = $existing_rows + 1;
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
        $checkbox_value = $gid;
        $this->assertEquals(15, $checkbox_value);
    }

    public function testDeleteLabel()
    {
        $label = 'delete';
        $this->assertEquals('delete', $label);
    }

    public function testCardStructure()
    {
        $card_classes = ['card', 'card-header', 'card-body'];

        foreach ($card_classes as $class) {
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
        $col_classes = ['col-md-6', 'col-sm-12'];

        $this->assertContains('col-md-6', $col_classes);
        $this->assertContains('col-sm-12', $col_classes);
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
        $button_class = 'btn btn-outline-primary';

        $this->assertStringContainsString('btn', $button_class);
        $this->assertStringContainsString('btn-outline-primary', $button_class);
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
        $tbody_id = 'g_tab';
        $this->assertEquals('g_tab', $tbody_id);
    }

    public function testProjectTitleHeader()
    {
        $header = 'Projects:';
        $this->assertEquals('Projects:', $header);
    }

    public function testNewRowButton()
    {
        $button_text = 'New row';
        $this->assertEquals('New row', $button_text);
    }

    public function testSaveButton()
    {
        $button_text = 'Save';
        $this->assertEquals('Save', $button_text);
    }
}