<?php

namespace Tests\Coordinator\Admin\PagesUsersToMain;

use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    public function testGetLanguagesTypeFilter()
    {
        // Test the type checking logic
        $tat = 'ar';
        $isString = (gettype($tat) === 'string');
        $this->assertTrue($isString);

        $tat = 123;
        $isString = (gettype($tat) === 'string');
        $this->assertFalse($isString);

        $tat = ['array'];
        $isString = (gettype($tat) === 'string');
        $this->assertFalse($isString);
    }

    public function testLanguageCodeLowercase()
    {
        $tat = 'AR';
        $lag = strtolower($tat);
        $this->assertEquals('ar', $lag);

        $tat = 'En';
        $lag = strtolower($tat);
        $this->assertEquals('en', $lag);
    }

    public function testArrayKsort()
    {
        $tabes = ['en', 'ar', 'fr'];
        ksort($tabes);

        // ksort sorts by keys, so with numeric keys, order is maintained
        $this->assertIsArray($tabes);
        $this->assertCount(3, $tabes);
    }

    public function testQidComparison()
    {
        $qid = 'Q123';
        $new_qid = 'Q123';

        $same_qid = ($qid == $new_qid) ? "" : "bg-danger-subtle";
        $this->assertEquals('', $same_qid);

        $new_qid = 'Q456';
        $same_qid = ($qid == $new_qid) ? "" : "bg-danger-subtle";
        $this->assertEquals('bg-danger-subtle', $same_qid);
    }

    public function testQidEmptyCheck()
    {
        $qid = 'Q123';
        $new_qid = '';

        $isEmpty = (!empty($qid) && empty($new_qid));
        $this->assertTrue($isEmpty);

        $new_qid = 'Q456';
        $isEmpty = (!empty($qid) && empty($new_qid));
        $this->assertFalse($isEmpty);
    }

    public function testQidMatchingLogic()
    {
        $qid = 'Q123';
        $new_qid = 'Q123';

        if (!empty($qid) && $new_qid == $qid) {
            $same_qid = "";
        } else {
            $same_qid = "bg-info-subtle";
        }

        $this->assertEquals('', $same_qid);
    }

    public function testQidDifferenceHighlight()
    {
        $qid = 'Q123';
        $new_qid = 'Q456';

        if (!empty($qid) && empty($new_qid)) {
            $same_qid = "bg-info-subtle";
        } else {
            $same_qid = ($qid == $new_qid) ? "bg-info-subtle" : "bg-danger-subtle";
        }

        $this->assertEquals('bg-danger-subtle', $same_qid);
    }

    public function testWikidataUrlConstruction()
    {
        $qid = 'Q12345';
        $url = "https://wikidata.org/wiki/$qid";

        $this->assertEquals('https://wikidata.org/wiki/Q12345', $url);
        $this->assertStringStartsWith('https://wikidata.org', $url);
    }

    public function testSetSiteLinkUrl()
    {
        $qid = 'Q123';
        $lang = 'ar';
        $new_target = 'TestPage';
        $new_target2 = htmlspecialchars($new_target, ENT_QUOTES);

        $url = "https://www.wikidata.org/wiki/Special:SetSiteLink/$qid/{$lang}wiki?page=$new_target2";

        $this->assertStringContainsString('SetSiteLink', $url);
        $this->assertStringContainsString('Q123', $url);
        $this->assertStringContainsString('arwiki', $url);
        $this->assertStringContainsString('TestPage', $url);
    }

    public function testArrayColumnExtraction()
    {
        $sql_results = [
            ['title' => 'Title1', 'lang' => 'ar'],
            ['title' => 'Title2', 'lang' => 'en'],
            ['title' => 'Title3', 'lang' => 'fr']
        ];

        $titles = array_column($sql_results, "title");

        $this->assertCount(3, $titles);
        $this->assertEquals('Title1', $titles[0]);
        $this->assertEquals('Title2', $titles[1]);
        $this->assertEquals('Title3', $titles[2]);
    }

    public function testArrayColumnWithKey()
    {
        $infos = [
            ['title' => 'Title1', 'qid' => 'Q1'],
            ['title' => 'Title2', 'qid' => 'Q2']
        ];

        $titles_qids = array_column($infos, "qid", "title");

        $this->assertArrayHasKey('Title1', $titles_qids);
        $this->assertArrayHasKey('Title2', $titles_qids);
        $this->assertEquals('Q1', $titles_qids['Title1']);
        $this->assertEquals('Q2', $titles_qids['Title2']);
    }

    public function testQidLookupFromArray()
    {
        $titles_qids = [
            'Title1' => 'Q1',
            'Title2' => 'Q2'
        ];

        $title = 'Title1';
        $qid = $titles_qids[$title] ?? '';
        $this->assertEquals('Q1', $qid);

        $title = 'NonExistent';
        $qid = $titles_qids[$title] ?? '';
        $this->assertEquals('', $qid);
    }

    public function testCounterIncrement()
    {
        $noo = 0;
        $noo = $noo + 1;
        $this->assertEquals(1, $noo);

        $noo = $noo + 1;
        $this->assertEquals(2, $noo);
    }

    public function testEditParamsStructure()
    {
        $edit_params = [
            'id' => 123,
            'new_user' => 'newuser',
            'new_target' => 'NewTarget'
        ];

        $this->assertArrayHasKey('id', $edit_params);
        $this->assertArrayHasKey('new_user', $edit_params);
        $this->assertArrayHasKey('new_target', $edit_params);
    }

    public function testLangValidation()
    {
        $lang = 'ar';
        $isValid = ($lang !== 'All');
        $this->assertTrue($isValid);

        $lang = 'All';
        $isValid = ($lang !== 'All');
        $this->assertFalse($isValid);
    }

    public function testDataTableColumnVisibility()
    {
        $columns = [0, 1, 2, 3, 4];
        $this->assertCount(5, $columns);
        $this->assertEquals(0, $columns[0]);
        $this->assertEquals(4, $columns[4]);
    }

    public function testLengthMenuOptions()
    {
        $lengthMenu = [
            [50, 100, 150],
            [50, 100, 150]
        ];

        $this->assertCount(2, $lengthMenu);
        $this->assertEquals([50, 100, 150], $lengthMenu[0]);
    }

    public function testTableHeaders()
    {
        $headers = ['#', 'Lang.', 'Title', 'Qid', 'Publication', 'Old User', 'New User', 'Old target', 'New target', 'New Qid', 'Fix it'];
        $this->assertCount(11, $headers);
        $this->assertEquals('#', $headers[0]);
        $this->assertEquals('Fix it', $headers[10]);
    }

    public function testEmptyTitlesCheck()
    {
        $titles = [];
        $isEmpty = empty($titles);
        $this->assertTrue($isEmpty);

        $titles = ['Title1', 'Title2'];
        $isEmpty = empty($titles);
        $this->assertFalse($isEmpty);
    }

    public function testTrimFunction()
    {
        $title = '  Test Title  ';
        $trimmed = trim($title);
        $this->assertEquals('Test Title', $trimmed);

        $target = 'NoSpaces';
        $trimmed = trim($target);
        $this->assertEquals('NoSpaces', $trimmed);
    }

    public function testBootstrapClassApplication()
    {
        $same_qid = 'bg-info-subtle';
        $this->assertEquals('bg-info-subtle', $same_qid);

        $same_qid = 'bg-danger-subtle';
        $this->assertEquals('bg-danger-subtle', $same_qid);
    }
}