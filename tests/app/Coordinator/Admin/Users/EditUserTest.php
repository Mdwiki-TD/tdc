<?php

namespace Tests\Coordinator\Admin\Users;

use App\Coordinator\Admin\Users\EditUserController;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EditUserController::class)]
class EditUserTest extends TestCase
{
    public function testEditUserFormHeaderTitle()
    {
        $userId = '456';
        $headerTitle = (!empty($userId)) ? "Edit User" : "Add New User";
        $this->assertEquals("Edit User", $headerTitle);

        $userId = '';
        $headerTitle = (!empty($userId)) ? "Edit User" : "Add New User";
        $this->assertEquals("Add New User", $headerTitle);
    }
}
