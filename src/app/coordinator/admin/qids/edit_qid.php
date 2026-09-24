<?php
// src/app/coordinator/admin/qids/edit_qid.php

namespace App\Coordinator\Admin\Qids;

use App\Coordinator\Admin\Common\AbstractController;

require_once __DIR__ . '/post.php';

/**
 * Class EditQidController
 * Renders the add/edit form for a single qid entry (GET request only;
 * submission is handled by QidsPostProcessor).
 */
class EditQidController extends AbstractController
{
    private string $id;
    private string $title;
    private string $qid;
    private string $qidTable;

    public function __construct()
    {
        $this->id       = $_GET['id'] ?? '';
        $this->title    = $_GET['title'] ?? '';
        $this->qid      = $_GET['qid'] ?? '';
        $table          = $_GET['qid_table'] ?? '';
        $this->qidTable = in_array($table, ['qids', 'qids_others'], true) ? $table : 'qids';
    }

    protected function createPostProcessor(): object
    {
        return new QidsPostProcessor($_GET['qid_table'] ?? '');
    }
    public function handlePostRequest(): void
    {
        // Instantiate and execute processor
        $postProcessor = $this->createPostProcessor();
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
        $this->validateCoordinator();
        $this->renderHeaderScripts();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        } else {
            $this->renderFormCard();
        }
    }

    /**
     * Builds the id/title/qid edit-or-add form markup.
     */
    private function buildFormHtml(string $id, string $title, string $qid, string $qidTable): string
    {
        $title2 = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        $idRow = <<<HTML
            <div class='col-md-3'>
                <div class='input-group mb-3'>
                    <div class='input-group-prepend'>
                        <span class='input-group-text'>Id</span>
                    </div>
                    <input class='form-control' type='text' name='rows[1][id]' value='$id' readonly/>
                </div>
            </div>
        HTML;

        if (empty($id)) {
            $idRow = "";
        }

        return <<<HTML
            <form action='index.php?ty=qids/edit_qid&qid_table=$qidTable&nonav=120' method="POST">
                {$this->createCsrfTokenField()}
                <input name='qid_table' value="$qidTable" type="hidden"/>
                <input name='edit' value="1" type="hidden"/>
                <div class='container'>
                    <div class='row'>
                        $idRow
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>Title</span>
                                </div>
                                <input class='form-control' type='text' name='rows[1][title]' value='$title2' required/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>Qid</span>
                                </div>
                                <input class='form-control' type='text' name='rows[1][qid]' value='$qid' required/>
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
        $headerTitle = ($this->id !== '') ? 'Edit Qid' : 'Add New Qid';
        $form = $this->buildFormHtml($this->id, $this->title, $this->qid, $this->qidTable);

        $this->echoCard($headerTitle, $form);
    }
}

// Instantiate and execute controller
$controller = new EditQidController();
$controller->handleRequest();
