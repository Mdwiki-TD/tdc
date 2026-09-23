<?php
// src/app/coordinator/admin/wikirefs_options/edit_post.php

namespace App\Coordinator\Admin\WikiRefsOptions;

use function App\APICalls\MdwikiSql\execute_query;
use function App\csrf\verify_csrf_token;

/**
 * Class WikiRefsOptionsEditPostHandler
 * Handles the POST submission for a single language_settings row:
 * delete, update, or insert. Pure request/DB logic, no rendering.
 */
class WikiRefsOptionsEditPostHandler
{
    private array $errors = [];
    private array $texts = [];

    /**
     * @return array{errors: string[], texts: string[]}
     */
    public function handle(array $post): array
    {
        if (!verify_csrf_token()) {
            $this->errors[] = "Invalid or Reused CSRF Token!";
            return $this->result();
        }

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
            $this->errors[] = "Id is empty.";
        }

        return $this->result();
    }

    private function boolInt($value): int
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0, 'max_range' => 1]]
        ) ?: 0;
    }

    private function result(): array
    {
        return ['errors' => $this->errors, 'texts' => $this->texts];
    }

    /**
     * Deletes a language_settings row.
     */
    private function deleteRow($id, string $langCode): void
    {
        $qua = "DELETE FROM language_settings WHERE id = ?";

        $result = execute_query($qua, [$id]);

        if ($result === false) {
            $this->errors[] = "Failed to delete language $langCode.";
        } else {
            $this->texts[] = "language $langCode deleted.";
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
            $this->errors[] = "Failed to update language $langCode.";
        } else {
            $this->texts[] = "language $langCode updated.";
        }
    }

    /**
     * Inserts a new language_settings row.
     */
    private function insertRow(string $langCode, int $expend, int $moveDots, int $addEnLang): void
    {
        if (empty($langCode)) {
            $this->errors[] = "Lang code is empty.";
            return;
        }

        $qua = "INSERT INTO language_settings (lang_code, expend, move_dots, add_en_lang) VALUES (?, ?, ?, ?)";
        $params = [$langCode, $expend, $moveDots, $addEnLang];

        $result = execute_query($qua, $params);

        if ($result === false) {
            $this->errors[] = "Failed to add language $langCode.";
        } else {
            $this->texts[] = "language $langCode added.";
        }
    }
}
