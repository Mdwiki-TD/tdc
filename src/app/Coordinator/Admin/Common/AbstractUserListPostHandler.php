<?php

namespace App\Coordinator\Admin\Common;

use App\SQLorAPI\UsersTable;

/**
 * Class AbstractUserListPostHandler
 *
 * Abstract base class for user list POST processors (Admins, FullTranslators, UsersNotInprocess).
 */
abstract class AbstractUserListPostHandler extends AbstractPostHandler
{
    protected bool $isPopUpPage = false;
    protected UsersTable $usersTable;

    abstract protected function getTableName(): string;
    abstract protected function getUsernameColumn(): string;
    abstract protected function getRecords(): array;

    protected function getEntityLabel(): string
    {
        return 'User';
    }

    protected function requiresAdmin(): bool
    {
        return false;
    }

    public function __construct()
    {
        parent::__construct();
        $this->usersTable = UsersTable::getInstance();
    }

    protected function process(array $post): void
    {
        if ($this->requiresAdmin() && !$this->validateAdmin()) {
            $this->exit();
            return;
        }
        $this->processRows($post['rows'] ?? []);
        if (isset($post['new'])) {
            $this->processNewRows($post['new']);
        }
    }

    protected function processNewRows(array $newRows): void
    {
        $tableName = $this->getTableName();
        $usernameCol = $this->getUsernameColumn();

        foreach ($newRows as $key => $table) {
            $rawUsername = $table[$usernameCol] ?? '';
            $username = trim($rawUsername);

            if (empty($username)) {
                $hasOtherData = false;
                foreach ($table as $k => $val) {
                    if ($k === $usernameCol || $k === 'is_active') {
                        continue;
                    }
                    if (trim((string)$val) !== '') {
                        $hasOtherData = true;
                        break;
                    }
                }
                if ($rawUsername !== '' || $hasOtherData) {
                    $this->addWarning("Username is required.");
                }
                continue;
            }

            $isActive = isset($table['is_active']) ? $this->boolInt($table['is_active'], 1) : 1;

            $qua = <<<SQL
                INSERT INTO $tableName ($usernameCol, is_active)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE
                    is_active = VALUES(is_active)
            SQL;

            $result = $this->db->executeQuery($qua, [$username, $isActive]);
            $this->verifyInsertResult($result, $username, 'Username');
        }
    }

    protected function processRows(array $rows): void
    {
        $tableName = $this->getTableName();
        $usernameCol = $this->getUsernameColumn();
        $entityLabel = $this->getEntityLabel();

        $records = $this->getRecords();
        $recordsById = array_column($records, null, 'id');

        foreach ($rows as $key => $table) {
            $id = $table['id'] ?? '';
            $del = $table['del'] ?? '';

            if (empty($id)) {
                continue;
            }
            $dbData = $recordsById[$id] ?? [];
            if (empty($dbData)) {
                $this->addWarning("$entityLabel with id $id does not exist.");
                continue;
            }

            if ($del === $id) {
                $this->deleteRow($id, $tableName, "User");
                continue;
            }
            $username = $dbData[$usernameCol];

            $isActive = $this->boolInt($table['is_active'] ?? 0);

            if ($isActive == $dbData['is_active']) {
                continue;
            }

            $qua = "UPDATE $tableName SET is_active = ? WHERE id = ?";

            $result = $this->db->executeQuery($qua, [$isActive, $id]);

            $this->verifyUpdateResult($result, $username, 'Username');
        }
    }
}
