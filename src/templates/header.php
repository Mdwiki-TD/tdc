<!DOCTYPE html>
<?php

use App\User\CurrentUser;
use App\Settings\Settings;
use function App\Head\print_full_head;
use function App\Head\write_body;

include_once __DIR__ . '/head.php';

function ba_alert(string $text): string
{
    return <<<HTML
	<div class='container'>
		<div class="alert alert-danger" role="alert">
			<i class="bi bi-exclamation-triangle"></i> $text
		</div>
	</div>
	HTML;
}

// Track page load time for performance monitoring
$timeStart = microtime(true);

echo print_full_head();

$settings = Settings::getInstance();

$currentUser = new CurrentUser($settings);

if ($msg = $currentUser->getAlertMessage()) {
	echo ba_alert($msg);
}

$coordTools = '<a href="tools.php" class="nav-link py-2 px-0 px-lg-2"><span class="navtitles"></span><i class="bi bi-tools me-1"></i> Tools</a>';


// Check if current user is a coordinator
if ($currentUser->isCoordinator()) {
	$coordTools = '<a href="/tdc/index.php" class="nav-link py-2 px-0 px-lg-2"><span class="navtitles"></span> <i class="bi bi-tools me-1"></i> Coordinator Tools</a>';
}

// Generate user menu based on authentication state
$liUser = <<<HTML
	<li class="nav-item col-lg-auto col-md-4 col-sm-6 col-6">
		<a href="/auth/login.php" class="nav-link py-2 px-0 px-lg-2">
			<i class="fas fa-sign-in-alt fa-sm fa-fw mr-2"></i> Login
		</a>
	</li>
HTML;

if ($currentUser->isLoggedIn()) {
	$username = $currentUser->getUsername();
	$liUser = <<<HTML
		<li class="nav-item col-lg-auto col-md-4 col-sm-6 col-6">
			<a href="/Translation_Dashboard/leaderboard.php?get=users&user={$username}" class="nav-link py-2 px-0 px-lg-2">
				<i class="fas fa-user fa-sm fa-fw mr-2"></i> <span class="navtitles">{$username}</span>
			</a>
		</li>
		<li class="nav-item col-lg-auto col-md-4 col-sm-6 col-6">
			<a class="nav-link py-2 px-0 px-lg-2" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
				<i class="fas fa-sign-out-alt fa-sm fa-fw mr-2"></i> <span class="d-lg-none navtitles">Logout</span>
			</a>
		</li>
	HTML;
}

echo "<body>";

// Output HTML header and navigation
echo write_body($coordTools, $liUser);

echo "<main id='body'><div id='maindiv' class='container-fluid'>";

?>
