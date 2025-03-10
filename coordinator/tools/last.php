<?PHP
//---
include_once 'Tables/langcode.php';
//---
use function Actions\WikiApi\make_view_by_number;
use function Actions\Html\make_mail_icon;
use function Actions\Html\make_talk_url;
use function Actions\Html\make_target_url;
use function Actions\MdwikiSql\fetch_query;
use function Actions\Html\make_cat_url;
use function Actions\Html\make_mdwiki_title;
// use function Actions\TDApi\get_td_api;
//---
$lang = $_GET['lang'] ?? 'All';
//---
if ($lang !== 'All' && !isset($code_to_lang[$lang])) {
    $lang = 'All';
};
//---
function filter_recent($lang)
{
    global $code_to_lang;
    //---
    $result = fetch_query("select DISTINCT lang from pages;");
    //---
    // http://localhost:9001/api.php?get=pages_users&distinct=1&select=lang
    // $result = get_td_api (array('get' => 'pages', 'distinct' => 1, 'select' => 'lang'));
        //---
    $tabes = array_map('current', $result);
    //---
    ksort($tabes);
    //---
    $lang_list = "<option data-tokens='All' value='All'>All</option>";
    //---
    foreach ($tabes as $codr) {
        $langeee = $code_to_lang[$codr] ?? '';
        $selected = ($codr == $lang) ? 'selected' : '';
        $lang_list .= <<<HTML
            <option data-tokens='$codr' value='$codr' $selected>$langeee</option>
            HTML;
    };
    //---
    $langse = <<<HTML
        <select aria-label="Language code"
            class="selectpicker"
            id='lang'
            name='lang'
            placeholder='two letter code'
            data-live-search="true"
            data-container="body"
            data-live-search-style="begins"
            data-bs-theme="auto"
            data-style='btn active'
            data-width="90%"
            >
            $lang_list
        </select>
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
$mail_th = (user_in_coord != false) ? "<th>Email</th>" : '';
//---
$recent_table = <<<HTML
	<table class="table table-sm table-striped table-mobile-responsive table-mobile-sided" id="last_tabel" style="font-size:90%;">
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                $mail_th
                <!-- <th>Lang</th> -->
                <th>Title</th>
                <th>Campaign</th>
                <!-- <th>Words</th> -->
                <th>Translated</th>
                <th>Publication date</th>
                <th>Views</th>
                <th>Fixref</th>
                <th>add_date</th>
            </tr>
        </thead>
        <tbody>
HTML;
//---
function make_td($tabg, $nnnn)
{
    //---
    global $code_to_lang, $Words_table, $views_sql, $cat_to_camp;
    //---
    $id       = $tabg['id'] ?? "";
    $date     = $tabg['date'] ?? "";
    //---
    //return $date . '<br>';
    //---
    $user     = $tabg['user'] ?? "";
    //---
    $llang    = $tabg['lang'] ?? "";
    $md_title = trim($tabg['title']);
    $cat      = $tabg['cat'] ?? "";
    $word     = $tabg['word'] ?? "";
    $targe    = trim($tabg['target']);
    $pupdate  = $tabg['pupdate'] ?? '';
    $add_date = $tabg['add_date'] ?? '';
    //---
    $user_name = $user;
    // $user_name is the first word of the user if length > 15
    if (strlen($user) > 15) {
        $user_name = explode(' ', $user);
        $user_name = $user_name[0];
    }
    //---
    $views_number = $views_sql[$targe] ?? '?';
    //---
    // $lang2 = $code_to_lang[$llang] ?? $llang;
    $lang2 = $llang;
    //---
    // $ccat = make_cat_url( $cat );
    $ccat = $cat_to_camp[$cat] ?? $cat;
    //---
    $worde = $word ?? $Words_table[$md_title];
    //---
    $nana = make_mdwiki_title($md_title);
    //---
    $targe33_name = $targe;
    //---
    // if ( strlen($targe33_name) > 20 ) {
    //     $targe33_name = substr($targe33_name, 0, 20) . '...';
    // }
    //---
    $targe33 = make_target_url($targe, $llang, $targe33_name);
    $targe2  = urlencode($targe);
    //---
    $view = make_view_by_number($targe, $views_number, $llang, $pupdate);
    //---
    $mail_icon = (user_in_coord != false) ? make_mail_icon($tabg) : '';
    $mail_icon_td = (!empty($mail_icon)) ? "<td data-content='Email'>$mail_icon</td>" : '';
    //---
    $talk = make_talk_url($llang, $user);
    //---
    $laly = <<<HTML
        <tr>
            <td data-content='#'>
                $nnnn
            </td>
            <td data-content='User'>
                <a href="/Translation_Dashboard/leaderboard.php?user=$user" data-bs-toggle="tooltip" data-bs-title="$user">$user_name</a> ($talk)
            </td>
            $mail_icon_td
            <!-- <td data-content='Lang'>
                <a href='/Translation_Dashboard/leaderboard.php?langcode=$llang'>$lang2</a>
            </td> -->
            <td style='max-width:150px;' data-content='Title'>
                $nana
            </td>
            <!-- <td>$date</td> -->
            <td data-content='Campaign'>
                $ccat
            </td>
            <!-- <td>$worde</td> -->
            <td style='max-width:150px;' data-content='Translated'>
                <a href='/Translation_Dashboard/leaderboard.php?langcode=$llang'>$lang2</a> : $targe33
            </td>
            <td data-content='Publication date'>
                $pupdate
            </td>
            <td data-content='Views'>
                $view
            </td>
            <td data-content='Fixref'>
                <a target='_blank' href="../fixwikirefs.php?save=1&title=$targe2&lang=$llang">Fix</a>
            </td>
            <td data-content='add_date'>
                $add_date
            </td>
        </tr>
    HTML;
    //---
    return $laly;
};
//---
function get_recent_sql($lang)
{
    $lang_line = '';
    //---
    if (!empty($lang) && $lang != 'All') {
        $lang_line = "and lang = '$lang'";
    }
    //---
    $params0 = array('get' => 'pages', 'target' => 'not_empty', 'limit' => '250', 'order' => 'pupdate');
    $params1 = array('get' => 'pages', 'target' => 'not_empty', 'limit' => '250', 'order' => 'add_date');
    //---
    if (!empty($lang) && $lang != 'All') {
        $params0['lang'] = $lang;
        $params1['lang'] = $lang;
    }
    //---
    $dd0 = fetch_query("select * from pages where target != '' $lang_line ORDER BY pupdate DESC limit 250");
    // $dd0 = get_td_api ($params0);
    //---
    $dd1 = fetch_query("select * from pages where target != '' $lang_line ORDER BY add_date DESC limit 250");
    // $dd1 = get_td_api ($params1);
    //---
    // merage the two arrays without duplicates
    $tab = array_unique(array_merge($dd0, $dd1), SORT_REGULAR);
    //---
    // sort the table by add_date
    usort($tab, function ($a, $b) {
        // return strtotime($b['add_date']) - strtotime($a['add_date']);
        return strtotime($b['pupdate']) - strtotime($a['pupdate']);
    });
    //---
    return $tab;
}
//---
$qsl_results = get_recent_sql($lang);
//---
$noo = 0;
foreach ($qsl_results as $tat => $tabe) {
    //---
    $noo = $noo + 1;
    $recent_table .= make_td($tabe, $noo);
    //---
};
//---
$recent_table .= <<<HTML
        </tbody>
    </table>
HTML;
//---
$uuu = filter_recent($lang);
//---
echo <<<HTML
<div class='card-header'>
    <form method='get' action='index.php'>
        <input name='ty' value='last' hidden/>
        <div class='row'>
            <div class='col-md-5'>
                <h4>Most recent translations:</h4>
            </div>
            <div class='col-md-3'>
                $uuu
            </div>
            <div class='aligncenter col-md-2'>
                <input class='btn btn-outline-primary' type='submit' name='start' value='Filter' />
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
    function pupwindow(url) {
        window.open(url, 'popupWindow', 'width=850,height=550,scrollbars=yes');
    };

    $(document).ready(function() {
        var t = $('#last_tabel').DataTable({
            order: [
                [6, 'desc']
            ],
            paging: false,
            // lengthMenu: [[100, 150, 200], [250, 150, 200]],
            // scrollY: 800
        });
    });
</script>
