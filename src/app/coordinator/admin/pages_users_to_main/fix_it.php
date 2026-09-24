<?php
// src/app/coordinator/admin/pages_users_to_main/fix_it.php

namespace App\Coordinator\Admin\PagesUsersToMain;

use App\Coordinator\Admin\Common\AbstractController;
use function App\APICalls\MdwikiSql\fetch_query;
use function App\Utils\Html\make_form_input_col;

require_once __DIR__ . '/fix_it_post.php';

/**
 * Class FixItController
 * Handles displaying the page edit form and duplicate entry checks (GET requests).
 */
class FixItController extends AbstractController
{
    public function handlePostRequest(): void
    {
        $postProcessor = new FixItPostProcessor();
        $result = $postProcessor->handle($_POST);
        $postProcessor->RenderMesseges($result);

        if ($postProcessor->shouldShowForm()) {
            $this->renderFormCard();
        }
    }
    /**
     * Executes authorization check and handles the incoming request.
     */
    public function handleRequest(): void
    {
        $this->handlePopupRequest(
            fn() => $this->renderFormCard(),
            fn() => $this->handlePostRequest()
        );
    }

    /**
     * Renders the HTML structure and form.
     */
    private function renderFormCard(): void
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


        $this->echoCard("Edit Page ({$oldTarget})", $formHtml);
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

        $titleCol = make_form_input_col('Title', 'title', $title, 'col-md-3', 'required', false, 'title');
        $langEsc = htmlspecialchars($lang, ENT_QUOTES, 'UTF-8');
        $targetCol = make_form_input_col('New target', 'new_target', $newTarget, 'col-md-3', 'required', false, 'new_target');
        $userCol = make_form_input_col('New user', 'new_user', $newUser, 'col-md-3', 'required', false, 'new_user');

        $pupdateEsc = htmlspecialchars($pupdate, ENT_QUOTES, 'UTF-8');

        return <<<HTML
            <form action='index.php?ty=pages_users_to_main/fix_it&nonav=120' method="POST">
                {$this->createCsrfTokenField()}
                <input id='id' name='id' value='$id' type='hidden'/>
                <input name='edit' value="1" type="hidden"/>
                $testLine
                <div class='container'>
                    <div class='row'>
                        $titleCol
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>lang</span>
                                </div>
                                <input class='form-control lang_input' type='text' id='lang' name='lang' value='$langEsc' required/>
                            </div>
                        </div>
                        $targetCol
                        $userCol
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>Published</span>
                                </div>
                                <input class='form-control' type='text' id='pupdate' name='pupdate' value='$pupdateEsc' placeholder='YYYY-MM-DD' required/>
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
