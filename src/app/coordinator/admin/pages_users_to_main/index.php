<?php
// src/app/coordinator/admin/pages_users_to_main/index.php

namespace App\Coordinator\Admin\PagesUsersToMain;

use App\User\CurrentUser;
use App\Tables\Langs\LangsTables;
use function App\Utils\Html\make_mdwiki_title;
use function App\Utils\Html\make_target_url;
use function App\Utils\Html\make_edit_icon_new;
use function App\SQLorAPI\Recent\get_pages_users_to_main;
use function App\SQLorAPI\Funcs\get_pages_users_langs;
use function Tools\RecentHelps\filter_recent2;
use function App\SQLorAPI\Funcs\td_or_sql_titles_infos;

if (!CurrentUser::getInstance()->isCoordinator()) {
	header('Location: /index.php');
	exit;
};
$lang = $_GET['lang'] ?? 'All';

if ($lang !== 'All' && !isset(LangsTables::$LCodeToLang[$lang])) {
    $lang = 'All';
};

function get_languages()
{

    $tabes = [];

    $llangs = get_pages_users_langs();

    foreach ($llangs as $tat) {

        if (gettype($tat) !== 'string') {
            // echo "<br>tat: $tat";
            continue;
        }

        $lag = strtolower($tat);

        $tabes[] = $lag;

    };

    ksort($tabes);

    return $tabes;
}

$ToggleColumn = <<<HTML
    <div>
        <span class="toggle-vis btn" data-column="0">Toggle columns:</span>
        <a class="toggle-vis btn btn-outline-primary" data-column="0" type="button">#</a>
        <a class="toggle-vis btn btn-outline-primary" data-column="1" type="button">Lang.</a>
        <a class="toggle-vis btn btn-outline-primary" data-column="2" type="button">Title</a>
        <a class="toggle-vis btn btn-outline-primary" data-column="3" type="button">Qid</a>
        <a class="toggle-vis btn btn-outline-primary" data-column="4" type="button">Publication</a>
    </div>
HTML;

$recentTable = <<<HTML
    $ToggleColumn
	<table class="table table-sm table-striped table-mobile-responsive table-mobile-sided table_text_left" id="pages_table" style="font-size:90%;">
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

function page_users_make_td($tabg, $nnnn)
{

    $id          = $tabg['id'] ?? "";

    $user      = $tabg['user'] ?? "";
    $lang      = $tabg['lang'] ?? "";
    $mdTitle = trim($tabg['title'] ?? '');
    $target      = trim($tabg['target'] ?? '');
    $pupdate  = $tabg['pupdate'] ?? '';

    $mdwikiTitle = make_mdwiki_title($mdTitle);
    $targe33 = make_target_url($target, $lang);

    $newUser   = $tabg['new_user'] ?? "";
    $newTarget = $tabg['new_target'] ?? "";

    $targe44 = make_target_url($newTarget, $lang);

    $editParams = array(
        'id'   => $id,
        'new_user'   => $newUser,
        'new_target'   => $newTarget

    );

    $editIcon = make_edit_icon_new("pages_users_to_main/fix_it", $editParams);

    $qid          = $tabg['qid'] ?? "";
    $newQid      = $tabg['new_qid'] ?? "";

    $qidLink = (!empty($qid)) ? "<a target='_blank' href='https://wikidata.org/wiki/$qid'>$qid</a>" : "";
    $newQidLink = (!empty($newQid)) ? "<a target='_blank' href='https://wikidata.org/wiki/$newQid'>$newQid</a>" : "";

    $newTarget2 = htmlspecialchars($newTarget, ENT_QUOTES);

    if (!empty($qid) && empty($newQid)) {
        $sameQid = "bg-info-subtle";
        $newQidLink = "<a class='fw-bold' target='_blank' href='https://www.wikidata.org/wiki/Special:SetSiteLink/$qid/{$lang}wiki?page=$newTarget2' u-lang='$lang' u-qid='$qid' u-target='$newTarget2'>Link it!</a>";
    } else {
        $sameQid = ($qid == $newQid) ? "bg-info-subtle" : "bg-danger-subtle";
    }

    if (!empty($qid) && $newQid == $qid) {
        $sameQid = "";
        $newQidLink = "<a target='_blank' href='https://wikidata.org/wiki/$newQid'>Same</a>";
    }

    $laly = <<<HTML
        <tr>
            <td data-content='#'>
                $nnnn
            </td>
            <td data-content='Lang'>
                <a href='/Translation_Dashboard/leaderboard.php?langcode=$lang'>$lang</a>
            </td>
            <td data-content='Title'>
                $mdwikiTitle
            </td>
            <td data-content='Qid'> $qidLink </td>
            <td data-content='Publication'> $pupdate </td>
            <td data-content='Old User'>
                <a href='/Translation_Dashboard/leaderboard.php?user=$user'>$user</a>
            </td>
            <td data-content='New User'>
                <a href='/Translation_Dashboard/leaderboard.php?user=$newUser'>$newUser</a>
            </td>
            <td data-content='Old target'>
                $targe33
            </td>
            <td data-content='New target'>
                $targe44
            </td>
            <td data-content='New Qid' class="$sameQid">
                $newQidLink
            </td>
            <td data-content='Fix it'>
                $editIcon
            </td>
        </tr>
    HTML;

    return $laly;
};

$sqlResults = get_pages_users_to_main($lang);

$titles = array_column($sqlResults, "title");

// Only attempt to fetch QIDs if we have titles
$titlesQids = [];

if (!empty($titles)) {
    $infos = td_or_sql_titles_infos($titles);

    $titlesQids = array_column($infos, "qid", "title");

    // var_export($titlesQids);
}

$noo = 0;
foreach ($sqlResults as $tat => $tabe) {

    $tabe["qid"] = $titlesQids[$tabe["title"]] ?? "";

    $noo = $noo + 1;

    $recentTable .= page_users_make_td($tabe, $noo);

};

$recentTable .= <<<HTML
		</tbody>
	</table>
HTML;

$langTable = get_languages();
$filterLa = filter_recent2($lang, $langTable);

$countResult = count($sqlResults);

echo <<<HTML
    <div class='card'>
        <div class='card-header'>
            <form class='form-inline' style='margin-block-end: 0em;' method='get' action='index.php'>
                <input name='ty' value='pages_users_to_main' type='hidden'/>
                <div class='row'>
                    <div class='col-md-7'>
                        <h4>Userpages need to be moved to main pages: ($countResult)</h4>
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
                                $filterLa
                            </select>
                        </div>
                    </div>
                    <div class='aligncenter col-md-2'>
                        <input class='btn btn-outline-primary' type='submit' value='Filter' />
                    </div>
                </div>
            </form>
        </div>
        <div class='card-body'>
            $recentTable
        </div>
    </div>
HTML;

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
