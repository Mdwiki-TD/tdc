<?php
//---
namespace Add\AddPost;
/*
require_once __DIR__ . '/add_post.php';
use function Add\AddPost\add_pages_to_db;
*/
//---
use Tables\Main\MainTables;
use function Actions\MdwikiSql\execute_query;
use function Actions\MdwikiSql\fetch_query;

function insert_to_pages($t)
{
	//---
	$query1 = <<<SQL
        UPDATE pages
            SET target = ?, pupdate = ?, word = ?
        WHERE user = ? AND title = ? AND lang = ? and (target = '' OR target IS NULL);
    SQL;
	//---
	$params1 = [$t['target'], $t['pupdate'], $t['word'], $t['user'], $t['title'], $t['lang']];
	//---
	$_result1 = execute_query($query1, $params1);
	//---
	$query2 = <<<SQL
        INSERT INTO pages (title, word, translate_type, cat, lang, date, user, pupdate, target, add_date)
            SELECT ?, ?, ?, ?, ?, DATE(NOW()), ?, ?, ?, now()
        WHERE NOT EXISTS (SELECT 1 FROM pages WHERE title = ? AND lang = ? AND user = ? );
    SQL;
	//---
	$params2 = [$t['title'], $t['word'], $t['translate_type'], $t['cat'], $t['lang'], $t['user'], $t['pupdate'], $t['target'], $t['title'], $t['lang'], $t['user']];
	//---
	if (isset($_REQUEST['test'])) echo "$query1<br/>$query2";
	//---
	$result2 = execute_query($query2, $params2);
	//---
	return $result2;
}

function add_pages_to_db($title, $translate_type, $cat, $lang, $user, $target, $pupdate, $word)
{
	//---
	$translate_type = (!empty($translate_type)) ? $translate_type : 'lead';
	$cat = (!empty($cat)) ? $cat : 'RTT';
	//---
	if (empty($word)) {
		$word = MainTables::$x_Words_table[$title] ?? 0;
		if ($translate_type == 'all') $word = MainTables::$x_All_Words_table[$title] ?? 0;
	}
	//---
	// add them all to array
	$t = [
		'user'		=> trim($user),
		'lang'		=> trim($lang),
		'title'		=> trim($title),
		'target'	=> trim($target),
		'pupdate'	=> trim($pupdate),
		'cat'		=> trim($cat),
		'translate_type' => trim($translate_type),
		'word'		=> $word
	];
	//---
	insert_to_pages($t);
	//---
	$find_it = fetch_query("SELECT * FROM pages WHERE title = ? AND lang = ? AND user = ? AND target = ?", [$title, $lang, $user, $target]);
	//---
	$insert_done = (!empty($find_it)) ? true : false;
	//---
	return $insert_done;
}
