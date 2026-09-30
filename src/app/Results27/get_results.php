<?php

namespace App\Results\GetResults;

use App\Logger;
use App\Settings;

use function App\Results\GetCats\get_mdwiki_cat_members;

function get_cat_exists_and_missing($cat, $depth, $code, $useCache = true)
{
    $membersTo = get_mdwiki_cat_members($cat, $useCache, $depth);
    // z("<br>membersTo size:" . count($membersTo));
    $members = [];
    foreach ($membersTo as $mr) {
        $members[] = $mr;
    };
    Logger::debug("members size:" . count($members));

    $jsonFile = "cash_exists/$code.json";
    $exists = Settings::getInstance()->OpenTablesPathFile($jsonFile);

    Logger::debug("$jsonFile: exists size:" . count($exists));

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
    Logger::debug("end of get_cat_exists_and_missing <br>===============================");
    return $results;
}
