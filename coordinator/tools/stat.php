<?PHP
//---
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
};
//---
use Tables\Main\MainTables;
use function Actions\Html\makeDropdown;
use function Results\GetCats\get_mdwiki_cat_members;
use function SQLorAPI\Funcs\get_td_or_sql_categories;
use function SQLorAPI\Funcs\get_td_or_sql_qids;
//---
$cat = $_GET['cat'] ?? 'RTT';
//---
function filter_stat($cat)
{
	$cats_titles = [];
	//---
	$categories = get_td_or_sql_categories();
	//---
	foreach ($categories as $k => $tab) $cats_titles[] = $tab['category'] ?? "";
	//---
	$d33 = <<<HTML
		<div class="input-group">
			<span class="input-group-text">%s</span>
			%s
		</div>
	HTML;
	//---
	$y1 = makeDropdown($cats_titles, $cat, 'cat', '');
	$uuu = sprintf($d33, 'Category:', $y1);
	//---
	return $uuu;
}
//---
$uuu = filter_stat($cat);
//---
$fa = <<<HTML
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
	<div class='cardbody'>
HTML;
//---
$table = <<<HTML
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
//---
$titles = get_mdwiki_cat_members($cat, $use_cache = true, $depth = 1);
//---
$no_qid = 0;
$no_word = 0;
$no_allword = 0;
$no_ref = 0;
$no_allref = 0;
$no_Importance = 0;
$no_pv = 0;
$i = 0;
//---
$qids_t = get_td_or_sql_qids('all');
//---
$sql_qids = array_column($qids_t, 'qid', 'title');
//---
foreach ($titles as $title) {
	$i = $i + 1;
	//---
	$qid = $sql_qids[$title] ?? "";
	//---
	if (empty($qid)) $no_qid += 1;
	//---
	$qidurl = (!empty($qid)) ? "<a href='https://wikidata.org/wiki/$qid'>$qid</a>" : '';
	//---
	$word = MainTables::$x_Words_table[$title] ?? 0;
	//---
	$allword = MainTables::$x_All_Words_table[$title] ?? 0;
	if ($word == 0) $no_word += 1;
	if ($allword == 0) $no_allword += 1;
	//---
	$refs = MainTables::$x_Lead_Refs_table[$title] ?? 0;
	//---
	$all_refs = MainTables::$x_All_Refs_table[$title] ?? 0;
	//---
	if ($refs == 0) $no_ref += 1;
	if ($all_refs == 0) $no_allref += 1;
	//---
	$asse = MainTables::$x_Assessments_table[$title] ?? '';
	if (!isset(MainTables::$x_Assessments_table[$title])) $no_Importance += 1;
	//---
	$pv = MainTables::$x_enwiki_pageviews_table[$title] ?? 0;
	if (!isset(MainTables::$x_enwiki_pageviews_table[$title])) $no_pv += 1;
	//---
	//---
	$table .= <<<HTML
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
			$all_refs</td>
		<td data-content='Importance'>
			$asse</td>
		<td data-content='enwiki views'>
			<a href='https://en.wikipedia.org/w/api.php?action=query&prop=pageviews&titles=$title&redirects=1&pvipdays=30'>$pv</a></td>
	</tr>
	HTML;
}
//---
$table .= "</table>";
//---
$with_q = $i - $no_qid;
$with_word = $i - $no_word;
$with_allword = $i - $no_allword;
$with_ref = $i - $no_ref;
$with_allref = $i - $no_allref;
$with_Importance = $i - $no_Importance;
$with_pv = $i - $no_pv;
//---
echo $fa;
//---
$lilo = [
	'qid' => ['with' => $with_q, 'without' => $no_qid],
	'enwiki views' => ['with' => $with_pv, 'without' => $no_pv],
	'Importance' => ['with' => $with_Importance, 'without' => $no_Importance],
	'word' => ['with' => $with_word, 'without' => $no_word],
	'allword' => ['with' => $with_allword, 'without' => $no_allword],
	'ref' => ['with' => $with_ref, 'without' => $no_ref],
	'allref' => ['with' => $with_allref, 'without' => $no_allref],
];
//---
$ths = '';
$with = '';
$without = '';
//---
foreach ($lilo as $k => $v) {
	$ths .= "<th>$k</th>";
	$with .= "<td>{$v['with']}</td>";
	$without .= "<td>{$v['without']}</td>";
}
//---
echo <<<HTML
	<div class=''>
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
HTML;
//---

//---
echo $table;
//---
echo '
</div>
</div>
';
