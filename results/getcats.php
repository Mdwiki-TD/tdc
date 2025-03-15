<?PHP

namespace Results\GetCats;

/*
Usage:

use function Results\GetCats\start_with;
use function Results\GetCats\get_cat_from_cache;
use function Results\GetCats\get_categorymembers;
use function Results\GetCats\get_mmbrs;
use function Results\GetCats\get_mdwiki_cat_members;

*/

//---
if (isset($_REQUEST['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
};
//---
include_once __DIR__ . '/../Tables/tables.php';
include_once __DIR__ . '/../Tables/langcode.php';
include_once __DIR__ . '/../actions/functions.php';
//---
use function Actions\Functions\test_print;
use function Actions\MdwikiApi\get_mdwiki_url_with_params;
use function Actions\Functions\open_json_file;
use function Actions\Functions\start_with;

function get_cat_from_cache($cat)
{
    $tables_dir = isset($GLOBALS['tables_dir']) ? $GLOBALS['tables_dir'] : __DIR__ . '/../../td/Tables';
    // Initialize an empty array for the list
    $empty_list = array();

    // Construct the file path
    $file_path = $tables_dir . "/cats_cash/$cat.json";

    $new_list = open_json_file($file_path);

    // Check if 'list' key exists in the decoded JSON
    if (!isset($new_list['list']) || !is_array($new_list['list'])) {
        // Handle the case when 'list' key is missing or not an array
        test_print("Invalid format in JSON file $file_path");
        return $empty_list; // Return an empty list
    }
    $data = array();
    // Process the list
    foreach ($new_list['list'] as $key => $value) {
        // Check conditions
        if (!preg_match('/^(Category|File|Template|User):/', $value) && !preg_match('/\(disambiguation\)$/', $value)) {
            $data[] = $value;
        }
    }

    // Print list length
    // test_print("get_cat_from_cache: list length: " . count($data));

    return $data;
}

function get_categorymembers($cat)
{
    //---
    if (!start_with($cat, 'Category:')) {
        $cat = "Category:$cat";
    };
    //---
    $params = array(
        "action" => "query",
        "list" => "categorymembers",
        "cmtitle" => "$cat",
        "cmlimit" => "max",
        "cmtype" => "page|subcat",
        "format" => "json"
    );
    //---
    $items = array();
    //---
    $cmcontinue = 'x';
    //---
    while (!empty($cmcontinue)) {
        //---
        if ($cmcontinue != 'x') $params['cmcontinue'] = $cmcontinue;
        //---
        $resa = get_mdwiki_url_with_params($params);
        //---
        // if (!isset($resa["query"])) $resa = get_mdwiki_url_with_params($params);
        //---
        $continue   = $resa["continue"] ?? '';
        $cmcontinue = $continue["cmcontinue"] ?? ''; // "continue":{"cmcontinue":"page|434c4f42415a414d|60836",
        //---
        $query = $resa["query"] ?? array();
        $categorymembers = $query["categorymembers"] ?? array();
        $categorymembers = $categorymembers ?? array();
        //---
        // print htmlspecialchars( var_export( $categorymembers, 1 ) );
        //---
        //
        foreach ($categorymembers as $pages) {
            // echo( $pages["title"] . "\n" );
            if ($pages["ns"] == 0 or $pages["ns"] == 14) {
                $items[] = $pages["title"];
            };
        };
    };
    //---
    // $tt = array();
    // $tt['items']    = $items;
    // $tt['continue'] = $cmcontinue;
    //---
    test_print("get_categorymembers() items size:" . count($items));
    //---
    return $items;
    //---
};
//---
function get_mmbrs($cat, $use_cache = true)
{
    if ($use_cache || $_SERVER['SERVER_NAME'] == 'localhost') {
        //---
        $all = get_cat_from_cache($cat);
        //---
        if (empty($all)) $all = get_categorymembers($cat);
        //---
        return $all;
    };
    //---
    $all = get_categorymembers($cat);
    //---
    if (empty($all)) $all = get_cat_from_cache($cat);
    //---
    return $all;
}
//---
function get_mdwiki_cat_members($cat, $use_cache = true, $depth = 0, $camp = '')
{
    //---
    $titles = array();
    $cats = array();
    $cats[] = $cat;
    //---
    $depth_done = -1;
    //---
    while (count($cats) > 0 && $depth > $depth_done) {
        $cats2 = array();
        //---
        foreach ($cats as $cat1) {
            $all = get_mmbrs($cat1, $use_cache);
            //---
            foreach ($all as $title) {
                if (start_with($title, 'Category:')) {
                    $cats2[] = $title;
                } else {
                    $titles[] = $title;
                };
                //---
            };
        };
        //---
        // test_print("cats2 size:" . count($cats2));
        //---
        $depth_done++;
        //---
        $cats = $cats2;
        //---
    };
    //---
    // remove duplicates from $titles
    $titles = array_unique($titles);
    //---
    // test_print("cats size:" . count($cats));
    //---
    $newtitles = array();
    foreach ($titles as $title) {
        // find if not starts with Category:
        $test_value = preg_match('/^(File|Template|User):/', $title);
        // find if not ends with (disambiguation)
        $test_value2 = preg_match('/\(disambiguation\)$/', $title);
        //---
        if ($test_value == 0 && $test_value2 == 0) {
            $newtitles[] = $title;
        };
    };
    //---
    test_print("newtitles size:" . count($newtitles));
    test_print("end of get_mdwiki_cat_members <br>===============================");
    //---
    return $newtitles;
    //---
};
