<?php
//---
if (user_in_coord == false) {
    echo "<meta http-equiv='refresh' content='0; url=index.php'>";
    exit;
};
//---
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
};
//---
use function Actions\MdwikiSql\fetch_query;
use function Actions\Html\add_quotes;
use function TDWIKI\csrf\generate_csrf_token;
//---
function echo_form($id, $title, $new_target, $lang, $new_user, $pupdate)
{
    $test_line = (isset($_REQUEST['test'])) ? "<input name='test' value='1' hidden/>" : "";

    $title2 = add_quotes($title);
    $target2 = add_quotes($new_target);
    //---
    $csrf_token = generate_csrf_token(); // <input name='csrf_token' value="$csrf_token" hidden />
    //---
    echo <<<HTML
        <form action='index.php?ty=pages_users_to_main/fix_it&nonav=120' method="POST">
            <input name='csrf_token' value="$csrf_token" hidden />
            <input type='text' id='id' name='id' value='$id' hidden/>
            <input name='edit' value="1" hidden/>
            $test_line
            <div class='container'>
                <div class='row'>
                    <div class='col-md-3'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>Title</span>
                            </div>
                            <input class='form-control' type='text' id='title' name='title' value=$title2 required/>
                        </div>
                    </div>
                    <div class='col-md-3'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>lang</span>
                            </div>
                            <input class='form-control lang_input' type='text' id='lang' name='lang' value='$lang' required/>
                        </div>
                    </div>
                    <div class='col-md-3'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>New target</span>
                            </div>
                            <input class='form-control' type='text' id='new_target' name='new_target' value=$target2 required/>
                        </div>
                    </div>
                    <div class='col-md-3'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>New user</span>
                            </div>
                            <input class='form-control' type='text' id='new_user' name='new_user' value='$new_user' required/>
                        </div>
                    </div>
                    <div class='col-md-3'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>Publication date</span>
                            </div>
                            <input class='form-control' type='text' id='pupdate' name='pupdate' value='$pupdate' placeholder='YYYY-MM-DD' required/>
                        </div>
                    </div>
                </div>
                <div class='row'>
                    <div class='col-12'>
                        <input class='btn btn-outline-primary' type='submit' value='send'/>
                    </div>
                </div>
            </div>
        </form>
    HTML;
}
//---
echo '</div><script>
$("#mainnav").hide();
$("#maindiv").hide();
</script>
<div class="container-fluid">';
//---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require __DIR__ . '/fix_it_post.php';
} else {
    //---
    $id         = $_GET['id'] ?? '';
    $new_target = $_GET['new_target'] ?? '';
    $new_user   = $_GET['new_user'] ?? '';
    //---
    $page_data = fetch_query("SELECT * FROM pages_users WHERE id = ?", [$id]);
    //---
    $title      = $page_data[0]['title'] ?? '';
    $old_target = $page_data[0]['target'] ?? '';
    $lang       = $page_data[0]['lang'] ?? '';
    $user       = $page_data[0]['user'] ?? '';
    $pupdate    = $page_data[0]['pupdate'] ?? '';
    //---
    echo <<<HTML
    <div class='card'>
        <div class='card-header'>
            <h4>Edit Page ($old_target)</h4>
        </div>
        <div class='card-body'>
    HTML;
    //---
    echo_form($id, $title, $new_target, $lang, $new_user, $pupdate);
    //---
    echo <<<HTML
        </div>
    </div>
    HTML;
    //---
}
