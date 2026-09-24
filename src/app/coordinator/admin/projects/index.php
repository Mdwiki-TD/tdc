<?php
// src/app/coordinator/admin/projects/index.php

namespace App\Coordinator\Admin\Projects;

use App\Coordinator\Admin\Common\AbstractController;
use function App\SQLorAPI\Funcs\get_td_or_sql_projects;


require_once __DIR__ . '/post.php';

/**
 * Class ProjectsIndexController
 * Renders the editable projects table. On POST, delegates to
 * ProjectsPostProcessor first (its result is echoed inline), then
 * always renders the current state of the list/form below it.
 */
class ProjectsIndexController extends AbstractController
{
	/**
	 * Handles authentication and executes controller output.
	 */
	public function handleRequest(): void
	{
		$this->validateCoordinator();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$postProcessor = new ProjectsPostProcessor();
			$result = $postProcessor->handle($_POST);
            $postProcessor->RenderMesseges($result);
		}

		$projects = get_td_or_sql_projects();

		// Sort projects by g_id
		uasort($projects, function ($a, $b) {
			return $a['g_id'] <=> $b['g_id'];
		});

		$formText = $this->buildProjectRows($projects);
		$numb = count($projects) + 1;
		$formText .= $this->buildAddRowMarkup($numb);

		$this->renderCard($formText);
		$this->renderAddRowScript();
	}

	/**
	 * Builds the editable table rows for each existing project.
	 */
	private function buildProjectRows(array $projects): string
	{
		$formText = '';
		$numb = 0;

		foreach ($projects as $gtitleKey => $tab) {
			$numb++;

			$gid = $tab['g_id'] ?? '';
			$gtitle = $tab['g_title'] ?? '';

			$formText .= <<<HTML
                <tr>
                    <td data-content='id'>
                        <span><b>$gid</b></span>
                        <input name='rows[$numb][g_id]' value='$gid' type='hidden'/>
                    </td>
                    <td data-content='Project'>
                        <input class='form-control' name='rows[$numb][g_title]' value='$gtitle'/>
                    </td>
                    <td data-content='Delete'>
                        <input type='checkbox' name='rows[$numb][del]' value='$gid'/> <label> delete</label>
                    </td>
                </tr>
            HTML;
		}

		return $formText;
	}

	/**
	 * Builds the trailing empty row used to add a new project.
	 */
	private function buildAddRowMarkup(int $numb): string
	{
		return <<<HTML
            <tr>
                <td data-content="id">
                    <span><b>Add:</b></span>
                </td>
                <td data-content="user">
                    <input class='form-control td_user_input' name='rows[$numb][g_title]' />
                </td>
                <td data-content="delete">-
                </td>
            </tr>
        HTML;
	}

	/**
	 * Renders the projects card with the editable table form.
	 */
	private function renderCard(string $formText): void
	{


		echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <h4>Projects:</h4>
                </div>
                <div class='card-body'>
                    <form action="index.php?ty=projects" method="POST">
                        {$this->createCsrfTokenField()}
                        <input name='ty' value="projects" type="hidden"/>
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <table class='table table-striped compact table-mobile-responsive table-mobile-sided'>
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Project</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody id="g_tab">
                                        $formText
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="form-group d-flex justify-content-between">
                            <button type="submit" class="btn btn-outline-primary">Save</button>
                            <span role='button' id="add_row" class="btn btn-outline-primary" onclick='add_row()'>New row</span>
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
                function add_row() {
                    var ii = $('#g_tab >tr').length + 1;

                    var e = `
                        <tr>
                            <td>
                                <b>${ii}</b>
                            </td>
                            <td>
                                <input class='form-control' name='rows[${ii}][g_title]' value=''/>
                            </td>
                            <td>-</td>
                        </tr>
                    `;

                    $('#g_tab').append(e);
                };
            </script>
        HTML;
	}
}

// Instantiate and execute controller
$controller = new ProjectsIndexController();
$controller->handleRequest();
