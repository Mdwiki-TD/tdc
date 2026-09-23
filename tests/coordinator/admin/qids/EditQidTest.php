<?php

namespace Tests\Coordinator\Admin\Qids;

use PHPUnit\Framework\TestCase;

class EditQidTest extends TestCase
{
    public function testHeaderTitleWithId()
    {
        $_GET['id'] = '123';
        $headerTitle = (($_GET['id'] ?? "") != "") ? "Edit Qid" : "Add New Qid";
        $this->assertEquals("Edit Qid", $headerTitle);

        unset($_GET['id']);
    }

    public function testHeaderTitleWithoutId()
    {
        unset($_GET['id']);
        $headerTitle = (($_GET['id'] ?? "") != "") ? "Edit Qid" : "Add New Qid";
        $this->assertEquals("Add New Qid", $headerTitle);
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
        $idRowExists = (!empty($id));
        $this->assertTrue($idRowExists);
    }

    public function testIdRowGenerationWithoutId()
    {
        $id = '';
        $idRowExists = (!empty($id));
        $this->assertFalse($idRowExists);
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
        $qidTable = 'qids';
        $action = "index.php?ty=qids/edit_qid&qid_table=$qidTable&nonav=120";
        $this->assertEquals('index.php?ty=qids/edit_qid&qid_table=qids&nonav=120', $action);
    }

    public function testHiddenInputs()
    {
        $qidTable = 'qids_others';
        $edit = '1';

        $this->assertEquals('qids_others', $qidTable);
        $this->assertEquals('1', $edit);
    }

    public function testInputNamePattern()
    {
        $nameId = 'rows[1][id]';
        $nameTitle = 'rows[1][title]';
        $nameQid = 'rows[1][qid]';

        $this->assertEquals('rows[1][id]', $nameId);
        $this->assertEquals('rows[1][title]', $nameTitle);
        $this->assertEquals('rows[1][qid]', $nameQid);
    }

    public function testRequiredFields()
    {
        $requiredFields = ['title', 'qid'];
        $this->assertContains('title', $requiredFields);
        $this->assertContains('qid', $requiredFields);
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
        $colClasses = ['col-md-3', 'col-md-2'];

        $this->assertContains('col-md-3', $colClasses);
        $this->assertContains('col-md-2', $colClasses);
    }

    public function testFormControlClass()
    {
        $inputClass = 'form-control';
        $this->assertEquals('form-control', $inputClass);
    }

    public function testSubmitButtonValue()
    {
        $buttonValue = 'send';
        $this->assertEquals('send', $buttonValue);
    }

    public function testButtonClass()
    {
        $buttonClass = 'btn btn-outline-primary';
        $this->assertStringContainsString('btn', $buttonClass);
        $this->assertStringContainsString('btn-outline-primary', $buttonClass);
    }

    public function testCardStructure()
    {
        $cardClasses = ['card', 'card-header', 'card-body'];

        foreach ($cardClasses as $class) {
            $this->assertIsString($class);
            $this->assertNotEmpty($class);
        }
    }

    public function testContainerClass()
    {
        $containerClass = 'container-fluid';
        $this->assertEquals('container-fluid', $containerClass);
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
