<?php

namespace App\Tools;

use App\Coordinator\Admin\Common\AbstractControllerNoPost;
use function App\APICalls\TDApi\get_td_api;

/**
 * Class LastController
 * Renders recent translations for non-admin/general tools view.
 */
class LastController extends AbstractControllerNoPost
{
    private string $lang;
    private string $lastTable;

    public function __construct()
    {
        $this->lang = $_GET['lang'] ?? 'All';
        if (empty($this->lang)) {
            $this->lang = 'All';
        }

        $this->lastTable = $_GET['last_table'] ?? 'pages';
        $this->lastTable = in_array($this->lastTable, ['pages', 'pages_users'], true) ? $this->lastTable : 'pages';
    }

    /**
     * Handles request execution.
     */
    public function handleRequest(): void
    {
        $apiParamsUsers = [
            'get'    => 'pages_users',
            'target' => 'not_empty',
            'lang'   => $this->lang,
            'order'  => 'pupdate',
            'limit'  => '100',
        ];

        $apiParamsPages = [
            'get'    => 'pages_with_views',
            'target' => 'not_empty',
            'lang'   => $this->lang,
            'order'  => 'pupdate_or_add_date',
            'limit'  => '250',
        ];

        $apiResults = ($this->lastTable === 'pages') ? get_td_api($apiParamsPages) : get_td_api($apiParamsUsers);
        $qslResults = $apiResults['results'] ?? [];

        $recentRows = "";
        $noo = 0;

        foreach ($qslResults as $tat => $tabe) {
            $noo++;
            $recentRows .= $this->createLastTableData($tabe, $noo);
        }

        $apiParamsLangs = [
            'get' => ($this->lastTable === 'pages') ? 'pages_langs' : 'pages_users_langs',
        ];

        $langResults = get_td_api($apiParamsLangs);
        $result = $langResults['results'] ?? [];

        $filterByLang = $this->filterRecent($this->lang, $result);
        $countResult = count($result);

        $filterTa = $this->buildNamespaceFilter();

        $tableId = ($this->lastTable === 'pages') ? 'last_table' : 'last_users_table';

        $this->renderCard($countResult, $filterTa, $filterByLang, $tableId, $recentRows);
        $this->renderScript();
    }

    private function makeViewByNumber(string $target, $numb, string $lang, string $pupdate): string
    {
        $target = trim($target);
        $numb2 = (!empty($numb)) ? $numb : "?";
        $start = !empty($pupdate) ? $pupdate : '2019-01-01';
        $end = date("Y-m-d", strtotime("yesterday"));

        $url = 'https://pageviews.wmcloud.org/?' . http_build_query([
            'project'   => "$lang.wikipedia.org",
            'platform'  => 'all-access',
            'agent'     => 'all-agents',
            'start'     => $start,
            'end'       => $end,
            'redirects' => '0',
            'pages'     => $target,
        ], '', '&', PHP_QUERY_RFC3986);

        $numb3 = (is_numeric($numb2)) ? number_format((float)$numb2) : $numb2;
        $link = "<a target='_blank' href='$url'>$numb3</a>";

        if (is_numeric($numb2) && intval($numb2) > 0) {
            return $link;
        }

        $start2 = !empty($pupdate) ? str_replace('-', '', $pupdate) : '20190101';
        $url2 = 'https://wikimedia.org/api/rest_v1/metrics/pageviews/per-article/' . $lang . '.wikipedia/all-access/all-agents/' . rawurlencode($target) . '/daily/' . $start2 . '/2030010100';

        return "<a target='_blank' name='toget' data-json-url='$url2' href='$url'>$numb2</a>";
    }

    private function createLastTableData(array $tabg, int $nnnn): string
    {
        $user = $tabg['user'] ?? "";

        $llang    = $tabg['lang'] ?? "";
        $mdTitle  = trim($tabg['title'] ?? '');
        $target   = trim($tabg['target'] ?? '');
        $pupdate  = $tabg['pupdate'] ?? '';
        $addDate  = $tabg['add_date'] ?? '';
        $campaign = $tabg['campaign'] ?? '';

        if (strpos($addDate, ':') !== false) {
            $addDate = explode(' ', $addDate)[0];
        }

        $maxUsernameDisplayLength = 15;
        $userName = $user;
        if (strlen($user) > $maxUsernameDisplayLength) {
            $parts = explode(' ', $user);
            $userName = $parts[0];
        }

        $view = "";

        if ($this->lastTable === "pages") {
            $viewsNumber = $tabg['views'] ?? '?';
            $view = $this->makeViewByNumber($target, $viewsNumber, $llang, $pupdate);
        }

        $encodedTitle = rawurlencode(str_replace(' ', '_', $mdTitle));
        $escapedTitle = htmlspecialchars($mdTitle, ENT_QUOTES, 'UTF-8');

        $encodedTarget = rawurlencode(str_replace(' ', '_', $target));
        $escapedDisplay = htmlspecialchars($target, ENT_QUOTES, 'UTF-8');

        $targetLink = "<a target='_blank' href='https://{$llang}.wikipedia.org/wiki/{$encodedTarget}'>{$escapedDisplay}</a>";

        $mdTitleEncoded = rawurlencode($mdTitle);

        $flags = "";

        return <<<HTML
        <tr>
            <td>
                $nnnn
            </td>
            <td>
                <a href="/Translation_Dashboard/leaderboard.php?user=$user" data-bs-toggle="tooltip" data-bs-title="$user">
                    $userName
                </a>
            </td>
            <td>
                <a target='_blank' href='https://mdwiki.org/wiki/{$encodedTitle}'>{$escapedTitle}</a>
            </td>
            <td>
                $campaign
            </td>
            <td class="link_container">
                <a href='/Translation_Dashboard/leaderboard.php?langcode=$llang'>$llang</a>: $targetLink
            </td>
            <td>
                $pupdate
            </td>
            <td>
                $view
            </td>
            <td>
                <a href="//mdwikicx.toolforge.org/wiki/$llang/$mdTitleEncoded" target="_blank">$addDate</a>
            </td>
            <td>
                $flags
            </td>
        </tr>
    HTML;
    }

    private function filterRecent(string $lang, array $data): string
    {
        ksort($data);
        $langList = "<option data-tokens='All' value='All'>All</option>";

        foreach ($data as $codr) {
            $code    = $codr["lang"] ?? "";
            $autonym = $codr["autonym"] ?? "";
            if (empty($code)) {
                continue;
            }

            $selected = ($code === $lang) ? 'selected' : '';
            $langList .= <<<HTML
            <option data-tokens='$code' value='$code' $selected>($code) $autonym</option>
            HTML;
        }

        return $langList;
    }

    private function buildNamespaceFilter(): string
    {
        $data = [
            "pages"       => 'Main',
            "pages_users" => 'User',
        ];

        $filterTa = "";

        foreach ($data as $tableName => $label) {
            $checked = ($tableName === $this->lastTable) ? "checked" : "";
            $filterTa .= <<<HTML
        <div class="form-check form-check-inline">
            <input class="form-check-input"
                type="radio"
                name="last_table"
                id="radio_$tableName"
                value="$tableName"
                $checked>
            <label class="form-check-label" for="radio_$tableName">$label</label>
        </div>
    HTML;
        }

        return $filterTa;
    }

    private function renderCard(int $countResult, string $filterTa, string $filterByLang, string $tableId, string $recentRows): void
    {
        $campaignNumber = 3;
        $flagsNumber = 8;

        echo <<<HTML
    <div class='card'>
        <div class='card-header'>
            <form method='get' action='index.php'>
                <input name='ty' value='last' type='hidden'/>
                <div class='row'>
                    <div class='col-md-4'>
                        <h4>Recent translations ($countResult):</h4>
                    </div>
                    <div class='col-md-4'>
                        <div class="input-group">
                            <span class="input-group-text">Namespace:</span>
                            <div class="form-control">
                                $filterTa
                            </div>
                        </div>
                    </div>
                    <div class='col-md-3'>
                        <div class="input-group">
                            <!-- <span class="input-group-text">Lang:</span> -->  <!-- bg-light-subtle -->
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
                                $filterByLang
                            </select>
                        </div>
                    </div>
                    <div class='aligncenter col-md-1'>
                        <input class='btn btn-outline-primary' type='submit' value='Filter' />
                    </div>
                </div>
            </form>
        </div>
        <div class='card-body'>
            <div class="d-none d-md-inline">
                <span class="" data-column="0">Toggle columns:</span>
                <a class="toggle-vis btn btn-outline-primary" data-column="$campaignNumber" type="button">Campaign</a>
                <a class="toggle-vis btn btn-outline-primary" data-column="$flagsNumber" type="button">Flags</a>
            </div>
            <table class="table table-sm table-striped table_text_left" id="$tableId" style="font-size:90%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Title</th>
                        <th>Campaign</th>
                        <th>Translated</th>
                        <th>Published</th>
                        <th>Views</th>
                        <th>Draft</th>
                        <th>Flags</th>
                    </tr>
                </thead>
                <tbody>
                    $recentRows
                </tbody>
            </table>
        </div>
    </div>
HTML;
    }

    private function renderScript(): void
    {
        echo <<<HTML
<script>
    $(document).ready(function() {
        var table;
        var tableElement = $('#last_table');
        if (tableElement.length) {
            table = $('#last_table').DataTable({
                stateSave: true,
                // order: [ [6, 'desc'] ],
                paging: false,
                // lengthMenu: [[100, 150, 200], [250, 150, 200]],
                // scrollY: 800,
                responsive: {
                    details: true
                }
            });
        }

        var usersTableElement = $('#last_users_table');
        if (usersTableElement.length) {
            table = $('#last_users_table').DataTable({
                stateSave: true,
                // paging: false,
                lengthMenu: [
                    [100, 150, 200],
                    [100, 150, 200]
                ],
                // scrollY: 800,
                responsive: {
                    details: true
                }
            });
        }
        if (table) {
            document.querySelectorAll('a.toggle-vis').forEach((el) => {
                el.addEventListener('click', function(e) {
                    e.preventDefault();

                    // add class mb_btn_active to this
                    el.classList.toggle('btn-outline-primary');
                    el.classList.toggle('btn-outline-secondary');

                    let columnIdx = e.target.getAttribute('data-column');
                    let column = table.column(columnIdx);

                    // Toggle the visibility
                    column.visible(!column.visible());
                });
            });
        }

    });
</script>
HTML;
    }
}
