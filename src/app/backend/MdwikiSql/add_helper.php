<?php
// src/app/coordinator/helpers/add_helper.php

namespace App\MdwikiSql\AddHelper;

use function App\MdwikiSql\execute_query;
use function App\MdwikiSql\fetch_query;

function insert_to_pages(array $pageData): bool
{
	// Replace underscores with spaces for string values
	foreach ($pageData as $key => $value) {
		if (is_string($value)) {
			$pageData[$key] = str_replace('_', ' ', $value);
		}
	}

	// Check if the record already exists in the database
	$checkQuery = <<<SQL
		SELECT 1 FROM pages
		WHERE user = ? AND title = ? AND lang = ?
		LIMIT 1;
	SQL;

	$checkParams = [$pageData['user'], $pageData['title'], $pageData['lang']];
	$exists = fetch_query($checkQuery, $checkParams);

	// If record exists, UPDATE it
	if ($exists && count($exists) > 0) {
		$updateQuery = <<<SQL
			UPDATE pages
			SET target = ?, pupdate = ?, word = ?
			WHERE user = ? AND title = ? AND lang = ? AND (target = '' OR target IS NULL);
		SQL;

		$updateParams = [
			$pageData['target'],
			$pageData['pupdate'],
			$pageData['word'],
			$pageData['user'],
			$pageData['title'],
			$pageData['lang']
		];

		if (isset($_REQUEST['test'])) {
			echo "updateQuery: $updateQuery<br/>";
		}

		return execute_query($updateQuery, $updateParams);
	}

	// If record does not exist, INSERT it
	$insertQuery = <<<SQL
		INSERT INTO pages (title, word, translate_type, cat, lang, date, user, pupdate, target, add_date)
		VALUES (?, ?, ?, ?, ?, DATE(NOW()), ?, ?, ?, NOW());
	SQL;

	$insertParams = [
		$pageData['title'],
		$pageData['word'],
		$pageData['translate_type'],
		$pageData['cat'],
		$pageData['lang'],
		$pageData['user'],
		$pageData['pupdate'],
		$pageData['target']
	];

	if (isset($_REQUEST['test'])) {
		echo "insertQuery: $insertQuery<br/>";
	}

	return execute_query($insertQuery, $insertParams);
}

/**
 * Add pages to the database and verify insertion.
 *
 * @param string $title
 * @param string $translateType
 * @param string|null $cat
 * @param string $lang
 * @param string $user
 * @param string $target
 * @param string $pupdate
 * @param int|string|null $word
 * @return bool
 */
function add_pages_to_db(
	string $title,
	string $translateType,
	?string $cat,
	string $lang,
	string $user,
	string $target,
	string $pupdate,
	int|string|null $word = null
): bool {
	$cat = (!empty($cat)) ? $cat : 'RTT';

	// Add them all to array
	$t = [
		'user'           => trim($user),
		'lang'           => trim($lang),
		'title'          => trim($title),
		'target'         => trim($target),
		'pupdate'        => trim($pupdate),
		'cat'            => trim($cat),
		'translate_type' => trim($translateType),
		'word'           => $word,
	];

	return insert_to_pages($t);
}

function checkAfterAdd(
	string $title,
	string $lang,
	string $user,
	string $target,
): bool {

	$findIt = fetch_query(
		"SELECT 1 FROM pages WHERE title = ? AND lang = ? AND user = ? AND target = ?",
		[$title, $lang, $user, $target]
	);

	return (!empty($findIt));
}
