<?php
// src/app/coordinator/admin/tt/edit_translate_type.php

namespace App\Coordinator\Admin\TranslateType;

use App\Coordinator\Admin\Common\AbstractEditController;

require_once __DIR__ . '/edit_tt_post.php';
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
        $this->id    = $_GET['id'] ?? '';
        $this->title = isset($_GET['title']) ? rawurldecode($_GET['title']) : '';
        $this->lead  = $_GET['lead'] ?? '';
        $this->full  = $_GET['full'] ?? '';
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
            <form action='index.php?ty=edit_translate_type&nonav=120' method="POST">
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

    protected function getCardTitle(): string
    {
        return $this->id !== '' ? 'Edit Translate type' : 'Add Translate type';
    }

    protected function buildForm(): string
    {
        return $this->buildFormHtml($this->title, $this->lead, $this->full, $this->id);
    }
}
