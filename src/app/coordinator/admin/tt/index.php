<?php
// src/app/coordinator/admin/tt/index.php

namespace App\Coordinator\Admin\TranslateType;

use App\User\CurrentUser;
use App\Tables\SqlTables\TablesSql;
use function App\Utils\Html\makeDropdown;
use function App\Utils\Html\make_mdwiki_title;
use function App\Utils\Html\make_edit_icon_new;
use function App\Results\GetCats\get_mdwiki_cat_members;
use function App\APICalls\MdwikiSql\fetch_query;

/**
 * Class TtIndexController
 * Renders the Translate Type dashboard, listing titles either from a
 * Wikipedia category (via get_mdwiki_cat_members) or from the full set
 * of known titles (translate_type + qids not yet classified), each with
 * its lead/full translation flags and an edit link.
 */
class TtIndexController
{
    private string $cat;
    private array $fullTranslatesTab = [];
    private array $newTitles = [];

    public function __construct()
    {
        $this->cat = $_GET['cat'] ?? 'All';
    }

    /**
     * Handles authentication and executes controller output.
     */
    public function handleRequest(): void
    {
        // Check user authorization
        if (!CurrentUser::getInstance()->isCoordinator()) {
            header('Location: /index.php');
            exit;
        }

        $filterHtml = $this->renderFilterSelect($this->cat);

        $this->loadTranslateTypeData();
        $this->loadCategoryTitles();

        [$tableRows, $ttCount] = $this->buildTableRows();

        $newRow = make_edit_icon_new('tt/edit_translate_type', ['new' => 1], 'Add one!');

        $this->renderMainCard($filterHtml, $ttCount, $tableRows);
        $this->renderAddNewCard($newRow);
        $this->renderDataTableScript();
    }

    /**
     * Builds the category filter dropdown markup.
     */
    private function renderFilterSelect(string $cat): string
    {
        $catsTitles = array_keys(TablesSql::$sCatToCamp);

        $template = <<<HTML
            <div class="input-group">
                <span class="input-group-text">%s</span>
                %s
            </div>
        HTML;

        $dropdown = makeDropdown($catsTitles, $cat, 'cat', 'All');

        return sprintf($template, 'Category:', $dropdown);
    }

    /**
     * Loads the translate_type table into a title-keyed lookup array.
     */
    private function loadTranslateTypeData(): void
    {
        $translateTypeSql = <<<SQL
            SELECT tt_id, tt_title, tt_lead, tt_full
            FROM translate_type
        SQL;

        foreach (fetch_query($translateTypeSql) as $k => $tab) {
            $this->fullTranslatesTab[$tab['tt_title']] = [
                'id'   => $tab['tt_id'],
                'lead' => $tab['tt_lead'],
                'full' => $tab['tt_full'],
            ];
        }
    }

    /**
     * Populates TablesSql::$sCatTitles either from the full set of
     * known/unclassified titles ("All") or from a Wikipedia category's
     * members.
     */
    private function loadCategoryTitles(): void
    {
        TablesSql::$sCatTitles = [];

        if ($this->cat === 'All') {
            $rows = fetch_query('SELECT DISTINCT title from qids WHERE title not in (SELECT tt_title FROM translate_type)');

            foreach ($rows as $key => $gg) {
                if (!in_array($gg['title'], $this->fullTranslatesTab)) {
                    $this->newTitles[] = $gg['title'];
                }
            }

            TablesSql::$sCatTitles = array_keys($this->fullTranslatesTab);
        } else {
            TablesSql::$sCatTitles = get_mdwiki_cat_members($this->cat, true, 1);
        }
    }

    /**
     * Builds every table row and returns [$html, $count].
     */
    private function buildTableRows(): array
    {
        $tableRows = '';
        $ttCount = 0;

        foreach (TablesSql::$sCatTitles as $title) {
            if (in_array($title, $this->newTitles)) {
                continue;
            }

            $ttCount++;

            $table = $this->fullTranslatesTab[$title] ?? [];

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
    private function renderRow(string $id, string $title, $lead, $full, int $numb): string
    {
        $editParams = [
            'id'    => $id,
            'title' => $title,
            'lead'  => $lead,
            'full'  => $full,
        ];

        $editIcon = make_edit_icon_new('tt/edit_translate_type', $editParams);

        $mdTitle = make_mdwiki_title($title);

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
        $testin = (($_GET['test'] ?? '') != '') ? '<input type="hidden" name="test" value="1" />' : "";

        echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <form action="index.php?ty=tt" method="GET">
                        $testin
                        <input name='ty' value="tt" type="hidden"/>
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
                </div>
                <div class='card-body'>
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
                </div>
            </div>
        HTML;
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
                        // order: [[5    , 'desc']],
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

// Instantiate and execute controller
$controller = new TtIndexController();
$controller->handleRequest();
