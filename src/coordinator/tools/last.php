<?php

declare(strict_types=1);

use Tables\SqlTables\TablesSql;
use Tables\Main\MainTables;
use Tables\Langs\LangsTables;

use function Tools\RecentHelps\filter_recent;
use function Tools\RecentHelps\do_add_date;
// use function Actions\Html\make_cat_url;
use function Tools\RecentHelps\filter_table;
use function Actions\WikiApi\make_view_by_number;
use function Actions\Html\make_mail_icon_new;
use function Actions\Html\make_talk_url;
use function Actions\Html\make_target_url;
use function Actions\Html\make_mdwiki_title;
use function SQLorAPI\Recent\get_recent_pages_users;
use function SQLorAPI\Funcs\get_pages_users_langs;
use function SQLorAPI\Funcs\get_pages_langs;
use function SQLorAPI\Recent\get_recent_sql;

/**
 * RecentTranslations
 * مسؤول عن بناء جدول "Recent translations" وعرضه
 */
class RecentTranslations
{
    private array $viewsSql;
    private string $globalUsername;
    private bool $userInCoord;

    private array $allowedTables = ['pages', 'pages_users'];

    public function __construct(array $viewsSql = [], string $globalUsername = '', bool $userInCoord = false)
    {
        $this->viewsSql = $viewsSql;
        $this->globalUsername = $globalUsername;
        $this->userInCoord = $userInCoord;
    }

    private function safeGet(string $key, $default = null)
    {
        return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
    }

    private function normalizeLastTable(string|null $raw): string
    {
        $last = $raw ?: 'pages';
        return in_array($last, $this->allowedTables, true) ? $last : 'pages';
    }

    private function renderRow(array $row, int $index, bool $addAdd, string $lastTable): string
    {
        // Extract with defaults
        $date       = $row['date'] ?? '';
        $user       = $row['user'] ?? '';
        $langCode   = $row['lang'] ?? '';
        $mdTitle    = trim($row['title'] ?? '');
        $cat        = $row['cat'] ?? '';
        $word       = $row['word'] ?? '';
        $target     = trim($row['target'] ?? '');
        $pupdate    = $row['pupdate'] ?? '';
        $addDate    = $row['add_date'] ?? '';
        $mdwikiRevid = $row['mdwiki_revid'] ?? '';

        // simplify add_date (keep date part if time included)
        if (strpos($addDate, ':') !== false) {
            $addDate = explode(' ', $addDate)[0];
        }

        // user name display logic
        $userName = $user;
        if (mb_strlen($user, 'UTF-8') > 15) {
            $parts = preg_split('/\s+/', $user);
            $userName = $parts[0] ?? $user;
        }

        $campaignTd = '';
        $mailIconTd = '';
        $viewTd = '';

        if ($lastTable === 'pages') {
            $viewsNumber = $row['views'] ?? '';
            if (empty($viewsNumber)) {
                $viewsNumber = $this->viewsSql[$target] ?? '?';
            }

            // $ccat = make_cat_url( $cat );
            $ccat = TablesSql::$s_cat_to_camp[$cat] ?? $cat;

            $word = $word ?? MainTables::$x_Words_table[$mdTitle] ?? '';

            $view = make_view_by_number($target, $viewsNumber, $langCode, $pupdate);

            $mailIcon = $this->userInCoord ? make_mail_icon_new($row, 'pup_window_email') : '';
            $mailIconTd = !empty($mailIcon) ? "<td data-content='Email'>$mailIcon</td>" : '';

            $viewTd = <<<HTML
                <td data-content='Views'>
                    $view
                </td>
            HTML;

            $campaignTd = <<<HTML
                <td data-content='Campaign'>
                    $ccat
                </td>
            HTML;
        }

        $langDisplay = $langCode; // could map via LangsTables::$L_code_to_lang if desired

        $mdTitleHtml = make_mdwiki_title($mdTitle);

        $targetDisplay = $target;
        // $targetDisplay = mb_strlen($targetDisplay, 'UTF-8') > 15 ? mb_substr($targetDisplay, 0, 15) . '...' : $targetDisplay;

        $targetLink = make_target_url($target, $langCode, $targetDisplay);
        $talk = make_talk_url($langCode, $user);

        $mdTitleEncoded = rawurlencode($mdTitle);

        $addAddCell = $addAdd ? <<<HTML
            <td data-content='add_date'>
                <a href="//medwiki.toolforge.org/wiki/{$langCode}/{$mdTitleEncoded}" target="_blank">{$addDate}</a>
            </td>
        HTML : '';

        $params = [
            'title' => $target,
            'lang'  => $langCode,
            'sourcetitle' => $mdTitle,
            'mdwiki_revid' => $mdwikiRevid,
        ];

        if ($this->globalUsername !== 'Mr. Ibrahem') {
            $params['save'] = 1;
        }

        $fixwikirefs = '/fixwikirefs.php?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        // ensure escaping of attributes that may contain quotes (user can contain quotes)
        $userAttr = htmlspecialchars($user, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $userNameEsc = htmlspecialchars($userName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $rowHtml = <<<HTML
            <tr>
                <td data-content='#'>{$index}</td>
                <td data-content='User'>
                    <a href="/Translation_Dashboard/leaderboard.php?user={$userAttr}" data-bs-toggle="tooltip" data-bs-title="{$userAttr}">{$userNameEsc}</a> ({$talk})
                </td>
                {$mailIconTd}
                <td data-content='Title'>{$mdTitleHtml}</td>
                {$campaignTd}
                <td data-content='Translated' class="link_container">
                    <a href='/Translation_Dashboard/leaderboard.php?langcode={$langCode}'>{$langDisplay}</a> : {$targetLink}
                </td>
                <td data-content='Publication date'>{$pupdate}</td>
                {$viewTd}
                <td data-content='Fixref'>
                    <a target='_blank' href="{$fixwikirefs}">Fix</a>
                </td>
                {$addAddCell}
            </tr>
        HTML;

        return $rowHtml;
    }

    public function render(): void
    {
        $rawLastTable = $this->safeGet('last_table', 'pages');
        $lastTable = $this->normalizeLastTable($rawLastTable);

        $lang = $this->safeGet('lang', 'All');
        if ($lang !== 'All' && !isset(LangsTables::$L_code_to_lang[$lang])) {
            $lang = 'All';
        }

        $mailTh = $this->userInCoord ? "<th>Email</th>" : '';

        // fetch data
        $results = ($lastTable === 'pages') ? get_recent_sql($lang) : get_recent_pages_users($lang);

        // determine add_add (kept simple here; original forced true)
        $addAdd = true; // or: (bool) do_add_date($results);
        $thAdd = $addAdd ? "<th>add_date</th>" : '';

        $rowsHtml = '';
        $counter = 0;
        foreach ($results as $item) {
            $counter++;
            $rowsHtml .= $this->renderRow($item, $counter, $addAdd, $lastTable);
        }

        $tableId = ($lastTable === 'pages') ? 'last_tabel' : 'last_users_tabel';

        // build thead
        if ($lastTable === 'pages') {
            $thead = <<<HTML
                <tr>
                    <th>#</th>
                    <th>User</th>
                    {$mailTh}
                    <th>Title</th>
                    <th>Campaign</th>
                    <th>Translated</th>
                    <th>Publication date</th>
                    <th>Views</th>
                    <th>Fixref</th>
                    {$thAdd}
                </tr>
            HTML;
        } else {
            $thead = <<<HTML
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Title</th>
                    <th>Translated</th>
                    <th>Publication date</th>
                    <th>Fixref</th>
                    {$thAdd}
                </tr>
            HTML;
        }

        $recentTable = <<<HTML
            <table class="table table-sm table-striped table-mobile-responsive table-mobile-sided table_text_left" id="{$tableId}" style="font-size:90%;">
                <thead>{$thead}</thead>
                <tbody>{$rowsHtml}</tbody>
            </table>
        HTML;

        // options for filters/menu
        $resultForFilters = ($lastTable === 'pages') ? get_pages_langs() : get_pages_users_langs();
        $filterByLang = filter_recent($lang, $resultForFilters);
        $data = [
            'pages' => 'Main',
            'pages_users' => 'User',
        ];
        $filterTable = filter_table($data, $lastTable, 'last_table');
        $countResult = count($resultForFilters);

        echo <<<HTML
            <div class='card-header'>
                <form method='get' action='index.php'>
                    <input name='ty' value='last' type='hidden'/>
                    <div class='row'>
                        <div class='col-md-4'>
                            <h4>Recent translations ({$countResult}):</h4>
                        </div>
                        <div class='col-md-4'>
                            {$filterTable}
                        </div>
                        <div class='col-md-3'>
                            {$filterByLang}
                        </div>
                        <div class='aligncenter col-md-1'>
                            <input class='btn btn-outline-primary' type='submit' value='Filter' />
                        </div>
                    </div>
                </form>
            </div>
            <div class='card-body'>
                {$recentTable}
            </div>
            HTML;
    }
}

// --- bootstrap usage ---
// Note: اجعل المتغيرات التالية تُمرَر حسب السياق الفعلي في تطبيقك:
$views_sql = $views_sql ?? []; // إذا كانت موجودة من مكان آخر
$global_username = $GLOBALS['global_username'] ?? '';
$user_in_coord = defined('user_in_coord') ? (bool) user_in_coord : false;

$rt = new RecentTranslations($views_sql, $global_username, $user_in_coord);
$rt->render();

// DataTables init script: init only tables that exist
$tableSelector = ($tableId === 'last_tabel') ? '#last_tabel' : '#last_users_tabel';
echo <<<JS
    <script>
        (function() {
            function initIfExists(selector, options) {
                if (document.querySelector(selector)) {
                    $(selector).DataTable(options);
                }
            }

            $(document).ready(function() {
                initIfExists('#last_tabel', {
                    stateSave: true,
                    paging: false
                });

                initIfExists('#last_users_tabel', {
                    stateSave: true,
                    order: [[4, 'desc']],
                    lengthMenu: [[100,150,200],[100,150,200]]
                });
            });
        })();
    </script>
    JS;
