<?PHP

use Tables\SqlTables\TablesSql;
use Tables\Main\MainTables;
use Tables\Langs\LangsTables;

use function APICalls\WikiApi\make_view_by_number;
use function Utils\Html\make_mail_icon_new;
use function Utils\Html\make_talk_url;
use function Utils\Html\make_mdwiki_title;
use function SQLorAPI\Recent\get_recent_pages_users;
use function SQLorAPI\Funcs\get_pages_users_langs;
use function SQLorAPI\Funcs\get_pages_langs;
use function SQLorAPI\Recent\get_recent_sql;

$last_table = $_GET['last_table'] ?? 'pages';
$last_table = in_array($last_table, ['pages', 'pages_users']) ? $last_table : 'pages';

function last_make_td($tabg, $nnnn, $last_table)
{
    $user     = $tabg['user'] ?? "";

    $llang    = $tabg['lang'] ?? "";
    $md_title = trim($tabg['title'] ?? '');
    $cat      = $tabg['cat'] ?? "";
    $word     = $tabg['word'] ?? "";
    $target   = trim($tabg['target'] ?? '');
    $pupdate  = $tabg['pupdate'] ?? '';
    $add_date = $tabg['add_date'] ?? '';

    $mdwiki_revid = $tabg['mdwiki_revid'] ?? '';

    // if $add_date has : then split before first space
    if (strpos($add_date, ':') !== false) {
        $add_date = explode(' ', $add_date)[0];
    };

    $user_name = $user;
    // $user_name is the first word of the user if length > 15
    if (strlen($user) > 15) {
        $user_name = explode(' ', $user);
        $user_name = $user_name[0];
    }

    $view = "";

    $mail_icon = make_mail_icon_new($tabg, 'pup_window_email');

    if ($last_table == "pages") {
        $views_number = $tabg['views'] ?? '?';

        if (!$word || $word == 0) {
            $word = MainTables::$x_Words_table[$md_title] ?? 0;
        }

        $view = make_view_by_number($target, $views_number, $llang, $pupdate);
    }

    // $ccat = make_cat_url( $cat );
    $ccat = TablesSql::$s_cat_to_camp[$cat] ?? $cat;

    $mdwiki_title = make_mdwiki_title($md_title);

    $encoded_target = rawurlencode(str_replace(' ', '_', $target));
    $escaped_display = htmlspecialchars($target, ENT_QUOTES, 'UTF-8');
    $target_link = "<a target='_blank' href='https://{$llang}.wikipedia.org/wiki/{$encoded_target}'>{$escaped_display}</a>";

    $talk = make_talk_url($llang, $user);

    $md_title_encoded = rawurlencode($md_title);

    $params = [
        "title" => $target,
        "lang" => $llang,
        "sourcetitle" => $md_title,
        "mdwiki_revid" => $mdwiki_revid,
    ];

    if ($GLOBALS['global_username'] !== "Mr. Ibrahem") {
        $params['save'] = 1;
    };

    $fixwikirefs = "/fixwikirefs.php?" . http_build_query($params, '', '&', PHP_QUERY_RFC3986);

    $flags = "";

    $laly = <<<HTML
        <tr>
            <td>
                $nnnn
            </td>
            <td>
                <a href="/Translation_Dashboard/leaderboard.php?user=$user" data-bs-toggle="tooltip" data-bs-title="$user">
                    $user_name
                </a> ($talk)
            </td>
            <td>
                $mail_icon
            </td>
            <td>
                $mdwiki_title
            </td>
            <td>
                $ccat
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

$lang = $_GET['lang'] ?? 'All';

if ($lang !== 'All' && !isset(LangsTables::$L_code_to_lang[$lang])) {
    $lang = 'All';
};

if ($last_table == 'pages') {
    $qsl_results = get_recent_sql($lang);
} else {
    $qsl_results = get_recent_pages_users($lang);
}

$recent_rows = "";

$noo = 0;

foreach ($qsl_results as $tat => $tabe) {
    $noo = $noo + 1;
    $recent_rows .= last_make_td($tabe, $noo, $last_table);
};

$table_id = ($last_table == 'pages') ? 'last_table' : 'last_users_table';

$Toggle_column = "";

$thead = <<<HTML
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
HTML;

$Campaign_number = 4;
$flags_number = 10;
$fix_number = 8;
$Toggle_column = <<<HTML
    <div>
        <span class="" data-column="0">Toggle columns:</span>
        <a class="toggle-vis btn btn-outline-primary" data-column="$Campaign_number" type="button">Campaign</a>
        <a class="toggle-vis btn btn-outline-primary" data-column="$fix_number" type="button">Fixref</a>
        <a class="toggle-vis btn btn-outline-primary" data-column="$flags_number" type="button">Flags</a>
    </div>
HTML;


$recent_table = <<<HTML
    $Toggle_column
    <table class="table table-sm table-striped table_text_left" id="$table_id" style="font-size:90%;">
        <thead>
            $thead
        </thead>
        <tbody>
            $recent_rows
        </tbody>
    </table>
HTML;

if ($last_table == 'pages') {
    $result = get_pages_langs();
} else {
    $result = get_pages_users_langs();
}

function filter_recent($lang, $result)
{

    ksort($result);

    $lang_list = "<option data-tokens='All' value='All'>All</option>";

    foreach ($result as $codr) {
        $langeee = LangsTables::$L_code_to_lang[$codr] ?? '';
        $selected = ($codr == $lang) ? 'selected' : '';
        $lang_list .= <<<HTML
            <option data-tokens='$codr' value='$codr' $selected>$langeee</option>
            HTML;
    };
    return $lang_list;
}

$filter_by_lang = filter_recent($lang, $result);


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

$count_result = count($result);

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
            $recent_table
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
