<?php
// src/app/coordinator/admin/Emails/edit_user.php

namespace App\Coordinator\Admin\Emails;

use App\Coordinator\Admin\Common\AbstractController;
use function App\Utils\Html\make_project_to_user;


require_once __DIR__ . '/post.php';

/**
 * Class EditUserController
 * Renders the add/edit form for a single user's email/wiki/project
 * data (GET request only; submission is handled by EmailsPostProcessor).
 */
class EditUserController extends AbstractController
{
    private string $user;
    private string $wiki;
    private string $project;
    private string $email;
    private string $userId;

    public function __construct()
    {
        $this->user    = $_GET['user'] ?? '';
        $this->wiki    = $_GET['wiki'] ?? '';
        $this->project = $_GET['project'] ?? '';
        $this->email   = $_GET['email'] ?? '';
        $this->userId  = $_GET['user_id'] ?? $_POST['user_id'] ?? '';
    }

    /**
     * Executes authorization check and renders the form view.
     */
    public function handleRequest(): void
    {
        $this->validateCoordinator();
        $this->renderHeaderScripts();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Instantiate and execute processor
            $postProcessor = new EmailsPostProcessor();
            $result = $postProcessor->handle($_POST);
            $postProcessor->RenderMesseges($result);

            if ($postProcessor->shouldShowForm()) {
                // user_id=192&user=Dr3939&email=&wiki=zh&project=Wiki
                $this->renderFormCard();
            }
        } else {
            $this->renderFormCard();
        }
    }
    /**
     * Builds the user/email/wiki/project edit-or-add form markup.
     */
    private function buildFormHtml(string $user, string $wiki, string $project, string $email, string $userId): string
    {
        $projectLine = make_project_to_user($project);



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
            <form action='index.php?ty=Emails/edit_user&nonav=120' method="POST">
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

    /**
     * Renders the card wrapping the form.
     */
    private function renderFormCard(): void
    {
        $headerTitle = (!empty($this->userId)) ? 'Edit User' : 'Add New User';

        echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <h4>$headerTitle</h4>
                </div>
                <div class='card-body'>
        HTML;

        echo $this->buildFormHtml($this->user, $this->wiki, $this->project, $this->email, $this->userId);

        echo "</div></div>";
    }
}

// Instantiate and execute controller
$controller = new EditUserController();
$controller->handleRequest();
