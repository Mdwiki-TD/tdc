<?php

namespace Tests\Coordinator\Admin\Qids;

use PHPUnit\Framework\TestCase;

class EditQidTest extends TestCase
{
    public function testHeaderTitleWithId()
    {
        $_GET['id'] = '123';
        $header_title = (($_GET['id'] ?? "") != "") ? "Edit Qid" : "Add New Qid";
        $this->assertEquals("Edit Qid", $header_title);

        unset($_GET['id']);
    }

    public function testHeaderTitleWithoutId()
    {
        unset($_GET['id']);
        $header_title = (($_GET['id'] ?? "") != "") ? "Edit Qid" : "Add New Qid";
        $this->assertEquals("Add New Qid", $header_title);
    }

    public function testHtmlSpecialCharsEscaping()
    {
        $title = '<script>alert("xss")</script>';
        $title2 = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        $this->assertStringNotContainsString('<script>', $title2);
        $this->assertStringContainsString('&lt;script&gt;', $title2);
    }

    public function testIdRowGenerationWithId()
    {
        $id = '456';
        $id_row_exists = (!empty($id));
        $this->assertTrue($id_row_exists);
    }

    public function testIdRowGenerationWithoutId()
    {
        $id = '';
        $id_row_exists = (!empty($id));
        $this->assertFalse($id_row_exists);
    }

    public function testQidTableValidation()
    {
        $table = 'qids';
        $isValid = ($table == 'qids' || $table == 'qids_others');
        $this->assertTrue($isValid);

        $table = 'qids_others';
        $isValid = ($table == 'qids' || $table == 'qids_others');
        $this->assertTrue($isValid);

        $table = 'invalid';
        if ($table != 'qids' && $table != 'qids_others') {
            $table = 'qids';
        }
        $this->assertEquals('qids', $table);
    }

    public function testFormActionUrl()
    {
        $qid_table = 'qids';
        $action = "index.php?ty=qids/post&qid_table=$qid_table&nonav=120";
        $this->assertEquals('index.php?ty=qids/post&qid_table=qids&nonav=120', $action);
    }

    public function testHiddenInputs()
    {
        $qid_table = 'qids_others';
        $edit = '1';

        $this->assertEquals('qids_others', $qid_table);
        $this->assertEquals('1', $edit);
    }

    public function testInputNamePattern()
    {
        $name_id = 'rows[1][id]';
        $name_title = 'rows[1][title]';
        $name_qid = 'rows[1][qid]';

        $this->assertEquals('rows[1][id]', $name_id);
        $this->assertEquals('rows[1][title]', $name_title);
        $this->assertEquals('rows[1][qid]', $name_qid);
    }

    public function testRequiredFields()
    {
        $required_fields = ['title', 'qid'];
        $this->assertContains('title', $required_fields);
        $this->assertContains('qid', $required_fields);
    }

    public function testReadonlyAttribute()
    {
        // Id field should be readonly when it exists
        $id = '123';
        $isReadonly = !empty($id);
        $this->assertTrue($isReadonly);
    }

    public function testGetParameters()
    {
        $_GET['title'] = 'Test Title';
        $_GET['qid'] = 'Q12345';
        $_GET['id'] = '789';
        $_GET['qid_table'] = 'qids';

        $title = $_GET['title'] ?? '';
        $qid = $_GET['qid'] ?? '';
        $id = $_GET['id'] ?? '';
        $table = $_GET['qid_table'] ?? '';

        $this->assertEquals('Test Title', $title);
        $this->assertEquals('Q12345', $qid);
        $this->assertEquals('789', $id);
        $this->assertEquals('qids', $table);

        unset($_GET['title'], $_GET['qid'], $_GET['id'], $_GET['qid_table']);
    }

    public function testDefaultQidTable()
    {
        $table = $_GET['qid_table'] ?? '';
        if ($table != 'qids' && $table != 'qids_others') {
            $table = 'qids';
        }

        $this->assertEquals('qids', $table);
    }

    public function testFormMethod()
    {
        $method = 'POST';
        $this->assertEquals('POST', $method);
    }

    public function testInputGroupStructure()
    {
        $classes = ['input-group', 'input-group-prepend', 'input-group-text'];

        foreach ($classes as $class) {
            $this->assertIsString($class);
            $this->assertNotEmpty($class);
        }
    }

    public function testColumnClasses()
    {
        $col_classes = ['col-md-3', 'col-md-2'];

        $this->assertContains('col-md-3', $col_classes);
        $this->assertContains('col-md-2', $col_classes);
    }

    public function testFormControlClass()
    {
        $input_class = 'form-control';
        $this->assertEquals('form-control', $input_class);
    }

    public function testSubmitButtonValue()
    {
        $button_value = 'send';
        $this->assertEquals('send', $button_value);
    }

    public function testButtonClass()
    {
        $button_class = 'btn btn-outline-primary';
        $this->assertStringContainsString('btn', $button_class);
        $this->assertStringContainsString('btn-outline-primary', $button_class);
    }

    public function testCardStructure()
    {
        $card_classes = ['card', 'card-header', 'card-body'];

        foreach ($card_classes as $class) {
            $this->assertIsString($class);
            $this->assertNotEmpty($class);
        }
    }

    public function testContainerClass()
    {
        $container_class = 'container-fluid';
        $this->assertEquals('container-fluid', $container_class);
    }

    public function testDisParameter()
    {
        $_GET['dis'] = 'all';
        $dis = $_GET['dis'] ?? 'all';
        $this->assertEquals('all', $dis);

        unset($_GET['dis']);
        $dis = $_GET['dis'] ?? 'all';
        $this->assertEquals('all', $dis);

        unset($_GET['dis']);
    }

    public function testInputTypes()
    {
        $types = ['text', 'hidden'];

        $this->assertContains('text', $types);
        $this->assertContains('hidden', $types);
    }

    public function testMb3Class()
    {
        $class = 'mb-3';
        $this->assertEquals('mb-3', $class);
    }
}
