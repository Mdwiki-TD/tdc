<?php
// src/app/coordinator/admin/qids/edit_qid.php

namespace App\Coordinator\Admin\Qids;

use App\Coordinator\Admin\Common\AbstractController;
use function App\Utils\Html\make_form_input_col;

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
        $this->qidTable = $_GET['qid_table'] ?? '';

        if ($this->qidTable !== 'qids' && $this->qidTable !== 'qids_others') {
            $this->qidTable = 'qids';
        }
    }

    public function handlePostRequest(): void
    {
        // Instantiate and execute processor
        $postProcessor = new QidsPostProcessor($_GET['qid_table'] ?? '');
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
     * Builds the id/title/qid edit-or-add form markup.
     */
    private function buildFormHtml(string $id, string $title, string $qid, string $qidTable): string
    {
        $idRow = !empty($id)
            ? make_form_input_col('Id', 'rows[1][id]', $id, 'col-md-3', '', true)
            : '';

        $titleCol = make_form_input_col('Title', 'rows[1][title]', $title, 'col-md-3', 'required');
        $qidCol = make_form_input_col('Qid', 'rows[1][qid]', $qid, 'col-md-3', 'required');

        return <<<HTML
            <form action='index.php?ty=qids/edit_qid&qid_table=$qidTable&nonav=120' method="POST">
                {$this->createCsrfTokenField()}
                <input name='qid_table' value="$qidTable" type="hidden"/>
                <input name='edit' value="1" type="hidden"/>
                <div class='container'>
                    <div class='row'>
                        $idRow
                        $titleCol
                        $qidCol
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
