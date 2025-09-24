<?php
//---
if (user_in_coord == false) {
    echo "<meta http-equiv='refresh' content='0; url=index.php'>";
    exit;
};
//---
use function APICalls\MdwikiSql\execute_query;
use function APICalls\MdwikiSql\fetch_query;
use function TDWIKI\csrf\generate_csrf_token;
use function TDWIKI\csrf\verify_csrf_token;
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
$id         = $_GET['id'] ?? $_POST['id'] ?? '';
$table      = $_GET['table'] ?? $_POST['table'] ?? 'pages';
//---
echo <<<HTML
<div class='card'>
    <div class='card-header'>
        <h4>Edit Page (id: $id, table: $table)</h4>
    </div>
    <div class='card-body'>
HTML;
//---
function delete_page($id, $table)
{
    $qua = "DELETE FROM $table WHERE id = ?";
    // ---
    $params = [$id];
    // ---
    execute_query($qua, $params);
    //---
}

function edit_page($id, $table, $title, $target, $lang, $user, $pupdate)
{
    $qua = "UPDATE $table
    SET
        title = ?,
        target = ?,
        lang = ?,
        user = ?,
        pupdate = ?
    WHERE
        id = ?
    ";
    $params = [$title, $target, $lang, $user, $pupdate, $id];
    //---
    execute_query($qua, $params);
    //---
    if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
        echo "<pre>$qua</pre>";
        // echo "<pre>$params</pre>";
    }
}

function echo_form($id, $table)
{
    //---
    $page_data = fetch_query("SELECT * FROM $table WHERE id = ?", [$id]);
    //---
    $title      = $page_data[0]['title'] ?? '';
    $target     = $page_data[0]['target'] ?? '';
    $lang       = $page_data[0]['lang'] ?? '';
    $user       = $page_data[0]['user'] ?? '';
    $pupdate    = $page_data[0]['pupdate'] ?? '';
    //---
    $test_line = (isset($_REQUEST['test'])) ? '<input type="hidden" name="test" value="1" />' : "";

	$title2 = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
	$target2 = htmlspecialchars($target, ENT_QUOTES, 'UTF-8');
    //---
    $csrf_token = generate_csrf_token(); // <input name='csrf_token' value="$csrf_token" type="hidden"/>
    //---
    echo <<<HTML
        <form action='index.php?ty=translated/edit_page&nonav=120' method="POST">
            <input name='csrf_token' value="$csrf_token" type="hidden"/>
            <input id='id' name='id' value='$id' type='hidden'/>
            <input name='edit' value="1" type="hidden"/>
            <input name='table' value="$table" type="hidden"/>
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
                                <span class='input-group-text'>target</span>
                            </div>
                            <input class='form-control' type='text' id='target' name='target' value='$target2' required/>
                        </div>
                    </div>
                    <div class='col-md-3'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>user</span>
                            </div>
                            <input class='form-control' type='text' id='user' name='user' value='$user' required/>
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
                    <div class='col-md-3'>
                        <div class='input-group form-control mb-3 alert alert-warning'>
                            <div class='input-group-prepend'>
                                <span class='me-3'>Delete?</span>
                            </div>
                            <div class="form-check form-switch form-inline">
                                <input class="form-check-input" type="checkbox" name="delete" value="$id">
                            </div>
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token()) {
    if (isset($_POST['delete'])) {
        delete_page($_POST['delete'], $table);
        // ---
    } elseif (isset($_POST['edit'])) {
        $title      = $_POST['title'] ?? '';
        $target     = $_POST['target'] ?? '';
        $lang       = $_POST['lang'] ?? '';
        $user       = $_POST['user'] ?? '';
        $pupdate    = $_POST['pupdate'] ?? '';
        //---
        edit_page($id, $table, $title, $target, $lang, $user, $pupdate);
    }
    //---
    // green text success
    echo <<<HTML
        <div class='alert alert-success' role='alert'>Page updated<br>
            window will close in 3 seconds
        </div>
        <script>
            setTimeout(function() {
                window.close();
            }, 3000);
        </script>
    HTML;
} else {
    echo_form($id, $table);
}
//---
echo <<<HTML
    </div>
</div>
HTML;
//---
