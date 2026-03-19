<?PHP

function process_make_td($tab, $nnnn)
{
    // { "id": 3284, "title": "Triquetral fracture", "user": "SeaTub", "lang": "es", "cat": "RTT", "translate_type": "all", "word": 198, "add_date": "2026-02-17 03:00:00", "campaign": "Main", "autonym": "español" }

    $user      = $tab['user'] ?? "";
    $lang_code = $tab['lang'] ?? "";
    $md_title  = $tab['title'] ?? "";
    $autonym   = $tab['autonym'] ?? "";
    $campaign  = $tab['campaign'] ?? "";

    $date     = $tab['add_date'] ?? "";
    // if $date has : then split before first space `2026-02-25 03:00:00` > `2026-02-25`
    if (strpos($date, ':') !== false) {
        $date = explode(' ', $date)[0];
    };
    $lang_title = "($lang_code) $autonym";

    $talk_url = "//$lang_code.wikipedia.org/w/index.php?title=User_talk:$user&action=edit&section=new";

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
                <a href="//mdwiki.org/wiki/$md_title_encoded" target="_blank">$md_title</a>
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

function get_td_api(array $params): array
{
    $endPoint = (($_SERVER['SERVER_NAME'] ?? '') == 'localhost') ? 'http://localhost:9001' : 'https://mdwiki.toolforge.org';
    $endPoint .= '/api.php';

    $out = file_get_contents("$endPoint?" . http_build_query($params));

    $results = json_decode($out, true);

    if (!is_array($results)) {
        $results = [];
    }

    $result = $results['results'] ?? [];

    return $result;
}
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
            <table id="process_table" class="table table-sm table-striped table-mobile-responsive table-mobile-sided table_text_left" style="font-size:90%;">
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

    <script>
        $(document).ready(function() {
            $('#process_table').DataTable({
                stateSave: true,
                lengthMenu: [
                    [25, 50, 100, 200],
                    [25, 50, 100, 200]
                ]
            });
        });
    </script>
HTML;
