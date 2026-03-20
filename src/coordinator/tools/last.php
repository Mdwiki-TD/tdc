<?PHP

use Tables\SqlTables\TablesSql;
use Tables\Main\MainTables;
use Tables\Langs\LangsTables;

use function APICalls\WikiApi\make_view_by_number;
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

    if ($last_table == "pages") {
        $views_number = $tabg['views'] ?? '?';

        if (!$word || $word == 0) {
            $word = MainTables::$x_Words_table[$md_title] ?? 0;
        }

        $view = make_view_by_number($target, $views_number, $llang, $pupdate);
    }

    // $ccat = make_cat_url( $cat );
    $ccat = TablesSql::$s_cat_to_camp[$cat] ?? $cat;

    $encoded_title = rawurlencode(str_replace(' ', '_', $md_title));
    $escaped_title = htmlspecialchars($md_title, ENT_QUOTES, 'UTF-8');

    $encoded_target = rawurlencode(str_replace(' ', '_', $target));
    $escaped_display = htmlspecialchars($target, ENT_QUOTES, 'UTF-8');

    $target_link = "<a target='_blank' href='https://{$llang}.wikipedia.org/wiki/{$encoded_target}'>{$escaped_display}</a>";

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
                </a>
            </td>
            <td>
                <a target='_blank' href='https://mdwiki.org/wiki/{$encoded_title}'>{$escaped_title}</a>
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
        $langeee = LangsTables::$L_code_to_lang[$codr] ?? '';
        $selected = ($codr == $lang) ? 'selected' : '';
        $lang_list .= <<<HTML
            <option data-tokens='$codr' value='$codr' $selected>$langeee</option>
            HTML;
    };
    return $lang_list;
}

$lang = $_GET['lang'] ?? 'All';

$qsl_results = ($last_table == 'pages') ? get_recent_sql($lang) : get_recent_pages_users($lang);

$recent_rows = "";

$noo = 0;

foreach ($qsl_results as $tat => $tabe) {
    $noo = $noo + 1;
    $recent_rows .= last_make_td($tabe, $noo, $last_table);
};

$Campaign_number = 3;
$flags_number = 8;


$table_id = ($last_table == 'pages') ? 'last_table' : 'last_users_table';

$result = ($last_table == 'pages') ? get_pages_langs() : get_pages_users_langs();

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
                <input name='ty' value='last' type='hidden'/>
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
                <a class="toggle-vis btn btn-outline-primary" data-column="$flags_number" type="button">Flags</a>
            </div>
            <table class="table table-sm table-striped table_text_left" id="$table_id" style="font-size:90%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Title</th>
                        <th>Campaign</th>
                        <th>Translated</th>
                        <th>Published</th>
                        <th>Views</th>
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
