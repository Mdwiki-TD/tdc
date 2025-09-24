<?php
//---
if (user_in_coord == false) {
    echo "<meta http-equiv='refresh' content='0; url=index.php'>";
    exit;
};
//---
require_once __DIR__ . '/../add/add_post.php';
//---
use function APICalls\MdwikiSql\execute_query;
use function APICalls\MdwikiSql\fetch_query;
use function TDWIKI\csrf\verify_csrf_token;
use function Utils\Html\div_alert; // echo div_alert($texts, 'success');
use function Add\AddPost\add_pages_to_db;
//---

function delete_user_page($id)
{
    execute_query("DELETE FROM pages_users_to_main WHERE id = ?", [$id]);
    execute_query("DELETE FROM pages_users WHERE id = ?", [$id]);
    //---
    $find_it_1 = fetch_query("SELECT 1 FROM pages_users       WHERE id = ? LIMIT 1", [$id]);
    $find_it_2 = fetch_query("SELECT 1 FROM pages_users_to_main WHERE id = ? LIMIT 1", [$id]);
    $delete_done = (empty($find_it)) ? true : false;
    //---
    $delete_done = empty($find_it_1) && empty($find_it_2);
    //---
    return $delete_done;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token() && isset($_POST['edit'])) {
    // ---
    $texts = [];
    $errors = [];
    //---
    $title      = $_POST['title'] ?? '';
    $lang       = $_POST['lang'] ?? '';
    $new_target = $_POST['new_target'] ?? '';
    $new_user   = $_POST['new_user'] ?? '';
    $pupdate    = $_POST['pupdate'] ?? '';
    //---
    $id         = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    //---
    if ($id <= 0) {
        $errors[] = "Invalid id supplied.";
    }
    //---
    $page_data = fetch_query("SELECT * FROM pages_users WHERE id = ?", [$id]);
    //---
    if (empty($page_data)) {
        $errors[] = "Page with id:($id) not found.";
    } else {
        $t_type    = $page_data[0]['translate_type'] ?? '';
        $cat       = $page_data[0]['cat'] ?? '';
        $word      = $page_data[0]['word'] ?? '';
        //---
        $result = add_pages_to_db($title, $t_type, $cat, $lang, $new_user, $new_target, $pupdate, $word);
        //---
        if ($result === false) {
            $errors[] = "Failed to add translations.";
        } else {
            $texts[] = "Translations added successfully.";
            //---
            $del_it = delete_user_page($id);
            //---
            if ($del_it) {
                $texts[] = "Page with id:($id) deleted from pages_users .";
            } else {
                $errors[] = "Failed to delete page with id:($id).";
            }
        }
    }
    //---
    echo div_alert($texts, 'success');
    echo div_alert($errors, 'danger');
}
