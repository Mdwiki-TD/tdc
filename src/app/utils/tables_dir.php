<?php

namespace App\Utils\TablesDir;

use function App\Utils\Functions\test_print;

function open_td_tables_file($filePath)
{

    if (!is_file($filePath)) {
        test_print("---- open_td_tables_file: file $filePath does not exist");
        return [];
    }
    $contents = file_get_contents($filePath);

    if ($contents === false) {
        test_print("---- Failed to read file contents from $filePath");
        return [];
    }

    $result = json_decode($contents, true);

    if ($result === null || $result === false) {
        test_print("---- Failed to decode JSON from $filePath");
        $result = [];
    } else {
        $len = count($result);
        if (isset($result['list'])) $len = count($result['list']);

        test_print("---- open_td_tables_file File: $filePath: Exists size: $len");
    }

    return $result;
}
