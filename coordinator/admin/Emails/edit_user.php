<?php
//---
if (user_in_coord == false) {
    echo "<meta http-equiv='refresh' content='0; url=index.php'>";
    exit;
};
//---
use function Actions\Html\make_project_to_user;
use function TDWIKI\csrf\generate_csrf_token;
//---
echo '</div><script>
    $("#mainnav").hide();
    $("#maindiv").hide();
</script>
<div class="container-fluid">';
//---
//---
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
};
//---
$user    = $_GET['user'] ?? '';
$wiki    = $_GET['wiki'] ?? '';
$project = $_GET['project'] ?? '';
$email   = $_GET['email'] ?? '';
$user_id = $_GET['user_id'] ?? '';
//---
$header_title = ($user_id != "") ? "Edit User" : "Add New User";
//---
echo <<<HTML
<div class='card'>
    <div class='card-header'>
        <h4>$header_title</h4>
    </div>
    <div class='card-body'>
HTML;

function echo_form($user, $wiki, $project, $email, $user_id)
{
    //---
    $project_line = make_project_to_user($project);
    //---
    $csrf_token = generate_csrf_token(); // <input name='csrf_token' value="$csrf_token" hidden />
    //---
    $id_row = <<<HTML
        <div class='col-md-3'>
            <div class='input-group mb-3'>
                <div class='input-group-prepend'>
                    <span class='input-group-text'>User id</span>
                </div>
                <input class='form-control' type='text' name='emails[1][user_id]' value='$user_id' readonly/>
            </div>
        </div>
    HTML;
    // ---
    if ($user_id == "") $id_row = "";
    // ---
    echo <<<HTML
        <form action='index.php?ty=Emails/post&nonav=120' method="POST">
            <input name='csrf_token' value="$csrf_token" hidden />
            <input name='edit' value="1" hidden/>
            <div class='container'>
                <div class='row'>
                    $id_row
                    <div class='col-md-3'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>User</span>
                            </div>
                            <input class='form-control' type='text' name='emails[1][username]' value='$user' required/>
                        </div>
                    </div>
                    <div class='col-md-3'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>email</span>
                            </div>
                            <input class='form-control' type='text' name='emails[1][email]' value='$email'/>
                        </div>
                    </div>
                    <div class='col-md-3'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>wiki</span>
                            </div>
                            <input class='form-control' type='text' name='emails[1][wiki]' value='$wiki'/>
                        </div>
                    </div>
                    <div class='col-md-3'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>project</span>
                            </div>
                            <select name='emails[1][project]' class='form-select options'>$project_line</select>
                        </div>
                    </div>
                    <div class='col-md-2'>
                        <input class='btn btn-outline-primary' type='submit' value='send'/>
                    </div>
                </div>
            </div>
        </form>
    HTML;
}
//---
echo_form($user, $wiki, $project, $email, $user_id);
//---
echo <<<HTML
    </div>
</div>
HTML;
//---
