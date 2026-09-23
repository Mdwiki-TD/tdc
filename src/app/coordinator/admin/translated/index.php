<?php
// src/app/coordinator/admin/translated/index.php

namespace App\Coordinator\Admin\Translated;

use App\User\CurrentUser;
use App\Tables\Langs\LangsTables;
use function App\Utils\Html\make_mdwiki_title;
use function App\Utils\Html\make_target_url;
use function App\Utils\Html\make_edit_icon_new;
use function App\SQLorAPI\Recent\get_recent_translated;
use function App\SQLorAPI\Recent\get_total_translations_count;
use function App\SQLorAPI\Funcs\get_pages_langs;
use function Tools\RecentHelps\filter_table;
use function Tools\RecentHelps\filter_recent2;

$CurrentUser = CurrentUser::getInstance();

if (!$CurrentUser->isCoordinator()) {
	header('Location: /index.php');
	exit;
};
$globalUsername = $CurrentUser->getUsername();

$lang = $_GET['lang'] ?? 'All';

$table = (isset($_GET['table'])) ? $_GET['table'] : "pages";

if (!isset($_GET['table']) && $globalUsername == "Mr. Ibrahem") $table = "pages_users";

if ($lang !== 'All' && !isset(LangsTables::$LCodeToLang[$lang])) {
    $lang = 'All';
};

function get_languages()
{

    $tabes = [];

    $llangs = get_pages_langs();

    foreach ($llangs as $tat) {

        if (gettype($tat) !== 'string') {
            echo "<br>tat: $tat";
            continue;
        }

        $tabes[] = strtolower($tat);
    };

    ksort($tabes);

    return $tabes;
}

function translated_make_td($tabg, $nnnn, $table)
{

    $id = $tabg['id'] ?? "";

    $user = $tabg['user'] ?? "";
    $lang = $tabg['lang'] ?? "";
    $mdTitle = trim($tabg['title'] ?? '');
    $target = trim($tabg['target'] ?? '');
    $pupdate  = $tabg['pupdate'] ?? '';

    $mdwikiTitle = make_mdwiki_title($mdTitle);

    $targe33 = make_target_url($target, $lang);

    $editParams = array(
        'id'   => $id,
        'table' => $table
    );

    $editIcon = make_edit_icon_new("translated/edit_page", $editParams);

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
				$mdwikiTitle
			</td>
			<td data-content='Translated'>
				$targe33
			</td>
			<td data-content='Published'>
				$pupdate
			</td>
			<td data-content='Edit'>
				$editIcon
			</td>
		</tr>
	HTML;

    return $laly;
};

function pagination_links($limit, $page, $table, $lang, $totalCount)
{

    $totalPages = ceil($totalCount / $limit);

    $baseUrl = "?ty=translated&lang=$lang&table=$table&limit=$limit&page=";

    $links = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    $links .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '"><a class="page-link" href="' . $baseUrl . '1">&laquo;</a></li>';
    $links .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '"><a class="page-link" href="' . $baseUrl . ($page - 1) . '"><</a></li>';

    for ($i = max(1, $page - 3); $i <= min($totalPages, $page + 3); $i++) {
        $active = $i == $page ? ' active' : '';
        $links .= "<li class=\"page-item$active\"><a class=\"page-link\" href=\"$baseUrl$i\">$i</a></li>";
    }

    $links .= '<li class="page-item' . ($page >= $totalPages ? ' disabled' : '') . '"><a class="page-link" href="' . $baseUrl . ($page + 1) . '">></a></li>';
    $links .= '<li class="page-item' . ($page >= $totalPages ? ' disabled' : '') . '"><a class="page-link" href="' . $baseUrl . $totalPages . '">&raquo;</a></li>';
    $links .= '</ul></nav>';

    $offset = ($page - 1) * $limit;

    // احسب رقم أول عنصر في الصفحة الحالية
    $startItem = $offset + 1;

    // احسب آخر عنصر في الصفحة الحالية
    $endItem = min($offset + $limit, $totalCount);

    // إنشاء النص التوضيحي للصفحة
    $summary = "<p class=\"text-center\">";
    $summary .= "Page " . ($offset / $limit + 1) . " from $totalPages ";
    $summary .= "($startItem - $endItem from total " . number_format($totalCount) . " logs)";
    $summary .= "</p>";

    return $summary . $links;
}

$recentTable = <<<HTML
	<table class="table table-sm table-striped table-mobile-responsive table-mobile-sided table_text_left" id="pages_table" style="font-size:90%;">
		<thead>
			<tr>
				<th>#</th>
				<th>User</th>
				<th>Lang.</th>
				<th>Title</th>
				<th>Translated</th>
				<th>Published</th>
				<th>Edit</th>
			</tr>
		</thead>
		<tbody>
HTML;

$limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 500;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sqlResults = get_recent_translated($lang, $table, $limit, $offset);

$totalCount = get_total_translations_count($lang, $table);

$pagination = "";

if ($totalCount > $limit) {
    $pagination = pagination_links($limit, $page, $table, $lang, $totalCount);
}

$noo = 0;
foreach ($sqlResults as $tat => $tabe) {

    $noo = $noo + 1;
    $recentTable .= translated_make_td($tabe, $noo, $table);

};

$recentTable .= <<<HTML
		</tbody>
	</table>
HTML;

$langTable = get_languages();
$filterLang = filter_recent2($lang, $langTable);

$data = [
    "pages" => 'Main',
    "pages_users" => 'User',
];

$filterNs = filter_table($data, $table, 'table');

$countResult = count($sqlResults);

echo <<<HTML
    <div class='card'>
        <div class='card-header'>
            <form class='form-inline' style='margin-block-end: 0em;' method='get' action='index.php'>
                <input name='ty' value='translated' type='hidden'/>
                <div class='row'>
                    <div class='col-md-4'>
                        <h4>Translated Pages ($countResult):</h4>
                    </div>
                    <div class='col-md-4'>
                        <div class="input-group">
                            <span class="input-group-text">Namespace:</span>
                            <div class="form-control">
                                $filterNs
                            </div>
                        </div>
                    </div>
                    <div class='col-md-3'>
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
                            $filterLang
                        </select>
                    </div>
                    <div class='aligncenter col-md-1'>
                        <input class='btn btn-outline-primary' type='submit' value='Filter' />
                    </div>
                </div>
            </form>
        </div>
        <div class='card-body'>
            $pagination
        </div>
    </div>
HTML;

echo $recentTable;

?>
<script>
    $(document).ready(function() {
        var t = $('#pages_table').DataTable({
            stateSave: true,
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
