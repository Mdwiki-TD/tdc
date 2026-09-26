<?php
// src/app/tools/last.php

namespace App\Tools;

use App\Coordinator\RecentTranslations;
use App\Coordinator\Admin\Common\AbstractControllerNoPost;

/**
 * Class LastController
 * Renders the "Recent translations" for non-admin/general tools view.
 */
class LastController extends AbstractControllerNoPost
{
	private RecentTranslations $helper;
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

		$this->helper = new RecentTranslations($this->lang, $this->lastTable);
	}

	/**
	 * Handles request execution.
	 */
	public function handleRequest(): void
	{
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


	/**
	 * Renders the main filter + results card.
	 */
	public function renderMainCard(int $countResult, string $filterTa, string $filterByLang, string $tableId, string $recentRows): void
	{
		$campaignNumber = 3;
		$flagsNumber = 8;

		$header = <<<HTML
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
		HTML;
		$body = <<<HTML
			<div class="d-none d-md-inline">
				<span class='' data-column="0">Toggle columns:</span>
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
		HTML;

		$this->echoBs5Card($header, $body, '');
	}

	/**
	 * Renders a single "recent translations" table row.
	 */
	public function createLastTableData(array $tabg, int $nnnn): string
	{
		$user	 = $tabg['user'] ?? '';
		$llang	= $tabg['lang'] ?? '';
		$mdTitle  = trim($tabg['title'] ?? '');
		$target   = trim($tabg['target'] ?? '');
		$pupdate  = $tabg['pupdate'] ?? '';
		$addDate  = $tabg['add_date'] ?? '';
		$campaign = $tabg['campaign'] ?? '';

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
}
