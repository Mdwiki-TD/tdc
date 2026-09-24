<?php
// src/app/coordinator/admin/pages_users_to_main/fix_it.php

namespace App\Coordinator\Admin\PagesUsersToMain;

use App\Coordinator\Admin\Common\AbstractController;
use function App\APICalls\MdwikiSql\fetch_query;


require_once __DIR__ . '/fix_it_post.php';

/**
 * Class FixItController
 * Handles displaying the page edit form and duplicate entry checks (GET requests).
 */
class FixItController extends AbstractController
{
    /**
     * Executes authorization check and handles the incoming request.
     */
    public function handleRequest(): void
    {
        $this->validateCoordinator();

        $this->renderHeaderScripts();

        // Delegate POST requests to the POST processor
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postProcessor = new FixItPostProcessor();
            $postProcessor->handle();
            return;
        }

        // Handle GET request and render view
        $this->renderFormView();
    }

    /**
     * Renders the HTML structure and form.
     */
    private function renderFormView(): void
    {

        $id        = $_GET['id'] ?? '';
        $newTarget = $_GET['new_target'] ?? '';
        $newUser   = $_GET['new_user'] ?? '';

        $pageData = fetch_query("SELECT * FROM pages_users WHERE id = ?", [$id]);

        $title = $pageData[0]['title'] ?? '';
        $lang  = $pageData[0]['lang'] ?? '';

        // Check if page already exists in main pages table
        $inDb = fetch_query("SELECT * FROM pages WHERE title = ? AND lang = ? AND (target != '' AND target IS NOT NULL)", [$title, $lang]);

        if (!empty($inDb)) {
            echo $this->renderDuplicatePageAlert($inDb);
        }

        $oldTarget = $pageData[0]['target'] ?? '';
        $pupdate   = $pageData[0]['pupdate'] ?? '';

        $formHtml = $this->buildFormHtml($id, $title, $newTarget, $lang, $newUser, $pupdate);

        echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <h4>Edit Page ($oldTarget)</h4>
                </div>
                <div class='card-body'>
                    $formHtml
                </div>
            </div>
        HTML;
    }

    /**
     * Builds and returns the HTML card for duplicate pages warnings.
     */
    private function renderDuplicatePageAlert(array $inDb): string
    {
        $dbTarget  = $inDb[0]['target'] ?? '';
        $dbUser    = $inDb[0]['user'] ?? '';
        $dbPupdate = $inDb[0]['pupdate'] ?? '';
        $lang      = $inDb[0]['lang'] ?? '';

        return <<<HTML
            <div class='card mb-3'>
                <div class='card-header alert alert-danger'>
                    <h4>Duplicate page already exists in DB:</h4>
                </div>
                <div class='card-body p-1'>
                    <ul class='list-group'>
                        <li class='list-group-item'>
                            <span class='fw-bold'>Target:</span>
                            <a target='_blank' href='https://$lang.wikipedia.org/wiki/$dbTarget'>$dbTarget</a>
                        </li>
                        <li class='list-group-item'>
                            <span class='fw-bold'>User:</span>
                            $dbUser
                        </li>
                        <li class='list-group-item'>
                            <span class='fw-bold'>Published:</span>
                            $dbPupdate
                        </li>
                    </ul>
                </div>
            </div>
        HTML;
    }

    /**
     * Constructs and returns the HTML form code.
     */
    private function buildFormHtml($id, $title, $newTarget, $lang, $newUser, $pupdate): string
    {
        $testLine = isset($_REQUEST['test']) ? '<input type="hidden" name="test" value="1" />' : "";

        $title2  = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $target2 = htmlspecialchars($newTarget, ENT_QUOTES, 'UTF-8');



        return <<<HTML
            <form action='index.php?ty=pages_users_to_main/fix_it&nonav=120' method="POST">
                {$this->createCsrfTokenField()}
                <input id='id' name='id' value='$id' type='hidden'/>
                <input name='edit' value="1" type="hidden"/>
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
                                    <span class='input-group-text'>New target</span>
                                </div>
                                <input class='form-control' type='text' id='new_target' name='new_target' value='$target2' required/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>New user</span>
                                </div>
                                <input class='form-control' type='text' id='new_user' name='new_user' value='$newUser' required/>
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
}

// Execute the controller
$controller = new FixItController();
$controller->handleRequest();
