<?php
//---
use Tables\Main\MainTables;
use function Actions\MdwikiSql\execute_query;

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
	$params2 = [$t['title'], $t['word'], $t['type'], $t['cat'], $t['lang'], $t['user'], $t['pupdate'], $t['target'], $t['title'], $t['lang'], $t['user']];
	//---
	if (isset($_REQUEST['test'])) echo "$query1<br/>$query2";
	//---
	$result2 = execute_query($query2, $params2);
	//---
	return $result2;
}

function add_to_db($title, $type, $cat, $lang, $user, $target, $pupdate)
{
	//---
	$word = MainTables::$x_Words_table[$title] ?? 0;
	if ($type == 'all') $word = MainTables::$x_All_Words_table[$title] ?? 0;
	//---
	// add them all to array
	$t = [
		'user'		=> trim($user),
		'lang'		=> trim($lang),
		'title'		=> trim($title),
		'target'	=> trim($target),
		'pupdate'	=> trim($pupdate),
		'cat'		=> trim($cat),
		'type'		=> trim($type),
		'word'		=> $word
	];
	//---
	insert_to_pages($t);
	//---
};
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
foreach ($_POST['rows'] ?? [] as $key => $table) {
	// { "id": "1", "camp": "Main", "cat1": "RTT", "cat2": "", "dep": "1" }
	//---
	$mdtitle	= $table['mdtitle'] ?? '';
	$cat		= rawurldecode($table['cat']) ?? '';
	$type		= $table['type'] ?? '';
	$user		= rawurldecode($table['user']) ?? '';
	$lang		= $table['lang'] ?? '';
	$target		= $table['target'] ?? '';
	$pupdate	= $table['pupdate'] ?? '';
	//---
	if (!empty($mdtitle) && !empty($lang) && !empty($user)) { // && !empty($target)
		//---
		add_to_db($mdtitle, $type, $cat, $lang, $user, $target, $pupdate);
		//---
	};
};
