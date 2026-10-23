<?php

namespace App\Coordinator\Admin\FullTranslators;

use App\Coordinator\Admin\Common\AbstractUserListPostHandler;

/**
 * Class FullTranslatorsPostProcessor
 * Handles add/update/delete of "full article translator" users.
 */
class FullTranslatorsPostProcessor extends AbstractUserListPostHandler
{
    private const DB_TABLE_NAME = 'full_translators';

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
        return $this->usersTable->getFullTranslators();
    }
}
