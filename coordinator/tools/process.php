<?PHP

use function Actions\Html\make_mdwiki_title;
use function SQLorAPI\Get\get_process_all;
// use function Actions\Html\make_cat_url;

echo <<<HTML
    <div class='card-header'>
        <h4>Translations in process:</h4>
    </div>
    <div class='card-body'>
HTML;

/**
 * Generates an HTML table row displaying translation details.
 *
 * Constructs a table row (<tr>) with columns for the row number, user (with links to the user's dashboard and talk page),
 * language (linked to its leaderboard), formatted title, campaign, and date. It utilizes global mappings to convert
 * language codes to full names and categories to campaign names, and formats the title via a markdown utility.
 *
 * @param array $tabg Associative array containing translation details with keys 'id', 'date', 'user', 'lang', 'title', and 'cat'.
 * @param int $nnnn The row number for the first table cell.
 * @return string The HTML string representing the constructed table row.
 */
function make_td($tabg, $nnnn)
{
    //---
    global $code_to_lang, $cat_to_camp;
    // global $Words_table, $views_sql, $user_name;
    //---
    $id       = $tabg['id'] ?? "";
    $date     = $tabg['date'] ?? "";
    //---
    //return $date . '<br>';
    //---
    $user     = $tabg['user'] ?? "";
    $llang    = $tabg['lang'] ?? "";
    $md_title = $tabg['title'] ?? "";
    $cat      = $tabg['cat'] ?? "";
    // $word     = $tabg['word'] ?? "";
    // $pupdate  = $tabg['date'] ?? '';
    //---
    $talk_url = "//$llang.wikipedia.org/w/index.php?title=User_talk:$user&action=edit&section=new";
    //---
    $lang2 = $code_to_lang[$llang] ?? $llang;
    //---
    // $ccat = make_cat_url( $cat );
    $ccat = $cat_to_camp[$cat] ?? $cat;
    //---
    // $worde = $word ?? $Words_table[$md_title];
    //---
    $nana = make_mdwiki_title($md_title);
    //---
    // $mail_params = array( 'user' => $user, 'lang' => $llang, 'date' => $date, 'title' => $md_title, 'nonav' => '1');
    // $mail_url = "index.php?ty=Emails/msg&" . http_build_query( $mail_params );
    // $onclick = 'pupwindow("' . $mail_url . '")';
    // $mail = "<a class='btn btn-outline-primary btn-sm' onclick='$onclick'>Email</a>";
    //---
    $laly = <<<HTML
        <tr>
            <td data-content="#">
                $nnnn
            </td>
            <td data-content="User">
                <a target='' href='/Translation_Dashboard/leaderboard.php?user=$user'>$user</a> (<a target="_blank" href="$talk_url">talk</a>)
            </td>
            <td data-content="Lang.">
                <a target='' href='/Translation_Dashboard/leaderboard.php?langcode=$llang'>$lang2</a>
            </td>
            <td style='max-width:150px;' data-content="Title">
                $nana
            </td>
            <td data-content="Campaign">
                $ccat
            </td>
            <td data-content="Date">
                $date
            </td>
        </tr>
        HTML;
    //---
    return $laly;
};
$dd1 = get_process_all();
//---
$sato = <<<HTML
	<table class="table table-sm table-striped soro table-mobile-responsive table-mobile-sided" style="font-size:90%;">
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th><span data-toggle="tooltip" title="Language">Lang.</span></th>
                <th>Title</th>
                <th>Campaign</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
HTML;
//---
$noo = 0;
foreach ($dd1 as $tat => $tabe) {
    //---
    $noo = $noo + 1;
    $sato .= make_td($tabe, $noo);
    //---
};
//---
$sato .= <<<HTML
        </tbody>
    </table>
HTML;
echo $sato;
//---
?>
<script>
    function pupwindow(url) {
        window.open(url, 'popupWindow', 'width=850,height=550,scrollbars=yes');
    };
</script>
