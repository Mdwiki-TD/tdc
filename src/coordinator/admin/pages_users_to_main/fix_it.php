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
use function Utils\Html\div_alert; // echo div_alert($texts, 'success');
use function APICalls\MdwikiSql\fetch_query;
use function TDWIKI\csrf\generate_csrf_token;
//---
function echo_form($id, $title, $new_target, $lang, $new_user, $pupdate)
{
    $test_line = (isset($_REQUEST['test'])) ? '<input type="hidden" name="test" value="1" />' : "";

    $title2 = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $target2 = htmlspecialchars($new_target, ENT_QUOTES, 'UTF-8');
    //---
    $csrf_token = generate_csrf_token(); // <input name='csrf_token' value="$csrf_token" type="hidden"/>
    //---
    return <<<HTML
        <form action='index.php?ty=pages_users_to_main/fix_it&nonav=120' method="POST">
            <input name='csrf_token' value="$csrf_token" type="hidden"/>
            <input id='id' name='id' value='$id' type='hidden'/>
            <input name='edit' value="1" type="hidden"/>
            $test_line
            <div class='container'>
                <div class='row'>
                    <div class='col-md-3'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>Title</span>
                            </div>
                            <input class='form-control' type='text' id='title' name='title' value='$title2' required/>
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
                            <input class='form-control' type='text' id='new_target' name='new_target' value='$target2' required/>
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
function page_already_exist($in_db)
{
    // ---
    // var_export(json_encode($in_db, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    // '[ { "id": 7553, "title": "Baclofen toxicity", "word": 213, "translate_type": "lead", "cat": "RTT", "lang": "uk", "user": "الاء هارون", "target": "سمية الباكلوفين", "date": "2025-04-25", "pupdate": "2025-02-20", "add_date": "2025-04-25 03:00:00", "deleted": 0 } ]'
    // ---
    $db_target = $in_db[0]['target'] ?? '';
    $db_user = $in_db[0]['user'] ?? '';
    $db_pupdate = $in_db[0]['pupdate'] ?? '';
    $lang = $in_db[0]['lang'] ?? '';
    // ---
    return <<<HTML
        <div class='card mb-3'>
            <div class='card-header alert alert-danger'>
                <h4>Duplicate page already exists in DB:</h4>
            </div>
            <div class='card-body p-1'>
                <ul class='list-group'>
                    <li class='list-group-item'>
                        <span class='fw-bold'>Target:</span>
                        <a target='_blank' href='https://$lang.wikipedia.org/wiki/$db_target'>$db_target</a>
                    </li>
                    <li class='list-group-item'>
                        <span class='fw-bold'>User:</span>
                        $db_user
                    </li>
                    <li class='list-group-item'>
                        <span class='fw-bold'>Publication date:</span>
                        $db_pupdate
                    </li>
                </ul>
            </div>
        </div>
        HTML;
}
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
    $lang       = $page_data[0]['lang'] ?? '';
    //---
    $in_db = fetch_query("SELECT * FROM pages WHERE title = ? AND lang = ? and (target != '' AND target IS NOT NULL)", [$title, $lang]);
    //---
    if (!empty($in_db)) {
        // ---
        echo page_already_exist($in_db);
        // ---
    }
    //---
    // var_export(json_encode($in_db, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    //---
    $old_target = $page_data[0]['target'] ?? '';
    $user       = $page_data[0]['user'] ?? '';
    $pupdate    = $page_data[0]['pupdate'] ?? '';
    //---
    $form = echo_form($id, $title, $new_target, $lang, $new_user, $pupdate);
    //---
    echo <<<HTML
        <div class='card'>
            <div class='card-header'>
                <h4>Edit Page ($old_target)</h4>
            </div>
            <div class='card-body'>
                $form
            </div>
        </div>
    HTML;
    //---
}
