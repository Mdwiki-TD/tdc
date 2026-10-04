<?php

namespace App\Tables\SqlTables;

use App\SQLorAPI\TitlesTable;
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

    public static $sCampaignInputList = [];
    public static $sProjectsTitleToId = [];

    private static bool $initialized = false;

    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        $categoriesTab = (CategoriesTable::getInstance())->getCategories();

        foreach ($categoriesTab as $k => $tab) {
            if (!empty($tab['category']) && !empty($tab['campaign'])) {
                self::$sCatTitles[] = $tab['campaign'];
                self::$sCatToCamp[$tab['category']] = $tab['campaign'];
                self::$sCampaignInputList[$tab['campaign']] = $tab['campaign'];
                self::$sCampInputDepth[$tab['category']] = $tab['depth'];

                $isDefault = $tab['is_default'];
                if ($isDefault == 1 || $isDefault == '1') self::$sMainCat = $tab['category'];
                if ($isDefault == 1 || $isDefault == '1') self::$sMainCamp = $tab['campaign'];
            }
        }

        $projectsTab = (TitlesTable::getInstance())->getProjects();
        self::$sProjectsTitleToId = array_column($projectsTab, 'g_id', 'g_title');
    }
}

TablesSql::init();
