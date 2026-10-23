<?php
// src/app/Coordinator/Admin/TranslateType/edit_translate_type.php

namespace App\Coordinator\Admin\TranslateType;

use App\Coordinator\Admin\Common\AbstractEditController;

/**
 *
 * Class EditTranslateTypeController
 * Renders the add/edit form for a single translate_type entry (GET
 * request only; submission is handled by TtPostProcessor).
 */
class EditTranslateTypeController extends AbstractEditController
{
    private string $id;
    private string $title;
    private string $lead;
    private string $full;

    public function __construct()
    {
        parent::__construct();
        $this->id = $_GET['id'] ?? '';
        $this->title = isset($_GET['title']) ? rawurldecode($_GET['title']) : '';
        $this->lead = $_GET['lead'] ?? '';
        $this->full = $_GET['full'] ?? '';
    }

    protected function createPostProcessor(): object
    {
        return new TtPostProcessor();
    }
    /**
     * Builds the id/title/lead/full edit-or-add form markup.
     */
    private function buildFormHtml(string $title, string $lead, string $full, string $id): string
    {
        $leadChecked = $this->isChecked($lead);
        $fullChecked = $this->isChecked($full);

        $title2 = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        $idRow = empty($id) ? "" : $this->inputGroup('Id', 'rows[1][id]', $id, 'readonly');
        $titleGroup = $this->inputGroup('Title', 'rows[1][title]', $title2, 'required');
        $leadSwitch = $this->switchField('Lead:', 'rows[1][lead]', $leadChecked);
        $fullSwitch = $this->switchField('Full:', 'rows[1][full]', $fullChecked);

        return <<<HTML
            <form action='index.php?ty=edit_translate_type&nonav=120' method="POST">
                {$this->createCsrfTokenField()}
                <input name='edit' value="1" type="hidden"/>
                <div class='container'>
                    <div class='row'>
                        $idRow
                        $titleGroup
                        <div class='col-md-3'>
                            <div class='row'>
                                <div class='col'>
                                    $leadSwitch
                                </div>
                                <div class='col'>
                                    $fullSwitch
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

    protected function getCardTitle(): string
    {
        return $this->id !== '' ? 'Edit Translate type' : 'Add Translate type';
    }

    protected function buildForm(): string
    {
        return $this->buildFormHtml($this->title, $this->lead, $this->full, $this->id);
    }
}
