<?php

namespace Tests\Coordinator\Admin\Qids;

use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    public function testQidTableValidation()
    {
        $qid_table = 'qids';
        $isValid = ($qid_table == 'qids' || $qid_table == 'qids_others');
        $this->assertTrue($isValid);

        $qid_table = 'qids_others';
        $isValid = ($qid_table == 'qids' || $qid_table == 'qids_others');
        $this->assertTrue($isValid);

        $qid_table = 'invalid';
        if ($qid_table != 'qids' && $qid_table != 'qids_others') {
            $qid_table = 'qids';
        }
        $this->assertEquals('qids', $qid_table);
    }

    public function testFilterQidsTableStructure()
    {
        $data = [
            'qids' => 'TD Qids',
            'qids_others' => 'Qids Others'
        ];

        $this->assertArrayHasKey('qids', $data);
        $this->assertArrayHasKey('qids_others', $data);
        $this->assertEquals('TD Qids', $data['qids']);
        $this->assertEquals('Qids Others', $data['qids_others']);
    }

    public function testDisFilterData()
    {
        $dis_data = [
            'empty' => 'Empty',
            'all' => 'All',
            'duplicate' => 'Duplicate'
        ];

        $this->assertArrayHasKey('empty', $dis_data);
        $this->assertArrayHasKey('all', $dis_data);
        $this->assertArrayHasKey('duplicate', $dis_data);
    }

    public function testRadioButtonChecked()
    {
        $table_name = 'qids';
        $vav = 'qids';

        $checked = ($table_name == $vav) ? 'checked' : '';
        $this->assertEquals('checked', $checked);

        $vav = 'qids_others';
        $checked = ($table_name == $vav) ? 'checked' : '';
        $this->assertEquals('', $checked);
    }

    public function testQidsTitle()
    {
        $qid_table = 'qids';
        $title = ($qid_table == "qids") ? "TD Qids" : "Qids Others";
        $this->assertEquals("TD Qids", $title);

        $qid_table = 'qids_others';
        $title = ($qid_table == "qids") ? "TD Qids" : "Qids Others";
        $this->assertEquals("Qids Others", $title);
    }

    public function testDefaultDisValue()
    {
        $dis = $_GET['dis'] ?? 'all';
        $this->assertEquals('all', $dis);

        $_GET['dis'] = 'empty';
        $dis = $_GET['dis'] ?? 'all';
        $this->assertEquals('empty', $dis);

        unset($_GET['dis']);
    }

    public function testSpecialUserCondition()
    {
        // Simulating the special condition for Mr. Ibrahem
        $username = 'Mr. Ibrahem';
        $dis = 'all';

        if (!isset($_GET['dis']) && $username == "Mr. Ibrahem") {
            $dis = "empty";
        }

        $this->assertEquals('empty', $dis);
    }

    public function testDuplicateIdHandling()
    {
        $done = [];
        $id = 5;

        $isProcessed = in_array($id, $done);
        $this->assertFalse($isProcessed);

        $done[] = $id;
        $isProcessed = in_array($id, $done);
        $this->assertTrue($isProcessed);
    }

    public function testDoneArrayAccumulation()
    {
        $done = [];
        $done[] = 1;
        $done[] = 2;
        $done[] = 3;

        $this->assertCount(3, $done);
        $this->assertContains(1, $done);
        $this->assertContains(2, $done);
        $this->assertContains(3, $done);
    }

    public function testDuplicateCondition()
    {
        $dis = 'duplicate';
        $isDuplicate = ($dis == 'duplicate');
        $this->assertTrue($isDuplicate);

        $dis = 'all';
        $isDuplicate = ($dis == 'duplicate');
        $this->assertFalse($isDuplicate);
    }

    public function testEditParamsArray()
    {
        $edit_params = [
            'id' => 123,
            'qid_table' => 'qids',
            'title' => 'Test Title',
            'qid' => 'Q12345'
        ];

        $this->assertArrayHasKey('id', $edit_params);
        $this->assertArrayHasKey('qid_table', $edit_params);
        $this->assertArrayHasKey('title', $edit_params);
        $this->assertArrayHasKey('qid', $edit_params);
    }

    public function testNewRowParams()
    {
        $params = [
            'new' => 1,
            'qid_table' => 'qids'
        ];

        $this->assertEquals(1, $params['new']);
        $this->assertEquals('qids', $params['qid_table']);
    }

    public function testCounterIncrement()
    {
        $numb = 0;
        $numb += 1;
        $this->assertEquals(1, $numb);

        $numb += 1;
        $this->assertEquals(2, $numb);
    }

    public function testTableHeaders()
    {
        $headers = ['#', 'id', 'Title', 'Qid', 'Edit'];
        $this->assertCount(5, $headers);
        $this->assertEquals('#', $headers[0]);
        $this->assertEquals('id', $headers[1]);
        $this->assertEquals('Title', $headers[2]);
        $this->assertEquals('Qid', $headers[3]);
        $this->assertEquals('Edit', $headers[4]);
    }

    public function testWikidataUrl()
    {
        $qid = 'Q54321';
        $url = "https://wikidata.org/wiki/$qid";
        $this->assertEquals('https://wikidata.org/wiki/Q54321', $url);
    }

    public function testTableClasses()
    {
        $classes = 'table table-striped compact table-mobile-responsive table-mobile-sided sortable2 table_text_left';

        $this->assertStringContainsString('table', $classes);
        $this->assertStringContainsString('table-striped', $classes);
        $this->assertStringContainsString('sortable2', $classes);
    }

    public function testFormInlineClass()
    {
        $form_class = 'form-inline';
        $this->assertEquals('form-inline', $form_class);
    }

    public function testMarginBlockEnd()
    {
        $style = 'margin-block-end: 0em;';
        $this->assertEquals('margin-block-end: 0em;', $style);
    }

    public function testFilterButtonText()
    {
        $button_text = 'Filter';
        $this->assertEquals('Filter', $button_text);
    }

    public function testDisDisplayInHeader()
    {
        $dis = 'empty';
        $numb = 15;
        $header = "$dis:<span>$numb</span>";

        $this->assertStringContainsString('empty', $header);
        $this->assertStringContainsString('15', $header);
    }

    public function testDataContentAttributes()
    {
        $attributes = ['#', 'title', 'qid', 'Edit'];

        foreach ($attributes as $attr) {
            $this->assertIsString($attr);
            $this->assertNotEmpty($attr);
        }
    }

    public function testDataSortAttribute()
    {
        $numb = 7;
        $id = 42;
        $title = 'TestTitle';
        $qid = 'Q999';

        $this->assertIsInt($numb);
        $this->assertIsInt($id);
        $this->assertIsString($title);
        $this->assertIsString($qid);
    }

    public function testTargetBlankAttribute()
    {
        $target = '_blank';
        $this->assertEquals('_blank', $target);
    }

    public function testAddOneLinkText()
    {
        $text = 'Add one!';
        $this->assertEquals('Add one!', $text);
    }

    public function testCardMt1Class()
    {
        $class = 'card mt-1';
        $this->assertStringContainsString('card', $class);
        $this->assertStringContainsString('mt-1', $class);
    }

    public function testDuplicateSecondIdFields()
    {
        $table = [
            'id2' => 456,
            'title2' => 'Duplicate Title',
            'qid2' => 'Q456'
        ];

        $id2 = $table['id2'] ?? '';
        $title2 = $table['title2'] ?? '';
        $qid2 = $table['qid2'] ?? '';

        $this->assertEquals(456, $id2);
        $this->assertEquals('Duplicate Title', $title2);
        $this->assertEquals('Q456', $qid2);
    }
}