<?PHP

// include_once 'tablesd/langcode.php';
use Tables\Langs\LangsTables;
use function Actions\Html\make_mdwiki_title;
use function Actions\Html\make_target_url;
use function Actions\Html\make_edit_icon_new;
use function SQLorAPI\Recent\get_pages_users_to_main;
use function SQLorAPI\Funcs\get_pages_users_langs;
use function Tools\RecentHelps\filter_recent;
use function SQLorAPI\Funcs\td_or_sql_titles_infos;
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
$Toggle_column = <<<HTML
    <div>
        <span class="toggle-vis btn" data-column="0">Toggle columns:</span>
        <a class="toggle-vis btn btn-outline-primary" data-column="0" type="button">#</a>
        <a class="toggle-vis btn btn-outline-primary" data-column="1" type="button">Lang.</a>
        <a class="toggle-vis btn btn-outline-primary" data-column="2" type="button">Title</a>
        <a class="toggle-vis btn btn-outline-primary" data-column="3" type="button">Qid</a>
        <a class="toggle-vis btn btn-outline-primary" data-column="4" type="button">Publication</a>
    </div>
HTML;
//---
$recent_table = <<<HTML
    $Toggle_column
	<table class="table table-sm table-striped table-mobile-responsive table-mobile-sided" id="pages_table" style="font-size:90%;">
		<thead>
			<tr>
				<th>#</th>
				<th>Lang.</th>
				<th>Title</th>
				<th>Qid</th>
				<th>Publication</th>
				<th>Old User</th>
				<th>New User</th>
				<th>Old target</th>
				<th>New target</th>
				<th>New Qid</th>
				<th>Fix it</th>
			</tr>
		</thead>
		<tbody>
HTML;

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
    $edit_params = array(
        'id'   => $id,
        'new_user'   => $new_user,
        'new_target'   => $new_target

    );
    //---
    $edit_icon = make_edit_icon_new("pages_users_to_main/fix_it", $edit_params);
    //---
    $qid          = $tabg['qid'] ?? "";
    $new_qid      = $tabg['new_qid'] ?? "";
    //---
    $qid_link = (!empty($qid)) ? "<a target='_blank' href='https://wikidata.org/wiki/$qid'>$qid</a>" : "";
    $new_qid_link = (!empty($new_qid)) ? "<a target='_blank' href='https://wikidata.org/wiki/$new_qid'>$new_qid</a>" : "";
    //---
    $new_target2 = htmlspecialchars($new_target, ENT_QUOTES);
    //---
    if (!empty($qid) && empty($new_qid)) {
        $same_qid = "bg-info-subtle";
        $new_qid_link = "<a class='fw-bold' target='_blank' href='https://www.wikidata.org/wiki/Special:SetSiteLink/$qid/{$lang}wiki?page=$new_target2' u-lang='$lang' u-qid='$qid' u-target='$new_target2'>Link it!</a>";
    } else {
        $same_qid = ($qid == $new_qid) ? "bg-info-subtle" : "bg-danger-subtle";
    }
    //---
    if (!empty($qid) && $new_qid == $qid) {
        $same_qid = "";
        $new_qid_link = "<a target='_blank' href='https://wikidata.org/wiki/$new_qid'>Same</a>";
    }
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
            <td data-content='Qid'> $qid_link </td>
            <td data-content='Publication'> $pupdate </td>
            <td data-content='Old User'>
                <a href='/Translation_Dashboard/leaderboard.php?user=$user'>$user</a>
            </td>
            <td data-content='New User'>
                <a href='/Translation_Dashboard/leaderboard.php?user=$new_user'>$new_user</a>
            </td>
            <td data-content='Old target'>
                $targe33
            </td>
            <td data-content='New target'>
                $targe44
            </td>
            <td data-content='New Qid' class="$same_qid">
                $new_qid_link
            </td>
            <td data-content='Fix it'>
                $edit_icon
            </td>
        </tr>
    HTML;
    //---
    return $laly;
};
//---
$sql_results = get_pages_users_to_main($lang);
//---
$titles = array_column($sql_results, "title");
//---
// Only attempt to fetch QIDs if we have titles
$titles_qids = [];
//---
if (!empty($titles)) {
    $infos = td_or_sql_titles_infos($titles);
    //---
    $titles_qids = array_column($infos, "qid", "title");
}
//---
$noo = 0;
foreach ($sql_results as $tat => $tabe) {
    //---
    $tabe["qid"] = $titles_qids[$tabe["title"]] ?? "";
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
$count_result = count($sql_results);
//---
echo <<<HTML
	<div class='card-header'>
		<form class='form-inline' style='margin-block-end: 0em;' method='get' action='index.php'>
			<input name='ty' value='pages_users_to_main' hidden/>
			<div class='row'>
				<div class='col-md-7'>
					<h4>Userpages need to be moved to main pages: ($count_result)</h4>
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
<script src="/tdc/js/fix_u_targets.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#pages_table').DataTable({
            stateSave: true,
            // order: [[10	, 'desc']],
            // paging: false,
            lengthMenu: [
                [50, 100, 150],
                [50, 100, 150]
            ],
            // scrollY: 800
        });

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
    });
</script>
