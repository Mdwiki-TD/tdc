<?php
// src/app/coordinator/admin/translated/EditPagePostHandler.php

namespace App\Coordinator\Admin\Translated;

use function App\APICalls\MdwikiSql\execute_query;
use function App\csrf\verify_csrf_token;

/**
 * Class EditPagePostHandler
 * Handles the POST submission for a single translated page row:
 * delete or update. Pure request/DB logic, no rendering.
 */
class EditPagePostHandler
{
    /**
     * Handles POST data submission for editing or deleting records.
     * @return array{success: bool, csrfError: bool}
     */
    public function handle(array $post, string $id, string $table): array
    {
        if (!verify_csrf_token()) {
            return ['success' => false, 'csrfError' => true];
        }

        if (isset($post['delete'])) {
            $this->deletePage($post['delete'], $table);
        } elseif (isset($post['edit'])) {
            $title   = $post['title'] ?? '';
            $target  = $post['target'] ?? '';
            $lang    = $post['lang'] ?? '';
            $user    = $post['user'] ?? '';
            $pupdate = $post['pupdate'] ?? '';

            $this->editPage($id, $table, $title, $target, $lang, $user, $pupdate);
        }

        return ['success' => true, 'csrfError' => false];
    }

    /**
     * Deletes a page record from the given database table.
     */
    private function deletePage(string $id, string $table): void
    {
        $query = "DELETE FROM {$table} WHERE id = ?";
        execute_query($query, [$id]);
    }

    /**
     * Updates page information in the database.
     */
    private function editPage(string $id, string $table, string $title, string $target, string $lang, string $user, string $pupdate): void
    {
        $query = "UPDATE {$table}
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

        execute_query($query, $params);

        if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
            echo "<pre>{$query}</pre>";
        }
    }
}
