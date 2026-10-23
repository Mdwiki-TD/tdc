<?php

namespace App\Coordinator\Admin\Admins;

use App\SQLorAPI\UsersTable;
use App\Coordinator\Admin\Common\AbstractUserListIndexController;

/**
 * Class AdminsIndexController
 * Renders the editable coordinators (admins) table. On POST, delegates
 * to AdminsPostProcessor first, then always renders the current state
 * of the list/form below it.
 */
class AdminsIndexController extends AbstractUserListIndexController
{
    public bool $forAdminOnly = true;

    protected function createPostProcessor(): object
    {
        return new AdminsPostProcessor();
    }

    protected function getTyName(): string
    {
        return 'admins';
    }

    protected function getCardTitle(): string
    {
        return 'Coordinators:';
    }

    protected function getUsernameField(): string
    {
        return 'username';
    }

    protected function shouldSortRecords(): bool
    {
        return true;
    }

    protected function getSaveButtonExtra(): string
    {
        return "\n                            <span> </span>";
    }

    protected function getUsersData(): array
    {
        return UsersTable::getInstance()->getCoordinators();
    }
}
