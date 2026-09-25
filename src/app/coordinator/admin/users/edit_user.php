<?php
// src/app/coordinator/admin/users/edit_user.php

namespace App\Coordinator\Admin\Users;

use App\Coordinator\Admin\Common\AbstractEditController;
use function App\Utils\Html\make_project_to_user;
use function App\MdwikiSql\get_user_by_id;

require_once __DIR__ . '/edit_user_post.php';

/**
 * Class EditUserController
 * Renders the add/edit form for a single user's email/wiki/project
 * data (GET request only; submission is handled by EditUserPostProcessor).
 */
class EditUserController extends AbstractEditController
{
    private string $userId;

    public function __construct()
    {
        $this->userId = $_GET['user_id'] ?? $_POST['user_id'] ?? '';
    }
    protected function createPostProcessor(): object
    {
        return new EditUserPostProcessor();
    }
    /**
     * Builds the user/email/wiki/project edit-or-add form markup.
     */
    private function buildFormHtml(string $userId): string
    {
        $userInfo  = get_user_by_id($userId);
        $user      = $userInfo['username'] ?? '';
        $wiki      = $userInfo['wiki'] ?? '';
        $user_group = $userInfo['user_group'] ?? '';
        $email     = $userInfo['email'] ?? '';

        $projectLine = make_project_to_user($user_group);

        $idRow = <<<HTML
            <div class='col-md-3'>
                <div class='input-group mb-3'>
                    <div class='input-group-prepend'>
                        <span class='input-group-text'>User id</span>
                    </div>
                    <input class='form-control' type='text' name='emails[1][user_id]' value='$userId' readonly/>
                </div>
            </div>
        HTML;

        if (empty($userId)) {
            $idRow = "";
        }

        return <<<HTML
            <form action='index.php?ty=edit_user&nonav=120&user_id={$this->userId}' method="POST">
                {$this->createCsrfTokenField()}
                <input name='edit' value="1" type="hidden"/>
                <div class='container'>
                    <div class='row'>
                        $idRow
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>User</span>
                                </div>
                                <input class='form-control' type='text' name='emails[1][username]' value='$user' required/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>email</span>
                                </div>
                                <input class='form-control' type='text' name='emails[1][email]' value='$email'/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>wiki</span>
                                </div>
                                <input class='form-control' type='text' name='emails[1][wiki]' value='$wiki'/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>project</span>
                                </div>
                                <select name='emails[1][project]' class='form-select options'>$projectLine</select>
                            </div>
                        </div>
                        <div class='col-md-2'>
                            <input class='btn btn-outline-primary' type='submit' value='send'/>
                        </div>
                    </div>
                </div>
            </form>
        HTML;
    }


    protected function getCardTitle(): string
    {
        return $this->userId !== '' ? 'Edit User' : 'Add New User';
    }
    protected function buildForm(): string
    {
        return $this->buildFormHtml($this->userId);
    }
}

// Instantiate and execute controller
$controller = new EditUserController();
$controller->handleRequest();
