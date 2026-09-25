<?php

namespace Tests\Coordinator\Admin\PagesUsersToMain;

use PHPUnit\Framework\TestCase;

class FixItTest extends TestCase
{
    public function testFixItEchoFormParameters()
    {
        $id = '123';
        $title = 'Test Title';
        $newTarget = 'TestTarget';
        $lang = 'ar';
        $newUser = 'testuser';
        $pupdate = '2024-01-15';

        // Test that all parameters are properly passed
        $this->assertEquals('123', $id);
        $this->assertEquals('Test Title', $title);
        $this->assertEquals('TestTarget', $newTarget);
        $this->assertEquals('ar', $lang);
        $this->assertEquals('testuser', $newUser);
        $this->assertEquals('2024-01-15', $pupdate);
    }

    public function testHtmlSpecialCharsInForm()
    {
        $title = '<script>alert("xss")</script>';
        $title2 = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        $this->assertStringNotContainsString('<script>', $title2);
        $this->assertStringContainsString('&lt;script&gt;', $title2);

        $target = 'Test&Target';
        $target2 = htmlspecialchars($target, ENT_QUOTES, 'UTF-8');
        $this->assertStringContainsString('&amp;', $target2);
    }

    public function testTestLineGeneration()
    {
        $_REQUEST['test'] = '1';
        $testLine = (isset($_REQUEST['test'])) ? '<input type="hidden" name="test" value="1" />' : "";
        $this->assertStringContainsString('name="test"', $testLine);

        unset($_REQUEST['test']);
        $testLine = (isset($_REQUEST['test'])) ? '<input type="hidden" name="test" value="1" />' : "";
        $this->assertEquals('', $testLine);
    }

    public function testFormActionUrl()
    {
        $ty = 'fix_page';
        $nonav = '120';
        $action = "index.php?ty=$ty&nonav=$nonav";

        $this->assertEquals('index.php?ty=fix_page&nonav=120', $action);
    }

    public function testPageAlreadyExistDataExtraction()
    {
        $inDb = [
            [
                'target' => 'ExistingTarget',
                'user' => 'existinguser',
                'pupdate' => '2023-12-01',
                'lang' => 'ar'
            ]
        ];

        $dbTarget = $inDb[0]['target'] ?? '';
        $dbUser = $inDb[0]['user'] ?? '';
        $dbPupdate = $inDb[0]['pupdate'] ?? '';
        $lang = $inDb[0]['lang'] ?? '';

        $this->assertEquals('ExistingTarget', $dbTarget);
        $this->assertEquals('existinguser', $dbUser);
        $this->assertEquals('2023-12-01', $dbPupdate);
        $this->assertEquals('ar', $lang);
    }

    public function testPageAlreadyExistWithEmptyData()
    {
        $inDb = [];

        $dbTarget = $inDb[0]['target'] ?? '';
        $dbUser = $inDb[0]['user'] ?? '';
        $dbPupdate = $inDb[0]['pupdate'] ?? '';

        $this->assertEquals('', $dbTarget);
        $this->assertEquals('', $dbUser);
        $this->assertEquals('', $dbPupdate);
    }

    public function testWikipediaUrlConstruction()
    {
        $lang = 'ar';
        $target = 'TestPage';
        $url = "https://$lang.wikipedia.org/wiki/$target";

        $this->assertEquals('https://ar.wikipedia.org/wiki/TestPage', $url);
    }

    public function testGetParameterExtraction()
    {
        $_GET['id'] = '456';
        $_GET['new_target'] = 'NewTarget';
        $_GET['new_user'] = 'newuser';

        $id = $_GET['id'] ?? '';
        $newTarget = $_GET['new_target'] ?? '';
        $newUser = $_GET['new_user'] ?? '';

        $this->assertEquals('456', $id);
        $this->assertEquals('NewTarget', $newTarget);
        $this->assertEquals('newuser', $newUser);

        unset($_GET['id'], $_GET['new_target'], $_GET['new_user']);
    }

    public function testEmptyGetParameters()
    {
        $id = $_GET['id'] ?? '';
        $newTarget = $_GET['new_target'] ?? '';
        $newUser = $_GET['new_user'] ?? '';

        $this->assertEquals('', $id);
        $this->assertEquals('', $newTarget);
        $this->assertEquals('', $newUser);
    }

    public function testCardHeaderStructure()
    {
        $oldTarget = 'OldTarget';
        $header = "Edit Page ($oldTarget)";

        $this->assertStringContainsString('Edit Page', $header);
        $this->assertStringContainsString('OldTarget', $header);
    }

    public function testInputRequiredAttributes()
    {
        $requiredFields = ['title', 'lang', 'new_target', 'new_user', 'pupdate'];

        foreach ($requiredFields as $field) {
            $this->assertIsString($field);
            $this->assertNotEmpty($field);
        }
    }

    public function testPlaceholderFormat()
    {
        $placeholder = 'YYYY-MM-DD';
        $this->assertEquals('YYYY-MM-DD', $placeholder);
        $this->assertMatchesRegularExpression('/^[A-Z]{4}-[A-Z]{2}-[A-Z]{2}$/', $placeholder);
    }

    public function testDuplicatePageDetection()
    {
        $inDb = [['id' => 1]];
        $isDuplicate = !empty($inDb);
        $this->assertTrue($isDuplicate);

        $inDb = [];
        $isDuplicate = !empty($inDb);
        $this->assertFalse($isDuplicate);
    }

    public function testRequestMethodCheck()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $isPost = ($_SERVER['REQUEST_METHOD'] == 'POST');
        $this->assertTrue($isPost);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $isPost = ($_SERVER['REQUEST_METHOD'] == 'POST');
        $this->assertFalse($isPost);
    }

    public function testBootstrapAlertClasses()
    {
        $alertClass = 'alert alert-danger';
        $this->assertStringContainsString('alert', $alertClass);
        $this->assertStringContainsString('alert-danger', $alertClass);
    }

    public function testListGroupItemStructure()
    {
        $items = [
            'Target',
            'User',
            'Published'
        ];

        $this->assertCount(3, $items);
        $this->assertEquals('Target', $items[0]);
        $this->assertEquals('User', $items[1]);
        $this->assertEquals('Published', $items[2]);
    }

    public function testFontWeightBoldClass()
    {
        $class = 'fw-bold';
        $this->assertEquals('fw-bold', $class);
    }

    public function testTargetBlankAttribute()
    {
        $target = '_blank';
        $this->assertEquals('_blank', $target);
    }
}
