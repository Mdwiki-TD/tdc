<?PHP

use function Utils\Html\make_mdwiki_title;
use function APICalls\TDApi\get_td_api;

function process_make_td($tab, $nnnn)
{
    // { "id": 3284, "title": "Triquetral fracture", "user": "SeaTub", "lang": "es", "cat": "RTT", "translate_type": "all", "word": 198, "add_date": "2026-02-17 03:00:00", "campaign": "Main", "autonym": "español" }
    $date     = $tab['date'] ?? $tab['add_date'] ?? "";

    // if $date has : then split before first space `2026-02-25 03:00:00` > `2026-02-25`
    if (strpos($date, ':') !== false) {
        $date = explode(' ', $date)[0];
    };
    $user      = $tab['user'] ?? "";
    $lang_code = $tab['lang'] ?? "";
    $md_title  = $tab['title'] ?? "";
    $autonym   = $tab['autonym'] ?? "";
    $campaign  = $tab['campaign'] ?? "";

    $lang_title = "($lang_code) $autonym";

    $talk_url = "//$lang_code.wikipedia.org/w/index.php?title=User_talk:$user&action=edit&section=new";

    $mdwiki_link = make_mdwiki_title($md_title);
    $md_title_encoded = rawurlencode($md_title);

    $laly = <<<HTML
        <tr>
            <td data-content="#">
                $nnnn
            </td>
            <td data-content="User">
                <a target='' href='/Translation_Dashboard/leaderboard.php?user=$user'>$user</a> (<a target="_blank" href="$talk_url">talk</a>)
            </td>
            <td data-content="Lang.">
                <a target='' href='/Translation_Dashboard/leaderboard.php?langcode=$lang_code'>$lang_title</a>
            </td>
            <td data-content="Title">
                $mdwiki_link
            </td>
            <td data-content="Campaign">
                $campaign
            </td>
            <td data-content="Draft">
                <a href="//mdwikicx.toolforge.org/wiki/$lang_code/$md_title_encoded" target="_blank">$date</a>
            </td>
        </tr>
        HTML;

    return $laly;
};

$data = get_td_api(['get' => 'in_process', 'limit' => "100", "order" => 'add_date']);

$tbody_html = "";
$noo = 0;
foreach ($data as $tat => $tabe) {
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
                        <th>Draft</th>
                    </tr>
                </thead>
                <tbody>
                    $tbody_html
                </tbody>
            </table>
        </div>
    </div>
HTML;
