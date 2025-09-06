<?PHP
use Tables\SqlTables\TablesSql;
use Tables\Langs\LangsTables;
// use Tables\Main\MainTables;
use function Actions\Html\make_mdwiki_title;
use function SQLorAPI\Process\get_process_all_new;
// use function Actions\Html\make_cat_url;

echo <<<HTML
    <div class='card-header'>
        <h4>Translations in process:</h4>
    </div>
    <div class='card-body'>
HTML;

function make_td($tabg, $nnnn)
{
    //---
    // global $views_sql, $user_name;
    //---
    $id       = $tabg['id'] ?? "";
    $date     = $tabg['date'] ?? $tabg['add_date'] ?? "";
    //---
    // if $_date_ has : then split before first space
    if (strpos($date, ':') !== false) {
        $date = explode(' ', $date)[0];
    };
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
    $lang2 = LangsTables::$L_code_to_lang[$llang] ?? $llang;
    //---
    // $ccat = make_cat_url( $cat );
    $ccat = TablesSql::$s_cat_to_camp[$cat] ?? $cat;
    //---
    // $worde = $word ?? MainTables::$x_Words_table[$md_title];
    //---
    $nana = make_mdwiki_title($md_title);
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
            <td data-content="Title">
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
$sato = <<<HTML
	<table class="table table-sm table-striped soro table-mobile-responsive table-mobile-sided table_text_left" style="font-size:90%;">
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
$dd1 = get_process_all_new();
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
