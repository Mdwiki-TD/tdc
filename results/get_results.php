<?php

namespace Results\GetResults;

/*
Usage:

use function Results\GetResults\get_results;
use function Results\GetResults\get_cat_exists_and_missing;

*/

use function Results\GetCats\get_mdwiki_cat_members;
use function Results\GetCats\open_json_file;
use function Actions\Functions\test_print;

function get_cat_exists_and_missing($cat, $camp, $depth, $code, $use_cache = true)
{
    $members_to = get_mdwiki_cat_members($cat, $use_cache = $use_cache, $depth = $depth, $camp = $camp);
    // z("<br>members_to size:" . count($members_to));
    $members = array();
    foreach ($members_to as $mr) {
        $members[] = $mr;
    };
    test_print("members size:" . count($members));
    $json_file = __DIR__ . "/../../Translation_Dashboard/Tables/cash_exists/$code.json";

    $exists = open_json_file($json_file);

    test_print("$json_file: exists size:" . count($exists));

    // Find missing elements
    // $missing = array_diff($members, $exists);
    $missing = array();
    foreach ($members as $mem) {
        if (!in_array($mem, $exists)) $missing[] = $mem;
    };

    // Remove duplicates from $missing
    $missing = array_unique($missing);

    // Calculate length of exists
    $exs_len = count($members) - count($missing);

    $results = array(
        "len_of_exists" => $exs_len,
        "missing" => $missing
    );
    test_print("end of get_cat_exists_and_missing <br>===============================");
    return $results;
}