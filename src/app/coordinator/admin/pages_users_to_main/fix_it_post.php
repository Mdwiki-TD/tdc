<?php
// src/app/coordinator/admin/pages_users_to_main/fix_it_post.php

namespace App\Coordinator\Admin\PagesUsersToMain;

use App\User\CurrentUser;
use function App\APICalls\MdwikiSql\execute_query;
use function App\APICalls\MdwikiSql\fetch_query;
use function App\csrf\verify_csrf_token;
use function App\Utils\Html\div_alert;
use function Add\AddPost\add_pages_to_db;

/**
 * Class FixItPostProcessor
 * Handles input validation, database inserts, and table cleaning for POST submissions.
 */
class FixItPostProcessor
{
    /**
     * Validates and triggers the submission handling.
     */
    public function handle(): void
    {
        // Check coordinator authorization
        if (!CurrentUser::getInstance()->isCoordinator()) {
            header('Location: /index.php');
            exit;
        }

        // Verify POST method and form trigger flag
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['edit'])) {
            exit;
        }

        // CSRF Token validation
        if (!verify_csrf_token()) {
            echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
            echo $this->renderCloseButton();
            return;
        }

        $this->processFormData();
    }

    /**
     * Processes submission inputs and executes database changes.
     */
    private function processFormData(): void
    {
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
            $tType = $pageData[0]['translate_type'] ?? '';
            $cat   = $pageData[0]['cat'] ?? '';
            $word  = $pageData[0]['word'] ?? '';

            $result = add_pages_to_db($title, $tType, $cat, $lang, $newUser, $newTarget, $pupdate, $word);

            if ($result === false) {
                $errors[] = "Failed to add translations.";
            } else {
                $texts[] = "Translations added successfully.";

                $deleted = $this->deleteUserPage($id);

                if ($deleted) {
                    $texts[] = "Page with id:($id) deleted from pages_users.";
                } else {
                    $errors[] = "Failed to delete page with id:($id).";
                }
            }
        }

        echo div_alert($texts, 'success');
        echo div_alert($errors, 'danger');
        echo $this->renderCloseButton();
    }

    /**
     * Removes the record from user pages tables after successful processing.
     */
    private function deleteUserPage(int $id): bool
    {
        execute_query("DELETE FROM pages_users_to_main WHERE id = ?", [$id]);
        execute_query("DELETE FROM pages_users WHERE id = ?", [$id]);

        $findIt1 = fetch_query("SELECT 1 FROM pages_users WHERE id = ? LIMIT 1", [$id]);
        $findIt2 = fetch_query("SELECT 1 FROM pages_users_to_main WHERE id = ? LIMIT 1", [$id]);

        return empty($findIt1) && empty($findIt2);
    }

    /**
     * Generates a close button HTML block.
     */
    private function renderCloseButton(): string
    {
        return <<<HTML
            <div class="aligncenter">
                <a class="btn btn-outline-primary" onclick="window.close()">Close</a>
            </div>
        HTML;
    }
}
