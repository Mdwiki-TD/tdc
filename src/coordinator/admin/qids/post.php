<?php
// ---
use function Utils\Html\div_alert; // echo div_alert($texts, 'success');
use function APICalls\MdwikiSql\execute_query;
use function APICalls\MdwikiSql\fetch_query;
use function APICalls\MdwikiSql\check_one;
use function TDWIKI\csrf\verify_csrf_token;

// ---
echo '</div><script>
    $("#mainnav").hide();
    $("#maindiv").hide();
</script>';
// ---
$qid_table = $_GET["qid_table"] ?? '';
// ---
if ($qid_table != 'qids' && $qid_table != 'qids_others') $qid_table = 'qids';
// ---
$texts = [];
$errors = [];

function add_it($id, $title, $qid, $qid_table)
{
	$qua = "INSERT INTO $qid_table (title, qid) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM $qid_table WHERE (title = ? OR qid = ?))";
	// ---
	$params = [$title, $qid, $title, $qid];
	// ---
	if (!empty($id)) {
		$qua = "UPDATE $qid_table SET title = ?, qid = ? WHERE id = ? ";
		$params = [$title, $qid, $id];
	}
	// ---
	execute_query($qua, $params);
	// ---
	if (!empty($qid)) {
		$qua2 = <<<SQL
			UPDATE $qid_table SET qid = ?
			WHERE title = ? and (qid = '' OR qid IS NULL);
		SQL;
		// ---
		execute_query($qua2, [$qid, $title]);
	}
}

function work_one_rows($qid, $id, $title, $qid_table)
{
	global $texts, $errors;
	// ---
	add_it($id, $title, $qid, $qid_table);
	// ---
	$qid_of_title = check_one($select = "qid", $where = "title", $value = $title, $table = $qid_table);
	// ---
	if (!empty($qid_of_title) && $qid_of_title == $qid) {
		$texts[] = "Data Changes successfully of title: $title, Qid: $qid";
	} else {
		$errors[] = "Failed to chanhe data of title: $title, Qid: $qid. Found: qid in db:$qid_of_title";
	}
}

function work_one_rows_add_new($qid, $title, $qid_table)
{
	global $texts, $errors;
	// ---
	add_it("", $title, $qid, $qid_table);
	// ---
	$qid_of_title = check_one($select = "qid", $where = "title", $value = $title, $table = $qid_table);
	// ---
	if (!empty($qid_of_title) && $qid_of_title == $qid) {
		$texts[] = "Qid added successfully for title: $title.";
	} else {
		$errors[] = "Failed to add Qid for title: $title. qid_of_title:$qid_of_title";
	}
}
// ---
if (verify_csrf_token()) {
	// ---
	// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
	// ---
	foreach ($_POST['rows'] ?? [] as $key => $table) {
		// '{ "ty": "qids/post", "qid_table": "qids", "rows": { "1": { "title": "23423434", "qid": "" } } }'
		// ---
		$title = trim($table['title'] ?? '');
		$qid   = trim($table['qid'] ?? '');
		$id    = $table['id'] ?? '';
		// ---
		if (empty($title)) {
			$errors[] = "Title is required. qid=($qid)";
			continue;
		}
		// ---
		if (empty($qid)) {
			$errors[] = "Qid is required. title=($title)";
			continue;
		}
		// ---
		if ($qid && !empty($qid)) {
			$tx_tab = check_one($select = "*", $where = "qid", $value = $qid, $table = $qid_table);
			// ---
			if ($tx_tab) {
				$tx_id = $tx_tab['id'];
				$title_of_qid = $tx_tab['title'];
				// ---
				if (!empty($id) && $tx_id != $id) {
					$errors[] = "Qid:($qid) already used in database with with id:($tx_id).";
					continue;
				}
				// ---
				if (!empty($title_of_qid) && empty($id) && $title_of_qid != $title) {
					$errors[] = "Qid:($qid) already used in database with title:($title_of_qid).";
					continue;
				}
			}
		}
		// ---
		$tt_tab = check_one($select = "*", $where = "title", $value = $title, $table = $qid_table);
		// ---
		if ($tt_tab) {
			$qid_of_title5 = $tt_tab['qid'];
			$tt_id = $tt_tab['id'];
			// ---
			if (!empty($id) && $tt_id != $id) {
				$errors[] = "Title:($title) already used in database with qid:($qid_of_title5), new qid:($qid)";
				continue;
			}
			// ---
			if (empty($id) && !empty($qid_of_title5) && $qid_of_title5 != $qid) {
				$errors[] = "Title:($title) already used in database with qid:($qid_of_title5), new qid:($qid)";
				continue;
			}
			// ---
		}
		// ---
		if (empty($id)) {
			work_one_rows_add_new($qid, $title, $qid_table);
		} else {
			work_one_rows($qid, $id, $title, $qid_table);
		}
	}
	// ---
	if (!empty($texts)) {
		$texts[] = "table:($qid_table)";
	} elseif (!empty($errors)) {
		$errors[] = "table:($qid_table)";
	}
	// ---
	echo div_alert($texts, 'success');
	echo div_alert($errors, 'danger');
	// ---
	// echo div_alert(["return to Qids page in 1 seconds"]);
};
// ---
echo <<<HTML
	<div class="aligncenter">
		<a class="btn btn-outline-primary" onclick="window.close()">Close</a>
	</div>
HTML;
