<?php
// src/app/coordinator/admin/pages_users_to_main/fix_page_post.php

namespace App\Coordinator\Admin\PagesUsersToMain;

use App\MdwikiSql\AddHelper;
use App\Tables\Main\MainTables;
use App\Coordinator\Admin\Common\AbstractPostHandler;

/**
 * Class FixItPostProcessor
 * Handles input validation, database inserts, and table cleaning for POST submissions.
 */
class FixItPostProcessor extends AbstractPostHandler
{
    protected bool $isPopUpPage = true;

    private AddHelper $addHelper;

    public function __construct()
    {
        parent::__construct();
        $this->addHelper = new AddHelper();
    }

    public function process(array $post): void
    {
        $this->processFormData($post);
    }

    /**
     * Processes submission inputs and executes database changes.
     */
    private function processFormData(array $post): void
    {
        $overwrite = (int)($post['overwrite'] ?? "0") == 1;
        $title     = $post['title'] ?? '';
        $lang      = $post['lang'] ?? '';
        $newTarget = $post['new_target'] ?? '';
        $newUser   = $post['new_user'] ?? '';
        $pupdate   = $post['pupdate'] ?? '';
        $id        = isset($post['id']) ? (int)$post['id'] : 0;

        if ($id <= 0) {
            $this->addError("Invalid id supplied.");
        }

        $pageData = $this->db->fetchQuery("SELECT * FROM pages_users WHERE id = ?", [$id]);

        if (empty($pageData)) {
            $this->addError("Page with id:($id) not found.");
            return;
        }

        $translateType = $pageData[0]['translate_type'] ?? '';
        $cat   = $pageData[0]['cat'] ?? '';
        $word  = $pageData[0]['word'] ?? '';

        $translateType = (!empty($translateType)) ? $translateType : 'lead';

        if (empty($word)) {
            $word = MainTables::getWord($title, $translateType);
        }

        $add = $this->addHelper->addPagesToDb(
            $title,
            $translateType,
            $cat,
            $lang,
            $newUser,
            $newTarget,
            $pupdate,
            $word,
            $overwrite,
        );
        if ($add === false) {
            $this->addError("Failed to add translations.");
        } else {
            $this->addText("Translations added successfully.");
        }

        $result = $this->addHelper->checkAfterAdd($title, $lang, $newUser, $newTarget);

        if ($result === false) {
            $this->addError("checkAfterAdd: Failed to add translations.");
        } else {
            $deleted = $this->deleteUserPage($id);

            if ($deleted) {
                $this->addText("Page with id:($id) deleted from pages_users.");
            } else {
                $this->addError("Failed to delete page with id:($id).");
            }
        }
    }

    /**
     * Removes the record from user pages tables after successful processing.
     */
    private function deleteUserPage(int $id): bool
    {
        $this->db->executeQuery("DELETE FROM pages_users_to_main WHERE id = ?", [$id]);
        $this->db->executeQuery("DELETE FROM pages_users WHERE id = ?", [$id]);

        $findIt1 = $this->db->fetchQuery("SELECT 1 FROM pages_users WHERE id = ? LIMIT 1", [$id]);
        $findIt2 = $this->db->fetchQuery("SELECT 1 FROM pages_users_to_main WHERE id = ? LIMIT 1", [$id]);

        return empty($findIt1) && empty($findIt2);
    }
}
