<?php
// src/app/coordinator/admin/admins/admins_post.php

namespace App\Coordinator\Admin\Admins;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use function App\APICalls\MdwikiSql\execute_query;

/**
 * Class AdminsPostProcessor
 * Handles add/update/delete of coordinator (admin) users.
 */
class AdminsPostProcessor extends AbstractPostHandler
{
    private const DB_TABLE_NAME = 'coordinators';

    /**
     * Validates and processes the incoming submission.
     */
    protected function process(array $post): void
    {
        $this->validateCoordinator();
        $this->processRows($post['rows'] ?? []);

    }

    /**
     * Processes each submitted coordinator row: delete, add, or update.
     */
    private function processRows(array $rows): void
    {
        foreach ($rows as $key => $table) {
            // '{ "id": "11", "username": "Ifteebd10", "del": "11" }'
            // '{ "id": "11", "username": "Ifteebd10", "is_new": "yes" }
            $uId = $table['id'] ?? '';
            $del = $table['del'] ?? '';
            $username = $table['username'] ?? '';

            if (!empty($del) && !empty($uId)) {
                $qua2 = "DELETE FROM " . self::DB_TABLE_NAME . " WHERE id = ?";

                $result = execute_query($qua2, [$uId]);

                if ($result === false) {
                    $this->addError("Failed to delete user $username.");
                    continue;
                }

                $this->addText("User $username deleted.");
                continue;
            }

            $username = trim($username);

            $isActive = $table['is_active'] ?? '';
            $activeOriginalValue = $table['active_orginal_value'] ?? '';

            if ($isActive == $activeOriginalValue && !empty($uId)) {
                continue;
            }

            if (!empty($username)) {
                $tableName = self::DB_TABLE_NAME;

                $qua = <<<SQL
                    INSERT INTO $tableName (username, is_active)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE
                        is_active = VALUES(is_active)
                SQL;

                $result = execute_query($qua, [$username, $isActive]);

                if ($result === false) {
                    $this->addError("Failed to add user $username.");
                } else {
                    $this->addText((empty($uId)) ? "User $username Added." : "User $username Updated.");
                }
            }
        }
    }

}
