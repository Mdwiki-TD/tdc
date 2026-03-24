<?PHP
//---
// use function APICalls\MdwikiSql\update_settings;
use function APICalls\MdwikiSql\update_settings_value;
use function TDWIKI\csrf\verify_csrf_token;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

$close_btn = <<<HTML
	<div class="aligncenter">
		<a class="btn btn-outline-primary" onclick="window.close()">Close</a>
	</div>
HTML;

if (!verify_csrf_token()) {
    echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
    echo $close_btn;
    return;
}
foreach ($_POST['rows'] ?? [] as $key => $table) {
    // { "id": "2", "title": "translation_button_in_progress_table", "displayed": "Display translation button in progress table?", "value": "1", "type": "check" }
    //---
    $id        = $table["id"] ?? '';
    $title     = $table["title"] ?? '';
    $displayed = $table["displayed"] ?? '';
    $value     = $table["value"] ?? '';
    $type      = $table["type"] ?? '';
    //---
    // if (empty($title) || empty($displayed) || empty($type)) continue;
    // $re = update_settings($id, $title, $displayed, $value, $type);
    //---
    if (empty($id) || empty($value)) continue;
    //---
    $re = update_settings_value($id, $value);
}
