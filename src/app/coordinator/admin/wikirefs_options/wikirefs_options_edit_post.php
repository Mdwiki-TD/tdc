<?php
// src/app/coordinator/admin/wikirefs_options/wikirefs_options_edit_post.php

namespace App\Coordinator\Admin\WikiRefsOptions;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use function App\APICalls\MdwikiSql\execute_query;

/**
 * Class WikiRefsOptionsEditPostHandler
 * Handles the POST submission for a single language_settings row:
 * delete, update, or insert.
 */
class WikiRefsOptionsEditPostHandler extends AbstractPostHandler
{
    protected function process(array $post): void
    {
        $langCode  = trim($post['lang_code'] ?? '');
        $expend    = $this->boolInt($post['expend'] ?? 0);
        $moveDots  = $this->boolInt($post['move_dots'] ?? 0);
        $addEnLang = $this->boolInt($post['add_en_lang'] ?? 0);

        if (isset($post['delete'])) {
            $this->deleteRow($post['delete'], $langCode);
        } elseif (($post['id'] ?? '') != "") {
            $this->updateRow($post['id'], $langCode, $expend, $moveDots, $addEnLang);
        } elseif (($post['new'] ?? '') != "") {
            $this->insertRow($langCode, $expend, $moveDots, $addEnLang);
        } else {
            $this->addError("Id is empty.");
        }
    }

    /**
     * Deletes a language_settings row.
     */
    private function deleteRow($id, string $langCode): void
    {
        $qua = "DELETE FROM language_settings WHERE id = ?";

        $result = execute_query($qua, [$id]);

        if ($result === false) {
            $this->addError("Failed to delete language $langCode.");
        } else {
            $this->addText("language $langCode deleted.");
        }
    }

    /**
     * Updates an existing language_settings row.
     */
    private function updateRow($id, string $langCode, int $expend, int $moveDots, int $addEnLang): void
    {
        $qua = "UPDATE language_settings
            SET
                lang_code = ?,
                expend = ?,
                move_dots = ?,
                add_en_lang = ?
            WHERE
                id = ?
            ";
        $params = [$langCode, $expend, $moveDots, $addEnLang, $id];

        $result = execute_query($qua, $params);

        if ($result === false) {
            $this->addError("Failed to update language $langCode.");
        } else {
            $this->addText("language $langCode updated.");
        }
    }

    /**
     * Inserts a new language_settings row.
     */
    private function insertRow(string $langCode, int $expend, int $moveDots, int $addEnLang): void
    {
        if (empty($langCode)) {
            $this->addError("Lang code is empty.");
            return;
        }

        $qua = "INSERT INTO language_settings (lang_code, expend, move_dots, add_en_lang) VALUES (?, ?, ?, ?)";
        $params = [$langCode, $expend, $moveDots, $addEnLang];

        $result = execute_query($qua, $params);

        if ($result === false) {
            $this->addError("Failed to add language $langCode.");
        } else {
            $this->addText("language $langCode added.");
        }
    }
}
