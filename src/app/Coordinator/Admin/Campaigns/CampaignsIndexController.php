<?php
// src/app/Coordinator/Admin/campaigns/index.php

namespace App\Coordinator\Admin\Campaigns;

use App\Coordinator\Admin\Common\AbstractController;
use App\SQLorAPI\CategoriesTable;

/**
 * Class CampaignsIndexController
 * Renders the editable campaigns/categories table. On POST, delegates
 * to CampaignsPostProcessor first, then always renders the current
 * state of the list/form below it.
 */
class CampaignsIndexController extends AbstractController
{
    protected function createPostProcessor(): object
    {
        return new CampaignsPostProcessor();
    }
    public function renderFormCard(): void
    {
        $categories = (CategoriesTable::getInstance())->getCategories();

        $tableRows = $this->buildTableRows($categories);

        $tableRows .= $this->buildAddRowMarkup();

        $this->renderCard($tableRows);
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

            $id = $table['id'] ?? '';
            $category1 = $table['category'] ?? '';
            $category2 = $table['category2'] ?? '';
            $campaign = $table['campaign'] ?? '';
            $depth = $table['depth'] ?? '';

            $checked = $this->isChecked($table['is_default']);

            $tableRows .= <<<HTML
                <tr>
                    <div class='form-group'>
                        <th data-content="#">
                            $numb
                            <input class="form-control" name='rows[$numb][id]' value='$id' type='hidden'/>
                        </th>
                        <!-- <th data-content="#">
                            <input class="form-control" name='rows[$numb][id]' value='$id' type='text' readonly/>
                        </th> -->
                        <td data-content="Campaign">
                            <input class="form-control" size='15' name='rows[$numb][campaign]' value='$campaign' data-original='$campaign'/>
                        </td>
                        <td data-content="Category1">
                            <input class="form-control" size='25' name='rows[$numb][category]' value='$category1' data-original='$category1'/>
                        </td>
                        <td data-content="Category2">
                            <input class="form-control" size='25' name='rows[$numb][category2]' value='$category2' data-original='$category2'/>
                        </td>
                        <td data-content="Depth">
                            <input class="form-control w-auto" type='number' name='rows[$numb][depth]' value='$depth' data-original='$depth' min='0' max='10'/>
                        </td>
                        <td data-content="Default Cat">
                            <input class="form-check-input" type='radio' id='default_cat' name='default_cat' value='$id' data-original='$id' $checked>
                        </td>
                        <td data-content="delete">
                            <div class='input-group alert alert-warning p-1 mb-1'>
                                <div class="form-check form-switch form-inline">
                                    <input class="form-check-input" type="checkbox" name="rows[$numb][del]" value="$id">
                                </div>
                            </div>
                        </td>
                    </div>
                </tr>
            HTML;
        }

        return $tableRows;
    }

    /**
     * Builds the trailing empty row used to add a new project.
     */
    private function buildAddRowMarkup(): string
    {
        return <<<HTML
            <tr>
                <td data-content="id">
                    <span><b>Add:</b></span>
                </td>
                <!-- <td>-</td> -->
                <td><input class='form-control' name='new[1][campaign]' placeholder='Campaign' value=''/></td>
                <td><input class='form-control' name='new[1][category]' placeholder='Category1' value=''/></td>
                <td><input class='form-control' name='new[1][category2]' placeholder='Category2' value=''/></td>
                <td><input class='form-control w-auto' type='number' name='new[1][depth]' value='0' min='0' max='10'/></td>
                <td>-</td>
                <td>-</td>
            </tr>
        HTML;
    }
    /**
     * Renders the campaigns card with the editable table form.
     */
    private function renderCard(string $tableRows): void
    {

        $form = <<<HTML
            <form action="index.php?ty=campaigns" method="POST" id="new_form_post">
                {$this->createCsrfTokenField()}
                <input name='ty' value="campaigns" type="hidden"/>
                <div class="form-group">
                    <table class='table table-striped compact table-mobile-responsive table-mobile-sided'>
                        <thead>
                            <tr>
                                <th>#</th>
                                <!-- <th>id</th> -->
                                <th>Campaign</th>
                                <th>Category1</th>
                                <th>Category2</th>
                                <th>Depth</th>
                                <th>Default</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            $tableRows
                        </tbody>
                    </table>
                </div>
                <div class="form-group d-flex justify-content-between">
                    <button type="submit" class="btn btn-outline-primary">Save</button>
                </div>
            </form>
        HTML;

        $this->echoCard("Campaigns:", $form);
    }

}


