<?php
// src/app/coordinator/admin/tt/edit_translate_type.php

namespace App\Coordinator\Admin\TranslateType;

use App\Coordinator\Admin\Common\AbstractController;

require_once __DIR__ . '/post.php';

/**
 * Class EditTranslateTypeController
 * Renders the add/edit form for a single translate_type entry (GET
 * request only; submission is handled by TtPostProcessor).
 */
class EditTranslateTypeController extends AbstractController
{
    private string $id;
    private string $title;
    private string $lead;
    private string $full;

    public function __construct()
    {
        $this->id    = $_GET['id'] ?? '';
        $this->title = isset($_GET['title']) ? rawurldecode($_GET['title']) : '';
        $this->lead  = $_GET['lead'] ?? '';
        $this->full  = $_GET['full'] ?? '';
    }

    protected function createPostProcessor(): object
    {
        return new TtPostProcessor();
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
     * Builds the id/title/lead/full edit-or-add form markup.
     */
    private function buildFormHtml(string $title, string $lead, string $full, string $id): string
    {
        $leadChecked = ($lead == 1 || $lead == "1") ? 'checked' : '';
        $fullChecked = ($full == 1 || $full == "1") ? 'checked' : '';

        $title2 = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        $idRow = <<<HTML
            <div class='col-md-3'>
                <div class='input-group mb-3'>
                    <div class='input-group-prepend'>
                        <span class='input-group-text'>Id</span>
                    </div>
                    <input class='form-control' type='text' value='$id' name='rows[1][id]' readonly/>
                </div>
            </div>
        HTML;

        if (empty($id)) {
            $idRow = "";
        }

        return <<<HTML
            <form action='index.php?ty=tt/edit_translate_type&nonav=120' method="POST">
                {$this->createCsrfTokenField()}
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
                            <div class='row'>
                                <div class='col'>
                                    <div class='input-group form-control mb-3'>
                                        <div class='input-group-prepend'>
                                            <span class='me-3'>Lead:</span>
                                        </div>
                                        <div class="form-check form-switch form-inline">
                                            <input class='form-check-input' type='checkbox' name='rows[1][lead]' value='1' $leadChecked>
                                        </div>
                                    </div>
                                </div>
                                <div class='col'>
                                    <div class='input-group form-control mb-3'>
                                        <div class='input-group-prepend'>
                                            <span class='me-3'>Full:</span>
                                        </div>
                                        <div class="form-check form-switch form-inline">
                                            <input class='form-check-input' type='checkbox' name='rows[1][full]' value='1' $fullChecked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-2'>
                            <input class='btn btn-outline-primary' type='submit' value='Save'/>
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
        $headerTitle = (!empty($this->id)) ? 'Edit Translate type' : 'Add Translate type';
        $form = $this->buildFormHtml($this->title, $this->lead, $this->full, $this->id);

        $this->echoCard($headerTitle, $form);
    }
}

// Instantiate and execute controller
$controller = new EditTranslateTypeController();
$controller->handleRequest();
