<?PHP

namespace Results\GetCats;

/*
Usage:
use function Results\GetCats\get_category_from_cache;
use function Results\GetCats\fetch_category_members;
use function Results\GetCats\get_category_members;
use function Results\GetCats\get_mdwiki_cat_members;
*/

if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
};

use function Utils\Functions\test_print;
use function APICalls\MdwikiApi\get_mdwiki_url_with_params;
use function Utils\Functions\start_with;
use function Utils\TablesDir\open_td_Tables_file;

function get_category_from_cache(string $category): array
{
    $file_path = "cats_cash/{$category}.json";

    $data = open_td_Tables_file($file_path);

    if (!isset($data['list']) || !is_array($data['list'])) {
        test_print("Invalid format in JSON file: $file_path");
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
    if (!start_with($category, 'Category:')) {
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

function get_category_members(string $category, bool $use_cache = true): array
{
    if ($use_cache) {
        $cached_members = get_category_from_cache($category);
        if (!empty($cached_members)) {
            return $cached_members;
        }
    }

    $all = fetch_category_members($category);

    if (empty($all) && !$use_cache) {
        $all = get_category_from_cache($category);
    }
    return $all;
}

function get_mdwiki_cat_members(string $category, bool $use_cache = true, int $depth = 0, string $camp = ''): array
{
    $titles = [];
    $categories_to_process = [$category];
    $current_depth = 0;

    while (!empty($categories_to_process) && $current_depth <= $depth) {
        $next_categories = [];

        foreach ($categories_to_process as $current_category) {
            $members = get_category_members($current_category, $use_cache);

            foreach ($members as $member) {
                if (start_with($member, 'Category:')) {
                    $next_categories[] = $member;
                } else {
                    $titles[] = $member;
                }
            }
        }

        $categories_to_process = array_unique($next_categories);
        $current_depth++;
    }

    // تصفية النتائج النهائية
    $filtered_titles = array_filter($titles, function ($title) {
        return !preg_match('/^(File|Template|User):/', $title) && !preg_match('/\(disambiguation\)$/', $title);
    });

    $unique_titles = array_unique($filtered_titles);
    test_print("Final titles count: " . count($unique_titles));
    test_print("End of get_mdwiki_cat_members <br>===============================");
    return $unique_titles;
}
