<?PHP

use Tables\SqlTables\TablesSql;
use Tables\Langs\LangsTables;
use function Utils\Html\make_mdwiki_title;
use function SQLorAPI\Process\get_process_all_new;

function process_make_td($tabg, $nnnn)
{
    $date     = $tabg['date'] ?? $tabg['add_date'] ?? "";

    // if $date has : then split before first space
    if (strpos($date, ':') !== false) {
        $date = explode(' ', $date)[0];
    };
    $user     = $tabg['user'] ?? "";
    $llang    = $tabg['lang'] ?? "";
    $md_title = $tabg['title'] ?? "";
    $cat      = $tabg['cat'] ?? "";

    $talk_url = "//$llang.wikipedia.org/w/index.php?title=User_talk:$user&action=edit&section=new";

    $lang2 = LangsTables::$L_code_to_lang[$llang] ?? $llang;

    $ccat = TablesSql::$s_cat_to_camp[$cat] ?? $cat;

    $nana = make_mdwiki_title($md_title);

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

    return $laly;
};

$tbody_html = "";

$dd1 = get_process_all_new();

$noo = 0;
foreach ($dd1 as $tat => $tabe) {
    $noo = $noo + 1;
    $tbody_html .= process_make_td($tabe, $noo);
};


echo <<<HTML
    <div class='card'>
        <div class='card-header'>
            <h4>Translations in process:</h4>
        </div>
        <div class='card-body'>
            <table class="table table-sm table-striped soro table-mobile-responsive table-mobile-sided table_text_left" style="font-size:90%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th><span data-bs-toggle="tooltip" title="Language">Lang.</span></th>
                        <th>Title</th>
                        <th>Campaign</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    $tbody_html
                </tbody>
            </table>
        </div>
    </div>
HTML;
