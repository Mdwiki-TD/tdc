<?php
// src/app/Coordinator/Admin/admins/index.php

namespace App\Coordinator\Admin\Admins;

use App\SQLorAPI\UsersTable;
use App\Coordinator\Admin\Common\AbstractController;
/**
 * Class AdminsIndexController
 * Renders the editable coordinators (admins) table. On POST, delegates
 * to AdminsPostProcessor first, then always renders the current state
 * of the list/form below it.
 */
class AdminsIndexController extends AbstractController
{
    public bool $forAdminOnly = true;
    private const TY_NAME = 'admins';

    public function __construct()
    {
        parent::__construct();
    }
    protected function createPostProcessor(): object
    {
        return new AdminsPostProcessor();
    }

    public function renderFormCard(): void
    {
        $coordinators = (UsersTable::getInstance())->getCoordinators();
        sort($coordinators);

        $formText = $this->buildCoordinatorRows($coordinators);
        $formText .= $this->buildAddRowMarkup();

        $this->renderCard($formText);
    }
    /**
     * Builds the editable table rows for each existing coordinator.
     */
    private function buildCoordinatorRows(array $coordinators): string
    {
        $formText = '';
        $numb = 0;

        foreach ($coordinators as $key => $table) {
            $numb++;

            $userId = $table['id'] ?? '';
            $usere = $table['username'] ?? '';
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
                        <input name='rows[$numb][username]' value='$usere' type='hidden'/>
                    </td>
                    <td data-content="active">
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
     * Builds the trailing empty row used to add a new coordinator.
     */
    private function buildAddRowMarkup(): string
    {
        return <<<HTML
            <tr>
                <td data-content="id">
                    <span><b>Add:</b></span>
                </td>
                <td data-content="user">
                    <input class='form-control td_user_input' name='new[1][username]' />
                </td>
                <td data-content="active">
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
     * Renders the coordinators card with the editable table form.
     */
    private function renderCard(string $formText): void
    {
        $tyName = self::TY_NAME;

        echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <h4>Coordinators:</h4>
                </div>
                <div class='card-body'>
                    <form action="index.php?ty=$tyName" method="POST">
                        {$this->createCsrfTokenField()}
                        <input name='ty' value="$tyName" type="hidden"/>
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <table class='table table-striped compact table-mobile-responsive table-mobile-sided'>
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
                            <span> </span>
                        </div>
                    </form>
                </div>
            </div>
        HTML;
    }
}


