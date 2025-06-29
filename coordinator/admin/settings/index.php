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
// use function Infos\TdConfig\get_configs;
use function SQLorAPI\Funcs\get_td_or_sql_settings;
use function TDWIKI\csrf\generate_csrf_token;
//---
// $conf = get_configs('conf.json');
//---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require __DIR__ . '/post.php';
}
//---
$csrf_token = generate_csrf_token(); // <input name='csrf_token' value="$csrf_token" type="hidden"/>
//---
echo <<<HTML
    <div class='card-header'>
        <h4>Settings:</h4>
    </div>
    <div class='card-body'>
        <div class='row'>
            <form action='index.php' method="POST">
                <input name='csrf_token' value="$csrf_token" type="hidden"/>
                <input name='ty' value='settings' type="hidden"/>
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
            <table class='table table-striped compact table-mobile-responsive table-mobile-sided'>
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
        // ---
        $ignored = $v['ignored'] ?? 0;
        // ---
        if ($ignored == 1 || $ignored == "1") {
            continue;
        }
        // ---
        $id       = $v['id'] ?? "";
        $title    = $v['title'] ?? "";
        $displayed = $v['displayed'] ?? "";
        $value    = $v['value'] ?? "";
        //---
        $nn += 1;
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
                <input name='rows[$nn][id]' value='$id' type="hidden"/>
                <td data-order='$nn' data-content='#'>
                    $nn
                </td>
                <td data-content='Option'>
                    $displayed
                    <!-- <input class='form-control' name='rows[$nn][title]' value="$title" type="hidden"/>
                    <input class='form-control' name='rows[$nn][displayed]' value='$displayed' type="hidden"/> -->
                </td>
                <td data-content='Value'>
                    $value_line
                    <!-- <input class='form-control' name='rows[$nn][type]' value='$type' type="hidden"/> -->
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
