<?php
// src/app/Coordinator/Admin/TranslateType/TranslateTypeIndexController.php

namespace App\Coordinator\Admin\TranslateType;

use App\Coordinator\Admin\Common\AbstractControllerNoPost;
use App\SQLorAPI\CategoriesTable;
use App\Utils\HtmlUrls;
use App\Utils\Html;
use App\Results27\GetCats;

/**
 * Class TranslateTypeIndexController
 * Renders the Translate Type dashboard, listing titles either from a
 * Wikipedia category (via get_mdwiki_cat_members) or from the full set
 * of known titles (translate_type + qids not yet classified), each with
 * its lead/full translation flags and an edit link.
 */
class TranslateTypeIndexController extends AbstractControllerNoPost
{
    private string $cat;

    public function __construct()
    {
        parent::__construct();
        $cat = $_GET['cat'] ?? 'All';
        $this->cat = strtolower($cat);
    }

    /**
     * Handles authentication and executes controller output.
     */
    public function handleRequest(): void
    {
        $this->validateCoordinator();

        $filterHtml = $this->renderFilterSelect($this->cat);

        $this->loadTranslateTypeData();

        [$tableRows, $ttCount] = $this->buildTableRows();

        $newRow = HtmlUrls::make_edit_icon_new('edit_translate_type', ['new' => 1], 'Add one!');

        $this->renderMainCard($filterHtml, $ttCount, $tableRows);
        $this->renderAddNewCard($newRow);
        $this->renderDataTableScript();
    }

    private function loadCatToCamp(): array
    {
        $categoriesTab = (CategoriesTable::getInstance())->getCategories();
        $CatToCamp = [];
        foreach ($categoriesTab as $k => $tab) {
            if (!empty($tab['category']) && !empty($tab['campaign'])) {
                $CatToCamp[$tab['category']] = $tab['campaign'];
            }
        }

        return $CatToCamp;
    }
    /**
     * Builds the category filter dropdown markup.
     */
    private function renderFilterSelect(string $cat): string
    {
        $CatToCamp = $this->loadCatToCamp();

        $catsTitles = array_keys($CatToCamp);

        $template = <<<HTML
			<div class="input-group">
				<span class="input-group-text">%s</span>
				%s
			</div>
		HTML;

        $dropdown = Html::makeDropdown($catsTitles, $cat, 'cat', 'All');

        return sprintf($template, 'Category:', $dropdown);
    }

    /**
     * Loads the translate_type table into a title-keyed lookup array.
     */
    private function loadTranslateTypeData(): array
    {
        $translateTypeSql = <<<SQL
			SELECT tt_id, tt_title, tt_lead, tt_full
			FROM translate_type
		SQL;
        $params = [];
        if ($this->cat !== 'all') {
            $translateTypeSql .= " WHERE tt_title IN (SELECT article_id FROM category_members WHERE category = ?)";
            $params[] = $this->cat;
        }
        $fullTranslatesTab = [];

        foreach ($this->db->fetchQuery($translateTypeSql, $params) as $k => $tab) {
            $fullTranslatesTab[$tab['tt_title']] = [
                'id'   => $tab['tt_id'],
                'lead' => $tab['tt_lead'],
                'full' => $tab['tt_full'],
            ];
        }
        return $fullTranslatesTab;
    }

    public function loadCategoryTitles(array $fullTranslatesTab): array
    {
        $newTitles = [];
        if ($this->cat !== 'all') {
            $rows = $this->db->fetchQuery('SELECT article_id FROM category_members WHERE category = ?', [$this->cat]);

            foreach ($rows as $gg) {
                if (!in_array($gg['article_id'], $fullTranslatesTab)) {
                    $newTitles[] = $gg['article_id'];
                }
            }
        }
        return $newTitles;
    }

    /**
     * Builds every table row and returns [$html, $count].
     */
    private function buildTableRows(): array
    {
        $fullTranslatesTab = $this->loadTranslateTypeData();

        // $newTitles = $this->loadCategoryTitles($fullTranslatesTab);

        $tableRows = '';
        $ttCount = 0;

        foreach ($fullTranslatesTab as $title => $table) {
            $ttCount++;

            $id   = $table['id'] ?? '';
            $lead = $table['lead'] ?? 1;
            $full = $table['full'] ?? 0;

            $tableRows .= $this->renderRow($id, $title, $lead, $full, $ttCount);
        }

        return [$tableRows, $ttCount];
    }

    /**
     * Renders a single translate-type table row.
     */
    private function renderRow(string $id, string $title, mixed $lead, mixed $full, int $numb): string
    {
        $editParams = [
            'id'    => $id,
            'title' => $title,
            'lead'  => $lead,
            'full'  => $full,
        ];

        $editIcon = HtmlUrls::make_edit_icon_new('edit_translate_type', $editParams);

        $mdTitle = HtmlUrls::make_mdwiki_title($title);

        $leadChecked = ($lead == 1 || $lead == "1") ? 'checked' : '';
        $fullChecked = ($full == 1 || $full == "1") ? 'checked' : '';

        return <<<HTML
			<tr>
				<th data-sort="$numb">
					$numb
				</th>
				<td data-sort="$title">
					$mdTitle
				</td>
				<td data-sort='$lead'>
					<div class='form-check form-switch'>
						<input class='form-check-input' type='checkbox' name='lead_$numb' value='1' $leadChecked disabled>
					</div>
				</td>
				<td data-sort='$full'>
					<div class='form-check form-switch'>
						<input class='form-check-input' type='checkbox' name='full_$numb' value='1' $fullChecked disabled>
					</div>
				</td>
				<td>
					$editIcon
				</td>
			</tr>
		HTML;
    }

    /**
     * Renders the main filter + results card.
     */
    private function renderMainCard(string $filterHtml, int $ttCount, string $tableRows): void
    {
        $header = <<<HTML
			<form action="index.php?ty=translate_type" method="GET">
				<input name='ty' value="translate_type" type="hidden"/>
				<div class='row'>
					<div class='col-md-6'>
						<h4>Translate Type ($ttCount):</h4>
					</div>
					<div class='col-md-4'>
						$filterHtml
					</div>
					<div class='aligncenter col-md-2'><input class='btn btn-outline-primary' type='submit' value='Filter' /></div>
				</div>
			</form>
		HTML;
        $body = <<<HTML
			<table id='em' class='table table-striped compact table_responsive table_text_left'>
				<thead>
					<tr>
						<th>#</th>
						<th>Title</th>
						<th>Lead</th>
						<th>Full</th>
						<th>Edit</th>
					</tr>
				</thead>
				<tbody id="tab_ma">
					$tableRows
				</tbody>
			</table>
		HTML;

        $this->echoBs5Card($header, $body, '');
    }

    /**
     * Renders the "Add one!" shortcut card below the results table.
     */
    private function renderAddNewCard(string $newRow): void
    {
        echo <<<HTML
			<div class='card mt-1'>
				<div class='card-body'>
					$newRow
				</div>
			</div>
		HTML;
    }

    /**
     * Renders DataTables initialization script.
     */
    private function renderDataTableScript(): void
    {
        echo <<<HTML
			<script type="text/javascript">
				$(document).ready(function() {
					var t = $('#em').DataTable({
						stateSave: true,
						// order: [[5	, 'desc']],
						// paging: false,
						lengthMenu: [
							[250, 500],
							[250, 500]
						],
						// scrollY: 800
					});
				});
			</script>
		HTML;
    }
}
