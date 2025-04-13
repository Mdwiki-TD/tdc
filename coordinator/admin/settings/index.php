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
// include_once 'actions/functions.php';
//---
// use function Infos\TdConfig\get_configs;
use function SQLorAPI\Get\get_td_or_sql_settings;
//---
// $conf = get_configs('conf.json');
//---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require __DIR__ . '/post.php';
}
//---
echo <<<HTML
    <div class='card-header'>
        <h4>Settings:</h4>
    </div>
    <div class='card-body'>
        <div class='row'>
            <form action='index.php' method='POST'>
                <input name='ty' value='settings' hidden/>
    HTML;
//---
$nn = 0;
//---
function make_settings_tab($tabe)
{
    //---
    global $nn;
    //---
    $tab = <<<HTML
            <table class='table table-striped compact table-mobile-responsive table-mobile-sided' style='font-size:95%;width:100%;'>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Option</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
    HTML;
    //---
    foreach ($tabe as $key => $v) {
        $nn += 1;
        $id       = $v['id'] ?? "";
        $title    = $v['title'] ?? "";
        $displayed = $v['displayed'] ?? "";
        $value    = $v['value'] ?? "";
        //---
        $type     = $v['type'] ?? $v['Type'] ?? "";
        //---
        $value_line = <<<HTML
            <input class='form-control' size='4' name='rows[$nn][value]' value='$value'/>
        HTML;
        //---
        if ($type == 'check') {
            $checked = ($value == 1 || $value == "1") ? 'checked' : '';
            $value_line = <<<HTML
                <div class='form-check form-switch'>
                    <input type='hidden' name='rows[$nn][value]' value='0'>
                    <input class='form-check-input' type='checkbox' name='rows[$nn][value]' value='1' $checked>
                </div>
            HTML;
        }
        //---
        $tr = <<<HTML
            <tr>
                <input name='rows[$nn][id]' value='$id' hidden/>
                <td data-order='$nn' data-content='#'>
                    $nn
                </td>
                <td data-content='Option'>
                    $displayed
                    <!-- <input class='form-control' name='rows[$nn][title]' value="$title" hidden/>
                    <input class='form-control' name='rows[$nn][displayed]' value='$displayed' hidden/> -->
                </td>
                <td data-content='Value'>
                    $value_line
                    <!-- <input class='form-control' name='rows[$nn][type]' value='$type' hidden/> -->
                </td>
            </tr>
        HTML;
        //---
        $tab .= $tr;
        //---
    };
    //---
    $result = <<<HTML
        <div class='form-group'>
                $tab
                </tbody>
            </table>
        </div>
    HTML;
    //---
    return $result;
    //---
};
//---
$qq = get_td_or_sql_settings();
//---
$text = make_settings_tab($qq);
//---
echo $text;
//---
echo <<<HTML
            <button type='submit' class='btn btn-outline-primary'>Save</button>
        </form>
    </div>
</div>
HTML;
//---
