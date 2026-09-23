<?php

use User\CurrentUser;

if (!CurrentUser::getInstance()->isCoordinator()) {
    header('Location: /index.php');
    exit;
};

use function APICalls\MdwikiSql\execute_query;
use function APICalls\MdwikiSql\fetch_query;
use function TDWIKI\csrf\verify_csrf_token;
use function Utils\Html\div_alert;
use function Add\AddPost\add_pages_to_db;

function deleteUserPage($id)
{
    execute_query("DELETE FROM pages_users_to_main WHERE id = ?", [$id]);
    execute_query("DELETE FROM pages_users WHERE id = ?", [$id]);

    $find_it_1 = fetch_query("SELECT 1 FROM pages_users       WHERE id = ? LIMIT 1", [$id]);
    $find_it_2 = fetch_query("SELECT 1 FROM pages_users_to_main WHERE id = ? LIMIT 1", [$id]);

    // $delete_done = (empty($find_it)) ? true : false;
    $delete_done = empty($find_it_1) && empty($find_it_2);

    return $delete_done;
}


$close_btn = <<<HTML
	<div class="aligncenter">
		<a class="btn btn-outline-primary" onclick="window.close()">Close</a>
	</div>
HTML;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['edit'])) {
    exit;
}

if (!verify_csrf_token()) {
    echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
    echo $close_btn;
    return;
}


$texts  = [];
$errors = [];

$title     = $_POST['title'] ?? '';
$lang      = $_POST['lang'] ?? '';
$newTarget = $_POST['new_target'] ?? '';
$newUser   = $_POST['new_user'] ?? '';
$pupdate   = $_POST['pupdate'] ?? '';
$id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    $errors[] = "Invalid id supplied.";
}

$pageData = fetch_query("SELECT * FROM pages_users WHERE id = ?", [$id]);

if (empty($pageData)) {
    $errors[] = "Page with id:($id) not found.";
} else {
    $tType    = $pageData[0]['translate_type'] ?? '';
    $cat       = $pageData[0]['cat'] ?? '';
    $word      = $pageData[0]['word'] ?? '';

    $result = add_pages_to_db($title, $tType, $cat, $lang, $newUser, $newTarget, $pupdate, $word);

    if ($result === false) {
        $errors[] = "Failed to add translations.";
    } else {
        $texts[] = "Translations added successfully.";

        $deleted = deleteUserPage($id);

        if ($deleted) {
            $texts[] = "Page with id:($id) deleted from pages_users .";
        } else {
            $errors[] = "Failed to delete page with id:($id).";
        }
    }
}

echo div_alert($texts, 'success');
echo div_alert($errors, 'danger');

echo $close_btn;
