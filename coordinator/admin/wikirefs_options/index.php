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
// include_once 'infos/td_config.php';
//---
use function Infos\TdConfig\get_configs;
use function SQLorAPI\Funcs\get_pages_langs;
use function TDWIKI\csrf\generate_csrf_token;

//---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require __DIR__ . '/post.php';
}
//---
$tabes = get_configs('fixwikirefs.json');
//---
$testin = (($_REQUEST['test'] ?? '') != '') ? "<input name='test' value='1' hidden/>" : "";
//---
function make_td($lang, $tabg, $numb)
{
    //---
    $lang = strtolower($lang);
    //---
    $expend2        = ($tabg['expend'] == 1) ? 'checked' : '';
    $move_dots      = ($tabg['move_dots'] == 1) ? 'checked' : '';
    $add_en_lang     = ($tabg['add_en_lang'] == 1) ? 'checked' : '';
    //---
    $laly = <<<HTML
        <tr>
            <td data-content='#'>
                $numb
            </td>
            <td data-content='#'>
                <span>$lang</span>
                <input name='lang[]$numb' value='$lang' hidden/>
            </td>
            <td data-content='Move dots'>
                <div class='form-check form-switch'>
                    <input class='form-check-input' type='checkbox' name='move_dots[]$numb' value='$lang' $move_dots/>
                </div>
            </td>
            <td data-content='Expend infobox'>
                <div class='form-check form-switch'>
                    <input class='form-check-input' type='checkbox' name='expend[]$numb' value='$lang' $expend2/>
                </div>
            </td>
            <td data-content='Add |language=en'>
                <div class='form-check form-switch'>
                    <input class='form-check-input' type='checkbox' name='add_en_lang[]$numb' value='$lang' $add_en_lang/>
                </div>
            </td>
            <td data-content='Delete'>
                <input type='checkbox' name='del[]$numb' value='$lang'>
            </td>
        </tr>
        HTML;
    //---
    return $laly;
};
//---
$langs_d = get_pages_langs();
//---
foreach ($langs_d as $tat) {
    $lal = strtolower($tat);
    //---
    if (!isset($tabes[$lal])) {
        $tabes[$lal] = array('expend' => 0, 'move_dots' => 0, 'add_en_lang' => 0);
    };
};
//---
ksort($tabes);
//---
$n = -1;
// ---
$sato = "";
// ---
foreach ($tabes as $lang => $tab) {
    //---
    $n += 1;
    $sato .= make_td($lang, $tab, $n);
    //---
};
//---
$csrf_token = generate_csrf_token(); // <input name='csrf_token' value="$csrf_token" hidden />
//---
echo <<<HTML
    <div class='card-header'>
        <h4>Fix wikirefs options:</h4>
    </div>
    <div class='card-body'>
        <form action="index.php?ty=wikirefs_options" method="POST">
            <input name='csrf_token' value="$csrf_token" hidden />
            $testin
            <input name="ty" value="wikirefs_options" hidden/>
			<div class="form-group">
                <table id="em2" class="table table-sm table-striped table-mobile-responsive table-mobile-sided" style="font-size:90%;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Lang.</th>
                            <th>Move dots</th>
                            <th>Expand infobox</th>
                            <th>add |language=en</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody id="refs_tab">
                        $sato
                    </tbody>
                </table>
            </div>
            <div class="form-group d-flex justify-content-between">
                <button type="submit" class="btn btn-outline-primary">Save</button>
                <span role="button" id="add_row" class="btn btn-outline-primary" onclick="add_row()">New row</span>
            </div>
        </form>
    </div>
HTML;
//---
?>
<script type="text/javascript">
    function add_row() {
        var ii = $('#refs_tab >tr').length + 1;
        var e = `
            <tr>
                <td>${ii}</td>
                <td><input class='form-control' name='newlang[]${ii}' placeholder='lang code.'/></td>
                <td><input class='form-control' type='text' name='move_dotsx[]${ii}' value='0' disabled/></td>
                <td><input class='form-control' type='text' name='expendx[]${ii}' value='0' disabled/></td>
                <td><input class='form-control' type='text' name='add_en_langx[]${ii}' value='0' disabled/></td>
                <td>-</td>
            </tr>
        `;
        $('#refs_tab').append(e);
    };

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
