<?php

use function Utils\Functions\test_print;
use function Utils\HtmlSide\create_side;

echo <<<HTML
	<!-- </div> -->
	<script>$("#coord").addClass("active");</script>
	<!-- <div id="maindiv" class="container-fluid"> -->
HTML;

function echo_card_start($file_name, $ty, ?CurrentUser $currentUser = null)
{
	$currentUser = $currentUser ?? CurrentUser::getInstance();
	$sidebar = create_side($file_name, $ty, $currentUser->isCoordinator());
	echo <<<HTML
		<div class='row content'>
			<!-- <div class='col-md-2 px-0' style="width: 10.66666667%;"> -->
			<div class='col-md-2 px-0 colmd2 border'>
				<div class="d-none d-md-block p-2 mt-3 position-relative d-flex align-items-center">
					<div class="">
						<!-- <button class="border rounded-3 p-1 text-decoration-none" onclick="toggleSidebar()">
							<i class="bi bi-list bi-lg py-2 p-1"></i>
						</button> -->
						<span class="logo-text">
							<span class="hide-on-collapse-inline fw-bold mb-0 h5">
								Coordinator Tools
							</span>
						</span>
						<div class="show-on-collapse">
							<div class="form-check form-switch">
								<input class="form-check-input" type="checkbox" id="keep-close-toggle"
									onchange="ToggleKeepSideBarClose()">
							</div>
							<label class="form-check-label" for="keep-close-toggle">Keep close
							</label>
						</div>
					</div>
					<button class="main-toggle-btn position-absolute top-50 start-100 translate-middle"
						onclick="toggleSidebar()">
						<i class="fas fa-chevron-left"></i>
					</button>

				</div>
				<div class="d-block d-md-none Dropdown_menu_toggle px-3">☰ Open Sidebar</div>
				<hr>
				<div class="div_menu navbar-collapse">
					$sidebar
				</div>
			</div>
			<div class='px-0 col-md-10 colmd10'>
				<div class='container-fluid'>
					<div class='card'>
	HTML;
}

$currentUser = CurrentUser::getInstance();

$default_ty = $currentUser->isCoordinator() ? "last_coord" : "last";

$ty = $_GET['ty'] ?? $_POST['ty'] ?? $default_ty;

if ($ty == 'translate_type') $ty = 'tt';

$filename = $_SERVER['SCRIPT_NAME'];

if (!isset($_GET['nonav'])) {
	echo_card_start($filename, $ty, $currentUser);
};

// list of folders in coordinator
$corrd_folders = array_map('basename', glob(__DIR__ . '/coordinator/admin/*', GLOB_ONLYDIR));

$tools_files = [
	"categories",
	"last",
	"process_total",
	"process",
	"recent_helps",
	"stat",
];

// test_print("corrd_folders" . json_encode($corrd_folders));

$adminfile = __DIR__ . "/coordinator/admin/$ty.php";

if (in_array($ty, $tools_files)) {
	include_once __DIR__ . "/coordinator/tools/$ty.php";
	//
} elseif ($ty == "sidebar") {
	$sidebar = create_side($filename, $ty, $currentUser->isCoordinator());
	echo $sidebar;
	//
} elseif (in_array($ty, $corrd_folders) && $currentUser->isCoordinator()) {
	include_once __DIR__ . "/coordinator/admin/$ty/index.php";
	//
} elseif (is_file($adminfile) && $currentUser->isCoordinator()) {
	include_once $adminfile;
} else {
	test_print("can't find $adminfile");
	include_once __DIR__ . "/coordinator/404.php";
};

echo <<<HTML
			</div>
		</div>
	</div>
</div>
HTML;

echo "<script src='/tdc/js/autocomplate.js'></script>";
