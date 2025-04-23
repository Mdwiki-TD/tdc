<?PHP

// include_once 'tablesd/langcode.php';
use Tables\Langs\LangsTables;
use function Actions\Html\make_mdwiki_title;
use function Actions\Html\make_talk_url;
use function Actions\Html\make_target_url;
use function SQLorAPI\Recent\get_recent_translated;
use function SQLorAPI\Get\get_pages_langs;
use function Tools\RecentHelps\filter_table;
use function Tools\RecentHelps\filter_recent;
//---
$lang = $_GET['lang'] ?? 'All';
//---
// if ($_SERVER['SERVER_NAME'] == 'localhost' && $lang == "All") $lang = "ar";
//---
$table = (isset($_GET['table'])) ? $_GET['table'] : "pages";
//---
if ($lang !== 'All' && !isset(LangsTables::$L_code_to_lang[$lang])) {
    $lang = 'All';
};
//---
function get_languages()
{
    //---
    $tabes = [];
    //---
    $llangs = get_pages_langs();
    //---
    foreach ($llangs as $tat) {
        //---
        if (gettype($tat) !== 'string') {
            echo "<br>tat: $tat";
            continue;
        }
        //---
        $lag = strtolower($tat);
        //---
        $tabes[] = $lag;
        //---
    };
    //---
    ksort($tabes);
    //---
    return $tabes;
}
//---
$recent_table = <<<HTML
	<table class="table table-sm table-striped table-mobile-responsive table-mobile-sided" id="pages_table" style="font-size:90%;">
		<thead>
			<tr>
				<th>#</th>
				<th>User</th>
				<th>Lang.</th>
				<th>Title</th>
				<th>Translated</th>
				<th>Publication date</th>
				<th>Edit</th>
			</tr>
		</thead>
		<tbody>
HTML;
//---
function make_edit_icon($id, $title, $target, $lang, $user, $pupdate, $table)
{
    //---
    $edit_params = array(
        'id'   => $id,
        // 'title'     => $title,
        // 'target'  => $target,
        // 'lang'    => $lang,
        // 'user'    => $user,
        // 'pupdate' => $pupdate,
        'table' => $table,
        'nonav' => 1

    );
    //---
    if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
        $edit_params['test'] = 1;
    }
    //---
    $edit_url = "index.php?ty=translated/edit_page&" . http_build_query($edit_params);
    //---
    $onclick = 'pupwindow1("' . $edit_url . '")';
    //---
    return <<<HTML
		<a class='btn btn-outline-primary btn-sm' onclick='$onclick'>Edit</a>
	HTML;
}
//---
function make_td($tabg, $nnnn, $table)
{
    //---
    $id          = $tabg['id'] ?? "";
    //---
    $user      = $tabg['user'] ?? "";
    $lang      = $tabg['lang'] ?? "";
    $md_title = trim($tabg['title'] ?? '');
    $target      = trim($tabg['target'] ?? '');
    $pupdate  = $tabg['pupdate'] ?? '';
    //---
    $mdwiki_title = make_mdwiki_title($md_title);
    //---
    $targe33 = make_target_url($target, $lang);
    //---
    $edit_icon = make_edit_icon($id, $md_title, $target, $lang, $user, $pupdate, $table);
    //---
    $laly = <<<HTML
		<tr>
			<td data-content='#'>
				$nnnn
			</td>
			<td data-content='User'>
				<a href='/Translation_Dashboard/leaderboard.php?user=$user'>$user</a>
			</td>
			<td data-content='Lang.'>
				<a href='/Translation_Dashboard/leaderboard.php?langcode=$lang'>$lang</a>
			</td>
			<td data-content='Title'>
				$mdwiki_title
			</td>
			<td data-content='Translated'>
				$targe33
			</td>
			<td data-content='Publication date'>
				$pupdate
			</td>
			<td data-content='Edit'>
				$edit_icon
			</td>
		</tr>
	HTML;
    //---
    return $laly;
};
//---
$qsl_results = get_recent_translated($lang, $table);
//---
$noo = 0;
foreach ($qsl_results as $tat => $tabe) {
    //---
    $noo = $noo + 1;
    $recent_table .= make_td($tabe, $noo, $table);
    //---
};
//---
$recent_table .= <<<HTML
		</tbody>
	</table>
HTML;
//---
$lang_table = get_languages();
$filter_la = filter_recent($lang, $lang_table);
//---
$data = [
    "pages" => 'Main',
    "pages_users" => 'User',
];
//---
$filter_ta = filter_table($data, $table, 'table');
//---
echo <<<HTML
	<div class='card-header'>
		<form class='form-inline' style='margin-block-end: 0em;' method='get' action='index.php'>
			<input name='ty' value='translated' hidden/>
			<div class='row'>
				<div class='col-md-3'>
					<h4>Translated Pages:</h4>
				</div>
				<div class='col-md-4'>
					$filter_ta
				</div>
				<div class='col-md-3'>
					$filter_la
				</div>
				<div class='aligncenter col-md-2'>
					<input class='btn btn-outline-primary' type='submit' value='Filter' />
				</div>
			</div>
		</form>
	</div>
	<div class='card-body'>
HTML;
//---
echo $recent_table;
//---
?>
<script>
    $(document).ready(function() {
        var t = $('#pages_table').DataTable({
            // order: [[10	, 'desc']],
            // paging: false,
            lengthMenu: [
                [50, 100, 150],
                [50, 100, 150]
            ],
            // scrollY: 800
        });
    });
</script>
