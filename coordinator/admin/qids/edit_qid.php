<?php
// ---
if (user_in_coord == false) {
	echo "<meta http-equiv='refresh' content='0; url=index.php'>";
	exit;
};
// ---
use function Actions\Html\add_quotes;
use function TDWIKI\csrf\generate_csrf_token;
// ---
echo '</div><script>
    $("#mainnav").hide();
    $("#maindiv").hide();
</script>
<div class="container-fluid">';
// ---
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
};
// ---
$header_title = (($_GET['id'] ?? "") != "") ? "Edit Qid" : "Add New Qid";
//---
echo <<<HTML
<div class='card'>
    <div class='card-header'>
        <h4>$header_title</h4>
    </div>
    <div class='card-body'>
HTML;

function echo_form_post($id, $title, $qid, $qid_table)
{
	// ---
	$title2 = add_quotes($title);
	// ---
	$csrf_token = generate_csrf_token(); // <input name='csrf_token' value="$csrf_token" hidden />
	// ---
	$id_row = <<<HTML
		<div class='col-md-3'>
			<div class='input-group mb-3'>
				<div class='input-group-prepend'>
					<span class='input-group-text'>Id</span>
				</div>
				<input class='form-control' type='text' name='rows[1][id]' value='$id' readonly/>
			</div>
		</div>
	HTML;
	// ---
	if ($id == "") {
		$id_row = "";
	}
	// ---
	$dis = $_GET['dis'] ?? 'all';
	// ---
	echo <<<HTML
        <form action='index.php?ty=qids/post&qid_table=$qid_table&nonav=120' method="POST">
            <input name='csrf_token' value="$csrf_token" hidden />
            <input name='qid_table' value="$qid_table" hidden/>
            <input name='edit' value="1" hidden/>
            <div class='container'>
                <div class='row'>
                    $id_row
                    <div class='col-md-3'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>Title</span>
                            </div>
                            <input class='form-control' type='text' name='rows[1][title]' value=$title2 required/>
                        </div>
                    </div>
                    <div class='col-md-3'>
                        <div class='input-group mb-3'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>Qid</span>
                            </div>
                            <input class='form-control' type='text' name='rows[1][qid]' value='$qid' required/>
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
// ---
$title  = $_GET['title'] ?? '';
$qid    = $_GET['qid'] ?? '';
$id     = $_GET['id'] ?? '';
$table  = $_GET['qid_table'] ?? '';
// ---
if ($table != 'qids' && $table != 'qids_others') $table = 'qids';
// ---
echo_form_post($id, $title, $qid, $table);
// ---
echo <<<HTML
    </div>
</div>
HTML;
// ---
