<?php

namespace App\Results\GetCats;

use function App\Utils\test_print;
use function App\APICalls\MdwikiApi\get_mdwiki_url_with_params;
use function App\Utils\TablesDir\open_td_tables_file;

function get_category_from_cache(string $category): array
{
    $tablesPath = getenv("TABLES_PATH") !== false ? getenv("TABLES_PATH") : ($_ENV["TABLES_PATH"] ?? "");

    $filePath = "$tablesPath/cats_cash/$category.json";

    $data = open_td_tables_file($filePath);

    if (!isset($data['list']) || !is_array($data['list'])) {
        test_print("Invalid format in JSON file: $filePath");
        return [];
    }

    // تصفية العناصر غير المرغوب فيها
    $data2 = array_filter($data['list'], function ($item) {
        return !preg_match('/^(Category|File|Template|User):/', $item) && !preg_match('/\(disambiguation\)$/', $item);
    });

    return $data2;
}

function fetch_category_members(string $category): array
{
    if (!str_starts_with($category, 'Category:')) {
        $category = "Category:$category";
    }

    $params = [
        "action" => "query",
        "list" => "categorymembers",
        "cmtitle" => $category,
        "cmlimit" => "max",
        "cmtype" => "page|subcat",
        "format" => "json"
    ];

    $items = [];
    $cmcontinue = null;

    do {
        if ($cmcontinue) {
            $params['cmcontinue'] = $cmcontinue;
        }

        $response = get_mdwiki_url_with_params($params);
        $query = $response['query']['categorymembers'] ?? [];
        $cmcontinue = $response['continue']['cmcontinue'] ?? null;

        foreach ($query as $member) {
            if (in_array($member['ns'], [0, 14])) { // Namespace 0: Pages, 14: Categories
                $items[] = $member['title'];
            }
        }
    } while ($cmcontinue);

    test_print("Fetched category members count: " . count($items));
    return $items;
}

function get_category_members(string $category, bool $useCache = true): array
{
    if ($useCache) {
        $cachedMembers = get_category_from_cache($category);
        if (!empty($cachedMembers)) {
            return $cachedMembers;
        }
    }

    $all = fetch_category_members($category);

    if (empty($all) && !$useCache) {
        $all = get_category_from_cache($category);
    }
    return $all;
}

function get_mdwiki_cat_members(string $category, bool $useCache = true, int $depth = 0): array
{
    $titles = [];
    $categoriesToProcess = [$category];
    $currentDepth = 0;

    while (!empty($categoriesToProcess) && $currentDepth <= $depth) {
        $nextCategories = [];

        foreach ($categoriesToProcess as $currentCategory) {
            $members = get_category_members($currentCategory, $useCache);

            foreach ($members as $member) {
                if (str_starts_with($member, 'Category:')) {
                    $nextCategories[] = $member;
                } else {
                    $titles[] = $member;
                }
            }
        }

        $categoriesToProcess = array_unique($nextCategories);
        $currentDepth++;
    }

    // تصفية النتائج النهائية
    $filteredTitles = array_filter($titles, function ($title) {
        return !preg_match('/^(File|Template|User):/', $title) && !preg_match('/\(disambiguation\)$/', $title);
    });

    $uniqueTitles = array_unique($filteredTitles);
    test_print("Final titles count: " . count($uniqueTitles));
    test_print("End of get_mdwiki_cat_members <br>===============================");
    return $uniqueTitles;
}
