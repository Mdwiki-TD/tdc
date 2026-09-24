<?php
// src/app/coordinator/admin/wikirefs_options/edit.php

namespace App\Coordinator\Admin\WikiRefsOptions;

use App\Coordinator\Admin\Common\AbstractController;

require_once __DIR__ . '/WikiRefsOptionsEditPostHandler.php';

/**
 * Class WikiRefsOptionsEditController
 * Handles add/edit/delete of a single language_settings row (GET
 * renders the form, POST persists the change via WikiRefsOptionsEditPostHandler).
 */
class WikiRefsOptionsEditController extends AbstractController
{
    private const FLAGS = ['move_dots', 'expend', 'add_en_lang'];

    protected function createPostProcessor(): object
    {
        return new WikiRefsOptionsEditPostHandler();
    }
    public function handlePostRequest(): void
    {
        $postProcessor = $this->createPostProcessor();
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
        $this->validateCoordinator();
        $this->renderHeaderScripts();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        } else {
            $this->renderFormCard();
        }
    }

    /**
     * Renders the add/edit form for the language settings row.
     */
    private function renderFormCard(): void
    {
        $id         = htmlspecialchars($_GET['id'] ?? '', ENT_QUOTES, 'UTF-8');
        $langCode   = htmlspecialchars($_GET['lang_code'] ?? '', ENT_QUOTES, 'UTF-8');
        $expend     = filter_var($_GET['expend'] ?? '', FILTER_VALIDATE_INT) ?: '';
        $moveDots   = filter_var($_GET['move_dots'] ?? '', FILTER_VALIDATE_INT) ?: '';
        $addEnLang  = filter_var($_GET['add_en_lang'] ?? '', FILTER_VALIDATE_INT) ?: '';

        $headerTitle = (!empty($id)) ? 'Edit language settings' : 'Add language settings';

        $idRow = <<<HTML
            <input class='form-control' value='$id' name='id' type='hidden'/>
        HTML;

        $deleteRow = <<<HTML
            <div class='col-6'>
                <div class='input-group form-control mb-1 alert alert-warning p-2'>
                    <div class='input-group-prepend'>
                        <span class='me-3'>Delete?</span>
                    </div>
                    <div class="form-check form-switch form-inline">
                        <input class="form-check-input" type="checkbox" name="delete" value="$id">
                    </div>
                </div>
            </div>
        HTML;

        if (empty($id)) {
            $idRow = "<input class='form-control' value='1' name='new' type='hidden'/>";
            $deleteRow = "";
        }

        $uRows = '';

        $params = [
            'move_dots'   => $moveDots,
            'expend'      => $expend,
            'add_en_lang' => $addEnLang,
        ];

        foreach ($params as $key => $value) {
            $checked = ($value == 1 || $value == "1") ? 'checked' : '';
            $uRows .= <<<HTML
                <div class='col-6'>
                    <div class='input-group form-control mb-3'>
                        <div class='input-group-prepend'>
                            <span class='me-3'>$key:</span>
                        </div>
                        <div class="form-check form-switch form-inline">
                            <input type='text' name='$key' value='0' hidden>
                            <input class='form-check-input' type='checkbox' name='$key' value='1' $checked>
                        </div>
                    </div>
                </div>
            HTML;
        }

        $form = <<<HTML
            <form action='index.php?ty=wikirefs_options/edit&nonav=120' method="POST">
                {$this->createCsrfTokenField()}
                <input name='edit' value="1" type="hidden"/>
                <div class='container'>
                    <div class='row'>
                        $idRow
                        <div class='col-md-12'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>Lang code</span>
                                </div>
                                <input class='form-control' type='text' name='lang_code' value='$langCode' required/>
                            </div>
                        </div>
                        <div class='col-md-12'>
                            <div class='row'>
                                $uRows
                                $deleteRow
                            </div>
                        </div>
                        <div class='col-md-12'>
                            <input class='btn btn-outline-primary' type='submit' value='Save'/>
                        </div>
                    </div>
                </div>
            </form>
        HTML;

        $this->echoCard($headerTitle, $form);
    }
}

// Instantiate and execute controller
$controller = new WikiRefsOptionsEditController();
$controller->handleRequest();
