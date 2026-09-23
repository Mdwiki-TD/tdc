<?php
// src/app/coordinator/admin/Emails/edit_user.php

namespace App\Coordinator\Admin\Emails;


use App\User\CurrentUser;

if (!CurrentUser::getInstance()->isCoordinator()) {
    header('Location: /index.php');
    exit;
};

use function App\Utils\Html\make_project_to_user;
use function App\csrf\generate_csrf_token;

echo '</div><script>
    $("#mainnav").hide();
    $("#maindiv").hide();
</script>
<div class="container-fluid">';

$user    = $_GET['user'] ?? '';
$wiki    = $_GET['wiki'] ?? '';
$project = $_GET['project'] ?? '';
$email   = $_GET['email'] ?? '';
$userId = $_GET['user_id'] ?? '';

$headerTitle = (!empty($userId)) ? "Edit User" : "Add New User";

echo <<<HTML
<div class='card'>
    <div class='card-header'>
        <h4>$headerTitle</h4>
    </div>
    <div class='card-body'>
HTML;

function edit_user_echo_form($user, $wiki, $project, $email, $userId)
{

    $projectLine = make_project_to_user($project);

    $csrfToken = generate_csrf_token(); // <input name='csrf_token' value="$csrfToken" type="hidden"/>

    $idRow = <<<HTML
        <div class='col-md-3'>
            <div class='input-group mb-3'>
                <div class='input-group-prepend'>
                    <span class='input-group-text'>User id</span>
                </div>
                <input class='form-control' type='text' name='emails[1][user_id]' value='$userId' readonly/>
            </div>
        </div>
    HTML;

    if (empty($userId)) $idRow = "";

    echo <<<HTML
        <form action='index.php?ty=Emails/post&nonav=120' method="POST">
            <input name='csrf_token' value="$csrfToken" type="hidden"/>
            <input name='edit' value="1" type="hidden"/>
            <div class='container'>
                <div class='row'>
                    $idRow
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
                            <select name='emails[1][project]' class='form-select options'>$projectLine</select>
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

edit_user_echo_form($user, $wiki, $project, $email, $userId);

echo <<<HTML
    </div>
</div>
HTML;

