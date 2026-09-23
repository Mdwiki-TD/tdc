<?php
// src/app/coordinator/admin/qids/post.php

namespace App\Coordinator\Admin\Qids;

use function App\Utils\Html\div_alert;
use function App\APICalls\MdwikiSql\execute_query;
use function App\APICalls\MdwikiSql\check_one;
use function App\csrf\verify_csrf_token;
use App\User\CurrentUser;

if (!CurrentUser::getInstance()->isCoordinator()) {
	header('Location: /index.php');
	exit;
};

echo '</div><script>
    $("#mainnav").hide();
    $("#maindiv").hide();
</script>';

$qidTable = $_GET["qid_table"] ?? '';

if ($qidTable != 'qids' && $qidTable != 'qids_others') $qidTable = 'qids';

$texts = [];
$errors = [];

function add_it($id, $title, $qid, $qidTable)
{
	$qua = "INSERT INTO $qidTable (title, qid) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM $qidTable WHERE (title = ? OR qid = ?))";

	$params = [$title, $qid, $title, $qid];

	if (!empty($id)) {
		$qua = "UPDATE $qidTable SET title = ?, qid = ? WHERE id = ? ";
		$params = [$title, $qid, $id];
	}

	execute_query($qua, $params);

	if (!empty($qid)) {
		$qua2 = <<<SQL
			UPDATE $qidTable SET qid = ?
			WHERE title = ? and (qid = '' OR qid IS NULL);
		SQL;

		execute_query($qua2, [$qid, $title]);
	}
}

function work_one_rows($qid, $id, $title, $qidTable, &$texts, &$errors)
{

	add_it($id, $title, $qid, $qidTable);

	$qidOfTitle = check_one($select = "qid", $where = "title", $value = $title, $table = $qidTable);

	if (!empty($qidOfTitle) && $qidOfTitle == $qid) {
		$texts[] = "Data Changes successfully of title: $title, Qid: $qid";
	} else {
		$errors[] = "Failed to chanhe data of title: $title, Qid: $qid. Found: qid in db:$qidOfTitle";
	}
}

function work_one_rows_add_new($qid, $title, $qidTable, &$texts, &$errors)
{

	add_it("", $title, $qid, $qidTable);

	$qidOfTitle = check_one($select = "qid", $where = "title", $value = $title, $table = $qidTable);

	if (!empty($qidOfTitle) && $qidOfTitle == $qid) {
		$texts[] = "Qid added successfully for title: $title.";
	} else {
		$errors[] = "Failed to add Qid for title: $title. qid_of_title:$qidOfTitle";
	}
}

$closeBtn = <<<HTML
	<div class="aligncenter">
		<a class="btn btn-outline-primary" onclick="window.close()">Close</a>
	</div>
HTML;


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	exit;
}

if (!verify_csrf_token()) {
	echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
	echo $closeBtn;
	return;
}

// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

foreach ($_POST['rows'] ?? [] as $key => $table) {
	// '{ "ty": "qids/post", "qid_table": "qids", "rows": { "1": { "title": "23423434", "qid": "" } } }'

	$title = trim($table['title'] ?? '');
	$qid   = trim($table['qid'] ?? '');
	$id    = $table['id'] ?? '';

	if (empty($title)) {
		$errors[] = "Title is required. qid=($qid)";
		continue;
	}

	if (empty($qid)) {
		$errors[] = "Qid is required. title=($title)";
		continue;
	}

	$txTab = check_one($select = "*", $where = "qid", $value = $qid, $table = $qidTable);

	if ($txTab) {
		$txId = $txTab['id'];
		$titleOfQid = $txTab['title'];

		if (!empty($id) && $txId != $id) {
			$errors[] = "Qid:($qid) already used in database with with id:($txId).";
			continue;
		}

		if (!empty($titleOfQid) && empty($id) && $titleOfQid != $title) {
			$errors[] = "Qid:($qid) already used in database with title:($titleOfQid).";
			continue;
		}
	}

	$ttTab = check_one($select = "*", $where = "title", $value = $title, $table = $qidTable);

	if ($ttTab) {
		$qidOfTitle5 = $ttTab['qid'];
		$ttId = $ttTab['id'];

		if (!empty($id) && $ttId != $id) {
			$errors[] = "Title:($title) already used in database with qid:($qidOfTitle5), new qid:($qid)";
			continue;
		}

		if (empty($id) && !empty($qidOfTitle5) && $qidOfTitle5 != $qid) {
			$errors[] = "Title:($title) already used in database with qid:($qidOfTitle5), new qid:($qid)";
			continue;
		}

	}

	if (empty($id)) {
		work_one_rows_add_new($qid, $title, $qidTable, $texts, $errors);
	} else {
		work_one_rows($qid, $id, $title, $qidTable, $texts, $errors);
	}
}

if (!empty($texts)) {
	$texts[] = "table:($qidTable)";
} elseif (!empty($errors)) {
	$errors[] = "table:($qidTable)";
}

echo div_alert($texts, 'success');
echo div_alert($errors, 'danger');

echo $closeBtn;
