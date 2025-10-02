<?PHP

use Tables\SqlTables\TablesSql;
use Tables\Main\MainTables;
use Tables\Langs\LangsTables;

use function Tools\RecentHelps\filter_recent;
use function Tools\RecentHelps\do_add_date;
use function APICalls\WikiApi\make_view_by_number;
use function Utils\Html\make_mail_icon_new;
use function Utils\Html\make_talk_url;
use function Utils\Html\make_target_url;
use function Utils\Html\make_mdwiki_title;
use function SQLorAPI\Recent\get_recent_pages_users;
use function SQLorAPI\Funcs\get_pages_users_langs;
// use function Utils\Html\make_cat_url;
use function SQLorAPI\Funcs\get_pages_langs;
use function SQLorAPI\Recent\get_recent_sql;
use function Tools\RecentHelps\filter_table;

$last_tables = ['pages', 'pages_users'];
// ---
$last_table = $_GET['last_table'] ?? 'pages';
// ---
$last_table = in_array($last_table, $last_tables) ? $last_table : 'pages';

function make_td($tabg, $nnnn, $add_add, $last_table)
{
    // $id       = $tabg['id'] ?? "";
    $date     = $tabg['date'] ?? "";
    //---
    $user     = $tabg['user'] ?? "";
    //---
    $llang    = $tabg['lang'] ?? "";
    $md_title = trim($tabg['title'] ?? '');
    $cat      = $tabg['cat'] ?? "";
    $word     = $tabg['word'] ?? "";
    $target   = trim($tabg['target'] ?? '');
    $pupdate  = $tabg['pupdate'] ?? '';
    $add_date = $tabg['add_date'] ?? '';
    // ---
    $mdwiki_revid = $tabg['mdwiki_revid'] ?? '';
    //---
    // if $add_date has : then split before first space
    if (strpos($add_date, ':') !== false) {
        $add_date = explode(' ', $add_date)[0];
    };
    //---
    $user_name = $user;
    // $user_name is the first word of the user if length > 15
    if (strlen($user) > 15) {
        $user_name = explode(' ', $user);
        $user_name = $user_name[0];
    }
    //---
    $Campaign_td = "";
    $mail_icon_td = "";
    $view_td = "";
    //---
    if ($last_table == "pages") {
        $views_number = $tabg['views'] ?? '?';
        //---
        // $ccat = make_cat_url( $cat );
        $ccat = TablesSql::$s_cat_to_camp[$cat] ?? $cat;
        //---
        $word = $word ?? MainTables::$x_Words_table[$md_title];
        //---
        $view = make_view_by_number($target, $views_number, $llang, $pupdate);
        //---
        $mail_icon = (user_in_coord != false) ? make_mail_icon_new($tabg, 'pup_window_email') : '';
        $mail_icon_td = (!empty($mail_icon)) ? "<td data-content='Email'>$mail_icon</td>" : '';
        //---
        $view_td = <<<HTML
            <td data-content='Views'>
                $view
            </td>
        HTML;
        //---
        $Campaign_td = <<<HTML
        <td data-content='Campaign'>
            $ccat
        </td>
        HTML;
    }
    //---
    // $lang2 = LangsTables::$L_code_to_lang[$llang] ?? $llang;
    $lang2 = $llang;
    //---
    $nana = make_mdwiki_title($md_title);
    //---
    $targe33_name = $target;
    //---
    // if ( strlen($targe33_name) > 15 ) {
    //     $targe33_name = substr($targe33_name, 0, 15) . '...';
    // }
    //---
    $target_link = make_target_url($target, $llang, $targe33_name);
    //---
    $talk = make_talk_url($llang, $user);
    //---
    $md_title_encoded = rawurlencode($md_title);
    //---
    $add_add_row = ($add_add) ? <<<HTML
        <td data-content='add_date'>
            <a href="//medwiki.toolforge.org/wiki/$llang/$md_title_encoded" target="_blank">$add_date</a>
        </td>
    HTML : '';
    // ---
    $params = [
        "title" => $target,
        "lang" => $llang,
        "sourcetitle" => $md_title,
        "mdwiki_revid" => $mdwiki_revid,
    ];
    // ---
    if ($GLOBALS['global_username'] !== "Mr. Ibrahem") {
        $params['save'] = 1;
    };
    // ---
    // $fixwikirefs = "../fixwikirefs.php?" . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    $fixwikirefs = "/fixwikirefs.php?" . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    // ---
    $laly = <<<HTML
        <tr>
            <td data-content='#'>
                $nnnn
            </td>
            <td data-content='User'>
                <a href="/Translation_Dashboard/leaderboard.php?user=$user" data-bs-toggle="tooltip" data-bs-title="$user">$user_name</a> ($talk)
            </td>
            $mail_icon_td
            <td data-content='Title'>
                $nana
            </td>
            $Campaign_td
            <!-- <td>$word</td> -->
            <td data-content='Translated' class="link_container">
                <a href='/Translation_Dashboard/leaderboard.php?langcode=$llang'>$lang2</a> : $target_link
            </td>
            <td data-content='Publication date'>
                $pupdate
            </td>
            $view_td
            <td data-content='Fixref'>
                <a target='_blank' href="$fixwikirefs">Fix</a>
            </td>
            $add_add_row
        </tr>
    HTML;
    // ---
    return $laly;
}

$lang = $_GET['lang'] ?? 'All';

if ($lang !== 'All' && !isset(LangsTables::$L_code_to_lang[$lang])) {
    $lang = 'All';
};

$mail_th = (user_in_coord != false) ? "<th>Email</th>" : '';
//---
if ($last_table == 'pages') {
    $qsl_results = get_recent_sql($lang);
} else {
    $qsl_results = get_recent_pages_users($lang);
}
//---
// $add_add = do_add_date($qsl_results);
$add_add = true;
$th_add = $add_add ? "<th>add_date</th>" : '';
//---
$recent_rows = "";
// ---
$noo = 0;
// ---
foreach ($qsl_results as $tat => $tabe) {
    $noo = $noo + 1;
    $recent_rows .= make_td($tabe, $noo, $add_add, $last_table);
};
//---
$table_id = ($last_table == 'pages') ? 'last_tabel' : 'last_users_tabel';
//---
if ($last_table == 'pages') {
    $thead = <<<HTML
        <tr>
            <th>#</th>
            <th>User</th>
            $mail_th
            <th>Title</th>
            <th>Campaign</th>
            <th>Translated</th>
            <th>Publication date</th>
            <th>Views</th>
            <th>Fixref</th>
            $th_add
        </tr>
    HTML;
} else {
    $thead = <<<HTML
        <tr>
            <th>#</th>
            <th>User</th>
            <th>Title</th>
            <th>Translated</th>
            <th>Publication date</th>
            <th>Fixref</th>
            $th_add
        </tr>
    HTML;
}
//---
$recent_table = <<<HTML
    <table class="table table-sm table-striped table-mobile-responsive table-mobile-sided table_text_left" id="$table_id" style="font-size:90%;">
        <thead>
            $thead
        </thead>
        <tbody>
            $recent_rows
        </tbody>
    </table>
HTML;
//---
if ($last_table == 'pages') {
    $result = get_pages_langs();
} else {
    $result = get_pages_users_langs();
}
//---
$filter_by_lang = filter_recent($lang, $result);
//---
$data = [
    "pages" => 'Main',
    "pages_users" => 'User',
];
//---
$filter_ta = filter_table($data, $last_table, 'last_table');
//---
$count_result = count($result);
//---
echo <<<HTML
    <div class='card'>
        <div class='card-header'>
            <form method='get' action='index.php'>
                <input name='ty' value='last' type='hidden'/>
                <div class='row'>
                    <div class='col-md-4'>
                        <h4>Recent translations ($count_result):</h4>
                    </div>
                    <div class='col-md-4'>
                        $filter_ta
                    </div>
                    <div class='col-md-3'>
                        $filter_by_lang
                    </div>
                    <div class='aligncenter col-md-1'>
                        <input class='btn btn-outline-primary' type='submit' value='Filter' />
                    </div>
                </div>
            </form>
        </div>
        <div class='card-body'>
            $recent_table
        </div>
    </div>
HTML;
//---
?>
<script>
    $(document).ready(function() {
        var t = $('#last_tabel').DataTable({
            stateSave: true,
            // order: [ [6, 'desc'] ],
            paging: false,
            // lengthMenu: [[100, 150, 200], [250, 150, 200]],
            // scrollY: 800
        });
        var t = $('#last_users_tabel').DataTable({
            stateSave: true,
            order: [
                [4, 'desc']
            ],
            // paging: false,
            lengthMenu: [
                [100, 150, 200],
                [100, 150, 200]
            ],
            // scrollY: 800
        });
    });
</script>
