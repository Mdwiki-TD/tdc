<?php
// src/app/coordinator/admin/last_coord/index.php

namespace App\Coordinator\Admin\LastCoord;

use App\User\CurrentUser;
use App\Coordinator\RecentTranslations;
use App\Coordinator\Admin\Common\AbstractControllerNoPost;
use function App\APICalls\TDApi\get_td_api;

/**
 * Class LastCoordIndexController
 * Renders the "Recent translations" dashboard, sourced from the TD API
 * (either the "pages" or "pages_users" endpoint), with language and
 * namespace filters and per-row mail/fixref/draft links.
 */
class LastCoordIndexController extends AbstractControllerNoPost
{
    private RecentTranslations $helper;
    private string $lang;
    private string $lastTable;
    private string $globalUsername;

    public function __construct()
    {
        $this->globalUsername = CurrentUser::getInstance()->getUsername();

        $this->lang = $_GET['lang'] ?? 'All';
        if (empty($this->lang)) {
            $this->lang = 'All';
        }

        $this->lastTable = $_GET['last_table'] ?? 'pages';
        $this->lastTable = in_array($this->lastTable, ['pages', 'pages_users'], true) ? $this->lastTable : 'pages';

        $this->helper = new RecentTranslations($this->lang, $this->lastTable);
    }

    /**
     * Builds the escaped "send mail" URL for a single row.
     */
    public function makeMailIconUrl(array $tab): string
    {
        $mailParams = [
            'user'   => $tab['user'] ?? '',
            'lang'   => $tab['lang'] ?? '',
            'target' => $tab['target'] ?? '',
            'date'   => $tab['pupdate'] ?? '',
            'title'  => $tab['title'] ?? '',
            'nonav'  => '1',
        ];

        $mailUrl = "index.php?ty=msg&" . http_build_query($mailParams, '', '&', PHP_QUERY_RFC3986);

        return htmlspecialchars($mailUrl, ENT_QUOTES, 'UTF-8');
    }
    /**
     * Handles authentication and executes controller output.
     */
    public function handleRequest(): void
    {
        $this->validateCoordinator();

        $recentRows = $this->CreateRecentRows();
        $this->ShowMainView($recentRows);

        $this->helper->renderDataTableScript();
    }
    public function CreateRecentRows(): string
    {
        $qslResults = $this->helper->fetchResults();

        $recentRows = '';
        $noo = 0;

        foreach ($qslResults as $tat => $tabe) {
            $noo++;
            $recentRows .= $this->createLastTableData($tabe, $noo);
        }
        return $recentRows;
    }
    public function ShowMainView(string $recentRows): void
    {
        $langResult = $this->helper->fetchLangOptions();
        $filterByLang = $this->helper->filterRecent($this->lang, $langResult);
        $countResult = count($langResult);

        $filterTa = $this->helper->buildNamespaceFilter();

        $tableId = ($this->lastTable === 'pages') ? 'last_table' : 'last_users_table';

        $this->renderMainCard(
            $countResult,
            $filterTa,
            $filterByLang,
            $tableId,
            $recentRows,
        );
    }

    public function CreateFixWikirefsUrl(array $tabg): string
    {
        $llang    = $tabg['lang'] ?? '';
        $mdTitle  = trim($tabg['title'] ?? '');
        $target   = trim($tabg['target'] ?? '');

        $mdwikiRevid = $tabg['mdwiki_revid'] ?? '';
        $params = [
            "title"        => $target,
            "lang"         => $llang,
            "sourcetitle"  => $mdTitle,
            "mdwiki_revid" => $mdwikiRevid,
        ];

        // TODO: This should ideally be moved to a configuration file.
        $excludedUsers = ['Mr. Ibrahem'];
        if (!in_array($this->globalUsername, $excludedUsers)) {
            $params['save'] = 1;
        }

        return "/fixwikirefs.php?" . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Renders a single "recent translations" table row.
     */
    public function createLastTableData(array $tabg, int $nnnn): string
    {
        $user     = $tabg['user'] ?? '';
        $llang    = $tabg['lang'] ?? '';
        $mdTitle  = trim($tabg['title'] ?? '');
        $target   = trim($tabg['target'] ?? '');
        $pupdate  = $tabg['pupdate'] ?? '';
        $addDate  = $tabg['add_date'] ?? '';
        $campaign = $tabg['campaign'] ?? '';

        $fixwikirefs = $this->CreateFixWikirefsUrl($tabg);

        // if $addDate has : then split before first space
        if (strpos($addDate, ':') !== false) {
            $addDate = explode(' ', $addDate)[0];
        }

        $maxUsernameDisplayLength = 15;
        $userName = $user;
        // $userName is the first word of the user if length > 15
        if (strlen($user) > $maxUsernameDisplayLength) {
            $parts = explode(' ', $user);
            $userName = $parts[0];
        }

        $view = '';

        if ($this->lastTable === "pages") {
            $viewsNumber = $tabg['views'] ?? '?';
            $view = $this->helper->makeViewByNumber($target, $viewsNumber, $llang, $pupdate);
        }

        $encodedTitle = rawurlencode(str_replace(' ', '_', $mdTitle));
        $escapedTitle = htmlspecialchars($mdTitle, ENT_QUOTES, 'UTF-8');

        $encodedTarget = rawurlencode(str_replace(' ', '_', $target));
        $escapedDisplay = htmlspecialchars($target, ENT_QUOTES, 'UTF-8');

        $targetLink = "<a target='_blank' href='https://{$llang}.wikipedia.org/wiki/{$encodedTarget}'>{$escapedDisplay}</a>";

        $mailIcon = $this->makeMailIconUrl($tabg);

        $escapedUser = rawurlencode($user);

        $talkUrl = <<<HTML
            (<a target='_blank' href='//{$llang}.wikipedia.org/w/index.php?title=User_talk:{$escapedUser}'>talk</a>)
        HTML;

        $mdTitleEncoded = rawurlencode($mdTitle);

        $flags = '';

        return <<<HTML
            <tr>
                <td>
                    $nnnn
                </td>
                <td>
                    <a href="/Translation_Dashboard/leaderboard.php?user=$user" data-bs-toggle="tooltip" data-bs-title="$user">
                        $userName
                    </a> $talkUrl
                </td>
                <td>
                    <a class='btn btn-outline-primary btn-sm spannowrap' pup-target='{$mailIcon}' onclick='pup_window_new(this)'>@</a>
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
                    <a target='_blank' href="$fixwikirefs">Fix</a>
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

    /**
     * Renders the main filter + results card.
     */
    public function renderMainCard(int $countResult, string $filterTa, string $filterByLang, string $tableId, string $recentRows): void
    {
        $campaignNumber = 4;
        $flagsNumber = 10;
        $fixNumber = 8;

        echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <form method='get' action='index.php'>
                        <input name='ty' value='last_coord' type='hidden'/>
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
                        <span class='' data-column="0">Toggle columns:</span>
                        <a class="toggle-vis btn btn-outline-primary" data-column="$campaignNumber" type="button">Campaign</a>
                        <a class="toggle-vis btn btn-outline-primary" data-column="$fixNumber" type="button">Fixref</a>
                        <a class="toggle-vis btn btn-outline-primary" data-column="$flagsNumber" type="button">Flags</a>
                    </div>
                    <table class="table table-sm table-striped table_text_left" id="$tableId" style="font-size:90%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th> <span title='Email'>@</span> </th>
                                <th>Title</th>
                                <th>Campaign</th>
                                <th>Translated</th>
                                <th>Published</th>
                                <th>Views</th>
                                <th>Fixref</th>
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
}
