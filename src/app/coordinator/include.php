<?php
// src/app/coordinator/include.php


require_once __DIR__ . '/admin/common/AbstractPostHandler.php';
require_once __DIR__ . '/admin/common/AbstractController.php';
require_once __DIR__ . '/admin/common/AbstractEditController.php';
require_once __DIR__ . '/admin/common/AbstractControllerNoPost.php';
// require_once __DIR__ . '/admin/common/FormBuilder.php';
require_once __DIR__ . '/helpers/include.php';

include_once __DIR__ . "/admin/add/index.php";
include_once __DIR__ . "/admin/admins/index.php";
include_once __DIR__ . "/admin/campaigns/index.php";
include_once __DIR__ . "/admin/full_translators/index.php";
include_once __DIR__ . "/admin/last_coord/index.php";
include_once __DIR__ . "/admin/pages_users_to_main/index.php";
include_once __DIR__ . "/admin/projects/index.php";
include_once __DIR__ . "/admin/qids/index.php";
include_once __DIR__ . "/admin/reports/index.php";
include_once __DIR__ . "/admin/settings/index.php";
include_once __DIR__ . "/admin/translated/index.php";
include_once __DIR__ . "/admin/tt/index.php";
include_once __DIR__ . "/admin/users/index.php";
include_once __DIR__ . "/admin/users_not_inprocess/index.php";
include_once __DIR__ . "/admin/wikirefs_options/index.php";

require_once __DIR__ . '/admin/index.php';
