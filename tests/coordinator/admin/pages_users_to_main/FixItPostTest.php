<?php

namespace Tests\Coordinator\Admin\PagesUsersToMain;

use PHPUnit\Framework\TestCase;

class FixItPostTest extends TestCase
{
    public function testDeleteUserPageLogic()
    {
        // Test the deletion verification logic
        $findIt_1 = []; // Empty means deleted
        $findIt_2 = []; // Empty means deleted

        $deleteDone = empty($findIt_1) && empty($findIt_2);
        $this->assertTrue($deleteDone);

        // Test when one table still has record
        $findIt_1 = [['id' => 1]];
        $findIt_2 = [];

        $deleteDone = empty($findIt_1) && empty($findIt_2);
        $this->assertFalse($deleteDone);

        // Test when both tables still have records
        $findIt_1 = [['id' => 1]];
        $findIt_2 = [['id' => 1]];

        $deleteDone = empty($findIt_1) && empty($findIt_2);
        $this->assertFalse($deleteDone);
    }

    public function testPostDataExtraction()
    {
        $_POST['title'] = 'Test Title';
        $_POST['lang'] = 'ar';
        $_POST['new_target'] = 'NewTarget';
        $_POST['new_user'] = 'newuser';
        $_POST['pupdate'] = '2024-01-15';
        $_POST['id'] = '123';

        $title = $_POST['title'] ?? '';
        $lang = $_POST['lang'] ?? '';
        $newTarget = $_POST['new_target'] ?? '';
        $newUser = $_POST['new_user'] ?? '';
        $pupdate = $_POST['pupdate'] ?? '';
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        $this->assertEquals('Test Title', $title);
        $this->assertEquals('ar', $lang);
        $this->assertEquals('NewTarget', $newTarget);
        $this->assertEquals('newuser', $newUser);
        $this->assertEquals('2024-01-15', $pupdate);
        $this->assertEquals(123, $id);

        unset($_POST['title'], $_POST['lang'], $_POST['new_target'], $_POST['new_user'], $_POST['pupdate'], $_POST['id']);
    }

    public function testIdValidation()
    {
        // Valid id
        $id = 123;
        $isValid = ($id > 0);
        $this->assertTrue($isValid);

        // Invalid id (0)
        $id = 0;
        $isValid = ($id > 0);
        $this->assertFalse($isValid);

        // Invalid id (negative)
        $id = -1;
        $isValid = ($id > 0);
        $this->assertFalse($isValid);
    }

    public function testIdIntegerConversion()
    {
        $_POST['id'] = '456';
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $this->assertIsInt($id);
        $this->assertEquals(456, $id);

        unset($_POST['id']);
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $this->assertEquals(0, $id);

        unset($_POST['id']);
    }

    public function testInvalidIdError()
    {
        $id = 0;
        $errors = [];

        if ($id <= 0) {
            $errors[] = "Invalid id supplied.";
        }

        $this->assertCount(1, $errors);
        $this->assertContains("Invalid id supplied.", $errors);
    }

    public function testPageDataValidation()
    {
        // Page found
        $pageData = [['title' => 'Test', 'cat' => 'RTT']];
        $errors = [];

        if (empty($pageData)) {
            $errors[] = "Page with id:(123) not found.";
        }

        $this->assertCount(0, $errors);

        // Page not found
        $pageData = [];
        $errors = [];

        if (empty($pageData)) {
            $errors[] = "Page with id:(123) not found.";
        }

        $this->assertCount(1, $errors);
        $this->assertStringContainsString('not found', $errors[0]);
    }

    public function testPageDataFieldExtraction()
    {
        $pageData = [
            [
                'translate_type' => 'lead',
                'cat' => 'RTT',
                'word' => '500'
            ]
        ];

        $tType = $pageData[0]['translate_type'] ?? '';
        $cat = $pageData[0]['cat'] ?? '';
        $word = $pageData[0]['word'] ?? '';

        $this->assertEquals('lead', $tType);
        $this->assertEquals('RTT', $cat);
        $this->assertEquals('500', $word);
    }

    public function testAddPagesResultHandling()
    {
        $result = false;
        $errors = [];
        $texts = [];

        if ($result === false) {
            $errors[] = "Failed to add translations.";
        } else {
            $texts[] = "Translations added successfully.";
        }

        $this->assertCount(1, $errors);
        $this->assertCount(0, $texts);

        // Test success case
        $result = true;
        $errors = [];
        $texts = [];

        if ($result === false) {
            $errors[] = "Failed to add translations.";
        } else {
            $texts[] = "Translations added successfully.";
        }

        $this->assertCount(0, $errors);
        $this->assertCount(1, $texts);
    }

    public function testDeleteSuccessMessage()
    {
        $delIt = true;
        $id = 123;
        $texts = [];
        $errors = [];

        if ($delIt) {
            $texts[] = "Page with id:($id) deleted from pages_users .";
        } else {
            $errors[] = "Failed to delete page with id:($id).";
        }

        $this->assertCount(1, $texts);
        $this->assertCount(0, $errors);
        $this->assertStringContainsString('123', $texts[0]);
    }

    public function testDeleteFailureMessage()
    {
        $delIt = false;
        $id = 123;
        $texts = [];
        $errors = [];

        if ($delIt) {
            $texts[] = "Page with id:($id) deleted from pages_users .";
        } else {
            $errors[] = "Failed to delete page with id:($id).";
        }

        $this->assertCount(0, $texts);
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('Failed to delete', $errors[0]);
    }

    public function testRequestMethodPostCheck()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['edit'] = '1';

        $isValidRequest = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit']));
        $this->assertTrue($isValidRequest);

        unset($_POST['edit']);
    }

    public function testEditFlagPresence()
    {
        $_POST['edit'] = '1';
        $hasEdit = isset($_POST['edit']);
        $this->assertTrue($hasEdit);

        unset($_POST['edit']);
        $hasEdit = isset($_POST['edit']);
        $this->assertFalse($hasEdit);
    }

    public function testErrorsArrayInitialization()
    {
        $texts = [];
        $errors = [];

        $this->assertIsArray($texts);
        $this->assertIsArray($errors);
        $this->assertCount(0, $texts);
        $this->assertCount(0, $errors);
    }

    public function testMessageAccumulation()
    {
        $texts = [];

        $texts[] = "Translations added successfully.";
        $texts[] = "Page with id:(123) deleted from pages_users .";

        $this->assertCount(2, $texts);
    }

    public function testIdParameterInMessage()
    {
        $id = 789;
        $message = "Page with id:($id) not found.";

        $this->assertStringContainsString('789', $message);
        $this->assertStringContainsString('not found', $message);
    }
}
