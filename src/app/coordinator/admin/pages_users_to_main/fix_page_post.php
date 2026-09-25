<?php
// src/app/coordinator/admin/pages_users_to_main/fix_page_post.php

namespace App\Coordinator\Admin\PagesUsersToMain;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use function App\APICalls\MdwikiSql\execute_query;
use function App\APICalls\MdwikiSql\fetch_query;
use function App\Coordinator\Helps\AddHelper\add_pages_to_db;

/**
 * Class FixItPostProcessor
 * Handles input validation, database inserts, and table cleaning for POST submissions.
 */
class FixItPostProcessor extends AbstractPostHandler
{
    protected bool $isPopUpPage = true;

	public function process(array $post): void
	{
        $this->processFormData($post);
	}

    /**
     * Processes submission inputs and executes database changes.
     */
    private function processFormData(array $post): void
    {
        $title     = $post['title'] ?? '';
        $lang      = $post['lang'] ?? '';
        $newTarget = $post['new_target'] ?? '';
        $newUser   = $post['new_user'] ?? '';
        $pupdate   = $post['pupdate'] ?? '';
        $id        = isset($post['id']) ? (int)$post['id'] : 0;

        if ($id <= 0) {
            $this->addError("Invalid id supplied.");
        }

        $pageData = fetch_query("SELECT * FROM pages_users WHERE id = ?", [$id]);

        if (empty($pageData)) {
            $this->addError("Page with id:($id) not found.");
        } else {
            $tType = $pageData[0]['translate_type'] ?? '';
            $cat   = $pageData[0]['cat'] ?? '';
            $word  = $pageData[0]['word'] ?? '';

            $result = add_pages_to_db($title, $tType, $cat, $lang, $newUser, $newTarget, $pupdate, $word);

            if ($result === false) {
                $this->addError("Failed to add translations.");
            } else {
                $this->addText("Translations added successfully.");

                $deleted = $this->deleteUserPage($id);

                if ($deleted) {
                    $this->addText("Page with id:($id) deleted from pages_users.");
                } else {
                    $this->addError("Failed to delete page with id:($id).");
                }
            }
        }
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

}
