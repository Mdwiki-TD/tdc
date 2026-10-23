<?php

namespace App\Coordinator\Admin\FullTranslators;

use App\SQLorAPI\UsersTable;
use App\Coordinator\Admin\Common\AbstractUserListIndexController;

/**
 * Class FullTranslatorsIndexController
 * Renders the editable "full article translators" table. On POST,
 * delegates to FullTranslatorsPostProcessor first, then always renders
 * the current state of the list/form below it.
 */
class FullTranslatorsIndexController extends AbstractUserListIndexController
{
    protected function createPostProcessor(): object
    {
        return new FullTranslatorsPostProcessor();
    }

    protected function getTyName(): string
    {
        return 'full_translators';
    }

    protected function getCardTitle(): string
    {
        return 'Full article translators:';
    }

    protected function getUsersData(): array
    {
        return UsersTable::getInstance()->getFullTranslators();
    }
}
