<?php

namespace Controllers;

use function SQLorAPI\Recent\get_recent_sql;
use function SQLorAPI\Recent\get_recent_pages_users;

class LastEditsController
{
    public static function getLastEdits($params = [])
    {
        $last_table = $params['last_table'] ?? 'pages';
        $lang = $params['lang'] ?? 'All';

        $last_tables = ['pages', 'pages_users'];
        $last_table = in_array($last_table, $last_tables) ? $last_table : 'pages';

        if ($last_table == 'pages') {
            $results = get_recent_sql($lang);
        } else {
            $results = get_recent_pages_users($lang);
        }

        return $results;
    }
}
