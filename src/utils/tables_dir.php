<?PHP

namespace Utils\TablesDir;
/*

use function Utils\TablesDir\open_td_Tables_file;

*/

if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
};

use function Utils\Functions\test_print;

function open_td_Tables_file($path)
{
    //---
    $tables_dir = getenv("HOME") . '/public_html/td/Tables';
    //---
    // if (substr($tables_dir, 0, 2) == 'I:') { $tables_dir = 'I:/mdwiki/mdwiki/public_html/td/Tables'; }
    //---
    $file_path = "$tables_dir/$path";
    //---
    if (!is_file($file_path)) {
        test_print("---- open_td_Tables_file: file $file_path does not exist");
        return [];
    }
    $contents = file_get_contents($file_path);

    if ($contents === null || $contents === false) {
        test_print("---- Failed to read file contents from $file_path");
        return [];
    }

    $result = json_decode($contents, true);

    if ($result === null || $result === false) {
        test_print("---- Failed to decode JSON from $file_path");
        $result = [];
    } else {
        $len = count($result);
        if (isset($result['list'])) $len = count($result['list']);
        // ---
        test_print("---- open_td_Tables_file File: $file_path: Exists size: $len");
    }

    return $result;
}
