<?php
//---
if (user_in_coord == false) {
    echo "<meta http-equiv='refresh' content='0; url=index.php'>";
    exit;
};
//---
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
};
//---
use function SQLorAPI\Funcs\get_td_or_sql_language_settings;
use function SQLorAPI\Funcs\get_pages_langs;
use function Actions\Html\make_edit_icon_new;
//---
// language_settings (lang_code, move_dots, expend, add_en_lang)
// ---
function make_td($tabg, $numb)
{
    //---
    $id             = $tabg['id'] ?? 0;
    $lang           = $tabg['lang_code'] ?? "";
    $expend2        = ($tabg['expend'] == 1) ? 'checked' : '';
    $move_dots      = ($tabg['move_dots'] == 1) ? 'checked' : '';
    $add_en_lang    = ($tabg['add_en_lang'] == 1) ? 'checked' : '';
    //---
    $lang = strtolower($lang);
    //---
    $edit_params = [
        'id'   => $id,
        'lang_code'  => $lang,
        'expend'  => $tabg['expend'],
        'move_dots'  => $tabg['move_dots'],
        'add_en_lang'  => $tabg['add_en_lang']
    ];
    //---
    $edit_icon = make_edit_icon_new("wikirefs_options/edit", $edit_params);
    //---
    $laly = <<<HTML
        <tr>
            <td data-content='#'>
                $numb
            </td>
            <td data-content='#'>
                <span>$lang</span>
            </td>
            <td data-content='Move dots'>
                <div class='form-check form-switch'>
                    <input class='form-check-input' type='checkbox' name='rows[$numb][move_dots]' value='1' $move_dots disabled/>
                </div>
            </td>
            <td data-content='Expend infobox'>
                <div class='form-check form-switch'>
                    <input class='form-check-input' type='checkbox' name='rows[$numb][expend]' value='1' $expend2 disabled/>
                </div>
            </td>
            <td data-content='Add |language=en'>
                <div class='form-check form-switch'>
                    <input class='form-check-input' type='checkbox' name='rows[$numb][add_en_lang]' value='1' $add_en_lang disabled/>
                </div>
            </td>
            <td data-content="Edit">
                $edit_icon
            </td>
        </tr>
        HTML;
    //---
    return $laly;
};
//---
$tabes = get_td_or_sql_language_settings();
//---
$tabes_codes = array_column($tabes, 'lang_code');
//---
$langs_d = get_pages_langs();
//---
foreach ($langs_d as $tat) {
    $lal = strtolower($tat);
    //---
    if (!in_array($lal, $tabes_codes)) {
        $tabes[] = ['lang_code' => $lal, 'expend' => 0, 'move_dots' => 0, 'add_en_lang' => 0];
    }
}
//---
// ksort($tabes);
usort($tabes, function ($a, $b) {
    return strcmp($a['lang_code'] ?? '', $b['lang_code'] ?? '');
});
//---
$n = -1;
// ---
$sato = "";
// ---
foreach ($tabes as $tab) {
    $n += 1;
    $sato .= make_td($tab, $n);
}
//---
echo <<<HTML
    <div class='card-header'>
        <h4>Fix wikirefs options:</h4>
    </div>
    <div class='card-body'>
        <div class="form-group">
            <table id="em2" class="table table-sm table-striped table-mobile-responsive table-mobile-sided table_text_left" style="font-size:90%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Lang.</th>
                        <th>Move dots</th>
                        <th>Expand infobox</th>
                        <th>add |language=en</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody id="refs_tab">
                    $sato
                </tbody>
            </table>
        </div>
        </div></div>
HTML;
//---
$new_row = make_edit_icon_new("wikirefs_options/edit", ["new" => 1], $text = "Add one!");
//---
echo <<<HTML
	<div class='card mt-2 mb-2'>
		<div class='card-body'>
			$new_row
		</div>
	</div>
HTML;
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#em2').DataTable({
            stateSave: true,
            lengthMenu: [
                [10, 50, 100, 150],
                [10, 50, 100, 150]
            ],
            // paging: false,
            // searching: false
        });
    });
</script>
