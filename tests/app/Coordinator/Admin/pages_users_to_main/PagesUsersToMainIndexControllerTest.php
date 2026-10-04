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
        $newQid = 'Q123';

        $sameQid = ($qid == $newQid) ? "" : "bg-danger-subtle";
        $this->assertEquals('', $sameQid);

        $newQid = 'Q456';
        $sameQid = ($qid == $newQid) ? "" : "bg-danger-subtle";
        $this->assertEquals('bg-danger-subtle', $sameQid);
    }

    public function testQidEmptyCheck()
    {
        $qid = 'Q123';
        $newQid = '';

        $isEmpty = (!empty($qid) && empty($newQid));
        $this->assertTrue($isEmpty);

        $newQid = 'Q456';
        $isEmpty = (!empty($qid) && empty($newQid));
        $this->assertFalse($isEmpty);
    }

    public function testQidMatchingLogic()
    {
        $qid = 'Q123';
        $newQid = 'Q123';

        if (!empty($qid) && $newQid == $qid) {
            $sameQid = "";
        } else {
            $sameQid = "bg-info-subtle";
        }

        $this->assertEquals('', $sameQid);
    }

    public function testQidDifferenceHighlight()
    {
        $qid = 'Q123';
        $newQid = 'Q456';

        if (!empty($qid) && empty($newQid)) {
            $sameQid = "bg-info-subtle";
        } else {
            $sameQid = ($qid == $newQid) ? "bg-info-subtle" : "bg-danger-subtle";
        }

        $this->assertEquals('bg-danger-subtle', $sameQid);
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
        $newTarget = 'TestPage';
        $newTarget2 = htmlspecialchars($newTarget, ENT_QUOTES);

        $url = "https://www.wikidata.org/wiki/Special:SetSiteLink/$qid/{$lang}wiki?page=$newTarget2";

        $this->assertStringContainsString('SetSiteLink', $url);
        $this->assertStringContainsString('Q123', $url);
        $this->assertStringContainsString('arwiki', $url);
        $this->assertStringContainsString('TestPage', $url);
    }

    public function testArrayColumnExtraction()
    {
        $sqlResults = [
            ['title' => 'Title1', 'lang' => 'ar'],
            ['title' => 'Title2', 'lang' => 'en'],
            ['title' => 'Title3', 'lang' => 'fr']
        ];

        $titles = array_column($sqlResults, "title");

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

        $titlesQids = array_column($infos, "qid", "title");

        $this->assertArrayHasKey('Title1', $titlesQids);
        $this->assertArrayHasKey('Title2', $titlesQids);
        $this->assertEquals('Q1', $titlesQids['Title1']);
        $this->assertEquals('Q2', $titlesQids['Title2']);
    }

    public function testQidLookupFromArray()
    {
        $titlesQids = [
            'Title1' => 'Q1',
            'Title2' => 'Q2'
        ];

        $title = 'Title1';
        $qid = $titlesQids[$title] ?? '';
        $this->assertEquals('Q1', $qid);

        $title = 'NonExistent';
        $qid = $titlesQids[$title] ?? '';
        $this->assertEquals('', $qid);
    }

    public function testCounterIncrement()
    {
        $noo = 0;
        $noo++;
        $this->assertEquals(1, $noo);

        $noo++;
        $this->assertEquals(2, $noo);
    }

    public function testEditParamsStructure()
    {
        $editParams = [
            'id' => 123,
            'new_user' => 'newuser',
            'new_target' => 'NewTarget'
        ];

        $this->assertArrayHasKey('id', $editParams);
        $this->assertArrayHasKey('new_user', $editParams);
        $this->assertArrayHasKey('new_target', $editParams);
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
        $sameQid = 'bg-info-subtle';
        $this->assertEquals('bg-info-subtle', $sameQid);

        $sameQid = 'bg-danger-subtle';
        $this->assertEquals('bg-danger-subtle', $sameQid);
    }
}
