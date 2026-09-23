<?php
// src/app/coordinator/admin/wikirefs_options/index.php

namespace App\Coordinator\Admin\WikiRefsOptions;

use App\User\CurrentUser;
use function App\SQLorAPI\Funcs\get_td_or_sql_language_settings;
use function App\SQLorAPI\Funcs\get_pages_langs;
use function App\Utils\Html\make_edit_icon_new;

/**
 * Class WikiRefsOptionsIndexController
 * Renders the wikirefs per-language options table (language_settings:
 * lang_code, move_dots, expend, add_en_lang), merging in any languages
 * that don"t have a settings row yet with default (off) values.
 */
class WikiRefsOptionsIndexController
{
    /**
     * Handles authentication and executes controller output.
     */
    public function handleRequest(): void
    {
        // Check user authorization
        if (!CurrentUser::getInstance()->isCoordinator()) {
            header("Location: /index.php");
            exit;
        }

        $tabes = $this->buildMergedLanguageSettings();

        $sato = "";
        $n = -1;

        foreach ($tabes as $tab) {
            $n++;
            $sato .= $this->renderRow($tab, $n);
        }

        $this->renderMainCard($sato);

        $newRow = make_edit_icon_new("wikirefs_options/edit", ["new" => 1], "Add one!");
        $this->renderAddNewCard($newRow);

        $this->renderDataTableScript();
    }

    /**
     * Loads persisted language settings and merges in any languages
     * that don"t yet have a row, sorted by lang_code.
     */
    private function buildMergedLanguageSettings(): array
    {
        $tabes = get_td_or_sql_language_settings();

        $tabesCodes = array_column($tabes, "lang_code");

        $langsD = get_pages_langs();

        foreach ($langsD as $tat) {
            $lal = strtolower($tat);

            if (!in_array($lal, $tabesCodes)) {
                $tabes[] = ["lang_code" => $lal, "expend" => 0, "move_dots" => 0, "add_en_lang" => 0];
            }
        }

        // ksort($tabes);
        usort($tabes, function ($a, $b) {
            return strcmp($a["lang_code"] ?? "", $b["lang_code"] ?? "");
        });

        return $tabes;
    }

    /**
     * Renders a single language settings table row.
     */
    private function renderRow(array $tabg, int $numb): string
    {
        $id         = $tabg["id"] ?? 0;
        $lang       = $tabg["lang_code"] ?? "";
        $expend2    = ($tabg["expend"] == 1) ? "checked" : "";
        $moveDots   = ($tabg["move_dots"] == 1) ? "checked" : "";
        $addEnLang  = ($tabg["add_en_lang"] == 1) ? "checked" : "";

        $lang = strtolower($lang);

        $editParams = [
            "id"          => $id,
            "lang_code"   => $lang,
            "expend"      => $tabg["expend"],
            "move_dots"   => $tabg["move_dots"],
            "add_en_lang" => $tabg["add_en_lang"],
        ];

        $editIcon = make_edit_icon_new("wikirefs_options/edit", $editParams);

        return <<<HTML
            <tr>
                <td data-content="#">
                    $numb
                </td>
                <td data-content="#">
                    <span>$lang</span>
                </td>
                <td data-content="Move dots" data-order="$moveDots">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="rows[$numb][move_dots]" value="1" $moveDots disabled/>
                    </div>
                </td>
                <td data-content="Expend infobox" data-order="$expend2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="rows[$numb][expend]" value="1" $expend2 disabled/>
                    </div>
                </td>
                <td data-content="Add |language=en" data-order="$addEnLang">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="rows[$numb][add_en_lang]" value="1" $addEnLang disabled/>
                    </div>
                </td>
                <td data-content="Edit">
                    $editIcon
                </td>
            </tr>
        HTML;
    }

    /**
     * Renders the main results table card.
     */
    private function renderMainCard(string $sato): void
    {
        echo <<<HTML
            <div class="card">
                <div class="card-header">
                    <h4>Fix wikirefs options:</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <table id="em2" class="table table-sm table-striped table-mobile-responsive table-mobile-sided table_text_left" style="font-size:90%;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Lang.</th>
                                    <th>Move dots</th>
                                    <th>Expand infobox</th>
                                    <th>add |language=en</th>
                                    <th>Edit</th>
                                </tr>
                            </thead>
                            <tbody id="refs_tab">
                                $sato
                            </tbody>
                        </table>
                    </div>
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
            <div class="card mt-2 mb-2">
                <div class="card-body">
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
                    $("#em2").DataTable({
                        stateSave: true,
                        lengthMenu: [
                            [10, 50, 100, 150],
                            [10, 50, 100, 150]
                        ],
                        // paging: false,
                        // searching: false
                    });
                });
            </script>
        HTML;
    }
}

// Instantiate and execute controller
$controller = new WikiRefsOptionsIndexController();
$controller->handleRequest();
