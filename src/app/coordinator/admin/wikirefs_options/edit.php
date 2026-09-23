<?php
// src/app/coordinator/admin/wikirefs_options/edit.php

namespace App\Coordinator\Admin\WikiRefsOptions;

use App\User\CurrentUser;
use function App\csrf\generate_csrf_token;
use function App\APICalls\MdwikiSql\execute_query;
use function App\Utils\Html\div_alert;
use function App\csrf\verify_csrf_token;

/**
 * Class WikiRefsOptionsEditController
 * Handles add/edit/delete of a single language_settings row (GET
 * renders the form, POST persists the change).
 */
class WikiRefsOptionsEditController
{
    private array $errors = [];
    private array $texts = [];

    /**
     * Executes authorization check and handles the incoming request.
     */
    public function handleRequest(): void
    {
        // Check user authorization
        if (!CurrentUser::getInstance()->isCoordinator()) {
            header('Location: /index.php');
            exit;
        }

        $this->renderHeaderScripts();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->renderForm();
            echo "</div> </div>";
            exit;
        }

        $this->handlePostRequest();

        echo <<<HTML
            </div>
        </div>
        HTML;
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
     * Renders the add/edit form for the language settings row.
     */
    private function renderForm(): void
    {
        $id         = htmlspecialchars($_GET['id'] ?? '', ENT_QUOTES, 'UTF-8');
        $langCode   = htmlspecialchars($_GET['lang_code'] ?? '', ENT_QUOTES, 'UTF-8');
        $expend     = filter_var($_GET['expend'] ?? '', FILTER_VALIDATE_INT) ?: '';
        $moveDots   = filter_var($_GET['move_dots'] ?? '', FILTER_VALIDATE_INT) ?: '';
        $addEnLang  = filter_var($_GET['add_en_lang'] ?? '', FILTER_VALIDATE_INT) ?: '';

        $headerTitle = (!empty($id)) ? 'Edit language settings' : 'Add language settings';

        echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <h4>$headerTitle</h4>
                </div>
                <div class='card-body'>
        HTML;

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

        $csrfToken = generate_csrf_token();

        echo <<<HTML
            <form action='index.php?ty=wikirefs_options/edit&nonav=120' method="POST">
                <input name='csrf_token' value="$csrfToken" type="hidden"/>
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
    }

    /**
     * Handles the POST submission: delete, update, or insert.
     */
    private function handlePostRequest(): void
    {
        if (!verify_csrf_token()) {
            echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
            return;
        }

        $langCode  = trim($_POST['lang_code'] ?? '');
        $expend    = filter_var($_POST['expend'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]) ?: 0;
        $moveDots  = filter_var($_POST['move_dots'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]) ?: 0;
        $addEnLang = filter_var($_POST['add_en_lang'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]) ?: 0;

        if (isset($_POST['delete'])) {
            $this->deleteRow($_POST['delete'], $langCode);
        } elseif (($_POST['id'] ?? '') != "") {
            $this->updateRow($_POST['id'], $langCode, $expend, $moveDots, $addEnLang);
        } elseif (($_POST['new'] ?? '') != "") {
            $this->insertRow($langCode, $expend, $moveDots, $addEnLang);
        } else {
            $this->errors[] = "Id is empty.";
        }

        echo div_alert($this->texts, 'success');
        echo div_alert($this->errors, 'danger');
    }

    /**
     * Deletes a language_settings row.
     */
    private function deleteRow($id, string $langCode): void
    {
        $qua = "DELETE FROM language_settings WHERE id = ?";

        $result = execute_query($qua, [$id]);

        if ($result === false) {
            $this->errors[] = "Failed to delete language $langCode.";
        } else {
            $this->texts[] = "language $langCode deleted.";
        }
    }

    /**
     * Updates an existing language_settings row.
     */
    private function updateRow($id, string $langCode, int $expend, int $moveDots, int $addEnLang): void
    {
        $qua = "UPDATE language_settings
            SET
                lang_code = ?,
                expend = ?,
                move_dots = ?,
                add_en_lang = ?
            WHERE
                id = ?
            ";
        $params = [$langCode, $expend, $moveDots, $addEnLang, $id];

        $result = execute_query($qua, $params);

        if ($result === false) {
            $this->errors[] = "Failed to update language $langCode.";
        } else {
            $this->texts[] = "language $langCode updated.";
        }
    }

    /**
     * Inserts a new language_settings row.
     */
    private function insertRow(string $langCode, int $expend, int $moveDots, int $addEnLang): void
    {
        if (empty($langCode)) {
            $this->errors[] = "Lang code is empty.";
            return;
        }

        $qua = "INSERT INTO language_settings (lang_code, expend, move_dots, add_en_lang) VALUES (?, ?, ?, ?)";
        $params = [$langCode, $expend, $moveDots, $addEnLang];

        $result = execute_query($qua, $params);

        if ($result === false) {
            $this->errors[] = "Failed to add language $langCode.";
        } else {
            $this->texts[] = "language $langCode added.";
        }
    }
}

// Instantiate and execute controller
$controller = new WikiRefsOptionsEditController();
$controller->handleRequest();
