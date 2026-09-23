<?php
// src/app/coordinator/admin/tt/edit_translate_type.php

namespace App\Coordinator\Admin\TranslateType;

use App\Coordinator\Admin\Common\AbstractController;
use function App\csrf\generate_csrf_token;

/**
 * Class EditTranslateTypeController
 * Renders the add/edit form for a single translate_type entry (GET
 * request only; submission is handled by TtPostController).
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

    /**
     * Executes authorization check and renders the form view.
     */
    public function handleRequest(): void
    {
        $this->validateCoordinator();

        $this->renderHeaderScripts();
        $this->renderFormCard();
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
     * Builds the id/title/lead/full edit-or-add form markup.
     */
    private function buildFormHtml(string $title, string $lead, string $full, string $id): string
    {
        $leadChecked = ($lead == 1 || $lead == "1") ? 'checked' : '';
        $fullChecked = ($full == 1 || $full == "1") ? 'checked' : '';

        $title2 = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        $csrfToken = generate_csrf_token();

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
            <form action='index.php?ty=tt/post&nonav=120' method="POST">
                <input name='csrf_token' value="$csrfToken" type="hidden"/>
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

        echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <h4>$headerTitle</h4>
                </div>
                <div class='card-body'>
                    $form
                </div>
            </div>
        HTML;
    }
}

// Instantiate and execute controller
$controller = new EditTranslateTypeController();
$controller->handleRequest();
