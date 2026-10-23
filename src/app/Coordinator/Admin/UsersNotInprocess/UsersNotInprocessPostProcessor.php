<?php

namespace App\Coordinator\Admin\UsersNotInprocess;

use App\Coordinator\Admin\Common\AbstractUserListPostHandler;

/**
 * Class UsersNotInprocessPostProcessor
 * Handles add/update/delete of users excluded from the "in process" table.
 */
class UsersNotInprocessPostProcessor extends AbstractUserListPostHandler
{
    private const DB_TABLE_NAME = 'users_no_inprocess';

    protected function getTableName(): string
    {
        return self::DB_TABLE_NAME;
    }

    protected function getUsernameColumn(): string
    {
        return 'user';
    }

    protected function getRecords(): array
    {
        return $this->usersTable->getUsersNoInprocess();
    }
}
