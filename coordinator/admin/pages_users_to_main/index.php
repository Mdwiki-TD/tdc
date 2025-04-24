<?PHP

// include_once 'tablesd/langcode.php';
use Tables\Langs\LangsTables;
use function Actions\Html\make_mdwiki_title;
use function Actions\Html\make_target_url;
use function SQLorAPI\Recent\get_pages_users_to_main;
use function SQLorAPI\Get\get_pages_users_langs;
use function Tools\RecentHelps\filter_recent;
//---
$lang = $_GET['lang'] ?? 'All';
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
    $llangs = get_pages_users_langs();
    //---
    foreach ($llangs as $tat) {
        //---
        if (gettype($tat) !== 'string') {
            // echo "<br>tat: $tat";
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
				<th>Lang.</th>
				<th>Title</th>
				<th>Publication</th>
				<th>Old User</th>
				<th>Old target</th>
				<th>New User</th>
				<th>New target</th>
				<th>Fix it</th>
			</tr>
		</thead>
		<tbody>
HTML;
//---
function make_edit_icon($id)
{
    //---
    $edit_params = array(
        'id'   => $id,
        'nonav' => 1

    );
    //---
    if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
        $edit_params['test'] = 1;
    }
    //---
    $edit_url = "index.php?ty=pages_users_to_main/edit_page&" . http_build_query($edit_params);
    //---
    $onclick = 'pupwindow1("' . $edit_url . '")';
    $onclick = '';
    //---
    return <<<HTML
		<a class='btn btn-outline-primary btn-sm' onclick='$onclick'>Fix it</a>
	HTML;
}
//---
function make_td($tabg, $nnnn)
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
    $targe33 = make_target_url($target, $lang);
    //---
    $new_user   = $tabg['new_user'] ?? "";
    $new_target = $tabg['new_target'] ?? "";
    //---
    $targe44 = make_target_url($new_target, $lang);
    //---
    $edit_icon = make_edit_icon($id);
    //---
    $laly = <<<HTML
		<tr>
			<td data-content='#'>
				$nnnn
			</td>
			<td data-content='Lang'>
				<a href='/Translation_Dashboard/leaderboard.php?langcode=$lang'>$lang</a>
			</td>
			<td data-content='Title'>
				$mdwiki_title
			</td>
			<td data-content='Publication'>
				$pupdate
			</td>
			<td data-content='Old User'>
				<a href='/Translation_Dashboard/leaderboard.php?user=$user'>$user</a>
			</td>
			<td data-content='Old target'>
				$targe33
			</td>
			<td data-content='New User'>
				<a href='/Translation_Dashboard/leaderboard.php?user=$new_user'>$new_user</a>
			</td>
			<td data-content='New target'>
				$targe44
			</td>
			<td data-content='Fix it'> $edit_icon </td>
		</tr>
	HTML;
    //---
    return $laly;
};
//---
$qsl_results = get_pages_users_to_main($lang);
//---
$noo = 0;
foreach ($qsl_results as $tat => $tabe) {
    //---
    $noo = $noo + 1;
    $recent_table .= make_td($tabe, $noo);
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
echo <<<HTML
	<div class='card-header'>
		<form class='form-inline' style='margin-block-end: 0em;' method='get' action='index.php'>
			<input name='ty' value='pages_users_to_main' hidden/>
			<div class='row'>
				<div class='col-md-6'>
					<h4>Translated Pages in user namespace to move to main namespace:</h4>
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
