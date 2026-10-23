<?php
// src/app/Coordinator/Admin/admins/admins_post.php

namespace App\Coordinator\Admin\Admins;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use App\Logger;
use App\SQLorAPI\UsersTable;
/**
 * Class AdminsPostProcessor
 * Handles add/update/delete of coordinator (admin) users.
 */
class AdminsPostProcessor extends AbstractPostHandler
{
    protected bool $isPopUpPage = false;

    private const DB_TABLE_NAME = 'coordinators';
    private UsersTable $usersTable;

    public function __construct()
    {
        parent::__construct();
        $this->usersTable = UsersTable::getInstance();
    }

    /**
     * Validates and processes the incoming submission.
     */
    protected function process(array $post): void
    {
        if (!$this->validateAdmin()) {
            $this->exit();
            return;
        }
        $this->processRows($post['rows'] ?? []);
        if (isset($post['new'])) {
            $this->processNewRows($post['new']);
        }
    }
    private function processNewRows(array $newRows): void
    {
        foreach ($newRows as $key => $table) {
            // '{ "id": "11", "username": "Ifteebd10"}
            $username = trim($table['username'] ?? '');

            if (empty($username)) {
                $this->addWarning("Username is required.");
                continue;
            }

            $isActive = $this->boolInt($table['is_active'] ?? 0, 1);
            $tableName = self::DB_TABLE_NAME;

            $qua = <<<SQL
                INSERT INTO $tableName (username, is_active)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE
                    is_active = VALUES(is_active)
            SQL;

            $result = $this->db->executeQuery($qua, [$username, $isActive]);
            $this->verifyInsertResult($result, $username, 'Username');
        }
    }
    /**
     * Processes each submitted coordinator row: delete, add, or update.
     */
    private function processRows(array $rows): void
    {
        $records = $this->usersTable->getCoordinators();
        $recordsById = array_column($records, null, 'id');

        foreach ($rows as $key => $table) {
            // '{ "id": "11", "username": "u", "del": "11" }'
            // '{ "id": "11", "username": "u", "is_active": 1}
            $id = $table['id'] ?? '';
            $del = $table['del'] ?? '';

            if (empty($id)) {
                continue;
            }
            $dbData = $recordsById[$id] ?? [];
            if (empty($dbData)) {
                $this->addWarning("Coordinator with id $id does not exist.");
                continue;
            }

            if ($del === $id) {
                $this->deleteRow($id, self::DB_TABLE_NAME, "User");
                continue;
            }
            $username = $dbData['username'];

            $isActive = $this->boolInt($table['is_active'] ?? 0);

            if ($isActive == $dbData['is_active']) {
                continue;
            }

            $tableName = self::DB_TABLE_NAME;

            $qua = "UPDATE $tableName SET is_active = ? WHERE id = ?";

            $result = $this->db->executeQuery($qua, [$isActive, $id]);

            $this->verifyUpdateResult($result, $username, 'Username');
        }
    }
}
