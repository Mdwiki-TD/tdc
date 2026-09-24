<?php
// src/app/coordinator/admin/tt/edit_translate_type.php

namespace App\Coordinator\Admin\TranslateType;

use App\Coordinator\Admin\Common\AbstractController;
use function App\Utils\Html\make_form_input_col;
use function App\Utils\Html\make_form_switch_col;

require_once __DIR__ . '/post.php';

/**
 * Class EditTranslateTypeController
 * Renders the add/edit form for a single translate_type entry (GET
 * request only; submission is handled by TtPostProcessor).
 */
class EditTranslateTypeController extends AbstractController
{
    private string $title;
    private string $lead;
    private string $full;
    private string $id;

    public function __construct()
    {
        $this->title = isset($_GET['title']) ? rawurldecode($_GET['title']) : '';
        $this->lead  = $_GET['lead'] ?? '';
        $this->full  = $_GET['full'] ?? '';
        $this->id    = $_GET['id'] ?? '';
    }

    public function handlePostRequest(): void
    {
        // Instantiate and execute processor
        $postProcessor = new TtPostProcessor();
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
     * Builds the id/title/lead/full edit-or-add form markup.
     */
    private function buildFormHtml(string $title, string $lead, string $full, string $id): string
    {
        $idRow = !empty($id)
            ? make_form_input_col('Id', 'rows[1][id]', $id, 'col-md-3', '', true)
            : '';

        $titleCol = make_form_input_col('Title', 'rows[1][title]', $title, 'col-md-3', 'required');
        $leadSwitch = make_form_switch_col('Lead:', 'rows[1][lead]', ($lead == 1 || $lead == "1"), 'col');
        $fullSwitch = make_form_switch_col('Full:', 'rows[1][full]', ($full == 1 || $full == "1"), 'col');

        return <<<HTML
            <form action='index.php?ty=tt/edit_translate_type&nonav=120' method="POST">
                {$this->createCsrfTokenField()}
                <input name='edit' value="1" type="hidden"/>
                <div class='container'>
                    <div class='row'>
                        $idRow
                        $titleCol
                        <div class='col-md-3'>
                            <div class='row'>
                                $leadSwitch
                                $fullSwitch
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
