<?php
// src/app/coordinator/admin/translated/EditPagePostHandler.php

namespace App\Coordinator\Admin\Translated;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use function App\APICalls\MdwikiSql\execute_query;

/**
 * Class EditPagePostHandler
 * Handles the POST submission for a single translated page row:
 * delete or update.
 */
class EditPagePostHandler extends AbstractPostHandler
{
    protected bool $isPopUpPage = true;

    private string $id;
    private string $table;

    /**
     * id/table come from the route, not the posted fields we loop over,
     * so they're passed in up front rather than through process()'s $post.
     */
    public function __construct(string $id, string $table)
    {
        $this->id = $id;
        $this->table = $table;
    }

    protected function process(array $post): void
    {
        if (isset($post['delete'])) {
            $deleteId = (string) $post['delete'];
            $result = $this->deletePage($deleteId, $this->table);
            if ($result === false) {
                $this->addError("Failed to delete page (id: {$deleteId}).");
            } else {
                $this->addText("Page (id: {$deleteId}) deleted successfully.");
            }
        } elseif (isset($post['edit'])) {
            $title   = $post['title'] ?? '';
            $target  = $post['target'] ?? '';
            $lang    = $post['lang'] ?? '';
            $user    = $post['user'] ?? '';
            $pupdate = $post['pupdate'] ?? '';

            $result = $this->editPage($this->id, $this->table, $title, $target, $lang, $user, $pupdate);
            if ($result === false) {
                $this->addError("Failed to update page (id: {$this->id}).");
            } else {
                $this->addText("Page (id: {$this->id}) updated successfully.");
            }
        }
    }

    /**
     * Deletes a page record from the given database table.
     *
     * @return array<mixed>|false
     */
    private function deletePage(string $id, string $table)
    {
        $query = "DELETE FROM {$table} WHERE id = ?";
        return execute_query($query, [$id]);
    }

    /**
     * Updates page information in the database.
     *
     * @return array<mixed>|false
     */
    private function editPage(string $id, string $table, string $title, string $target, string $lang, string $user, string $pupdate)
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

        $result = execute_query($query, $params);

        if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
            echo "<pre>{$query}</pre>";
        }

        return $result;
    }
}
