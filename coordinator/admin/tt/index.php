<?php
//---
/*

INSERT INTO translate_type (tt_title, tt_lead, tt_full) SELECT DISTINCT q.title, 1, 0 from qids q
	WHERE q.title not in (SELECT tt_title FROM translate_type)

*/
//---
// include_once 'results/getcats.php';
// include_once 'actions/functions.php';
use Tables\SqlTables\TablesSql;
use function Actions\Html\makeDropdown;
use function Actions\Html\make_mdwiki_title;
use function Actions\Html\make_edit_icon_new;
use function Results\GetCats\get_mdwiki_cat_members;
use function Actions\MdwikiSql\fetch_query;
use function TDWIKI\csrf\generate_csrf_token;
//---
$cat = $_REQUEST['cat'] ?? 'All';
$testin = (($_GET['test'] ?? '') != '') ? '<input type="hidden" name="test" value="1" />' : "";
//---
function filter_stat($cat)
{
	// array keys
	$cats_titles = array_keys(TablesSql::$s_cat_to_camp);
	//---
	$d33 = <<<HTML
		<div class="input-group">
			<span class="input-group-text">%s</span>
			%s
		</div>
	HTML;
	//---
	$y1 = makeDropdown($cats_titles, $cat, 'cat', 'All');
	$uuu = sprintf($d33, 'Category:', $y1);
	//---
	return $uuu;
}
//---
$uuu = filter_stat($cat);
//---
$new_titles = [];
$full_translates_tab = [];
//---
$translate_type_sql = <<<SQL
    SELECT tt_id, tt_title, tt_lead, tt_full
	FROM translate_type
SQL;
//---
foreach (fetch_query($translate_type_sql) as $k => $tab) {
	$full_translates_tab[$tab['tt_title']] = ['id' => $tab['tt_id'], 'lead' => $tab['tt_lead'], 'full' => $tab['tt_full']];
}
//---
TablesSql::$s_cat_titles = [];
//---
if ($cat == 'All') {
	foreach (fetch_query('SELECT DISTINCT title from qids WHERE title not in (SELECT tt_title FROM translate_type)') as $Key => $gg) {
		if (!in_array($gg['title'], $full_translates_tab)) {
			$new_titles[] = $gg['title'];
		}
	};
	TablesSql::$s_cat_titles = array_keys($full_translates_tab);
} else {
	TablesSql::$s_cat_titles = get_mdwiki_cat_members($cat, $use_cache = true, $depth = 1);
}

function make_row($id, $title, $lead, $full, $numb)
{
	//---
	$edit_params = array(
		'id'   => $id,
		'title'  => $title,
		'lead'  => $lead,
		'full'  => $full
	);
	//---
	$edit_icon = make_edit_icon_new("tt/edit_translate_type", $edit_params);
	//---
	$md_title = make_mdwiki_title($title);
	//---
	$lead_checked = ($lead == 1 || $lead == "1") ? 'checked' : '';
	$full_checked = ($full == 1 || $full == "1") ? 'checked' : '';
	//---
	return <<<HTML
	<tr>
		<th data-content="#" data-sort="$numb">
			$numb
		</th>
		<th data-content="#" data-sort="$id">
			$id
		</th>
		<td data-content="title" data-sort="$title">
			$md_title
		</td>
		<td data-content='Lead' data-sort='$lead'>
			<div class='form-check form-switch'>
				<input class='form-check-input' type='checkbox' name='lead_$numb' value='1' $lead_checked disabled>
			</div>
		</td>
		<td data-content='Full' data-sort='$full'>
			<div class='form-check form-switch'>
				<input class='form-check-input' type='checkbox' name='full_$numb' value='1' $full_checked disabled>
			</div>
		</td>
		<td data-content="Edit">
			$edit_icon
		</td>
	</tr>
	HTML;
}
//---
$table_rows = "";
//---
// $tt_count = count(TablesSql::$s_cat_titles);
//---
$tt_count = 0;
//---
foreach (TablesSql::$s_cat_titles as $title) {
	//---
	if (in_array($title, $new_titles)) continue;
	//---
	$tt_count += 1;
	//---
	$table = $full_translates_tab[$title] ?? [];
	//---
	$id			= $table['id'] ?? '';
	$lead 		= $table['lead'] ?? 1;
	$full		= $table['full'] ?? 0;
	//---
	$table_rows .= make_row($id, $title, $lead, $full, $tt_count);
	//---
};
//---
echo <<<HTML
	<div class='card-header'>
		<form action="index.php?ty=tt" method="GET">
			$testin
			<input name='ty' value="tt" type="hidden"/>
			<div class='row'>
				<div class='col-md-6'>
					<h4>Translate Type ($tt_count):</h4>
				</div>
				<div class='col-md-4'>
					$uuu
				</div>
				<div class='aligncenter col-md-2'><input class='btn btn-outline-primary' type='submit' value='Filter' /></div>
			</div>
		</form>
	</div>
	<div class='card-body'>
		<table id='em' class='table table-striped compact table-mobile-responsive table-mobile-sided table_text_left'>
			<thead>
				<tr>
					<th>#</th>
					<th>id</th>
					<th>Title</th>
					<th>Lead</th>
					<th>Full</th>
					<th>Edit</th>
				</tr>
			</thead>
			<tbody id="tab_ma">
				$table_rows
			</tbody>
		</table>
	</div>
	</div>
HTML;
//---
$csrf_token = generate_csrf_token(); // <input name='csrf_token' value="$csrf_token" type="hidden"/>
//---
$new_row = make_edit_icon_new("tt/edit_translate_type", ["new" => 1], $text = "Add one!");
//---
echo <<<HTML
	<div class='card mt-1'>
		<div class='card-body'>
			$new_row
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
