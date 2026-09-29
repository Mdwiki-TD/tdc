<?php

namespace App\Tables\SqlTables;

use function App\SQLorAPI\Funcs\get_td_or_sql_projects;
use App\SQLorAPI\CategoriesTable;

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

$categoriesTab = (new CategoriesTable())->getCategories();

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
