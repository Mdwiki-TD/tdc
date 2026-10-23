<?php

namespace App\Coordinator\Admin\Admins;

use App\Coordinator\Admin\Common\AbstractUserListPostHandler;

/**
 * Class AdminsPostProcessor
 * Handles add/update/delete of coordinator (admin) users.
 */
class AdminsPostProcessor extends AbstractUserListPostHandler
{
    private const DB_TABLE_NAME = 'coordinators';

    protected function getTableName(): string
    {
        return self::DB_TABLE_NAME;
    }

    protected function getUsernameColumn(): string
    {
        return 'username';
    }

    protected function getEntityLabel(): string
    {
        return 'Coordinator';
    }

    protected function requiresAdmin(): bool
    {
        return true;
    }

    protected function getRecords(): array
    {
        return $this->usersTable->getCoordinators();
    }
}
