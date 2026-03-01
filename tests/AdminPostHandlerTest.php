<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use AdminPost\UserTableHandler;
use AdminPost\ProjectsHandler;
use AdminPost\CampaignsHandler;
use AdminPost\SettingsHandler;
use AdminPost\TranslateTypeHandler;
use AdminPost\QidsHandler;
use AdminPost\EmailsHandler;
use AdminPost\AddPagesHandler;

/**
 * Unit tests for AdminPostHandler classes.
 */
class AdminPostHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset global mock variables before each test
        global $mockExecuteQueryResult, $mockExecuteQueryCalls, $mockCsrfValid;
        global $mockCheckOneResult, $mockSqlAddUserCalls, $mockSqlUpdateUserCalls;
        global $mockAddPagesResult, $mockAddPagesCalls;

        $mockExecuteQueryResult = true;
        $mockExecuteQueryCalls = [];
        $mockCsrfValid = true;
        $mockCheckOneResult = null;
        $mockSqlAddUserCalls = [];
        $mockSqlUpdateUserCalls = [];
        $mockAddPagesResult = true;
        $mockAddPagesCalls = [];
    }

    // =========================================
    // UserTableHandler Tests
    // =========================================

    public function testUserTableHandlerCreatesInstance(): void
    {
        $handler = new UserTableHandler('coordinator');
        $this->assertInstanceOf(UserTableHandler::class, $handler);
    }

    public function testUserTableHandlerProcessesNewUser(): void
    {
        global $mockExecuteQueryCalls;

        $handler = new UserTableHandler('coordinator');
        $postData = [
            'rows' => [
                1 => [
                    'user' => 'testuser',
                    'active' => '1',
                    'active_orginal_value' => ''
                ]
            ]
        ];

        $result = $handler->handleRequest($postData);

        $this->assertTrue($result);
        $this->assertNotEmpty($mockExecuteQueryCalls);
        $this->assertStringContainsString('INSERT INTO coordinator', $mockExecuteQueryCalls[0]['query']);
    }

    public function testUserTableHandlerSkipsUnchangedUser(): void
    {
        global $mockExecuteQueryCalls;

        $handler = new UserTableHandler('coordinator');
        $postData = [
            'rows' => [
                1 => [
                    'id' => '1',
                    'user' => 'testuser',
                    'active' => '1',
                    'active_orginal_value' => '1'
                ]
            ]
        ];

        $result = $handler->handleRequest($postData);

        $this->assertTrue($result);
        $this->assertEmpty($mockExecuteQueryCalls);
    }

    public function testUserTableHandlerDeletesUser(): void
    {
        global $mockExecuteQueryCalls;

        $handler = new UserTableHandler('coordinator');
        $postData = [
            'rows' => [
                1 => [
                    'id' => '1',
                    'user' => 'testuser',
                    'del' => '1'
                ]
            ]
        ];

        $result = $handler->handleRequest($postData);

        $this->assertTrue($result);
        $this->assertNotEmpty($mockExecuteQueryCalls);
        $this->assertStringContainsString('DELETE FROM coordinator', $mockExecuteQueryCalls[0]['query']);
    }

    public function testUserTableHandlerRejectsInvalidCsrf(): void
    {
        global $mockCsrfValid;
        $mockCsrfValid = false;

        $handler = new UserTableHandler('coordinator');
        $postData = [
            'rows' => [
                1 => ['user' => 'testuser', 'active' => '1']
            ]
        ];

        $result = $handler->handleRequest($postData);

        $this->assertFalse($result);
    }

    // =========================================
    // ProjectsHandler Tests
    // =========================================

    public function testProjectsHandlerCreatesInstance(): void
    {
        $handler = new ProjectsHandler();
        $this->assertInstanceOf(ProjectsHandler::class, $handler);
    }

    public function testProjectsHandlerAddsNewProject(): void
    {
        global $mockExecuteQueryCalls;

        $handler = new ProjectsHandler();
        $postData = [
            'rows' => [
                1 => [
                    'g_title' => 'New Project'
                ]
            ]
        ];

        $result = $handler->handleRequest($postData);

        $this->assertTrue($result);
        $this->assertNotEmpty($mockExecuteQueryCalls);
        $this->assertStringContainsString('INSERT INTO projects', $mockExecuteQueryCalls[0]['query']);
    }

    public function testProjectsHandlerUpdatesExistingProject(): void
    {
        global $mockExecuteQueryCalls;

        $handler = new ProjectsHandler();
        $postData = [
            'rows' => [
                1 => [
                    'g_id' => '1',
                    'g_title' => 'Updated Project'
                ]
            ]
        ];

        $result = $handler->handleRequest($postData);

        $this->assertTrue($result);
        $this->assertNotEmpty($mockExecuteQueryCalls);
        $this->assertStringContainsString('UPDATE projects', $mockExecuteQueryCalls[0]['query']);
    }

    public function testProjectsHandlerDeletesProject(): void
    {
        global $mockExecuteQueryCalls;

        $handler = new ProjectsHandler();
        $postData = [
            'rows' => [
                1 => [
                    'g_id' => '1',
                    'g_title' => 'Project',
                    'del' => '1'
                ]
            ]
        ];

        $result = $handler->handleRequest($postData);

        $this->assertTrue($result);
        $this->assertNotEmpty($mockExecuteQueryCalls);
        $this->assertStringContainsString('DELETE FROM projects', $mockExecuteQueryCalls[0]['query']);
    }

    // =========================================
    // CampaignsHandler Tests
    // =========================================

    public function testCampaignsHandlerCreatesInstance(): void
    {
        $handler = new CampaignsHandler();
        $this->assertInstanceOf(CampaignsHandler::class, $handler);
    }

    public function testCampaignsHandlerUpdatesCategory(): void
    {
        global $mockExecuteQueryCalls;

        $handler = new CampaignsHandler();
        $postData = [
            'rows' => [
                1 => [
                    'id' => '1',
                    'camp' => 'Test Campaign',
                    'cat1' => 'Category1',
                    'cat2' => 'Category2',
                    'dep' => '2'
                ]
            ],
            'default_cat' => '1'
        ];

        $result = $handler->handleRequest($postData);

        $this->assertTrue($result);
        $this->assertNotEmpty($mockExecuteQueryCalls);
        $this->assertStringContainsString('UPDATE categories', $mockExecuteQueryCalls[0]['query']);
    }

    public function testCampaignsHandlerAddsNewCategory(): void
    {
        global $mockExecuteQueryCalls;

        $handler = new CampaignsHandler();
        $postData = [
            'rows' => [],
            'new' => [
                1 => [
                    'id' => '',
                    'camp' => 'New Campaign',
                    'cat1' => 'NewCat1',
                    'cat2' => 'NewCat2',
                    'dep' => '1'
                ]
            ]
        ];

        $result = $handler->handleRequest($postData);

        $this->assertTrue($result);
        $this->assertNotEmpty($mockExecuteQueryCalls);
        $this->assertStringContainsString('INSERT INTO categories', $mockExecuteQueryCalls[0]['query']);
    }

    // =========================================
    // SettingsHandler Tests
    // =========================================

    public function testSettingsHandlerCreatesInstance(): void
    {
        $handler = new SettingsHandler();
        $this->assertInstanceOf(SettingsHandler::class, $handler);
    }

    public function testSettingsHandlerUpdatesSettings(): void
    {
        global $mockExecuteQueryCalls;

        $handler = new SettingsHandler();
        $postData = [
            'rows' => [
                1 => [
                    'id' => '1',
                    'value' => 'new_value'
                ]
            ]
        ];

        $result = $handler->handleRequest($postData);

        $this->assertTrue($result);
        $this->assertNotEmpty($mockExecuteQueryCalls);
        $this->assertStringContainsString('UPDATE settings', $mockExecuteQueryCalls[0]['query']);
    }

    public function testSettingsHandlerSkipsEmptyId(): void
    {
        global $mockExecuteQueryCalls;

        $handler = new SettingsHandler();
        $postData = [
            'rows' => [
                1 => [
                    'id' => '',
                    'value' => 'new_value'
                ]
            ]
        ];

        $result = $handler->handleRequest($postData);

        $this->assertTrue($result);
        $this->assertEmpty($mockExecuteQueryCalls);
    }

    // =========================================
    // TranslateTypeHandler Tests
    // =========================================

    public function testTranslateTypeHandlerCreatesInstance(): void
    {
        $handler = new TranslateTypeHandler();
        $this->assertInstanceOf(TranslateTypeHandler::class, $handler);
    }

    public function testTranslateTypeHandlerAddsNewType(): void
    {
        global $mockExecuteQueryCalls;

        $handler = new TranslateTypeHandler();
        $postData = [
            'rows' => [
                1 => [
                    'title' => 'New Type',
                    'lead' => '1',
                    'full' => '0'
                ]
            ]
        ];

        $result = $handler->handleRequest($postData);

        $this->assertTrue($result);
        $this->assertNotEmpty($mockExecuteQueryCalls);
        $this->assertStringContainsString('INSERT INTO translate_type', $mockExecuteQueryCalls[0]['query']);
    }

    public function testTranslateTypeHandlerRequiresTitle(): void
    {
        $handler = new TranslateTypeHandler();
        $postData = [
            'rows' => [
                1 => [
                    'title' => '',
                    'lead' => '1',
                    'full' => '0'
                ]
            ]
        ];

        $handler->handleRequest($postData);
        $errors = $handler->getErrors();

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Title is required', $errors[0]);
    }

    // =========================================
    // QidsHandler Tests
    // =========================================

    public function testQidsHandlerCreatesInstance(): void
    {
        $handler = new QidsHandler();
        $this->assertInstanceOf(QidsHandler::class, $handler);
    }

    public function testQidsHandlerValidatesTableName(): void
    {
        // Should default to 'qids' for invalid table name
        $handler = new QidsHandler('invalid_table');
        $this->assertInstanceOf(QidsHandler::class, $handler);
    }

    public function testQidsHandlerAcceptsValidTableNames(): void
    {
        $handler1 = new QidsHandler('qids');
        $handler2 = new QidsHandler('qids_others');

        $this->assertInstanceOf(QidsHandler::class, $handler1);
        $this->assertInstanceOf(QidsHandler::class, $handler2);
    }

    public function testQidsHandlerRequiresTitle(): void
    {
        $handler = new QidsHandler();
        $postData = [
            'rows' => [
                1 => [
                    'title' => '',
                    'qid' => 'Q123'
                ]
            ]
        ];

        $handler->handleRequest($postData);
        $errors = $handler->getErrors();

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Title is required', $errors[0]);
    }

    public function testQidsHandlerRequiresQid(): void
    {
        $handler = new QidsHandler();
        $postData = [
            'rows' => [
                1 => [
                    'title' => 'Test Title',
                    'qid' => ''
                ]
            ]
        ];

        $handler->handleRequest($postData);
        $errors = $handler->getErrors();

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Qid is required', $errors[0]);
    }

    // =========================================
    // EmailsHandler Tests
    // =========================================

    public function testEmailsHandlerCreatesInstance(): void
    {
        $handler = new EmailsHandler();
        $this->assertInstanceOf(EmailsHandler::class, $handler);
    }

    public function testEmailsHandlerRequiresUsername(): void
    {
        $handler = new EmailsHandler();
        $postData = [
            'emails' => [
                1 => [
                    'username' => '',
                    'email' => 'test@test.com'
                ]
            ]
        ];

        $handler->handleRequest($postData);
        $errors = $handler->getErrors();

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Username is required', $errors[0]);
    }

    public function testEmailsHandlerValidatesEmailFormat(): void
    {
        $handler = new EmailsHandler();
        $postData = [
            'emails' => [
                1 => [
                    'username' => 'testuser',
                    'email' => 'invalid-email',
                    'wiki' => '',
                    'project' => ''
                ]
            ]
        ];

        $handler->handleRequest($postData);
        $errors = $handler->getErrors();

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Invalid Email format', $errors[0]);
    }

    public function testEmailsHandlerAddsNewUser(): void
    {
        global $mockSqlAddUserCalls;

        $handler = new EmailsHandler();
        $postData = [
            'emails' => [
                1 => [
                    'username' => 'newuser',
                    'email' => 'newuser@example.com',
                    'wiki' => 'enwiki',
                    'project' => 'wikipedia'
                ]
            ]
        ];

        $handler->handleRequest($postData);

        $this->assertNotEmpty($mockSqlAddUserCalls);
        $this->assertEquals('newuser', $mockSqlAddUserCalls[0]['user']);
    }

    public function testEmailsHandlerUpdatesExistingUser(): void
    {
        global $mockSqlUpdateUserCalls;

        $handler = new EmailsHandler();
        $postData = [
            'emails' => [
                1 => [
                    'user_id' => '1',
                    'username' => 'existinguser',
                    'email' => 'existing@example.com',
                    'wiki' => 'enwiki',
                    'project' => 'wikipedia'
                ]
            ]
        ];

        $handler->handleRequest($postData);

        $this->assertNotEmpty($mockSqlUpdateUserCalls);
        $this->assertEquals('existinguser', $mockSqlUpdateUserCalls[0]['user']);
    }

    // =========================================
    // AddPagesHandler Tests
    // =========================================

    public function testAddPagesHandlerCreatesInstance(): void
    {
        $handler = new AddPagesHandler();
        $this->assertInstanceOf(AddPagesHandler::class, $handler);
    }

    public function testAddPagesHandlerAddsPage(): void
    {
        global $mockAddPagesCalls;

        $handler = new AddPagesHandler();
        $postData = [
            'rows' => [
                1 => [
                    'mdtitle' => 'Test Title',
                    'cat' => 'TestCat',
                    'type' => 'lead',
                    'user' => 'testuser',
                    'lang' => 'en',
                    'target' => 'target',
                    'pupdate' => '2024-01-01',
                    'word' => '1000'
                ]
            ]
        ];

        $handler->handleRequest($postData);

        $this->assertNotEmpty($mockAddPagesCalls);
        $this->assertEquals('Test Title', $mockAddPagesCalls[0]['mdtitle']);
    }

    public function testAddPagesHandlerRequiresFields(): void
    {
        $handler = new AddPagesHandler();
        $postData = [
            'rows' => [
                1 => [
                    'mdtitle' => '',
                    'cat' => '',
                    'type' => '',
                    'user' => '',
                    'lang' => '',
                    'target' => '',
                    'pupdate' => '',
                    'word' => ''
                ]
            ]
        ];

        $handler->handleRequest($postData);
        $errors = $handler->getErrors();

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Missing required fields', $errors[0]);
    }

    // =========================================
    // Render Tests
    // =========================================

    public function testRenderOutputsSuccessMessages(): void
    {
        $handler = new UserTableHandler('coordinator');

        // Process a row that will generate a success message
        $postData = [
            'rows' => [
                1 => [
                    'user' => 'testuser',
                    'active' => '1',
                    'active_orginal_value' => ''
                ]
            ]
        ];

        $handler->handleRequest($postData);

        ob_start();
        $handler->render();
        $output = ob_get_clean();

        $this->assertStringContainsString('alert-success', $output);
    }

    public function testRenderOutputsErrorMessages(): void
    {
        global $mockExecuteQueryResult;
        $mockExecuteQueryResult = false;

        $handler = new UserTableHandler('coordinator');

        $postData = [
            'rows' => [
                1 => [
                    'user' => 'testuser',
                    'active' => '1',
                    'active_orginal_value' => ''
                ]
            ]
        ];

        $handler->handleRequest($postData);

        ob_start();
        $handler->render();
        $output = ob_get_clean();

        $this->assertStringContainsString('alert-danger', $output);
    }

    public function testGetErrorsReturnsErrorArray(): void
    {
        $handler = new TranslateTypeHandler();
        $postData = [
            'rows' => [
                1 => [
                    'title' => '',
                    'lead' => '1'
                ]
            ]
        ];

        $handler->handleRequest($postData);
        $errors = $handler->getErrors();

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
    }

    public function testGetTextsReturnsSuccessArray(): void
    {
        $handler = new UserTableHandler('coordinator');
        $postData = [
            'rows' => [
                1 => [
                    'user' => 'testuser',
                    'active' => '1',
                    'active_orginal_value' => ''
                ]
            ]
        ];

        $handler->handleRequest($postData);
        $texts = $handler->getTexts();

        $this->assertIsArray($texts);
        $this->assertNotEmpty($texts);
    }
}
