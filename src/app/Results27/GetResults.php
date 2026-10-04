<?php

namespace App\Results27;

use App\Logger;
use App\Settings;

class GetResults
{
    public static function get_cat_exists_and_missing($cat, $depth, $code, $useCache = true)
    {
        $membersTo = GetCats::get_mdwiki_cat_members($cat, $useCache, $depth);
        $members = [];
        foreach ($membersTo as $mr) {
            $members[] = $mr;
        }
        Logger::debug("members size:" . count($members));

        $jsonFile = "cash_exists/$code.json";
        $exists = Settings::getInstance()->OpenTablesPathFile($jsonFile);

        Logger::debug("$jsonFile: exists size:" . count($exists));

        $missing = [];
        foreach ($members as $mem) {
            if (!in_array($mem, $exists)) $missing[] = $mem;
        }

        $missing = array_unique($missing);

        $exsLen = count($members) - count($missing);

        $results = array(
            "len_of_exists" => $exsLen,
            "missing" => $missing
        );
        Logger::debug("end of get_cat_exists_and_missing <br>===============================");
        return $results;
    }
}
