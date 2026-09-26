<?php
// src/app/coordinator/RecentTranslations.php

// use App\Coordinator\RecentTranslations;
namespace App\Coordinator;

use function App\APICalls\TDApi\get_td_api;

class RecentTranslations
{
    private string $lang;
    private string $lastTable;

    public function __construct(string $lang, string $lastTable)
    {
        $this->lang = $lang;
        $this->lastTable = $lastTable;
    }

    /**
     * Fetches the "recent translations" result set from the TD API.
     */
    public function fetchResults(): array
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

        $apiResults = ($this->lastTable === 'pages')
            ? get_td_api($apiParamsPages)
            : get_td_api($apiParamsUsers);

        return $apiResults['results'] ?? [];
    }

    /**
     * Fetches the list of language options for the current table.
     */
    public function fetchLangOptions(): array
    {
        $apiParamsLangs = [
            'get' => ($this->lastTable === 'pages') ? 'pages_langs' : 'pages_users_langs',
        ];

        $apiResults = get_td_api($apiParamsLangs);

        return $apiResults['results'] ?? [];
    }

    /**
     * Builds the namespace (pages / pages_users) radio filter markup.
     */
    public function buildNamespaceFilter(): string
    {
        $data = [
            'pages'       => 'Main',
            'pages_users' => 'User',
        ];

        $filterTa = '';

        foreach ($data as $tableName => $label) {
            $checked = ($tableName === $this->lastTable) ? 'checked' : '';
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

    /**
     * Builds the pageviews link/number for a single row.
     */
    public function makeViewByNumber(string $target, $numb, string $lang, string $pupdate): string
    {

        // TODO: remove makeViewByNumber, and use make_view_by_number from wiki_api.php
        // remove spaces and tab characters
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
            // 'range'  => 'all-time',
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

    /**
     * Builds the language filter <select> options.
     */
    public function filterRecent(string $lang, array $data): string
    {
        ksort($data);
        $langList = "<option data-tokens='All' value='All'>All</option>";

        foreach ($data as $codr) {
            $code    = $codr["lang"] ?? '';
            $autonym = $codr["autonym"] ?? '';
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

    /**
     * Renders the DataTables initialization and column-toggle scripts.
     */
    public function renderDataTableScript(): void
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
