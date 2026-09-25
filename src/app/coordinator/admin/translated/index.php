<?php
// src/app/coordinator/admin/translated/index.php

namespace App\Coordinator\Admin\Translated;

use App\User\CurrentUser;
use App\Coordinator\Admin\Common\AbstractControllerNoPost;
use App\Tables\Langs\LangsTables;
use function App\Utils\Html\make_mdwiki_title;
use function App\Utils\Html\make_target_url;
use function App\Utils\Html\make_edit_icon_new;
use function App\SQLorAPI\Recent\get_recent_translated;
use function App\SQLorAPI\Recent\get_total_translations_count;
use function App\SQLorAPI\Funcs\get_pages_langs;
use function App\Coordinator\Helps\RecentHelps\filter_table;
use function App\Coordinator\Helps\RecentHelps\filter_recent2;

/**
 * Class TranslatedIndexController
 * Manages rendering the translated pages dashboard with pagination and filtering.
 */
class TranslatedIndexController extends AbstractControllerNoPost
{
    private CurrentUser $currentUser;
    private string $lang;
    private string $table;

    public function __construct()
    {
        $this->currentUser = CurrentUser::getInstance();
        $this->lang  = $_GET['lang'] ?? 'All';
        $cand = $_GET['table'] ?? 'pages';
        $this->table = in_array($cand, ['pages', 'pages_users'], true) ? $cand : 'pages';

        $globalUsername = $this->currentUser->getUsername();
        if (!isset($_GET['table']) && $globalUsername === "Mr. Ibrahem") {
            $this->table = "pages_users";
        }

        if ($this->lang !== 'All' && !isset(LangsTables::$LCodeToLang[$this->lang])) {
            $this->lang = 'All';
        }
    }

    /**
     * Handles authentication and executes controller output.
     */
    public function handleRequest(): void
    {
        $this->validateCoordinator();

        $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 500;
        $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        $sqlResults = get_recent_translated($this->lang, $this->table, $limit, $offset);
        $totalCount = get_total_translations_count($this->lang, $this->table);

        $pagination = "";
        if ($totalCount > $limit) {
            $pagination = $this->renderPaginationLinks($limit, $page, $this->table, $this->lang, $totalCount);
        }

        $recentTable = $this->buildTableHead();

        $noo = 0;
        foreach ($sqlResults as $tat => $tabe) {
            $noo++;
            $recentTable .= $this->renderTableRow($tabe, $noo, $this->table);
        }

        $recentTable .= "</tbody></table>";

        $this->renderFilterCard(count($sqlResults), $pagination);
        echo $recentTable;
        $this->renderDataTableScript();
    }

    /**
     * Fetches and formats available target language codes.
     */
    private function getLanguages(): array
    {
        $tabes = [];
        $llangs = get_pages_langs();

        foreach ($llangs as $tat) {
            if (gettype($tat) !== 'string') {
                echo "<br>tat: $tat";
                continue;
            }
            $tabes[] = strtolower($tat);
        }

        ksort($tabes);
        return $tabes;
    }

    /**
     * Generates HTML table row for a translated page item.
     */
    private function renderTableRow(array $tabg, int $index, string $table): string
    {
        $id      = $tabg['id'] ?? "";
        $user    = $tabg['user'] ?? "";
        $lang    = $tabg['lang'] ?? "";
        $mdTitle = trim($tabg['title'] ?? '');
        $target  = trim($tabg['target'] ?? '');
        $pupdate = $tabg['pupdate'] ?? '';

        $mdwikiTitle = make_mdwiki_title($mdTitle);
        $targe33     = make_target_url($target, $lang);

        $editParams = [
            'id'    => $id,
            'table' => $table
        ];

        $editIcon = make_edit_icon_new("edit_page", $editParams);

        return <<<HTML
            <tr>
                <td data-content='#'>$index</td>
                <td data-content='User'>
                    <a href='/Translation_Dashboard/leaderboard.php?user=$user'>$user</a>
                </td>
                <td data-content='Lang.'>
                    <a href='/Translation_Dashboard/leaderboard.php?langcode=$lang'>$lang</a>
                </td>
                <td data-content='Title'>$mdwikiTitle</td>
                <td data-content='Translated'>$targe33</td>
                <td data-content='Published'>$pupdate</td>
                <td data-content='Edit'>$editIcon</td>
            </tr>
        HTML;
    }

    /**
     * Generates pagination UI links and summary text.
     */
    private function renderPaginationLinks(int $limit, int $page, string $table, string $lang, int $totalCount): string
    {
        $totalPages = (int)ceil($totalCount / $limit);
        $baseUrl   = "?ty=translated&lang=$lang&table=$table&limit=$limit&page=";

        $links = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        $links .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '"><a class="page-link" href="' . $baseUrl . '1">&laquo;</a></li>';
        $links .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '"><a class="page-link" href="' . $baseUrl . ($page - 1) . '"><</a></li>';

        for ($i = max(1, $page - 3); $i <= min($totalPages, $page + 3); $i++) {
            $active = ($i == $page) ? ' active' : '';
            $links .= "<li class=\"page-item$active\"><a class=\"page-link\" href=\"{$baseUrl}{$i}\">$i</a></li>";
        }

        $links .= '<li class="page-item' . ($page >= $totalPages ? ' disabled' : '') . '"><a class="page-link" href="' . $baseUrl . ($page + 1) . '">></a></li>';
        $links .= '<li class="page-item' . ($page >= $totalPages ? ' disabled' : '') . '"><a class="page-link" href="' . $baseUrl . $totalPages . '">&raquo;</a></li>';
        $links .= '</ul></nav>';

        $offset    = ($page - 1) * $limit;
        $startItem = $offset + 1;
        $endItem   = min($offset + $limit, $totalCount);

        $summary  = "<p class=\"text-center\">";
        $summary .= "Page " . ($offset / $limit + 1) . " from $totalPages ";
        $summary .= "($startItem - $endItem from total " . number_format($totalCount) . " logs)";
        $summary .= "</p>";

        return $summary . $links;
    }

    /**
     * Constructs opening table tags.
     */
    private function buildTableHead(): string
    {
        return <<<HTML
            <table class="table table-sm table-striped table-mobile-responsive table-mobile-sided table_text_left" id="pages_table" style="font-size:90%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Lang.</th>
                        <th>Title</th>
                        <th>Translated</th>
                        <th>Published</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
        HTML;
    }

    /**
     * Renders filter controls card header.
     */
    private function renderFilterCard(int $countResult, string $paginationHtml): void
    {
        $langTable  = $this->getLanguages();
        $filterLang = filter_recent2($this->lang, $langTable);

        $data = [
            "pages"       => 'Main',
            "pages_users" => 'User',
        ];

        $filterNs = filter_table($data, $this->table, 'table');

        echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <form class='form-inline' style='margin-block-end: 0em;' method='get' action='index.php'>
                        <input name='ty' value='translated' type='hidden'/>
                        <div class='row'>
                            <div class='col-md-4'>
                                <h4>Translated Pages ($countResult):</h4>
                            </div>
                            <div class='col-md-4'>
                                <div class="input-group">
                                    <span class="input-group-text">Namespace:</span>
                                    <div class="form-control">
                                        $filterNs
                                    </div>
                                </div>
                            </div>
                            <div class='col-md-3'>
                                <select aria-label="Language code"
                                    class="selectpicker"
                                    id='lang'
                                    name='lang'
                                    placeholder='Language code'
                                    data-live-search="true"
                                    data-container="body"
                                    data-live-search-style="begins"
                                    data-bs-theme="auto"
                                    data-style='btn active'
                                    data-width="90%"
                                    >
                                    $filterLang
                                </select>
                            </div>
                            <div class='aligncenter col-md-1'>
                                <input class='btn btn-outline-primary' type='submit' value='Filter' />
                            </div>
                        </div>
                    </form>
                </div>
                <div class='card-body'>
                    $paginationHtml
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
            <script>
                $(document).ready(function() {
                    var t = $('#pages_table').DataTable({
                        stateSave: true,
                        lengthMenu: [
                            [50, 100, 150],
                            [50, 100, 150]
                        ],
                    });
                });
            </script>
        HTML;
    }
}


