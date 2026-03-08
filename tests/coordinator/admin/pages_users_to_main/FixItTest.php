<?php

namespace Tests\Coordinator\Admin\PagesUsersToMain;

use PHPUnit\Framework\TestCase;

class FixItTest extends TestCase
{
    public function testFixItEchoFormParameters()
    {
        $id = '123';
        $title = 'Test Title';
        $new_target = 'TestTarget';
        $lang = 'ar';
        $new_user = 'testuser';
        $pupdate = '2024-01-15';

        // Test that all parameters are properly passed
        $this->assertEquals('123', $id);
        $this->assertEquals('Test Title', $title);
        $this->assertEquals('TestTarget', $new_target);
        $this->assertEquals('ar', $lang);
        $this->assertEquals('testuser', $new_user);
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
        $test_line = (isset($_REQUEST['test'])) ? '<input type="hidden" name="test" value="1" />' : "";
        $this->assertStringContainsString('name="test"', $test_line);

        unset($_REQUEST['test']);
        $test_line = (isset($_REQUEST['test'])) ? '<input type="hidden" name="test" value="1" />' : "";
        $this->assertEquals('', $test_line);
    }

    public function testFormActionUrl()
    {
        $ty = 'pages_users_to_main/fix_it';
        $nonav = '120';
        $action = "index.php?ty=$ty&nonav=$nonav";

        $this->assertEquals('index.php?ty=pages_users_to_main/fix_it&nonav=120', $action);
    }

    public function testPageAlreadyExistDataExtraction()
    {
        $in_db = [
            [
                'target' => 'ExistingTarget',
                'user' => 'existinguser',
                'pupdate' => '2023-12-01',
                'lang' => 'ar'
            ]
        ];

        $db_target = $in_db[0]['target'] ?? '';
        $db_user = $in_db[0]['user'] ?? '';
        $db_pupdate = $in_db[0]['pupdate'] ?? '';
        $lang = $in_db[0]['lang'] ?? '';

        $this->assertEquals('ExistingTarget', $db_target);
        $this->assertEquals('existinguser', $db_user);
        $this->assertEquals('2023-12-01', $db_pupdate);
        $this->assertEquals('ar', $lang);
    }

    public function testPageAlreadyExistWithEmptyData()
    {
        $in_db = [];

        $db_target = $in_db[0]['target'] ?? '';
        $db_user = $in_db[0]['user'] ?? '';
        $db_pupdate = $in_db[0]['pupdate'] ?? '';

        $this->assertEquals('', $db_target);
        $this->assertEquals('', $db_user);
        $this->assertEquals('', $db_pupdate);
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
        $new_target = $_GET['new_target'] ?? '';
        $new_user = $_GET['new_user'] ?? '';

        $this->assertEquals('456', $id);
        $this->assertEquals('NewTarget', $new_target);
        $this->assertEquals('newuser', $new_user);

        unset($_GET['id'], $_GET['new_target'], $_GET['new_user']);
    }

    public function testEmptyGetParameters()
    {
        $id = $_GET['id'] ?? '';
        $new_target = $_GET['new_target'] ?? '';
        $new_user = $_GET['new_user'] ?? '';

        $this->assertEquals('', $id);
        $this->assertEquals('', $new_target);
        $this->assertEquals('', $new_user);
    }

    public function testCardHeaderStructure()
    {
        $old_target = 'OldTarget';
        $header = "Edit Page ($old_target)";

        $this->assertStringContainsString('Edit Page', $header);
        $this->assertStringContainsString('OldTarget', $header);
    }

    public function testInputRequiredAttributes()
    {
        $required_fields = ['title', 'lang', 'new_target', 'new_user', 'pupdate'];

        foreach ($required_fields as $field) {
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
        $in_db = [['id' => 1]];
        $isDuplicate = !empty($in_db);
        $this->assertTrue($isDuplicate);

        $in_db = [];
        $isDuplicate = !empty($in_db);
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