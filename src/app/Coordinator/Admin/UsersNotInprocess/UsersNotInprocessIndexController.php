<?php

namespace App\Coordinator\Admin\UsersNotInprocess;

use App\SQLorAPI\UsersTable;
use App\Coordinator\Admin\Common\AbstractUserListIndexController;

/**
 * Class UsersNotInprocessIndexController
 * Renders the editable "users not added to in-process table" list. On
 * POST, delegates to UsersNotInprocessPostProcessor first, then always
 * renders the current state of the list/form below it.
 */
class UsersNotInprocessIndexController extends AbstractUserListIndexController
{
    protected function createPostProcessor(): object
    {
        return new UsersNotInprocessPostProcessor();
    }

    protected function getTyName(): string
    {
        return 'users_not_inprocess';
    }

    protected function getCardTitle(): string
    {
        return "Users Excluded from 'In Progress' Table:";
    }

    protected function getTableClass(): string
    {
        return 'table table-striped compact table-mobile-responsive table-mobile-sided table_text_left';
    }

    protected function getActiveDataContent(): string
    {
        return 'Active';
    }

    protected function getUserDataContent(): string
    {
        return 'User';
    }

    protected function getUsersData(): array
    {
        return UsersTable::getInstance()->getUsersNoInprocess();
    }
}
