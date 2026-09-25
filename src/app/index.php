<?php
// src/app/index.php

namespace App;

use App\User\CurrentUser;
use function App\Utils\Functions\test_print;
use function App\Utils\HtmlSide\create_side;

/**
 * Class AppRouter
 * Handles layout initialization and dynamic request routing for the application.
 */
class AppRouter
{
	private CurrentUser $currentUser;
	private bool $isCoordinator;
	private string $ty;
	private string $scriptName;

	/**
	 * @var array List of allowed tool scripts
	 */
	private array $toolsFiles = [
		"categories",
		"last",
		"process_total",
		"process",
		"recent_helps",
		"stat",
	];

	public function __construct()
	{
		$this->currentUser = CurrentUser::getInstance();
		$this->isCoordinator = $this->currentUser->isCoordinator();
		$this->scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
		$this->ty = $this->resolveTy();
	}

	/**
	 * Main entry point to run the application request.
	 */
	public function handleRequest(): void
	{
		if (!$this->shouldHideNav()) {
			$this->renderSidebarStart();
		}

		$this->dispatch();

		if (!$this->shouldHideNav()) {
			$this->renderSidebarEnd();
		}
	}

	/**
	 * Determines, maps, and sanitizes the requested route key ('ty').
	 */
	private function resolveTy(): string
	{
		$defaultTy = $this->isCoordinator ? "last_coord" : "last";
		$rawTy = $_GET['ty'] ?? $_POST['ty'] ?? $defaultTy;

		$preDefinedTy = [
			"add",
			"admins",
			"Campaigns",
			"Emails",
			"full_translators",
			"last_coord",
			"pages_users_to_main",
			"projects",
			"qids",
			"reports",
			"settings",
			"translated",
			"tt",
			"users_no_inprocess",
			"wikirefs_options",

			"Emails/edit_user",
			"Emails/msg",
			"pages_users_to_main/fix_it",
			"qids/edit_qid",
			"translated/edit_page",
			"tt/edit_translate_type",
			"wikirefs_options/edit",
		];
		if (in_array($rawTy, $preDefinedTy)) {
			return $rawTy;
		}
		// Map route aliases
		if ($rawTy === 'translate_type') {
			$rawTy = 'tt';
		}

		// Sanitize parameter to prevent directory traversal
		return preg_replace('/[^a-zA-Z0-9_-]/', '', $rawTy);
	}

	/**
	 * Checks if navigation header wrapper should be omitted.
	 */
	private function shouldHideNav(): bool
	{
		return isset($_GET['nonav']);
	}

	/**
	 * Renders the sidebar and opening HTML wrapper structure.
	 */
	private function renderSidebarStart(): void
	{
		$sidebar = create_side($this->scriptName, $this->ty, $this->isCoordinator);

		echo <<<HTML
        <div class='row content'>
            <div class='col-md-2 px-0 colmd2 border'>
                <div class="d-none d-md-block p-2 mt-3 position-relative d-flex align-items-center">
                    <div>
                        <span class="logo-text">
                            <span class="hide-on-collapse-inline fw-bold mb-0 h5">
                                Coordinator Tools
                            </span>
                        </span>
                        <div class="show-on-collapse">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="keep-close-toggle" onchange="ToggleKeepSideBarClose()">
                            </div>
                            <label class="form-check-label" for="keep-close-toggle">Keep close</label>
                        </div>
                    </div>
                    <button class="main-toggle-btn position-absolute top-50 start-100 translate-middle" onclick="toggleSidebar()">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </div>
                <div class="d-block d-md-none Dropdown_menu_toggle px-3">☰ Open Sidebar</div>
                <hr>
                <div class="div_menu navbar-collapse">
                    {$sidebar}
                </div>
            </div>
            <div class='px-0 col-md-10 colmd10'>
                <div class='container-fluid'>
                    <div class='card'>
        HTML;
	}

	private function renderSidebarEnd(): void
	{
		echo <<<HTML
                    </div>
                </div>
            </div>
        </div>
        HTML;
	}
	/**
	 * Dispatches the request to the target script or view based on routes and permissions.
	 */
	private function dispatch(): void
	{
		$coordFolders = $this->getCoordinatorFolders();
		$adminFile = __DIR__ . "/coordinator/admin/{$this->ty}.php";

		if (in_array($this->ty, $this->toolsFiles, true)) {
			include_once __DIR__ . "/tools/{$this->ty}.php";
			return;
		}

		if ($this->ty === "sidebar") {
			echo create_side($this->scriptName, $this->ty, $this->isCoordinator);
			return;
		}

		if ($this->isCoordinator && in_array($this->ty, $coordFolders, true)) {
			include_once __DIR__ . "/coordinator/admin/{$this->ty}/index.php";
			return;
		}

		if ($this->isCoordinator && is_file($adminFile)) {
			include_once $adminFile;
			return;
		}

		// Fallback for missing or unauthorized routes
		test_print("can't find {$adminFile}");
		include_once __DIR__ . "/coordinator/404.php";
	}

	/**
	 * Fetches existing directory names under coordinator/admin folder.
	 */
	private function getCoordinatorFolders(): array
	{
		$directories = glob(__DIR__ . '/coordinator/admin/*', GLOB_ONLYDIR);
		if ($directories === false) {
			return [];
		}

		return array_map('basename', $directories);
	}
}

// Instantiate and execute application router
$router = new AppRouter();
$router->handleRequest();
