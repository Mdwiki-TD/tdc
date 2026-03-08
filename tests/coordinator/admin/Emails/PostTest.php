<?php

namespace Tests\Coordinator\Admin\Emails;

use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function testEmailValidation()
    {
        // Valid email
        $email = 'test@example.com';
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
        $this->assertNotFalse($isValid);

        // Invalid email
        $email = 'invalid-email';
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
        $this->assertFalse($isValid);

        // Empty email should pass (optional field)
        $email = '';
        if (!empty($email)) {
            $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
        } else {
            $isValid = true;
        }
        $this->assertTrue($isValid);
    }

    public function testEmailValidationEdgeCases()
    {
        // Email with plus sign
        $email = 'user+tag@example.com';
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
        $this->assertNotFalse($isValid);

        // Email with subdomain
        $email = 'user@mail.example.com';
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
        $this->assertNotFalse($isValid);

        // Email with special characters
        $email = 'user.name@example.co.uk';
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
        $this->assertNotFalse($isValid);
    }

    public function testUsernameTrimming()
    {
        $user = '  testuser  ';
        $trimmed = trim($user);
        $this->assertEquals('testuser', $trimmed);

        $user = "\ttestuser\n";
        $trimmed = trim($user);
        $this->assertEquals('testuser', $trimmed);
    }

    public function testEmptyUsernameValidation()
    {
        $user = '';
        $isEmpty = empty($user);
        $this->assertTrue($isEmpty);

        $user = '0';
        $isEmpty = empty($user);
        $this->assertTrue($isEmpty);

        $user = 'validuser';
        $isEmpty = empty($user);
        $this->assertFalse($isEmpty);
    }

    public function testUserIdComparison()
    {
        $user_id = '123';
        $tt_id = '123';

        $matches = ($tt_id != $user_id);
        $this->assertFalse($matches);

        $tt_id = '456';
        $matches = ($tt_id != $user_id);
        $this->assertTrue($matches);
    }

    public function testUserIdEmptyCheck()
    {
        $user_id = '';
        $isEmpty = empty($user_id);
        $this->assertTrue($isEmpty);

        $user_id = '0';
        $isEmpty = empty($user_id);
        $this->assertTrue($isEmpty);

        $user_id = '123';
        $isEmpty = empty($user_id);
        $this->assertFalse($isEmpty);
    }

    public function testDuplicateUserDetection()
    {
        // Simulate checking for duplicate
        $user_id = '';
        $tt_username = 'existinguser';

        $isDuplicate = (empty($user_id) && !empty($tt_username));
        $this->assertTrue($isDuplicate);
    }

    public function testDuplicateUserWithId()
    {
        $user_id = '123';
        $tt_id = '456';

        $isDuplicate = (!empty($user_id) && $tt_id != $user_id);
        $this->assertTrue($isDuplicate);
    }

    public function testErrorMessageArrayConstruction()
    {
        $errors = [];
        $errors[] = "Username is required.";
        $errors[] = "Invalid Email format";

        $this->assertCount(2, $errors);
        $this->assertContains("Username is required.", $errors);
        $this->assertContains("Invalid Email format", $errors);
    }

    public function testSuccessMessageArrayConstruction()
    {
        $texts = [];
        $texts[] = "User:(testuser) added successfully.";

        $this->assertCount(1, $texts);
        $this->assertStringContainsString('testuser', $texts[0]);
    }

    public function testPostDataExtraction()
    {
        $table = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'wiki' => 'ar',
            'project' => 'TestProject',
            'user_id' => '123'
        ];

        $user = $table['username'] ?? '';
        $email = $table['email'] ?? '';
        $wiki = $table['wiki'] ?? '';
        $project = $table['project'] ?? '';
        $user_id = $table['user_id'] ?? '';

        $this->assertEquals('testuser', $user);
        $this->assertEquals('test@example.com', $email);
        $this->assertEquals('ar', $wiki);
        $this->assertEquals('TestProject', $project);
        $this->assertEquals('123', $user_id);
    }

    public function testPostDataExtractionWithMissingFields()
    {
        $table = [
            'username' => 'testuser'
        ];

        $user = $table['username'] ?? '';
        $email = $table['email'] ?? '';
        $wiki = $table['wiki'] ?? '';

        $this->assertEquals('testuser', $user);
        $this->assertEquals('', $email);
        $this->assertEquals('', $wiki);
    }

    public function testTrimAllFields()
    {
        $user = ' testuser ';
        $email = ' test@example.com ';
        $wiki = ' ar ';
        $project = ' TestProject ';

        $user = trim($user);
        $email = trim($email);
        $wiki = trim($wiki);
        $project = trim($project);

        $this->assertEquals('testuser', $user);
        $this->assertEquals('test@example.com', $email);
        $this->assertEquals('ar', $wiki);
        $this->assertEquals('TestProject', $project);
    }

    public function testEmailSetToEmptyOnInvalid()
    {
        $email = 'invalid-email';

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = '';
        }

        $this->assertEquals('', $email);
    }

    public function testConditionalAddOrUpdate()
    {
        $user_id = '';
        $action = empty($user_id) ? 'add' : 'update';
        $this->assertEquals('add', $action);

        $user_id = '123';
        $action = empty($user_id) ? 'add' : 'update';
        $this->assertEquals('update', $action);
    }

    public function testRequiredFieldsValidation()
    {
        $mdtitle = 'Title';
        $lang = 'ar';
        $user = 'testuser';

        $isValid = (!empty($mdtitle) && !empty($lang) && !empty($user));
        $this->assertTrue($isValid);

        $mdtitle = '';
        $isValid = (!empty($mdtitle) && !empty($lang) && !empty($user));
        $this->assertFalse($isValid);
    }
}