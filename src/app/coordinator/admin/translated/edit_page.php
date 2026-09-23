<?php
// src/app/coordinator/admin/translated/edit_page.php

namespace App\Coordinator\Admin\Translated;

use App\User\CurrentUser;
use function App\APICalls\MdwikiSql\fetch_query;
use function App\csrf\generate_csrf_token;

require_once __DIR__ . '/edit_page_post.php';

/**
 * Class EditPageController
 * Handles editing and deleting translated pages (GET renders the form,
 * POST is delegated to EditPagePostHandler).
 */
class EditPageController
{
    private string $id;
    private string $table;

    public function __construct()
    {
        $this->id = $_GET['id'] ?? $_POST['id'] ?? '';
        $this->table = $_GET['table'] ?? $_POST['table'] ?? 'pages';
    }

    /**
     * Entry point to handle request workflow.
     */
    public function handleRequest(): void
    {
        // Check user authorization
        if (!CurrentUser::getInstance()->isCoordinator()) {
            header('Location: /index.php');
            exit;
        }

        $this->renderHeaderScripts();

        echo <<<HTML
        <div class='card'>
            <div class='card-header'>
                <h4>Edit Page (id: {$this->id}, table: {$this->table})</h4>
            </div>
            <div class='card-body'>
        HTML;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->renderEditForm($this->id, $this->table);
            echo "</div></div>";
            exit;
        }

        $this->handlePostRequest();
    }

    /**
     * Renders UI scripts to isolate the modal/page layout.
     */
    private function renderHeaderScripts(): void
    {
        echo '</div><script>
            $("#mainnav").hide();
            $("#maindiv").hide();
        </script>
        <div class="container-fluid">';
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

        $csrfToken = generate_csrf_token();

        echo <<<HTML
            <form action='index.php?ty=translated/edit_page&nonav=120' method="POST">
                <input name='csrf_token' value="$csrfToken" type="hidden"/>
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
    }

    /**
     * Handles POST data submission for editing or deleting records.
     */
    private function handlePostRequest(): void
    {
        $closeBtn = $this->getCloseButtonHtml();

        $result = (new EditPagePostHandler())->handle($_POST, $this->id, $this->table);

        if ($result['csrfError']) {
            echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
            echo $closeBtn;
            return;
        }

        echo <<<HTML
            <div class='alert alert-success' role='alert'>Page updated<br>
                window will close in 3 seconds
            </div>
        HTML;
        echo $closeBtn;
        echo <<<HTML
            <script>
                setTimeout(function() {
                    window.close();
                }, 3000);
            </script>
            </div>
        </div>
        HTML;
    }

    /**
     * Returns HTML string for the window close button.
     */
    private function getCloseButtonHtml(): string
    {
        return <<<HTML
            <div class="aligncenter">
                <a class="btn btn-outline-primary" onclick="window.close()">Close</a>
            </div>
        HTML;
    }
}

// Instantiate and execute controller
$controller = new EditPageController();
$controller->handleRequest();
