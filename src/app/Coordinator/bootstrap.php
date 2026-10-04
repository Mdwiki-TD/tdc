<?php
// src/app/Coordinator/bootstrap.php


require_once __DIR__ . '/helpers/bootstrap.php';

// require_once __DIR__ . '/Admin/common/FormBuilder.php';
require_once __DIR__ . '/Admin/common/AbstractPostHandler.php';
require_once __DIR__ . '/Admin/common/AbstractController.php';
require_once __DIR__ . '/Admin/common/AbstractEditController.php';
require_once __DIR__ . '/Admin/common/AbstractControllerNoPost.php';

include_once __DIR__ . "/Admin/add/index.php";
include_once __DIR__ . "/Admin/admins/index.php";
include_once __DIR__ . "/Admin/campaigns/index.php";
include_once __DIR__ . "/Admin/full_translators/index.php";
include_once __DIR__ . "/Admin/last_coord/index.php";

include_once __DIR__ . "/Admin/pages_users_to_main/index.php";
include_once __DIR__ . "/Admin/pages_users_to_main/fix_page.php";

include_once __DIR__ . "/Admin/projects/index.php";
include_once __DIR__ . "/Admin/qids/index.php";
include_once __DIR__ . "/Admin/reports/index.php";
include_once __DIR__ . "/Admin/settings/index.php";
include_once __DIR__ . "/Admin/translated/index.php";
include_once __DIR__ . "/Admin/tt/index.php";
include_once __DIR__ . "/Admin/users/index.php";
include_once __DIR__ . "/Admin/users_not_inprocess/index.php";
include_once __DIR__ . "/Admin/wikirefs_options/index.php";


include_once __DIR__ . "/Admin/qids/edit_qid.php";
include_once __DIR__ . "/Admin/translated/edit_page.php";
include_once __DIR__ . "/Admin/tt/edit_translate_type.php";
include_once __DIR__ . "/Admin/users/edit_user.php";
include_once __DIR__ . "/Admin/users/msg.php";
include_once __DIR__ . "/Admin/wikirefs_options/wikirefs_options_edit.php";

require_once __DIR__ . '/Admin/index.php';
