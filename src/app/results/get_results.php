<?php

namespace App\Results\GetResults;

use function App\Results\GetCats\get_mdwiki_cat_members;
use function App\Utils\Functions\test_print;
use function App\Utils\TablesDir\open_td_tables_file;

function get_cat_exists_and_missing($cat, $depth, $code, $useCache = true)
{
    $membersTo = get_mdwiki_cat_members($cat, $useCache, $depth);
    // z("<br>membersTo size:" . count($membersTo));
    $members = [];
    foreach ($membersTo as $mr) {
        $members[] = $mr;
    };
    test_print("members size:" . count($members));

    $tablesPath = getenv("TABLES_PATH") !== false ? getenv("TABLES_PATH") : ($_ENV["TABLES_PATH"] ?? "");

    $jsonFile = "$tablesPath/cash_exists/$code.json";
    $exists = open_td_tables_file($jsonFile);

    test_print("$jsonFile: exists size:" . count($exists));

    // Find missing elements
    // $missing = array_diff($members, $exists);
    $missing = [];
    foreach ($members as $mem) {
        if (!in_array($mem, $exists)) $missing[] = $mem;
    };

    // Remove duplicates from $missing
    $missing = array_unique($missing);

    // Calculate length of exists
    $exsLen = count($members) - count($missing);

    $results = array(
        "len_of_exists" => $exsLen,
        "missing" => $missing
    );
    test_print("end of get_cat_exists_and_missing <br>===============================");
    return $results;
}
