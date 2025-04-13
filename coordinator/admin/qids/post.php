<?php
//---
use function Actions\MdwikiSql\execute_query;
//---
$qid_table = $_POST["qid_table"] ?? '';
//---
if ($qid_table != 'qids' && $qid_table != 'qids_others') $qid_table = 'qids';
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
foreach ($_POST['rows'] ?? [] as $key => $table) {
	// '{ "ty": "qids/post", "qid_table": "qids", "rows": { "1": { "title": "23423434", "qid": "" } } }'
	//---
	$title = $table['title'] ?? '';
	$qid   = $table['qid'] ?? '';
	//---
	if (empty($qid)) $qid = null;
	//---
	if (empty($title)) continue;
	//---
	$qua = "INSERT INTO $qid_table (title, qid) SELECT ?, ?
		WHERE NOT EXISTS (SELECT 1 FROM $qid_table WHERE qid = ?)";
	//---
	$params = [$title, $qid, $qid];
	//---
	execute_query($qua, $params);
	//---
}
// ---
$dis = $_GET["dis"] ?? '';
//---
echo <<<HTML
	<div class='alert alert-success' role='alert'>Qid Saved...<br>
		return to qids page in 1 seconds
	</div>
	<meta http-equiv='refresh' content='1; url=index.php?ty=qids&dis=$dis&qid_table=$qid_table'>
HTML;
//---
