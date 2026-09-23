<?php

namespace App\Tables\SqlTables;


/*
(\$)(full_translates|no_lead_translates|cat_titles|cat_to_camp|camp_to_cat|main_cat|main_camp|camps_cat2|camp_input_depth|campaign_input_list|catinput_list|projects_title_to_id)\b

TablesSql::$1s_$2

use App\Tables\SqlTables\TablesSql;

include_once __DIR__ . '/Tables/sql_tables.php';
*/

use function App\SQLorAPI\Funcs\get_td_or_sql_projects;
use function App\SQLorAPI\Funcs\get_td_or_sql_categories;

class TablesSql
{
    public static $sFullTranslates = [];
    public static $sNoLeadTranslates = [];

    public static $sCatTitles = [];
    public static $sCatToCamp = [];

    public static $sMainCat = ''; # RTT
    public static $sMainCamp = ''; # Main

    public static $sCampInputDepth = [];
    // public static $catinputDepth = [];

    public static $sCampaignInputList = [];
    public static $sProjectsTitleToId = [];
}

$categoriesTab = get_td_or_sql_categories();

foreach ($categoriesTab as $k => $tab) {
    if (!empty($tab['category']) && !empty($tab['campaign'])) {

        TablesSql::$sCatTitles[] = $tab['campaign'];

        TablesSql::$sCatToCamp[$tab['category']] = $tab['campaign'];

        TablesSql::$sCampaignInputList[$tab['campaign']] = $tab['campaign'];

        // $catinputDepth[$tab['category']] = $tab['depth'];
        TablesSql::$sCampInputDepth[$tab['campaign']] = $tab['depth'];

        $isDefault = $tab['is_default'];
        if ($isDefault == 1 || $isDefault == '1') TablesSql::$sMainCat = $tab['category'];
        if ($isDefault == 1 || $isDefault == '1') TablesSql::$sMainCamp = $tab['campaign'];
    };
};

$projectsTab = get_td_or_sql_projects();

TablesSql::$sProjectsTitleToId = array_column($projectsTab, 'g_id', 'g_title');
