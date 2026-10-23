<?php

namespace App\Coordinator\Admin\Common;

/**
 * Class AbstractUserListIndexController
 *
 * Abstract base class for user list index controllers (Admins, FullTranslators, UsersNotInprocess).
 */
abstract class AbstractUserListIndexController extends AbstractController
{
    abstract protected function getTyName(): string;
    abstract protected function getCardTitle(): string;
    abstract protected function getUsersData(): array;

    protected function getUsernameField(): string
    {
        return 'user';
    }

    protected function getTableClass(): string
    {
        return 'table table-striped compact table-mobile-responsive table-mobile-sided';
    }

    protected function getActiveDataContent(): string
    {
        return 'active';
    }

    protected function getUserDataContent(): string
    {
        return 'user';
    }

    protected function shouldSortRecords(): bool
    {
        return false;
    }

    protected function getSaveButtonExtra(): string
    {
        return '';
    }

    public function renderFormCard(): void
    {
        $users = $this->getUsersData();
        if ($this->shouldSortRecords()) {
            sort($users);
        }

        $formText = $this->buildRows($users);
        $formText .= $this->buildAddRowMarkup();

        $this->renderCard($formText);
    }

    protected function buildRows(array $users): string
    {
        $formText = '';
        $numb = 0;
        $usernameField = $this->getUsernameField();
        $activeDataContent = $this->getActiveDataContent();

        foreach ($users as $key => $table) {
            $numb++;

            $userId = $table['id'] ?? '';
            $usere = $table[$usernameField] ?? '';
            $isActive = $this->boolInt($table['is_active'] ?? 0);

            $activeChecked = $this->isChecked($isActive);

            $activeAttr = ($activeDataContent === 'Active') ? "data-content=\"Active\" data-order='$isActive'" : "data-content=\"active\"";

            $formText .= <<<HTML
                <tr>
                    <td data-content="id">
                        <input class="form-control" size="20" name="rows[$numb][id]" value="$userId" type="hidden"/>
                        <span><b>$userId</b></span>
                    </td>
                    <td data-content="user">
                        <span><a href='/Translation_Dashboard/leaderboard.php?user=$usere'>$usere</a></span>
                        <input name='rows[$numb][$usernameField]' value='$usere' type='hidden'/>
                    </td>
                    <td $activeAttr>
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

    protected function buildAddRowMarkup(): string
    {
        $usernameField = $this->getUsernameField();
        $userDataContent = $this->getUserDataContent();
        $activeDataContent = $this->getActiveDataContent();

        return <<<HTML
            <tr>
                <td data-content="id">
                    <span><b>Add:</b></span>
                </td>
                <td data-content="$userDataContent">
                    <input class='form-control td_user_input' name='new[1][$usernameField]' />
                </td>
                <td data-content="$activeDataContent">
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

    protected function renderCard(string $formText): void
    {
        $tyName = $this->getTyName();
        $tableClass = $this->getTableClass();
        $saveButtonExtra = $this->getSaveButtonExtra();

        $form = <<<HTML
            <form action="index.php?ty=$tyName" method="POST">
                {$this->createCsrfTokenField()}
                <input name='ty' value="$tyName" type="hidden"/>
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <table class='$tableClass'>
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
                    <button type="submit" class="btn btn-outline-primary">Save</button>$saveButtonExtra
                </div>
            </form>
        HTML;

        $this->echoCard($this->getCardTitle(), $form);
    }
}
