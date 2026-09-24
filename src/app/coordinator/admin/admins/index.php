<?php
// src/app/coordinator/admin/admins/index.php

namespace App\Coordinator\Admin\Admins;

use App\Coordinator\Admin\Common\AbstractController;
use function App\SQLorAPI\Funcs\get_coordinators;

require_once __DIR__ . '/post.php';

/**
 * Class AdminsIndexController
 * Renders the editable coordinators (admins) table. On POST, delegates
 * to AdminsPostProcessor first, then always renders the current state
 * of the list/form below it.
 */
class AdminsIndexController extends AbstractController
{
	private const TY_NAME = 'admins';

	/**
	 * Handles authentication and executes controller output.
	 */
	public function handleRequest(): void
	{
		$this->validateCoordinator();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postProcessor = new AdminsPostProcessor();
            $result = $postProcessor->handle($_POST);
            $postProcessor->RenderMesseges($result);
		}

		$coordinators = get_coordinators();
		sort($coordinators);

		$formText = $this->buildCoordinatorRows($coordinators);
		$numb = count($coordinators) + 1;
		$formText .= $this->buildAddRowMarkup($numb);

		$this->renderCard($formText);
		$this->renderAddRowScript();
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

			$userId   = $table['id'] ?? '';
			$usere    = $table['username'] ?? '';
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
                        <input name='rows[$numb][username]' value='$usere' type='hidden'/>
                    </td>
                    <td data-content="active">
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
	 * Builds the trailing empty row used to add a new coordinator.
	 */
	private function buildAddRowMarkup(int $numb): string
	{
		return <<<HTML
            <tr>
                <td data-content="id">
                    <span><b>Add:</b></span>
                </td>
                <td data-content="user">
                    <input class='form-control' name='rows[$numb][is_new]' value='yes' type='hidden'/>
                    <input class='form-control td_user_input' name='rows[$numb][username]' />
                </td>
                <td data-content="active">
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
                            <span role='button' id="add_row" class="btn btn-outline-primary" onclick='add_row_v()'>New row</span>
                            <span> </span>
                        </div>
                    </form>
                </div>
            </div>
        HTML;
	}

	/**
	 * Renders the client-side script for dynamically adding new rows.
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
                                <input class='form-control td_user_input' name='rows[${ii}][username]'/>
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

// Instantiate and execute controller
$controller = new AdminsIndexController();
$controller->handleRequest();
