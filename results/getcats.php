<?PHP

namespace Results\GetCats;

/*
Usage:
use function Results\GetCats\get_category_from_cache;
use function Results\GetCats\fetch_category_members;
use function Results\GetCats\get_category_members;
use function Results\GetCats\get_mdwiki_cat_members;
*/

if (isset($_REQUEST['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
};

include_once __DIR__ . '/../Tables/tables.php';
include_once __DIR__ . '/../Tables/langcode.php';
include_once __DIR__ . '/../actions/functions.php';

use function Actions\Functions\test_print;
use function Actions\MdwikiApi\get_mdwiki_url_with_params;
use function Actions\Functions\open_json_file;
use function Actions\Functions\start_with;

function get_category_from_cache($category)
{
    $tables_dir = isset($GLOBALS['tables_dir']) ? $GLOBALS['tables_dir'] : __DIR__ . '/../../td/Tables';
    $empty_list = array();
    $file_path = $tables_dir . "/cats_cash/$category.json";
    $new_list = open_json_file($file_path);
    if (!isset($new_list['list']) || !is_array($new_list['list'])) {
        test_print("Invalid format in JSON file $file_path");
        return $empty_list; // Return an empty list
    }
    $data = array();
    foreach ($new_list['list'] as $key => $value) {
        if (!preg_match('/^(Category|File|Template|User):/', $value) && !preg_match('/\(disambiguation\)$/', $value)) {
            $data[] = $value;
        }
    }
    return $data;
}
function fetch_category_members($category)
{
    if (!start_with($category, 'Category:')) {
        $category = "Category:$category";
    };
    $params = array(
        "action" => "query",
        "list" => "categorymembers",
        "cmtitle" => "$category",
        "cmlimit" => "max",
        "cmtype" => "page|subcat",
        "format" => "json"
    );
    $items = array();
    $cmcontinue = 'x';
    while (!empty($cmcontinue)) {
        if ($cmcontinue != 'x') $params['cmcontinue'] = $cmcontinue;
        $resa = get_mdwiki_url_with_params($params);
        $continue   = $resa["continue"] ?? '';
        $cmcontinue = $continue["cmcontinue"] ?? ''; // "continue":{"cmcontinue":"page|434c4f42415a414d|60836",
        $query = $resa["query"] ?? array();
        $categorymembers = $query["categorymembers"] ?? array();
        $categorymembers = $categorymembers ?? array();
        foreach ($categorymembers as $pages) {
            if ($pages["ns"] == 0 or $pages["ns"] == 14) {
                $items[] = $pages["title"];
            };
        };
    };
    test_print("fetch_category_members() items size:" . count($items));
    return $items;
};
function get_category_members($category, $use_cache = true)
{
    if ($use_cache || $_SERVER['SERVER_NAME'] == 'localhost') {
        $all = get_category_from_cache($category);
        if (empty($all)) $all = fetch_category_members($category);
        return $all;
    };
    $all = fetch_category_members($category);
    if (empty($all)) $all = get_category_from_cache($category);
    return $all;
}
function get_mdwiki_cat_members($category, $use_cache = true, $depth = 0, $camp = '')
{
    $titles = array();
    $cats = array();
    $cats[] = $category;
    $depth_done = -1;
    while (count($cats) > 0 && $depth > $depth_done) {
        $cats2 = array();
        foreach ($cats as $cat1) {
            $all = get_category_members($cat1, $use_cache);
            foreach ($all as $title) {
                if (start_with($title, 'Category:')) {
                    $cats2[] = $title;
                } else {
                    $titles[] = $title;
                };
            };
        };
        $depth_done++;
        $cats = $cats2;
    };
    $titles = array_unique($titles);
    $newtitles = array();
    foreach ($titles as $title) {
        $test_value = preg_match('/^(File|Template|User):/', $title);
        $test_value2 = preg_match('/\(disambiguation\)$/', $title);
        if ($test_value == 0 && $test_value2 == 0) {
            $newtitles[] = $title;
        };
    };
    test_print("newtitles size:" . count($newtitles));
    test_print("end of get_mdwiki_cat_members <br>===============================");
    return $newtitles;
};
