<?PHP

// include_once 'Tables/langcode.php';
use Tables\Langs\LangsTables;
use function Actions\Html\make_mdwiki_title;
use function Actions\Html\make_talk_url;
use function Actions\Html\make_target_url;
use function SQLorAPI\Recent\get_recent_translated;
use function SQLorAPI\Get\get_pages_langs;
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
function filter_by_language($lang)
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
	$lang_list = "<option data-tokens='All' value='All'>All</option>";
	//---
	foreach ($tabes as $codr) {
		$langeee = LangsTables::$L_code_to_lang[$codr] ?? '';
		$selected = ($codr == $lang) ? 'selected' : '';
		$lang_list .= <<<HTML
			<option data-tokens='$codr' value='$codr' $selected>$langeee</option>
			HTML;
	};
	//---
	$langse = <<<HTML
		<div class="input-group">
			<span class="input-group-text">Language:</span>
			<select aria-label="Language code"
				dir="ltr"
				class="selectpicker"
				id='lang'
				name='lang'
				placeholder='two letter code'
				data-live-search="true"
				data-container="body"
				data-live-search-style="begins"
				data-bs-theme="auto"
				data-style='btn active'
				data-width="70%"
				>
				$lang_list
			</select>
		</div>
	HTML;
	//---
	$uuu = <<<HTML
		<div class="input-group">
			$langse
		</div>
	HTML;
	//---
	return $uuu;
}
//---
function filter_table($data, $vav, $id)
{
	//---
	$l_list = "";
	//---
	foreach ($data as $table_name => $label) {
		$checked = ($table_name == $vav) ? "checked" : "";
		$l_list .= <<<HTML
			<div class="form-check form-check-inline">
				<input class="form-check-input"
					type="radio"
					name="$id"
					id="radio_$table_name"
					value="$table_name"
					$checked>
				<label class="form-check-label" for="radio_$table_name">$label</label>
			</div>
		HTML;
	}
	//---
	$uuu = <<<HTML
		<div class="input-group">
			<div class="form-control" style="background-color: transparent; border: none;">
				$l_list
			</div>
		</div>
	HTML;
	//---
	return $uuu;
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
		'title'     => $title,
		'target'  => $target,
		'lang'    => $lang,
		'user'    => $user,
		'pupdate' => $pupdate,
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
$filter_la = filter_by_language($lang);
//---
$data = [
	"pages" => 'In main space',
	"pages_users" => 'In user space',
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
					$filter_la
				</div>
				<div class='col-md-3'>
					$filter_ta
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
