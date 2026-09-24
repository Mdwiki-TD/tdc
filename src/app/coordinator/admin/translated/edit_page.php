<?php
// src/app/coordinator/admin/translated/edit_page.php

namespace App\Coordinator\Admin\Translated;

use App\Coordinator\Admin\Common\AbstractController;
use function App\APICalls\MdwikiSql\fetch_query;

require_once __DIR__ . '/EditPagePostHandler.php';

/**
 * Class EditPageController
 * Handles editing and deleting translated pages (GET renders the form,
 * POST is delegated to EditPagePostHandler).
 */
class EditPageController extends AbstractController
{
    private string $id;
    private string $table;

    public function __construct()
    {
        $this->id    = $_GET['id'] ?? $_POST['id'] ?? '';
        $cand        = $_GET['table'] ?? $_POST['table'] ?? '';
        $this->table = in_array($cand, ['pages', 'pages_users'], true) ? $cand : 'pages';
    }

    protected function createPostProcessor(): object
    {
        return new EditPagePostHandler($this->id, $this->table);
    }
    public function handlePostRequest(): void
    {
        $postProcessor = $this->createPostProcessor();
        $result = $postProcessor->handle($_POST);
        $postProcessor->RenderMesseges($result);
    }
    /**
     * Entry point to handle request workflow.
     */
    public function handleRequest(): void
    {
        $this->validateCoordinator();
        $this->renderHeaderScripts();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        } else {
            $this->renderEditForm($this->id, $this->table);
        }
    }

    /**
     * Displays the edit page form.
     */
    private function renderEditForm(string $id, string $table): void
    {
        $pageData = fetch_query("SELECT * FROM {$table} WHERE id = ?", [$id]);

        $title   = $pageData[0]['title'] ?? '';
        $target  = $pageData[0]['target'] ?? '';
        $lang    = $pageData[0]['lang'] ?? '';
        $user    = $pageData[0]['user'] ?? '';
        $pupdate = $pageData[0]['pupdate'] ?? '';

        $testLine = isset($_REQUEST['test']) ? '<input type="hidden" name="test" value="1" />' : "";

        $title2  = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $target2 = htmlspecialchars($target, ENT_QUOTES, 'UTF-8');

        $form = <<<HTML
            <form action='index.php?ty=translated/edit_page&nonav=120' method="POST">
                {$this->createCsrfTokenField()}
                <input id='id' name='id' value='$id' type='hidden'/>
                <input name='edit' value="1" type="hidden"/>
                <input name='table' value="$table" type="hidden"/>
                $testLine
                <div class='container'>
                    <div class='row'>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>Title</span>
                                </div>
                                <input class='form-control' type='text' id='title' name='title' value='$title2' required/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>lang</span>
                                </div>
                                <input class='form-control lang_input' type='text' id='lang' name='lang' value='$lang' required/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>target</span>
                                </div>
                                <input class='form-control' type='text' id='target' name='target' value='$target2' required/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>user</span>
                                </div>
                                <input class='form-control' type='text' id='user' name='user' value='$user' required/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>Published</span>
                                </div>
                                <input class='form-control' type='text' id='pupdate' name='pupdate' value='$pupdate' placeholder='YYYY-MM-DD' required/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group form-control mb-3 alert alert-warning'>
                                <div class='input-group-prepend'>
                                    <span class='me-3'>Delete?</span>
                                </div>
                                <div class="form-check form-switch form-inline">
                                    <input class="form-check-input" type="checkbox" name="delete" value="$id">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-12'>
                            <input class='btn btn-outline-primary' type='submit' value='send'/>
                        </div>
                    </div>
                </div>
            </form>
        HTML;

        $headerTitle = "Edit Page (id: {$this->id}, table: {$this->table})";

        $this->echoCard($headerTitle, $form);
    }
}

// Instantiate and execute controller
$controller = new EditPageController();
$controller->handleRequest();
