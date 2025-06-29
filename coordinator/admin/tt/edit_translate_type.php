<?php
//---
if (user_in_coord == false) {
    echo "<meta http-equiv='refresh' content='0; url=index.php'>";
    exit;
};
//---
use function TDWIKI\csrf\generate_csrf_token;
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
$title  = (isset($_GET['title'])) ? rawurldecode($_GET['title']) : "";
$lead   = $_GET['lead'] ?? '';
$full   = $_GET['full'] ?? '';
$id     = $_GET['id'] ?? '';
//---
$header_title = ($id != "") ? "Edit Translate type" : "Add Translate type";
//---
echo <<<HTML
<div class='card'>
    <div class='card-header'>
        <h4>$header_title</h4>
    </div>
    <div class='card-body'>
HTML;
function echo_form($title, $lead, $full, $id)
{
    $lead_checked = ($lead == 1 || $lead == "1") ? 'checked' : '';
    $full_checked = ($full == 1 || $full == "1") ? 'checked' : '';
    //---
    $title2 = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    //---
    $csrf_token = generate_csrf_token();
    //---
    $id_row = <<<HTML
        <div class='col-md-3'>
            <div class='input-group mb-3'>
                <div class='input-group-prepend'>
                    <span class='input-group-text'>Id</span>
                </div>
                <input class='form-control' type='text' value='$id' name='rows[1][id]' readonly/>
            </div>
        </div>
    HTML;
    // ---
    if ($id == "") $id_row = "";
    // ---
    echo <<<HTML
        <form action='index.php?ty=tt/post&nonav=120' method="POST">
            <input name='csrf_token' value="$csrf_token" type="hidden"/>
            <input name='edit' value="1" type="hidden"/>
            <div class='container'>
                <div class='row'>
                    $id_row
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
                                        <input class='form-check-input' type='checkbox' name='rows[1][lead]' value='1' $lead_checked>
                                    </div>
                                </div>
                            </div>
                            <div class='col'>
                                <div class='input-group form-control mb-3'>
                                    <div class='input-group-prepend'>
                                        <span class='me-3'>Full:</span>
                                    </div>
                                    <div class="form-check form-switch form-inline">
                                        <input class='form-check-input' type='checkbox' name='rows[1][full]' value='1' $full_checked>
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
//---
echo_form($title, $lead, $full, $id);
//---
echo <<<HTML
    </div>
</div>
HTML;
//---
