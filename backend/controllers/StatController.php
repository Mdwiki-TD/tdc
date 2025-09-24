<?php

namespace Controllers;

use Tables\Main\MainTables;
use function Results\GetCats\get_mdwiki_cat_members;
use function SQLorAPI\Funcs\get_td_or_sql_categories;
use function SQLorAPI\Funcs\get_td_or_sql_qids;

class StatController
{
    public static function getStats($params = [])
    {
        $cat = $params['cat'] ?? 'RTT';

        // Get categories for the filter dropdown
        $categories = get_td_or_sql_categories();
        $cats_titles = array_column($categories, 'category');

        // Get the data for the table
        $titles = get_mdwiki_cat_members($cat, true, 1);
        $qids_t = get_td_or_sql_qids('all');
        $sql_qids = array_column($qids_t, 'qid', 'title');

        $table_data = [];
        $summary = [
            'qid' => ['with' => 0, 'without' => 0],
            'enwiki_views' => ['with' => 0, 'without' => 0],
            'importance' => ['with' => 0, 'without' => 0],
            'word' => ['with' => 0, 'without' => 0],
            'allword' => ['with' => 0, 'without' => 0],
            'ref' => ['with' => 0, 'without' => 0],
            'allref' => ['with' => 0, 'without' => 0],
        ];

        foreach ($titles as $title) {
            $qid = $sql_qids[$title] ?? '';
            $word = MainTables::$x_Words_table[$title] ?? 0;
            $allword = MainTables::$x_All_Words_table[$title] ?? 0;
            $refs = MainTables::$x_Lead_Refs_table[$title] ?? 0;
            $all_refs = MainTables::$x_All_Refs_table[$title] ?? 0;
            $asse = MainTables::$x_Assessments_table[$title] ?? '';
            $pv = MainTables::$x_enwiki_pageviews_table[$title] ?? 0;

            $table_data[] = [
                'title' => $title,
                'qid' => $qid,
                'word' => $word,
                'allword' => $allword,
                'ref' => $refs,
                'all_ref' => $all_refs,
                'importance' => $asse,
                'enwiki_views' => $pv,
            ];

            if (empty($qid)) $summary['qid']['without']++; else $summary['qid']['with']++;
            if ($word == 0) $summary['word']['without']++; else $summary['word']['with']++;
            if ($allword == 0) $summary['allword']['without']++; else $summary['allword']['with']++;
            if ($refs == 0) $summary['ref']['without']++; else $summary['ref']['with']++;
            if ($all_refs == 0) $summary['allref']['without']++; else $summary['allref']['with']++;
            if (!isset(MainTables::$x_Assessments_table[$title])) $summary['importance']['without']++; else $summary['importance']['with']++;
            if (!isset(MainTables::$x_enwiki_pageviews_table[$title])) $summary['enwiki_views']['without']++; else $summary['enwiki_views']['with']++;
        }

        return [
            'categories' => $cats_titles,
            'table_data' => $table_data,
            'summary' => $summary,
        ];
    }
}
