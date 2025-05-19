لدي ثلاث ملفات الأول: index.php:
<?php

namespace SQLorAPI\Get;

/*
Usage:
use function SQLorAPI\Get\get_coordinator;
use function SQLorAPI\Get\super_function;
*/

use function Actions\MdwikiSql\fetch_query;
use function Actions\TDApi\get_td_api;

$settings_tabe = array_column(get_td_api(['get' => 'settings']), 'value', 'title');
//---
$use_td_api  = (($settings_tabe['use_td_api'] ?? "") == "1") ? true : false;
include_once __DIR__ . '/process_data.php';
include_once __DIR__ . '/recent_data.php';

function super_function($api_params, $sql_params, $sql_query)
{
    global $use_td_api;
    if ($use_td_api) {
        $data = get_td_api($api_params);
    } else {
        $data = fetch_query($sql_query, $sql_params);
    }
    return $data;
}

function get_coordinator()
{
    static $coordinator = [];
    if (!empty($coordinator ?? [])) {
        return $coordinator;
    }
    $api_params = ['get' => 'coordinator'];
    $query = "SELECT id, user FROM coordinator order by id";
    $data = super_function($api_params, [], $query);
    $coordinator = $data;
    return $data;
}
?>

الثاني: process_data.php
<?php

namespace SQLorAPI\Process;

/*
Usage:
use function SQLorAPI\Process\get_user_process_new;
*/

use function SQLorAPI\Get\super_function;

$data_index = [];

function get_user_process_new($user)
{
    global $data_index;
    if (!empty($data_index['inprocess_tdapi' . $user] ?? [])) {
        return $data_index['inprocess_tdapi' . $user];
    }
    $api_params = ['get' => 'in_process', 'user' => $user];
    $query = "select * from in_process where user = ?";
    $params = [$user];
    $data = super_function($api_params, $params, $query);
    $data_index['inprocess_tdapi' . $user] = $data;
    return $data;
}
?>
الثالث: process_data.php
<?php

namespace SQLorAPI\Recent;

/*
Usage:
use function SQLorAPI\Recent\get_recent_sql;
*/

use function SQLorAPI\Get\super_function;

$data_index = [];

function get_recent_sql($lang)
{
    $lang_line = '';
    $sql_params = [];
    $api_params = array('get' => 'pages_with_views', 'target' => 'not_empty', 'limit' => '250', 'order' => 'pupdate_or_add_date');
    if (!empty($lang) && $lang != 'All') {
        $lang_line = "and p.lang = ?";
        $sql_params[] = $lang;
        // ---
        $api_params['lang'] = $lang;
    }
    $sql_query = <<<SQL
        select distinct
            p.id, p.title, p.word, p.translate_type, p.cat,
            p.lang, p.user, p.target, p.date, p.pupdate, p.add_date, p.deleted, p.target, p.lang,
            (select v.views from views_new_all v where p.target = v.target AND p.lang = v.lang LIMIT 1) as views
        from pages p
        where p.target != ''
        $lang_line
        ORDER BY GREATEST(UNIX_TIMESTAMP(p.pupdate), UNIX_TIMESTAMP(p.add_date)) DESC
        limit 250
    SQL;
    $tab = super_function($api_params, $sql_params, $sql_query);
    // merage the two arrays without duplicates
    // $tab = array_unique(array_merge($dd0, $dd1), SORT_REGULAR);
    // sort the table by add_date
    // usort($tab, function ($a, $b) {
    //     return strtotime($b['pupdate']) - strtotime($a['pupdate']);
    // });
    return $tab;
}

?>

السؤال هو كيف يمكن جمع الوظائف من الثلاث الملفات كي استخدمها عبر namespace SQLorAPI\Get فقط دون الحاجة لاستعداء كل وظيفة من ملفها الخاص<?php
دون جمعها في نفس الملف بالضبط؟
