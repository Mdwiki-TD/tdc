<?php
//---
use function Actions\MdwikiSql\execute_query;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
$default_cat = $_POST['default_cat'] ?? '';
//---
foreach ($_POST['rows'] ?? [] as $key => $table) {
	// ---
	// { "1": { "id": "1", "camp": "Main", "cat1": "RTT", "cat2": "", "dep": "1" }, ... }
	//---
	$ido  = $table['id'] ?? '';
	//---
	if (empty($ido)) continue;
	//---
	$del  = $table['del'] ?? '';
	//---
	if (!empty($del) && $del != "0") {
		$qua2 = "DELETE FROM categories WHERE id = ?";
		execute_query($qua2, [$del]);
		continue;
	};
	//---
	$camp = $table['camp'];
	$cat1 = $table['cat1'];
	$cat2 = $table['cat2'];
	$dep  = $table['dep'];
	//---
	$def = ($default_cat == $ido) ? 1 : 0;
	//---
	$qua = "UPDATE categories
		SET
			campaign = ?,
			category = ?,
			category2 = ?,
			depth = ?,
			def = ?
		WHERE
			id = ?
	";
	//---
	$params = [$camp, $cat1, $cat2, $dep, $def, $ido];
	//---
	// if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) echo "<br>$qua<br>";
	//---
	execute_query($qua, $params);
};
// ---
if (isset($_POST['new'])) {
	// { "2": { "camp": "2", "cat1": "", "cat2": "", "dep": "0" }, "3": ... }
	// ---
	foreach ($_POST['new'] as $key => $table) {
		// { "id": "1", "camp": "Main", "cat1": "RTT", "cat2": "", "dep": "1" }
		//---
		$ido  = $table['id'] ?? '';
		$camp = $table['camp'];
		$cat1 = $table['cat1'];
		$cat2 = $table['cat2'];
		$dep  = $table['dep'];
		//---
		$def = ($default_cat == $ido) ? 1 : 0;
		//---
		$qua = "INSERT INTO categories (category, campaign, depth, def, category2) SELECT ?, ?, ?, ?, ?";
		$params = [$cat1, $camp, $dep, $def, $cat2];
		//---
		if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
			echo "<br>$qua<br>";
		};
		//---
		execute_query($qua, $params);
	};
};
