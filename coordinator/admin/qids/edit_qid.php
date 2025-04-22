<?php
//---
if (user_in_coord == false) {
    echo "<meta http-equiv='refresh' content='0; url=index.php'>";
    exit;
};
//---
// include_once 'actions/functions.php';
//---
use function Actions\MdwikiSql\execute_query;
use function Actions\Html\add_quotes;
use function TDWIKI\csrf\generate_csrf_token;
use function TDWIKI\csrf\verify_csrf_token;
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
$tabs = [];
//---
echo <<<HTML
<div class='card'>
    <div class='card-header'>
        <h4>Edit Qid</h4>
    </div>
    <div class='card-body'>
HTML;

function send_qid($id, $title, $qid, $qid_table)
{
    //---
    if ($qid_table != 'qids' && $qid_table != 'qids_others') $qid_table = 'qids';
    //---
    $qua = "UPDATE $qid_table
    SET
        title = ?,
        qid = ?
    WHERE
        id = ?
    ";
    $params = [$title, $qid, $id];
    //---
    execute_query($qua, $params);
    //---
    // green text success
    echo <<<HTML
        <div class='alert alert-success' role='alert'>Qid updated<br>
            window will close in 3 seconds
        </div>
        <!-- close window after 3 seconds -->
        <script>
            setTimeout(function() {
                window.close();
            }, 3000);
        </script>
    HTML;
}

function echo_form($id, $title, $qid, $qid_table)
{
    //---
    if ($qid_table != 'qids' && $qid_table != 'qids_others') $qid_table = 'qids';
    //---
    $title2 = add_quotes($title);
    //---
    $csrf_token = generate_csrf_token(); // <input name='csrf_token' value="$csrf_token" hidden />
    //---
    echo <<<HTML
        <form action='index.php?ty=qids/edit_qid&nonav=120' method="POST">
            <input name='csrf_token' value="$csrf_token" hidden />
            <input name='qid_table' value="$qid_table" hidden/>
            <input name='edit' value="1" hidden/>
            <div class='container'>
                <div class='row'>
                    <div class='col-md-3'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>Id</span>
                            </div>
                            <input class='form-control' type='text' value='$id' disabled/>
                            <input class='form-control' type='text' id='id' name='id' value='$id' hidden/>
                        </div>
                    </div>
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
                                <span class='input-group-text'>Qid</span>
                            </div>
                            <input class='form-control' type='text' id='qid' name='qid' value='$qid' required/>
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
if (isset($_POST['edit']) && verify_csrf_token()) {
    // ---
    $title  = $_POST['title'] ?? '';
    $qid    = $_POST['qid'] ?? '';
    $id     = $_POST['id'] ?? '';
    $qid_table     = $_POST['qid_table'] ?? '';
    // ---
    send_qid($id, $title, $qid, $qid_table);
} else {
    $title  = $_GET['title'] ?? '';
    $qid    = $_GET['qid'] ?? '';
    $id     = $_GET['id'] ?? '';
    $qid_table     = $_GET['qid_table'] ?? '';
    //---
    echo_form($id, $title, $qid, $qid_table);
}
//---
echo <<<HTML
    </div>
</div>
HTML;
//---
