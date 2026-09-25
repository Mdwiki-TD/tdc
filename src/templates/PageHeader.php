<?php
// src/templates/PageHeader.php

namespace App\Templates;

use App\User\CurrentUser;
use App\Settings\Settings;
use App\Head\PageHead;

class PageHeader
{
	private Settings $settings;
	private CurrentUser $currentUser;
	private PageHead $pageHead;
	private float $timeStart;
	private bool $hideNav;

	public function __construct(bool $hideNav = false)
	{
		// Track page load time for performance monitoring
		$this->timeStart = microtime(true);

		$this->settings    = Settings::getInstance();
		$this->currentUser = new CurrentUser($this->settings);
		$this->pageHead    = new PageHead();

		// When ?nonav is passed, the navbar is skipped entirely
		// instead of being rendered and then hidden with JS.
		$this->hideNav = $hideNav;
	}

	/**
	 * Builds a Bootstrap danger alert box.
	 */
	private function alert(string $text): string
	{
		return <<<HTML
        <div class='container'>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {$text}
            </div>
        </div>
        HTML;
	}

	/**
	 * Builds the "Tools" / "Coordinator Tools" nav link
	 * depending on the current user's role.
	 */
	private function buildCoordToolsLink(): string
	{
		if ($this->currentUser->isCoordinator()) {
			return '<a href="/tdc/index.php" class="nav-link py-2 px-0 px-lg-2"><span class="navtitles"></span><i class="bi bi-tools me-1"></i> Coordinator Tools</a>';
		}

		return '<a href="tools.php" class="nav-link py-2 px-0 px-lg-2"><span class="navtitles"></span><i class="bi bi-tools me-1"></i> Tools</a>';
	}

	/**
	 * Builds the user menu (login link, or username + logout)
	 * depending on the authentication state.
	 */
	private function buildUserMenu(): string
	{
		if (!$this->currentUser->isLoggedIn()) {
			return <<<HTML
                <li class="nav-item col-lg-auto col-md-4 col-sm-6 col-6">
                    <a href="/auth/login.php" class="nav-link py-2 px-0 px-lg-2">
                        <i class="fas fa-sign-in-alt fa-sm fa-fw mr-2"></i> Login
                    </a>
                </li>
            HTML;
		}

		$username = $this->currentUser->getUsername();

		return <<<HTML
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

	/**
	 * Renders the full page header: <head>, alert (if any),
	 * <body> tag, navigation, and opening of the main container.
	 */
	public function render(): void
	{
		echo "<!DOCTYPE html>";
		echo $this->pageHead->print_full_head();

		if ($msg = $this->currentUser->getAlertMessage()) {
			echo $this->alert($msg);
		}

		echo "<body>";

		if (!$this->hideNav) {
			echo $this->pageHead->write_body(
				$this->buildCoordToolsLink(),
				$this->buildUserMenu()
			);
		} else {
			echo "<header class='mb-3 border-bottom'> </header>";
		}

		echo "<main id='body'><div id='maindiv' class='container-fluid'>";
	}

	public function getCurrentUser(): CurrentUser
	{
		return $this->currentUser;
	}

	public function getLoadStartTime(): float
	{
		return $this->timeStart;
	}

	public function isNavHidden(): bool
	{
		return $this->hideNav;
	}
}

// Usage (replaces the old procedural src/templates/header.php):
// $pageHeader = new PageHeader();
// $pageHeader->render();
