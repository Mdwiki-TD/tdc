<?PHP

// include_once 'tablesd/langcode.php';
use Tables\Langs\LangsTables;
use function Actions\Html\make_mdwiki_title;
use function Actions\Html\make_talk_url;
use function Actions\Html\make_target_url;
use function Actions\Html\make_edit_icon_new;
use function SQLorAPI\Recent\get_recent_translated;
use function SQLorAPI\Recent\get_total_translations_count;
use function SQLorAPI\Funcs\get_pages_langs;
use function Tools\RecentHelps\filter_table;
use function Tools\RecentHelps\filter_recent;
//---
$lang = $_GET['lang'] ?? 'All';
//---
// if ($_SERVER['SERVER_NAME'] == 'localhost' && $lang == "All") $lang = "ar";
//---
$table = (isset($_GET['table'])) ? $_GET['table'] : "pages";
//---
if (!isset($_GET['table']) && $GLOBALS['global_username'] == "Mr. Ibrahem") $table = "pages_users";
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
        $tabes[] = strtolower($tat);
    };
    //---
    ksort($tabes);
    //---
    return $tabes;
}

function make_td($tabg, $nnnn, $table)
{
    //---
    $id = $tabg['id'] ?? "";
    //---
    $user = $tabg['user'] ?? "";
    $lang = $tabg['lang'] ?? "";
    $md_title = trim($tabg['title'] ?? '');
    $target = trim($tabg['target'] ?? '');
    $pupdate  = $tabg['pupdate'] ?? '';
    //---
    $mdwiki_title = make_mdwiki_title($md_title);
    //---
    $targe33 = make_target_url($target, $lang);
    //---
    $edit_params = array(
        'id'   => $id,
        'table' => $table
    );
    //---
    $edit_icon = make_edit_icon_new("translated/edit_page", $edit_params);
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
function pagination_links($limit, $page, $table, $lang, $total_count)
{
    //---
    $total_pages = ceil($total_count / $limit);
    //---
    $base_url = "?ty=translated&lang=$lang&table=$table&limit=$limit&page=";

    $links = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    $links .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '"><a class="page-link" href="' . $base_url . '1">&laquo;</a></li>';
    $links .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '"><a class="page-link" href="' . $base_url . ($page - 1) . '"><</a></li>';

    for ($i = max(1, $page - 3); $i <= min($total_pages, $page + 3); $i++) {
        $active = $i == $page ? ' active' : '';
        $links .= "<li class=\"page-item$active\"><a class=\"page-link\" href=\"$base_url$i\">$i</a></li>";
    }

    $links .= '<li class="page-item' . ($page >= $total_pages ? ' disabled' : '') . '"><a class="page-link" href="' . $base_url . ($page + 1) . '">></a></li>';
    $links .= '<li class="page-item' . ($page >= $total_pages ? ' disabled' : '') . '"><a class="page-link" href="' . $base_url . $total_pages . '">&raquo;</a></li>';
    $links .= '</ul></nav>';

    $offset = ($page - 1) * $limit;
    //---
    // احسب رقم أول عنصر في الصفحة الحالية
    $start_item = $offset + 1;

    // احسب آخر عنصر في الصفحة الحالية
    $end_item = min($offset + $limit, $total_count);

    // إنشاء النص التوضيحي للصفحة
    $summary = "<p class=\"text-center\">";
    $summary .= "Page " . ($offset / $limit + 1) . " from $total_pages ";
    $summary .= "($start_item - $end_item from total " . number_format($total_count) . " logs)";
    $summary .= "</p>";

    return $summary . $links;
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
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 500;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
// ---
$qsl_results = get_recent_translated($lang, $table, $limit, $offset);
//---
$total_count = get_total_translations_count($lang, $table);
//---
$pagination = "";
//---
if ($total_count > $limit) {
    $pagination = pagination_links($limit, $page, $table, $lang, $total_count);
}
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
$filter_lang = filter_recent($lang, $lang_table);
//---
$data = [
    "pages" => 'Main',
    "pages_users" => 'User',
];
//---
$filter_ns = filter_table($data, $table, 'table');
//---
$count_result = count($qsl_results);
//---
echo <<<HTML
	<div class='card-header'>
		<form class='form-inline' style='margin-block-end: 0em;' method='get' action='index.php'>
			<input name='ty' value='translated' hidden/>
			<div class='row'>
				<div class='col-md-4'>
					<h4>Translated Pages ($count_result):</h4>
				</div>
				<div class='col-md-4'>
					$filter_ns
				</div>
				<div class='col-md-3'>
					$filter_lang
				</div>
				<div class='aligncenter col-md-1'>
					<input class='btn btn-outline-primary' type='submit' value='Filter' />
				</div>
			</div>
		</form>
	</div>
	<div class='card-body'>
        $pagination
HTML;
//---
echo $recent_table;
//---
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
