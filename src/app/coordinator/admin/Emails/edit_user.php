<?php
// src/app/coordinator/admin/Emails/edit_user.php

namespace App\Coordinator\Admin\Emails;

use App\Coordinator\Admin\Common\AbstractController;
use function App\Utils\Html\make_project_to_user;
use function App\Utils\Html\make_form_input_col;
use function App\APICalls\MdwikiSql\get_user_by_id;

require_once __DIR__ . '/post.php';

/**
 * Class EditUserController
 * Renders the add/edit form for a single user's email/wiki/project
 * data (GET request only; submission is handled by EmailsPostProcessor).
 */
class EditUserController extends AbstractController
{
    private string $userId;

    public function __construct()
    {
        $this->userId  = $_GET['user_id'] ?? $_POST['user_id'] ?? '';
    }

    public function handlePostRequest(): void
    {
        // Instantiate and execute processor
        $postProcessor = new EmailsPostProcessor();
        $result = $postProcessor->handle($_POST);
        $postProcessor->RenderMesseges($result);

        if ($postProcessor->shouldShowForm()) {
            $this->renderFormCard();
        }
    }
    /**
     * Executes authorization check and renders the form view.
     */
    public function handleRequest(): void
    {
        $this->handlePopupRequest(
            fn() => $this->renderFormCard(),
            fn() => $this->handlePostRequest()
        );
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

        $idRow = !empty($userId)
            ? make_form_input_col('User id', 'emails[1][user_id]', $userId, 'col-md-3', '', true)
            : '';

        $userCol = make_form_input_col('User', 'emails[1][username]', $user, 'col-md-3', 'required');
        $emailCol = make_form_input_col('email', 'emails[1][email]', $email, 'col-md-3');
        $wikiCol = make_form_input_col('wiki', 'emails[1][wiki]', $wiki, 'col-md-3');

        return <<<HTML
            <form action='index.php?ty=Emails/edit_user&nonav=120&user_id={$this->userId}' method="POST">
                {$this->createCsrfTokenField()}
                <input name='edit' value="1" type="hidden"/>
                <div class='container'>
                    <div class='row'>
                        $idRow
                        $userCol
                        $emailCol
                        $wikiCol
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

    /**
     * Renders the card wrapping the form.
     */
    private function renderFormCard(): void
    {
        $headerTitle = (!empty($this->userId)) ? 'Edit User' : 'Add New User';
        $form = $this->buildFormHtml($this->userId);

        $this->echoCard($headerTitle, $form);
    }
}

// Instantiate and execute controller
$controller = new EditUserController();
$controller->handleRequest();
