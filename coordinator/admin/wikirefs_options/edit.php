<?php
//---
if (user_in_coord == false) {
    echo "<meta http-equiv='refresh' content='0; url=index.php'>";
    exit;
};
//---
use function TDWIKI\csrf\generate_csrf_token;
use function Actions\MdwikiSql\execute_query;
use function Actions\Html\div_alert; // echo div_alert($texts, 'success');
use function TDWIKI\csrf\verify_csrf_token; // if (verify_csrf_token())  {
//---
echo '</div><script>
    $("#mainnav").hide();
    $("#maindiv").hide();
</script>
<div class="container-fluid">';
//---
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
};
//---
/*
$edit_params = [
    'id'   => $id,
    'lang_code'  => $lang,
    'expend'  => $tabg['expend'],
    'move_dots'  => $tabg['move_dots'],
    'add_en_lang'  => $tabg['add_en_lang']
];
*/
// ---
function echo_form()
{
    //---
    $id          = htmlspecialchars($_GET['id'] ?? '', ENT_QUOTES, 'UTF-8');
    $lang_code   = htmlspecialchars($_GET['lang_code'] ?? '', ENT_QUOTES, 'UTF-8');
    $expend      = filter_var($_GET['expend'] ?? '', FILTER_VALIDATE_INT) ?: '';
    $move_dots   = filter_var($_GET['move_dots'] ?? '', FILTER_VALIDATE_INT) ?: '';
    $add_en_lang = filter_var($_GET['add_en_lang'] ?? '', FILTER_VALIDATE_INT) ?: '';
    // ---
    $header_title = ($id != "") ? "Edit language settings" : "Add language settings";
    //---
    echo <<<HTML
        <div class='card'>
            <div class='card-header'>
                <h4>$header_title</h4>
            </div>
            <div class='card-body'>
    HTML;
    //---
    $id_row = <<<HTML
        <input class='form-control' type='text' value='$id' name='id' hidden/>
    HTML;
    // ---
    $delete_row = <<<HTML
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
    // ---
    if ($id == "") {
        $id_row = "<input class='form-control' type='text' value='1' name='new' hidden/>";
        $delete_row = "";
    }
    // ---
    $u_rows = "";
    // ---
    $params = [
        'move_dots'  => $move_dots,
        'expend'  => $expend,
        'add_en_lang'  => $add_en_lang
    ];
    // ---
    foreach ($params as $key => $value) {
        $checked = ($value == 1 || $value == "1") ? 'checked' : '';
        $u_rows .= <<<HTML
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
    // ---
    $csrf_token = generate_csrf_token();
    //---
    echo <<<HTML
        <form action='index.php?ty=wikirefs_options/edit&nonav=120' method="POST">
            <input name='csrf_token' value="$csrf_token" hidden />
            <input name='edit' value="1" hidden/>
            <div class='container'>
                <div class='row'>
                    $id_row
                    <div class='col-md-12'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>Lang code</span>
                            </div>
                            <input class='form-control' type='text' name='lang_code' value='$lang_code' required/>
                        </div>
                    </div>
                    <div class='col-md-12'>
                        <div class='row'>
                            $u_rows
                            $delete_row
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
//---
$errors = [];
$texts = [];
//---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token()) {
    // ---
    $lang_code = trim($_POST['lang_code'] ?? '');
    $expend    = filter_var($_POST['expend'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]) ?: 0;
    $move_dots = filter_var($_POST['move_dots'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]) ?: 0;
    $add_en_lang = filter_var($_POST['add_en_lang'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]) ?: 0;
    // ---
    if (isset($_POST['delete'])) {
        $id = $_POST['delete'];
        $qua = "DELETE FROM language_settings WHERE id = ?";
        // ---
        $result = execute_query($qua, $params = [$id]);
        // ---
        if ($result === false) {
            $errors[] = "Failed to delete language $lang_code.";
        } else {
            $texts[] = "language $lang_code deleted.";
        }
        // ---
    } elseif (($_POST['id'] ?? '') != "") {
        // ---
        $id = $_POST['id'] ?? '';
        //---
        $qua = "UPDATE language_settings
            SET
                lang_code = ?,
                expend = ?,
                move_dots = ?,
                add_en_lang = ?
            WHERE
                id = ?
            ";
        $params = [$lang_code, $expend, $move_dots, $add_en_lang, $id];
        //---
        $result = execute_query($qua, $params);
        //---
        if ($result === false) {
            $errors[] = "Failed to update language $lang_code.";
        } else {
            $texts[] = "language $lang_code updated.";
        }
        // ---
    } elseif (($_POST['new'] ?? '') != "") {
        // ---
        if ($lang_code == "") {
            $errors[] = "Lang code is empty.";
        } else {
            // ---
            $qua = "INSERT INTO language_settings (lang_code, expend, move_dots, add_en_lang) VALUES (?, ?, ?, ?)";
            $params = [$lang_code, $expend, $move_dots, $add_en_lang];
            // ---
            $result = execute_query($qua, $params);
            // ---
            if ($result === false) {
                $errors[] = "Failed to add language $lang_code.";
            } else {
                $texts[] = "language $lang_code added.";
            }
        }
        // ---
    } else {
        $errors[] = "Id is empty.";
    }
    //---
    echo div_alert($texts, 'success');
    echo div_alert($errors, 'danger');
    //---
} else {
    echo_form();
}
//---
echo <<<HTML
    </div>
</div>
HTML;
//---
