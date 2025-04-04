<?PHP

namespace Tables\SqlTables;

//---
/*
(\$)(full_translates|no_lead_translates|cat_titles|cat_to_camp|camp_to_cat|main_cat|main_camp|camps_cat2|camp_input_depth|campaign_input_list|catinput_list|projects_title_to_id)\b

TablesSql::$1s_$2

use Tables\SqlTables\TablesSql;

include_once __DIR__ . '/Tables/sql_tables.php';
*/
//---
// include_once __DIR__ . '/../actions/functions.php';
//---
use function SQLorAPI\Get\get_td_or_sql_projects;
use function SQLorAPI\Get\get_td_or_sql_categories;

class TablesSql
{
    public static $s_full_translates = [];
    public static $s_no_lead_translates = [];
    //---
    public static $s_cat_titles = [];
    public static $s_cat_to_camp = [];
    public static $s_camp_to_cat = [];
    //---
    public static $s_main_cat = ''; # RTT
    public static $s_main_camp = ''; # Main
    //---
    public static $s_camps_cat2 = [];
    public static $s_camp_input_depth = [];
    // public static $catinput_depth = [];
    //---
    public static $s_campaign_input_list = [];
    public static $s_catinput_list = [];
    public static $s_settings = [];
    public static $s_projects_title_to_id = [];
}

$categories_tab = get_td_or_sql_categories();
//---
foreach ($categories_tab as $k => $tab) {
    if (!empty($tab['category']) && !empty($tab['campaign'])) {
        //---
        TablesSql::$s_cat_titles[] = $tab['campaign'];
        //---
        TablesSql::$s_camps_cat2[$tab['campaign']] = $tab['category2'];
        //---
        TablesSql::$s_cat_to_camp[$tab['category']] = $tab['campaign'];
        TablesSql::$s_camp_to_cat[$tab['campaign']] = $tab['category'];
        //---
        TablesSql::$s_catinput_list[$tab['category']] = $tab['category'];
        TablesSql::$s_campaign_input_list[$tab['campaign']] = $tab['campaign'];
        // ---
        // $catinput_depth[$tab['category']] = $tab['depth'];
        TablesSql::$s_camp_input_depth[$tab['campaign']] = $tab['depth'];
        //---
        $default  = $tab['def'];
        if ($default == 1 || $default == '1') TablesSql::$s_main_cat = $tab['category'];
        if ($default == 1 || $default == '1') TablesSql::$s_main_camp = $tab['campaign'];
        //---
    };
};
//---
$projects_tab = get_td_or_sql_projects();
//---
TablesSql::$s_projects_title_to_id = array_column($projects_tab, 'g_id', 'g_title');
