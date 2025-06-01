<?php
//---
use function Actions\MdwikiSql\execute_query;
use function Actions\Html\div_alert; // echo div_alert($texts, 'success');
use function TDWIKI\csrf\verify_csrf_token;
//---
// var_export(json_encode($_POST ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//---
$errors = [];
$texts = [];
//---
if (verify_csrf_token()) {

    foreach ($_POST['rows'] ?? [] as $key => $table) {
        // language_settings (lang_code, move_dots, expend, add_en_lang)
        // { "id": "3", "lang_code": "ab", "move_dots": "0", "expend": "1", "add_en_lang": "1", "delete": "1" }
        //---
        $u_id = $table['id'] ?? '';
        $del  = $table['delete'] ?? '';
        //---
        $lang_code   = $table['lang_code'] ?? '';
        $lang_code = trim($lang_code);
        //---
        if (!empty($del) && !empty($u_id)) {
            $qua2 = "DELETE FROM language_settings WHERE id = ?";
            // ---
            $result = execute_query($qua2, $params = [$u_id]);
            // ---
            if ($result === false) {
                $errors[] = "Failed to delete language $lang_code.";
                continue;
            }
            // ---
            $texts[] = "language $lang_code deleted.";
            // ---
            continue;
        };
        //---
        $add_en_lang = $table['add_en_lang'] ?? '';
        $move_dots   = $table['move_dots'] ?? '';
        $expend      = $table['expend'] ?? '';
        //---
        $is_new      = $table['is_new'] ?? '';
        //---
        if (!empty($lang_code) && empty($u_id) && $is_new == 'yes') {
            $qua = "INSERT INTO users_no_inprocess (user) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM users_no_inprocess WHERE user = ?)";
            //---
            $texts[] = "language $lang_code Added.";
            //---
            $result = execute_query($qua, $params = [$lang_code, $lang_code]);
            //---
            if ($result === false) {
                $errors[] = "Failed to add user $lang_code.";
            }
        };
    }
    // ---
    echo div_alert($texts, 'success');
    echo div_alert($errors, 'danger');
}
