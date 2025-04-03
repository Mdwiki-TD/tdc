<?PHP
//---
/*
include_once __DIR__ . '/Tables/sql_tables.php';
*/
//---
// include_once __DIR__ . '/../actions/functions.php';
//---
use function SQLorAPI\Get\get_td_or_sql_projects;
use function SQLorAPI\Get\get_td_or_sql_categories;

$cat_titles = [];
$cat_to_camp = [];
$camp_to_cat = [];
//---
$main_cat = ''; # RTT
$main_camp = ''; # Main
//---
$camps_cat2 = [];
$camp_input_depth = [];
// $catinput_depth = [];
//---
$campaign_input_list = [];
$catinput_list = [];
//---
$categories_tab = get_td_or_sql_categories();
//---
foreach ($categories_tab as $k => $tab) {
    if (!empty($tab['category']) && !empty($tab['campaign'])) {
        //---
        $cat_titles[] = $tab['campaign'];
        //---
        $camps_cat2[$tab['campaign']] = $tab['category2'];
        //---
        $cat_to_camp[$tab['category']] = $tab['campaign'];
        $camp_to_cat[$tab['campaign']] = $tab['category'];
        //---
        $catinput_list[$tab['category']] = $tab['category'];
        $campaign_input_list[$tab['campaign']] = $tab['campaign'];
        // ---
        // $catinput_depth[$tab['category']] = $tab['depth'];
        $camp_input_depth[$tab['campaign']] = $tab['depth'];
        //---
        $default  = $tab['def'];
        if ($default == 1 || $default == '1') $main_cat = $tab['category'];
        if ($default == 1 || $default == '1') $main_camp = $tab['campaign'];
        //---
    };
};
//---
$projects_tab = get_td_or_sql_projects();
//---
$projects_title_to_id = array_column($projects_tab, 'g_id', 'g_title');
