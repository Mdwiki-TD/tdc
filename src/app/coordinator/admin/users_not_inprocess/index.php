<?php
// src/app/coordinator/admin/users_not_inprocess/index.php

namespace App\Coordinator\Admin\UsersNotInprocess;

use App\Coordinator\Admin\Common\AbstractController;
use function App\SQLorAPI\Funcs\get_td_or_sql_users_no_inprocess;


require_once __DIR__ . '/users_not_inprocess_post.php';

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
    public function renderFormCard(): void {
        $users = get_td_or_sql_users_no_inprocess();

        $formText = $this->buildUserRows($users);
        $numb = count($users) + 1;
        $formText .= $this->buildAddRowMarkup($numb);

        $this->renderCard($formText);
        $this->renderAddRowScript();
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

            $userId   = $table['id'] ?? '';
            $usere    = $table['user'] ?? '';
            $isActive = $table['is_active'] ?? '';

            $activeChecked = ($isActive == 1 || $isActive == "1") ? 'checked' : '';

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
                            <input type='hidden' name='rows[$numb][active_orginal_value]' value='$isActive'>
                            <input type='hidden' name='rows[$numb][is_active]' value='0'>
                            <input class='form-check-input' type='checkbox' name='rows[$numb][is_active]' value='1' $activeChecked>
                        </div>
                    </td>
                    <td data-content="delete">
                        <input type='checkbox' name='rows[$numb][del]' value='$userId'/> <label> delete</label>
                    </td>
                </tr>
            HTML;
        }

        return $formText;
    }

    /**
     * Builds the trailing empty row used to add a new excluded user.
     */
    private function buildAddRowMarkup(int $numb): string
    {
        return <<<HTML
            <tr>
                <td data-content="id">
                    <span><b>Add:</b></span>
                </td>
                <td data-content="User">
                    <input class='form-control' name='rows[$numb][is_new]' value='yes' type='hidden'/>
                    <input class='form-control td_user_input' name='rows[$numb][user]' />
                </td>
                <td data-content="Active">
                    <div class="form-check form-switch">
                        <input type="hidden" name="rows[$numb][is_active]" value="1">
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
                    <!-- <span role='button' id="add_row" class="btn btn-outline-primary" onclick='add_row_v()'>New row</span> -->
                </div>
            </form>
        HTML;

        $this->echoCard("Users Not to be added to (in process) table:", $body);
    }

    /**
     * Renders the client-side script for dynamically adding new rows
     * (currently unused since the "New row" button is disabled above,
     * kept for parity with the legacy behaviour).
     */
    private function renderAddRowScript(): void
    {
        echo <<<'HTML'
            <script type="text/javascript">
                function add_row_v() {
                    var ii = $('#full_tab >tr').length + 1;

                    var e = `
                        <tr>
                            <td>
                                <b>${ii}</b>
                            </td>
                            <td>
                                <input class='form-control' name='rows[${ii}][is_new]' value='yes' type='hidden'/>
                                <input class='form-control' name='rows[${ii}][is_active]' value='1' type='hidden'/>
                                <input class='form-control td_user_input' name='rows[${ii}][user]'/>
                            </td>
                            <td>-</td>
                        </tr>
                    `;

                    $('#full_tab').append(e);
                };
            </script>
        HTML;
    }
}


