<?php
// src/app/Coordinator/Admin/users_not_inprocess/index.php

namespace App\Coordinator\Admin\UsersNotInprocess;

use App\SQLorAPI\UsersTable;
use App\Coordinator\Admin\Common\AbstractController;

/**
 * Class UsersNotInprocessIndexController
 * Renders the editable "users not added to in-process table" list. On
 * POST, delegates to UsersNotInprocessPostProcessor first, then always
 * renders the current state of the list/form below it.
 */
class UsersNotInprocessIndexController extends AbstractController
{
    private const TY_NAME = 'users_not_inprocess';

    protected function createPostProcessor(): object
    {
        return new UsersNotInprocessPostProcessor();
    }
    public function renderFormCard(): void
    {
        $users = (UsersTable::getInstance())->getUsersNoInprocess();

        $formText = $this->buildUserRows($users);

        $formText .= $this->buildAddRowMarkup();

        $this->renderCard($formText);
    }
    /**
     * Builds the editable table rows for each existing excluded user.
     */
    private function buildUserRows(array $users): string
    {
        $formText = '';
        $numb = 0;

        foreach ($users as $key => $table) {
            $numb++;

            $userId = $table['id'] ?? '';
            $usere = $table['user'] ?? '';
            $isActive = $this->boolInt($table['is_active'] ?? 0);

            $activeChecked = $this->isChecked($isActive);

            $formText .= <<<HTML
                <tr>
                    <td data-content="id">
                        <input class="form-control" size="20" name="rows[$numb][id]" value="$userId" type="hidden"/>
                        <span><b>$userId</b></span>
                    </td>
                    <td data-content="user">
                        <span><a href='/Translation_Dashboard/leaderboard.php?user=$usere'>$usere</a></span>
                        <input name='rows[$numb][user]' value='$usere' type='hidden'/>
                    </td>
                    <td data-content="Active" data-order='$isActive'>
                        <div class='form-check form-switch'>

                            <input type='hidden' name='rows[$numb][is_active]' value='0'>
                            <input class='form-check-input' type='checkbox' name='rows[$numb][is_active]' value='1' $activeChecked>
                        </div>
                    </td>
                    <td data-content="delete">
                        <div class='input-group alert alert-warning p-1 mb-1'>
                            <div class="form-check form-switch form-inline">
                                <input class="form-check-input" type="checkbox" name="rows[$numb][del]" value="$userId">
                            </div>
                        </div>
                    </td>
                </tr>
            HTML;
        }

        return $formText;
    }

    /**
     * Builds the trailing empty row used to add a new excluded user.
     */
    private function buildAddRowMarkup(): string
    {
        return <<<HTML
            <tr>
                <td data-content="id">
                    <span><b>Add:</b></span>
                </td>
                <td data-content="User">
                    <input class='form-control' name='new[1][is_new]' value='yes' type='hidden'/>
                    <input class='form-control td_user_input' name='new[1][user]' />
                </td>
                <td data-content="Active">
                    <div class="form-check form-switch">
                        <input type="hidden" name="new[1][is_active]" value="1">
                        -
                    </div>
                </td>
                <td data-content="delete">
                    -
                </td>
            </tr>
        HTML;
    }

    /**
     * Renders the users card with the editable table form.
     */
    private function renderCard(string $formText): void
    {

        $tyName = self::TY_NAME;

        $body = <<<HTML
            <form action="index.php?ty=$tyName" method="POST">
                {$this->createCsrfTokenField()}
                <input name='ty' value="$tyName" type="hidden"/>
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <table class='table table-striped compact table-mobile-responsive table-mobile-sided table_text_left'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Active</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody id="full_tab">
                                $formText
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-group d-flex justify-content-between">
                    <button type="submit" class="btn btn-outline-primary">Save</button>
                </div>
            </form>
        HTML;

        $this->echoCard("Users Excluded from 'In Progress' Table:", $body);
    }
}


