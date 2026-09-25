<?php
// src/app/index.php

namespace App;

use App\User\CurrentUser;
use App\Utils\SidebarMenu;
use App\Coordinator\Admin\AdminResolver;

/**
 * Class AppRouter
 * Handles layout initialization and dynamic request routing for the application.
 */
class AppRouter
{
	private AdminResolver $resolver;
	private CurrentUser $currentUser;
	private bool $isCoordinator;
	private string $ty;
	private string $scriptName;

	/**
	 * @var array<string, string> Map of tool route keys to controller class names
	 */
	private array $toolsControllers = [
		"categories"    => "\\App\\Tools\\CategoriesController",
		"last"          => "\\App\\Tools\\LastController",
		"process"       => "\\App\\Tools\\ProcessController",
		"process1"      => "\\App\\Tools\\Process1Controller",
		"process_total" => "\\App\\Tools\\ProcessTotalController",
		"stat"          => "\\App\\Tools\\StatController",
	];


	public function __construct(CurrentUser $currentUser)
	{

		$this->currentUser = $currentUser;
		$this->isCoordinator = $this->currentUser->isCoordinator();
		$this->scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

		$this->resolver = new AdminResolver($this->isCoordinator);

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

		$this->dispatch($this->ty);

		if (!$this->shouldHideNav()) {
			$this->renderSidebarEnd();
		}
	}

	/**
	 * Determines, maps, and sanitizes the requested route key ('ty').
	 */
	public function resolveTy(): string
	{
		$defaultTy = $this->isCoordinator ? "last_coord" : "last";
		$rawTy = $_GET['ty'] ?? $_POST['ty'] ?? $defaultTy;
		return $this->resolver->resolveTy($rawTy);
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
		$sidebar = new SidebarMenu($this->scriptName, $this->ty, $this->isCoordinator);
		$sidebar = $sidebar->render();

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
	private function dispatch(string $ty): void
	{
		if ($ty === "sidebar") {
			$sidebar = new SidebarMenu($this->scriptName, $ty, $this->isCoordinator);
			echo $sidebar->render();
			return;
		}

		if (isset($this->toolsControllers[$ty])) {
			$className = $this->toolsControllers[$ty];
			$controller = new $className();
			$controller->handleRequest();
			return;
		}
		$this->resolver->dispatch($ty);
	}
}
