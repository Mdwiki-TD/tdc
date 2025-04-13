<?PHP
//---
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
};
//---
include_once __DIR__ . '/header.php';
//---
use function Actions\Functions\test_print;
use function Actions\HtmlSide\create_side;

echo <<<HTML
	<!-- </div> -->
	<script>$("#coord").addClass("active");</script>
	<!-- <div id="maindiv" class="container-fluid"> -->
HTML;
//---
function echo_card_start($filename, $ty)
{
	$sidebar = create_side($filename, $ty);
	echo <<<HTML
	<style>
		@media (min-width: 768px) {
			.colmd2 {
				width: 12% !important;
			}
			.colmd10 {
				width: 88% !important;
			}
		}

	</style>
		<div class='row content'>
			<!-- <div class='col-md-2 px-0' style="width: 10.66666667%;"> -->
			<div class='col-md-2 px-0 colmd2'>
				$sidebar
			</div>
			<div class='px-0 col-md-10 colmd10'>
				<div class='container-fluid'>
					<div class='card'>
	HTML;
}
//---
$ty = $_REQUEST['ty'] ?? 'last';
//---
if ($ty == 'translate_type') $ty = 'tt';
//---
$filename = $_SERVER['SCRIPT_NAME'];
//---
if (!isset($_REQUEST['nonav'])) {
	echo_card_start($filename, $ty);
};
//---
// list of folders in coordinator
$corrd_folders = array_map('basename', glob('coordinator/admin/*', GLOB_ONLYDIR));
//---
$tools_folders = array_map(fn ($file) => basename($file, '.php'), glob('coordinator/tools/*.php'));
//---
// test_print("corrd_folders" . json_encode($corrd_folders));
// test_print("tools_folders" . json_encode($tools_folders));
//---
$adminfile = __DIR__ . "/coordinator/admin/$ty.php";

if (in_array($ty, $tools_folders)) {
	include_once __DIR__ . "/coordinator/tools/$ty.php";
	//
} elseif ($ty == "sidebar") {
	$sidebar = create_side($filename, $ty);
	echo $sidebar;
	//
} elseif (in_array($ty, $corrd_folders) && user_in_coord) {
	include_once __DIR__ . "/coordinator/admin/$ty/index.php";
	//
} elseif (is_file($adminfile) && user_in_coord) {
	include_once $adminfile;
} else {
	test_print("can't find $adminfile");
	include_once __DIR__ . "/coordinator/404.php";
};
//---
echo <<<HTML
			</div>
		</div>
	</div>
</div>
HTML;
//---
echo "<script src='/Translation_Dashboard/js/autocomplate.js'></script>";
//---
include_once __DIR__ . '/footer.php';
