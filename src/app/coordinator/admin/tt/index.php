<?php
// src/app/coordinator/admin/tt/index.php

namespace App\Coordinator\Admin\TranslateType;

use App\Tables\SqlTables\TablesSql;
use function App\Utils\Html\makeDropdown;
use function App\Utils\Html\make_mdwiki_title;
use function App\Utils\Html\make_edit_icon_new;
use function App\Results\GetCats\get_mdwiki_cat_members;
use function App\APICalls\MdwikiSql\fetch_query;
use function App\csrf\generate_csrf_token;
use App\User\CurrentUser;

if (!CurrentUser::getInstance()->isCoordinator()) {
	header('Location: /index.php');
	exit;
};
$cat = $_GET['cat'] ?? 'All';
$testin = (($_GET['test'] ?? '') != '') ? '<input type="hidden" name="test" value="1" />' : "";

function filter_stat($cat)
{
	// array keys
	$catsTitles = array_keys(TablesSql::$sCatToCamp);

	$d33 = <<<HTML
		<div class="input-group">
			<span class="input-group-text">%s</span>
			%s
		</div>
	HTML;

	$y1 = makeDropdown($catsTitles, $cat, 'cat', 'All');
	$uuu = sprintf($d33, 'Category:', $y1);

	return $uuu;
}

$uuu = filter_stat($cat);

$newTitles = [];
$fullTranslatesTab = [];

$translateTypeSql = <<<SQL
    SELECT tt_id, tt_title, tt_lead, tt_full
	FROM translate_type
SQL;

foreach (fetch_query($translateTypeSql) as $k => $tab) {
	$fullTranslatesTab[$tab['tt_title']] = ['id' => $tab['tt_id'], 'lead' => $tab['tt_lead'], 'full' => $tab['tt_full']];
}

TablesSql::$sCatTitles = [];

if ($cat == 'All') {
	foreach (fetch_query('SELECT DISTINCT title from qids WHERE title not in (SELECT tt_title FROM translate_type)') as $Key => $gg) {
		if (!in_array($gg['title'], $fullTranslatesTab)) {
			$newTitles[] = $gg['title'];
		}
	};
	TablesSql::$sCatTitles = array_keys($fullTranslatesTab);
} else {
	TablesSql::$sCatTitles = get_mdwiki_cat_members($cat, $useCache = true, $depth = 1);
}

function make_row($id, $title, $lead, $full, $numb)
{

	$editParams = array(
		'id'   => $id,
		'title'  => $title,
		'lead'  => $lead,
		'full'  => $full
	);

	$editIcon = make_edit_icon_new("tt/edit_translate_type", $editParams);

	$mdTitle = make_mdwiki_title($title);

	$leadChecked = ($lead == 1 || $lead == "1") ? 'checked' : '';
	$fullChecked = ($full == 1 || $full == "1") ? 'checked' : '';

	return <<<HTML
	<tr>
		<th data-sort="$numb">
			$numb
		</th>
		<!-- <th data-sort="$id"> $id </th> -->
		<td data-sort="$title">
			$mdTitle
		</td>
		<td data-sort='$lead'>
			<div class='form-check form-switch'>
				<input class='form-check-input' type='checkbox' name='lead_$numb' value='1' $leadChecked disabled>
			</div>
		</td>
		<td data-sort='$full'>
			<div class='form-check form-switch'>
				<input class='form-check-input' type='checkbox' name='full_$numb' value='1' $fullChecked disabled>
			</div>
		</td>
		<td>
			$editIcon
		</td>
	</tr>
	HTML;
}

$tableRows = "";

// $ttCount = count(TablesSql::$sCatTitles);

$ttCount = 0;

foreach (TablesSql::$sCatTitles as $title) {

	if (in_array($title, $newTitles)) continue;

	$ttCount += 1;

	$table = $fullTranslatesTab[$title] ?? [];

	$id			= $table['id'] ?? '';
	$lead 		= $table['lead'] ?? 1;
	$full		= $table['full'] ?? 0;

	$tableRows .= make_row($id, $title, $lead, $full, $ttCount);

};

$csrfToken = generate_csrf_token(); // <input name='csrf_token' value="$csrfToken" type="hidden"/>

$newRow = make_edit_icon_new("tt/edit_translate_type", ["new" => 1], $text = "Add one!");

echo <<<HTML
    <div class='card'>
		<div class='card-header'>
			<form action="index.php?ty=tt" method="GET">
				$testin
				<input name='ty' value="tt" type="hidden"/>
				<div class='row'>
					<div class='col-md-6'>
						<h4>Translate Type ($ttCount):</h4>
					</div>
					<div class='col-md-4'>
						$uuu
					</div>
					<div class='aligncenter col-md-2'><input class='btn btn-outline-primary' type='submit' value='Filter' /></div>
				</div>
			</form>
		</div>
		<div class='card-body'>
			<table id='em' class='table table-striped compact table_responsive table_text_left'>
				<thead>
					<tr>
						<th>#</th>
						<!-- <th>id</th> -->
						<th>Title</th>
						<th>Lead</th>
						<th>Full</th>
						<th>Edit</th>
					</tr>
				</thead>
				<tbody id="tab_ma">
					$tableRows
				</tbody>
			</table>
		</div>
	</div>
HTML;

echo <<<HTML
	<div class='card mt-1'>
		<div class='card-body'>
			$newRow
		</div>
	</div>
HTML;
?>
<script type="text/javascript">
	$(document).ready(function() {
		var t = $('#em').DataTable({
			stateSave: true,
			// order: [[5	, 'desc']],
			// paging: false,
			lengthMenu: [
				[250, 500],
				[250, 500]
			],
			// scrollY: 800
		});
	});
</script>

</div>
