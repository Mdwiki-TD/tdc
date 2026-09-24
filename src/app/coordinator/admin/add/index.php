<?php
// src/app/coordinator/admin/add/index.php

namespace App\Coordinator\Admin\Add;

use App\Coordinator\Admin\Common\AbstractController;
use function App\SQLorAPI\Funcs\get_td_or_sql_categories;


require_once __DIR__ . '/post.php';

/**
 * Class AddIndexController
 * Renders the "Add translations" form, including the category select
 * options and a single starter row. On POST, delegates to
 * AddPostProcessor first, then always renders the form below it.
 */
class AddIndexController extends AbstractController
{
    protected function createPostProcessor(): object
    {
        return new AddPostProcessor();
    }
    public function handlePostRequest(): void
    {
        $postProcessor = $this->createPostProcessor();
        $result = $postProcessor->handle($_POST);
        $postProcessor->RenderIndexMesseges($result);
    }
    /**
     * Handles authentication and executes controller output.
     */
    public function handleRequest(): void
    {
        $this->validateCoordinator();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        }

        $cats = $this->buildCategoryOptions();
        $table = $this->buildStarterRows($cats);

        $this->renderCard($cats, $table);
        $this->renderUrlSearchBlock();
    }

    /**
     * Builds <option> markup for every campaign category.
     */
    private function buildCategoryOptions(): string
    {
        $cats = '';
        $categories = get_td_or_sql_categories();

        foreach ($categories as $key => $ta) {
            $ca = $ta['category'] ?? '';
            $ds = $ta['campaign'] ?? '';

            if (!empty($ca)) {
                $cats .= "<option value='$ca'>$ds</option>";
            }
        }

        return $cats;
    }

    /**
     * Builds the initial table row(s) of the add-translations form.
     */
    private function buildStarterRows(string $cats): string
    {
        $typiesTemplate = <<<HTML
            <select name='rows[%s][type]' id='rows[%s][type]' class='form-select w-100' data-bs-theme="auto">
                <option value='lead'>Lead</option><option value='all'>All</option>
            </select>
        HTML;

        $table = '';

        foreach (range(1, 1) as $numb) {
            $catsLine = <<<HTML
                <select class='form-select catsoptions' name='rows[$numb][cat]' data-bs-theme="auto">
                    $cats
                </select>
            HTML;

            $typeLine = sprintf($typiesTemplate, $numb, $numb);

            $table .= <<<HTML
                <tr id="row_$numb">
                    <td data-order='$numb' data-content='#'>
                        $numb
                    </td>
                    <td data-content='Mdwiki Title'>
                        <input class="form-control mdtitles" size='15' name='rows[$numb][mdtitle]' required/>
                    </td>
                    <td data-content='Campaign'>
                        $catsLine
                    </td>
                    <td data-content='Type'>
                        $typeLine
                    </td>
                    <td data-content='User'>
                        <input class="form-control td_user_input" size='10' name='rows[$numb][user]' required/>
                    </td>
                    <td data-content='Lang.'>
                        <input class="form-control lang_input" size='2' name='rows[$numb][lang]' required/>
                    </td>
                    <td data-content='Target'>
                        <input class="form-control" size='20' name='rows[$numb][target]'/>
                    </td>
                    <td data-content='Published'>
                        <input class="form-control" size='10' name='rows[$numb][pupdate]' placeholder='YYYY-MM-DD'/>
                    </td>
                    <td data-content="Delete">
                        <div class="">
                            <button type="button" class="btn btn-danger btn-sm" onclick="delete_row($numb)">Delete</button>
                        </div>
                    </td>
                </tr>
            HTML;
        }

        return $table;
    }

    /**
     * Renders the main "Add translations" card with the form and table.
     */
    private function renderCard(string $cats, string $table): void
    {
        $testin = (($_GET['test'] ?? '') != '') ? '<input type="hidden" name="test" value="1" />' : "";

        echo <<<HTML
            <div class='card'>
                <select class='catsoptions' data-bs-theme="auto" hidden>$cats</select>
                <div class='card-header'>
                    <h4>Add translations:</h4>
                </div>
                <div class='cardbody p-2'>
                    <form action="index.php?ty=add" method="POST">
                        {$this->createCsrfTokenField()}
                        $testin
                        <input name='ty' value="add" type="hidden"/>
                        <div class="form-group">
                            <table class='table table-striped compact table-mobile-responsive table-mobile-sided' style='font-size:95%;'>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Mdwiki Title</th>
                                        <th>Campaign</th>
                                        <th>Type</th>
                                        <th>User</th>
                                        <th>Lang.</th>
                                        <th>Target</th>
                                        <th>Published</th>
                                    </tr>
                                </thead>
                                <tbody id='tab_data'>
                                    $table
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group d-flex justify-content-between">
                            <button type="submit" class="btn btn-outline-primary mb-10">Save</button>
                            <span role='button' id="add_new_row" class="btn btn-outline-primary" onclick='add_new_row()'>New row</span>
                        </div>
                    </form>
                </div>
            </div>
        HTML;
    }

    /**
     * Renders the "search by URL" helper block and its supporting script.
     */
    private function renderUrlSearchBlock(): void
    {
        echo <<<HTML
            <div class='cardbody p-3'>

                <div class='container'>
                    <div id='alert' class="alert alert-warning" role="alert" style="display:none;">
                        <i class="bi bi-exclamation-triangle"></i> <span id='alert_text'></span>
                    </div>
                </div>
                <div class="input-group">
                    <span class="input-group-text">URL</span>
                    <input class="form-control mdtitles url" size='15' id='url' name='url' value='https://ar.wikipedia.org/wiki/أتولتيفيماب/مافتيفيماب/أوديسيفيماب' />
                    <button class="btn btn-outline-primary mb-10" onclick="start_one_url(this)">Search</button>
                </div>
            </div>

            <script src='/tdc/js/add_by_url.js'></script>
        HTML;
    }
}

// Instantiate and execute controller
$controller = new AddIndexController();
$controller->handleRequest();
