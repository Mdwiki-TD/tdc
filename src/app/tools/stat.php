<?php

namespace App\Tools;

use App\Coordinator\Admin\Common\AbstractControllerNoPost;
use App\Tables\Main\MainTables;
use function App\Utils\Html\makeDropdown;
use function App\Results\GetCats\get_mdwiki_cat_members;
use function App\SQLorAPI\Funcs\get_td_or_sql_categories;
use function App\SQLorAPI\Funcs\get_td_or_sql_qids;

/**
 * Class StatController
 * Renders status and stats dashboard by category.
 */
class StatController extends AbstractControllerNoPost
{
    private string $cat;

    public function __construct()
    {
        $this->cat = $_GET['cat'] ?? 'RTT';
    }

    /**
     * Handles request execution.
     */
    public function handleRequest(): void
    {
        $uuu = $this->filterStat($this->cat);

        $tableHtml = <<<HTML
	<table class='table table-striped compact soro table-mobile-responsive table-mobile-sided table_text_left'>
		<thead>
			<tr>
				<th>#</th>
				<th>title</th>
				<th>qid</th>
				<th>lead word</th>
				<th>all word</th>
				<th>ref</th>
				<th>all ref</th>
				<th>Importance</th>
				<th>enwiki views</th>
			</tr>
		</thead>
		<tbody>
	HTML;

        $titles = get_mdwiki_cat_members($this->cat, true, 1);

        $noQid = 0;
        $noWord = 0;
        $noAllword = 0;
        $noRef = 0;
        $noAllref = 0;
        $noImportance = 0;
        $noPv = 0;
        $i = 0;

        $qids_t = get_td_or_sql_qids('all');
        $sqlQids = array_column($qids_t, 'qid', 'title');

        foreach ($titles as $title) {
            $i++;

            $qid = $sqlQids[$title] ?? "";

            if (empty($qid)) {
                $noQid++;
            }

            $qidurl = (!empty($qid)) ? "<a href='https://wikidata.org/wiki/$qid'>$qid</a>" : '';

            $word = MainTables::$xWordsTable[$title] ?? 0;

            $allword = MainTables::$xAllWordsTable[$title] ?? 0;
            if ($word == 0) {
                $noWord++;
            }
            if ($allword == 0) {
                $noAllword++;
            }

            $refs = MainTables::$xLeadRefsTable[$title] ?? 0;

            $allRefs = MainTables::$xAllRefsTable[$title] ?? 0;

            if ($refs == 0) {
                $noRef++;
            }
            if ($allRefs == 0) {
                $noAllref++;
            }

            $asse = MainTables::$xAssessmentsTable[$title] ?? '';
            if (!isset(MainTables::$xAssessmentsTable[$title])) {
                $noImportance++;
            }

            $pv = MainTables::$xEnwikiPageviewsTable[$title] ?? 0;
            if (!isset(MainTables::$xEnwikiPageviewsTable[$title])) {
                $noPv++;
            }

            $tableHtml .= <<<HTML
	<tr>
		<td data-content='#'>
			$i</td>
		<td data-content='Title'>
			<a href="https://mdwiki.org/wiki/$title">$title</a></td>
		<td data-content='Qid'>
			$qidurl</td>
		<td data-content='Lead Word'>
			$word</td>
		<td data-content='All Word'>
			$allword</td>
		<td data-content='Ref'>
			$refs</td>
		<td data-content='All Ref'>
			$allRefs</td>
		<td data-content='Importance'>
			$asse</td>
		<td data-content='enwiki views'>
			<a href='https://en.wikipedia.org/w/api.php?action=query&prop=pageviews&titles=$title&redirects=1&pvipdays=30'>$pv</a></td>
	</tr>
	HTML;
        }

        $tableHtml .= "</table>";

        $with_q = $i - $noQid;
        $withWord = $i - $noWord;
        $withAllword = $i - $noAllword;
        $withRef = $i - $noRef;
        $withAllref = $i - $noAllref;
        $withImportance = $i - $noImportance;
        $withPv = $i - $noPv;

        $lilo = [
            'qid' => ['with' => $with_q, 'without' => $noQid],
            'enwiki views' => ['with' => $withPv, 'without' => $noPv],
            'Importance' => ['with' => $withImportance, 'without' => $noImportance],
            'word' => ['with' => $withWord, 'without' => $noWord],
            'allword' => ['with' => $withAllword, 'without' => $noAllword],
            'ref' => ['with' => $withRef, 'without' => $noRef],
            'allref' => ['with' => $withAllref, 'without' => $noAllref],
        ];

        $ths = '';
        $with = '';
        $without = '';

        foreach ($lilo as $k => $v) {
            $ths .= "<th>$k</th>";
            $with .= "<td>{$v['with']}</td>";
            $without .= "<td>{$v['without']}</td>";
        }

        echo <<<HTML
	<div class='card'>
		<div class='card-header'>
			<form method='get' action='index.php'>
				<input name='ty' value='stat' type='hidden'/>
				<div class='row'>
					<div class='col-md-3'>
						<h4>Status:</h4>
					</div>
					<div class='col-md-3'>
						$uuu
					</div>
					<div class='aligncenter col-md-2'><input class='btn btn-outline-primary' type='submit' value='Filter' /></div>
				</div>
			</form>
		</div>
		<div class='card-body1'>
			<table class='table table-striped compact table_text_left'>
				<thead>
					<tr>
						<th>Key</th>
						$ths
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>With</th>
						$with
					</tr>
					<tr>
						<th>Without</th>
						$without
					</tr>
				</tbody>
			</table>
		</div>
		<div class='card-body'>
			$tableHtml
		</div>
	</div>
HTML;
    }

    private function filterStat(string $cat): string
    {
        $catsTitles = [];
        $categories = get_td_or_sql_categories();

        foreach ($categories as $k => $tab) {
            $catsTitles[] = $tab['category'] ?? "";
        }

        $d33 = <<<HTML
		<div class="input-group">
			<span class="input-group-text">%s</span>
			%s
		</div>
	HTML;

        $y1 = makeDropdown($catsTitles, $cat, 'cat', '');
        return sprintf($d33, 'Category:', $y1);
    }
}
