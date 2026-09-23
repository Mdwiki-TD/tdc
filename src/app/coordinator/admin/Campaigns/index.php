<?php
// src/app/coordinator/admin/Campaigns/index.php

namespace App\Coordinator\Admin\Campaigns;

use App\Coordinator\Admin\Common\AbstractController;
use function App\SQLorAPI\Funcs\get_td_or_sql_categories;
use function App\csrf\generate_csrf_token;

require_once __DIR__ . '/post.php';

/**
 * Class CampaignsIndexController
 * Renders the editable campaigns/categories table. On POST, delegates
 * to CampaignsPostProcessor first, then always renders the current
 * state of the list/form below it.
 */
class CampaignsIndexController extends AbstractController
{
    /**
     * Handles authentication and executes controller output.
     */
    public function handleRequest(): void
    {
        $this->validateCoordinator();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postProcessor = new CampaignsPostProcessor();
            $postProcessor->handle();
        }

        $categories = get_td_or_sql_categories();

        $tableRows = $this->buildTableRows($categories);

        $this->renderCard($tableRows);
        $this->renderAddRowScript();
    }

    /**
     * Builds the editable table rows for each existing campaign category.
     */
    private function buildTableRows(array $categories): string
    {
        $tableRows = '';
        $numb = 0;

        foreach ($categories as $key => $table) {
            $numb++;

            $id        = $table['id'] ?? '';
            $category1 = $table['category'] ?? '';
            $category2 = $table['category2'] ?? '';
            $campaign  = $table['campaign'] ?? '';
            $depth     = $table['depth'] ?? '';

            $checked = ($table['is_default'] == 1) ? 'checked' : '';

            $tableRows .= <<<HTML
                <tr>
                    <div class='form-group'>
                        <th data-content="#">
                            $numb
                            <input name='rows[$numb][id]' value='$id' data-original='$id' type='hidden'/>
                        </th>
                        <td data-content="Campaign">
                            <input class="form-control" size='15' name='rows[$numb][camp]' value='$campaign' data-original='$campaign'/>
                        </td>
                        <td data-content="Category1">
                            <input class="form-control" size='25' name='rows[$numb][cat1]' value='$category1' data-original='$category1'/>
                        </td>
                        <td data-content="Category2">
                            <input class="form-control" size='25' name='rows[$numb][cat2]' value='$category2' data-original='$category2'/>
                        </td>
                        <td data-content="Depth">
                            <input class="form-control w-auto" type='number' name='rows[$numb][dep]' value='$depth' data-original='$depth' min='0' max='10'/>
                        </td>
                        <td data-content="Default Cat">
                            <input class="form-check-input" type='radio' id='default_cat' name='default_cat' value='$id' data-original='$id' $checked>
                        </td>
                        <td data-content="Delete">
                            <input type='checkbox' name='rows[$numb][del]' value='$id'/> <label>delete</label>
                        </td>
                    </div>
                </tr>
            HTML;
        }

        return $tableRows;
    }

    /**
     * Renders the campaigns card with the editable table form.
     */
    private function renderCard(string $tableRows): void
    {
        $csrfToken = generate_csrf_token();

        echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <h4>Campaigns:</h4>
                </div>
                <div class='card-body'>
                    <form action="index.php?ty=Campaigns" method="POST" id="new_form_post">
                        <input name='csrf_token' value="$csrfToken" type="hidden"/>
                        <input name='ty' value="Campaigns" type="hidden"/>
                        <div class="form-group">
                            <table class='table table-striped compact table-mobile-responsive table-mobile-sided'>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Campaign</th>
                                        <th>Category1</th>
                                        <th>Category2</th>
                                        <th>Depth</th>
                                        <th>Default</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody id="tab_logic">
                                    $tableRows
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group d-flex justify-content-between">
                            <button type="submit" class="btn btn-outline-primary">Save</button>
                            <span role='button' id="add_row" class="btn btn-outline-primary" onclick='add_row()'>New row</span>
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
                var ii = 0;

                function add_row() {
                    ii += 1;
                    var e = `
                        <tr>
                            <td>${ii}</td>
                            <td><input class='form-control' name='new[${ii}][camp]' placeholder='Campaign' value=''/></td>
                            <td><input class='form-control' name='new[${ii}][cat1]' placeholder='Category1' value=''/></td>
                            <td><input class='form-control' name='new[${ii}][cat2]' placeholder='Category2' value=''/></td>
                            <td><input class='form-control w-auto' type='number' name='new[${ii}][dep]' value='0' min='0' max='10'/></td>
                            <td></td>
                            <td></td>
                        </tr>
                    `;

                    $('#tab_logic').append(e);
                };
            </script>
        HTML;
    }
}

// Instantiate and execute controller
$controller = new CampaignsIndexController();
$controller->handleRequest();
