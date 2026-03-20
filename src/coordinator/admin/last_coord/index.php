<?PHP

const MAX_USERNAME_DISPLAY_LENGTH = 15;

function make_view_by_number($target, $numb, $lang, $pupdate)
{
    // remove spaces and tab characters
    $target = trim($target);
    $numb2 = (!empty($numb)) ? $numb : "?";
    $start = !empty($pupdate) ? $pupdate : '2019-01-01';
    $end = date("Y-m-d", strtotime("yesterday"));

    $url = 'https://pageviews.wmcloud.org/?' . http_build_query(array(
        'project' => "$lang.wikipedia.org",
        'platform' => 'all-access',
        'agent' => 'all-agents',
        'start' => $start,
        'end' => $end,
        // 'range' => 'all-time',
        'redirects' => '0',
        'pages' => $target,
    ), '', '&', PHP_QUERY_RFC3986);
    // ---
    $numb3 = (is_numeric($numb2)) ? number_format($numb2) : $numb2;
    $link = "<a target='_blank' href='$url'>$numb3</a>";
    // ---
    if (is_numeric($numb2) && intval($numb2) > 0) {
        return $link;
    }
    // ---
    $start2 = !empty($pupdate) ? str_replace('-', '', $pupdate) : '20190101';
    // ---
    $url2 = 'https://wikimedia.org/api/rest_v1/metrics/pageviews/per-article/' . $lang . '.wikipedia/all-access/all-agents/' . rawurlencode($target) . '/daily/' . $start2 . '/2030010100';
    // ---
    $link = "<a target='_blank' name='toget' data-json-url='$url2' href='$url'>$numb2</a>";
    // ---
    return $link;
};
function make_mail_icon_url(array $tab): string
{
    $mail_params = [
        'user' => $tab['user'] ?? '',
        'lang' => $tab['lang'] ?? '',
        'target' => $tab['target'] ?? '',
        'date' => $tab['pupdate'] ?? '',
        'title' => $tab['title'] ?? '',
        'nonav' => '1'
    ];

    $mail_url = "index.php?ty=Emails/msg&" . http_build_query($mail_params, '', '&', PHP_QUERY_RFC3986);
    $escaped_url = htmlspecialchars($mail_url, ENT_QUOTES, 'UTF-8');

    return $escaped_url;
}

function post_url(string $endPoint, array $params = []): string
{
    $usr_agent = "WikiProjectMed Translation Dashboard/1.0 (https://mdwiki.toolforge.org/; tools.mdwiki@toolforge.org)";

    $ch = curl_init();

    $url = "{$endPoint}?" . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => $usr_agent,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 10,
    ]);

    $output = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($http_code !== 200) {
        error_log('post_url: Error: API request failed with status code ' . $http_code);
    }

    if ($output === FALSE) {
        error_log("post_url: cURL Error: " . curl_error($ch));
    }

    if (curl_errno($ch)) {
        error_log('post_url: Error:' . curl_error($ch));
    }

    curl_close($ch);
    return $output;
}

function get_td_api(array $params): array
{
    $endPoint = (($_SERVER['SERVER_NAME'] ?? '') == 'localhost') ? 'http://localhost:9001' : 'https://mdwiki.toolforge.org';
    $endPoint .= '/api.php';

    $out = post_url($endPoint, $params);

    $results = json_decode($out, true);

    if (!is_array($results)) {
        $results = [];
    }

    $result = $results['results'] ?? [];

    if (isset($result['error'])) {
        $result = [];
    }
    return $result;
}
function last_make_td($tabg, $nnnn, $last_table)
{
    $user     = $tabg['user'] ?? "";

    $llang    = $tabg['lang'] ?? "";
    $md_title = trim($tabg['title'] ?? '');
    $target   = trim($tabg['target'] ?? '');
    $pupdate  = $tabg['pupdate'] ?? '';
    $add_date = $tabg['add_date'] ?? '';
    $campaign = $tabg['campaign'] ?? '';

    $mdwiki_revid = $tabg['mdwiki_revid'] ?? '';

    // if $add_date has : then split before first space
    if (strpos($add_date, ':') !== false) {
        $add_date = explode(' ', $add_date)[0];
    };

    $user_name = $user;
    // $user_name is the first word of the user if length > 15
    if (strlen($user) > MAX_USERNAME_DISPLAY_LENGTH) {
        $user_name = explode(' ', $user);
        $user_name = $user_name[0];
    }

    $view = "";

    if ($last_table == "pages") {
        $views_number = $tabg['views'] ?? '?';

        $view = make_view_by_number($target, $views_number, $llang, $pupdate);
    }

    $encoded_title = rawurlencode(str_replace(' ', '_', $md_title));
    $escaped_title = htmlspecialchars($md_title, ENT_QUOTES, 'UTF-8');

    $encoded_target = rawurlencode(str_replace(' ', '_', $target));
    $escaped_display = htmlspecialchars($target, ENT_QUOTES, 'UTF-8');

    $target_link = "<a target='_blank' href='https://{$llang}.wikipedia.org/wiki/{$encoded_target}'>{$escaped_display}</a>";


    $mail_icon = make_mail_icon_url($tabg);

    $escaped_user = rawurlencode($user);

    $params = [
        "title" => $target,
        "lang" => $llang,
        "sourcetitle" => $md_title,
        "mdwiki_revid" => $mdwiki_revid,
    ];

    $excludedUsers = ['Mr. Ibrahem']; // TODO: This should ideally be moved to a configuration file.
    if (!in_array($GLOBALS['global_username'], $excludedUsers)) {
        $params['save'] = 1;
    };

    $fixwikirefs = "/fixwikirefs.php?" . http_build_query($params, '', '&', PHP_QUERY_RFC3986);

    $md_title_encoded = rawurlencode($md_title);

    $flags = "";

    $laly = <<<HTML
        <tr>
            <td>
                $nnnn
            </td>
            <td>
                <a href="/Translation_Dashboard/leaderboard.php?user=$user" data-bs-toggle="tooltip" data-bs-title="$user">
                    $user_name
                </a> (<a target='_blank' href='//{$llang}.wikipedia.org/w/index.php?title=User_talk:{$escaped_user}'>talk</a>)
            </td>
            <td>
    	        <a class='btn btn-outline-primary btn-sm spannowrap' pup-target='{$mail_icon}' onclick='pup_window_new(this)'>@</a>
            </td>
            <td>
                <a target='_blank' href='https://mdwiki.org/wiki/{$encoded_title}'>{$escaped_title}</a>
            </td>
            <td>
                $campaign
            </td>
            <td class="link_container">
                <a href='/Translation_Dashboard/leaderboard.php?langcode=$llang'>$llang</a>: $target_link
            </td>
            <td>
                $pupdate
            </td>
            <td>
                $view
            </td>
            <td>
                <a target='_blank' href="$fixwikirefs">Fix</a>
            </td>
            <td>
                <a href="//mdwikicx.toolforge.org/wiki/$llang/$md_title_encoded" target="_blank">$add_date</a>
            </td>
            <td>
                $flags
            </td>
        </tr>
    HTML;

    return $laly;
}

function filter_recent($lang, $data)
{

    ksort($data);
    $lang_list = "<option data-tokens='All' value='All'>All</option>";

    foreach ($data as $codr) {
        $code    = $codr["lang"] ?? "";
        $autonym = $codr["autonym"] ?? "";
        $selected = ($code == $lang) ? 'selected' : '';
        $lang_list .= <<<HTML
            <option data-tokens='$code' value='$code' $selected>($code) $autonym</option>
            HTML;
    };
    return $lang_list;
}

$lang = $_GET['lang'] ?? 'All';


$api_params_users = [
    'get' => 'pages_users',
    'target' => 'not_empty',
    "lang" => $lang,
    "order" => 'pupdate',
    'limit' => '100',
];

$api_params_pages = [
    'get' => 'pages_with_views',
    'target' => 'not_empty',
    "lang" => $lang,
    "order" => 'pupdate_or_add_date',
    'limit' => '250',
];

$last_table = $_GET['last_table'] ?? 'pages';
$last_table = in_array($last_table, ['pages', 'pages_users']) ? $last_table : 'pages';

$qsl_results = ($last_table == 'pages') ? get_td_api($api_params_pages) : get_td_api($api_params_users);

$recent_rows = "";

$noo = 0;

foreach ($qsl_results as $tat => $tabe) {
    $noo = $noo + 1;
    $recent_rows .= last_make_td($tabe, $noo, $last_table);
};

$Campaign_number = 4;
$flags_number = 10;
$fix_number = 8;

$table_id = ($last_table == 'pages') ? 'last_table' : 'last_users_table';

$api_params_langs = [
    'get' => ($last_table == 'pages') ? 'pages_langs' : 'pages_users_langs',
];

$result = get_td_api($api_params_langs);

$filter_by_lang = filter_recent($lang, $result);

$count_result = count($result);


$data = [
    "pages" => 'Main',
    "pages_users" => 'User',
];

$filter_ta = "";

foreach ($data as $table_name => $label) {
    $checked = ($table_name == $last_table) ? "checked" : "";
    $filter_ta .= <<<HTML
        <div class="form-check form-check-inline">
            <input class="form-check-input"
                type="radio"
                name="last_table"
                id="radio_$table_name"
                value="$table_name"
                $checked>
            <label class="form-check-label" for="radio_$table_name">$label</label>
        </div>
    HTML;
}

echo <<<HTML
    <div class='card'>
        <div class='card-header'>
            <form method='get' action='index.php'>
                <input name='ty' value='last_coord' type='hidden'/>
                <div class='row'>
                    <div class='col-md-4'>
                        <h4>Recent translations ($count_result):</h4>
                    </div>
                    <div class='col-md-4'>
                        <div class="input-group">
                            <span class="input-group-text">Namespace:</span>
                            <div class="form-control">
                                $filter_ta
                            </div>
                        </div>
                    </div>
                    <div class='col-md-3'>
                        <div class="input-group">
                            <!-- <span class="input-group-text">Lang:</span> -->  <!-- bg-light-subtle -->
                            <select aria-label="Language code"
                                class="selectpicker"
                                id='lang'
                                name='lang'
                                placeholder='Language code'
                                data-live-search="true"
                                data-container="body"
                                data-live-search-style="begins"
                                data-bs-theme="auto"
                                data-style='btn active'
                                data-width="90%"
                                >
                                $filter_by_lang
                            </select>
                        </div>
                    </div>
                    <div class='aligncenter col-md-1'>
                        <input class='btn btn-outline-primary' type='submit' value='Filter' />
                    </div>
                </div>
            </form>
        </div>
        <div class='card-body'>
            <div class="d-none d-md-inline">
                <span class="" data-column="0">Toggle columns:</span>
                <a class="toggle-vis btn btn-outline-primary" data-column="$Campaign_number" type="button">Campaign</a>
                <a class="toggle-vis btn btn-outline-primary" data-column="$fix_number" type="button">Fixref</a>
                <a class="toggle-vis btn btn-outline-primary" data-column="$flags_number" type="button">Flags</a>
            </div>
            <table class="table table-sm table-striped table_text_left" id="$table_id" style="font-size:90%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th> <span title='Email'>@</span> </th>
                        <th>Title</th>
                        <th>Campaign</th>
                        <th>Translated</th>
                        <th>Published</th>
                        <th>Views</th>
                        <th>Fixref</th>
                        <th>Draft</th>
                        <th>Flags</th>
                    </tr>
                </thead>
                <tbody>
                    $recent_rows
                </tbody>
            </table>
        </div>
    </div>
HTML;

?>
<script>
    $(document).ready(function() {
        var table;
        var tableElement = $('#last_table');
        if (tableElement.length) {
            table = $('#last_table').DataTable({
                stateSave: true,
                // order: [ [6, 'desc'] ],
                paging: false,
                // lengthMenu: [[100, 150, 200], [250, 150, 200]],
                // scrollY: 800,
                responsive: {
                    details: true
                }
            });
        }

        var usersTableElement = $('#last_users_table');
        if (usersTableElement.length) {
            table = $('#last_users_table').DataTable({
                stateSave: true,
                // paging: false,
                lengthMenu: [
                    [100, 150, 200],
                    [100, 150, 200]
                ],
                // scrollY: 800,
                responsive: {
                    details: true
                }
            });
        }
        if (table) {
            document.querySelectorAll('a.toggle-vis').forEach((el) => {
                el.addEventListener('click', function(e) {
                    e.preventDefault();

                    // add class mb_btn_active to this
                    el.classList.toggle('btn-outline-primary');
                    el.classList.toggle('btn-outline-secondary');

                    let columnIdx = e.target.getAttribute('data-column');
                    let column = table.column(columnIdx);

                    // Toggle the visibility
                    column.visible(!column.visible());
                });
            });
        }

    });
</script>
