<?PHP
//---
// use function Actions\MdwikiSql\update_settings;
use function Actions\MdwikiSql\update_settings_value;
use function TDWIKI\csrf\verify_csrf_token;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
if (verify_csrf_token()) {
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
        if (empty($id) || $value == "") continue;
        //---
        $re = update_settings_value($id, $value);
    }
}
