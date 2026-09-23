<?php
// src/app/coordinator/admin/wikirefs_options/edit.php

namespace App\Coordinator\Admin\WikiRefsOptions;

use App\User\CurrentUser;

if (!CurrentUser::getInstance()->isCoordinator()) {
    header('Location: /index.php');
    exit;
};

use function App\csrf\generate_csrf_token;
use function App\APICalls\MdwikiSql\execute_query;
use function App\Utils\Html\div_alert;
use function App\csrf\verify_csrf_token;

echo '</div><script>
    $("#mainnav").hide();
    $("#maindiv").hide();
</script>
<div class="container-fluid">';


function echo_form()
{

    $id          = htmlspecialchars($_GET['id'] ?? '', ENT_QUOTES, 'UTF-8');
    $langCode   = htmlspecialchars($_GET['lang_code'] ?? '', ENT_QUOTES, 'UTF-8');
    $expend      = filter_var($_GET['expend'] ?? '', FILTER_VALIDATE_INT) ?: '';
    $moveDots   = filter_var($_GET['move_dots'] ?? '', FILTER_VALIDATE_INT) ?: '';
    $addEnLang = filter_var($_GET['add_en_lang'] ?? '', FILTER_VALIDATE_INT) ?: '';

    $headerTitle = (!empty($id)) ? "Edit language settings" : "Add language settings";

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

    $uRows = "";

    $params = [
        'move_dots'  => $moveDots,
        'expend'  => $expend,
        'add_en_lang'  => $addEnLang
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

$id = $_GET['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo_form();
    echo "</div> </div>";
    exit;
}
if (!verify_csrf_token()) {
    echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
    return;
}

$errors = [];
$texts = [];


$langCode = trim($_POST['lang_code'] ?? '');
$expend    = filter_var($_POST['expend'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]) ?: 0;
$moveDots = filter_var($_POST['move_dots'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]) ?: 0;
$addEnLang = filter_var($_POST['add_en_lang'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]) ?: 0;

if (isset($_POST['delete'])) {
    $id = $_POST['delete'];
    $qua = "DELETE FROM language_settings WHERE id = ?";

    $result = execute_query($qua, $params = [$id]);

    if ($result === false) {
        $errors[] = "Failed to delete language $langCode.";
    } else {
        $texts[] = "language $langCode deleted.";
    }

} elseif (($_POST['id'] ?? '') != "") {

    $id = $_POST['id'];

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
        $errors[] = "Failed to update language $langCode.";
    } else {
        $texts[] = "language $langCode updated.";
    }

} elseif (($_POST['new'] ?? '') != "") {

    if (empty($langCode)) {
        $errors[] = "Lang code is empty.";
    } else {

        $qua = "INSERT INTO language_settings (lang_code, expend, move_dots, add_en_lang) VALUES (?, ?, ?, ?)";
        $params = [$langCode, $expend, $moveDots, $addEnLang];

        $result = execute_query($qua, $params);

        if ($result === false) {
            $errors[] = "Failed to add language $langCode.";
        } else {
            $texts[] = "language $langCode added.";
        }
    }

} else {
    $errors[] = "Id is empty.";
}

echo div_alert($texts, 'success');
echo div_alert($errors, 'danger');


echo <<<HTML
    </div>
</div>
HTML;

