<?php
// src/app/Utils/SidebarMenu.php

/**
 * Sidebar Navigation Generator
 *
 * Generates the sidebar navigation menu for the Translation Dashboard's
 * coordinator interface. Provides hierarchical menu structure with icons
 * and access control based on user permissions.
 *
 * Features:
 * - Hierarchical menu groups (Translations, Pages, Qids, Users, etc.)
 * - Role-based access control for admin items
 * - Bootstrap-compatible styling
 * - Responsive mobile support
 * - Active state tracking
 *
 * Menu Structure:
 * - Translations: Recent, In Process, Publish Reports
 * - Pages: Translate Type, Translated Pages, Qids
 * - Users: Coordinators, Emails, Full translators
 * - Others: Projects, Campaigns, Settings, Categories
 * - Tools: Status, Fix refs
 *
 * Usage Example:
 * ```php
 * use App\Utils\SidebarMenu;
 *
 * $sidebar = new SidebarMenu($_SERVER['SCRIPT_NAME'], 'last', true);
 * echo $sidebar->render();
 * ```
 *
 * @package     Utils
 * @author      Translation Dashboard Team
 * @version     2.0.0
 * @since       1.0.0
 * @license     GPL-3.0-or-later
 */

namespace App\Utils;

use App\Utils\SidebarItem;

class SidebarMenu
{
    private string $filename;
    private string $currentType;
    private bool   $userIsCoordinator;
    private bool   $isAdmin;

    /** @var array<string,string> */
    private array $mainMenuIcons;

    /** @var array<string,array<int,SidebarItem>> */
    private array $mainMenu;

    /**
     * @param string $filename          Current script filename (for building URLs)
     * @param string $ty                Current tool type (for active state highlighting)
     * @param bool   $userIsCoordinator Whether the current user is a coordinator
     * @param bool   $isAdmin           Whether the current user is a admin
     */
    public function __construct(string $filename, string $ty, bool $userIsCoordinator, bool $isAdmin)
    {
        $this->filename = $filename;
        $this->currentType = $ty;
        $this->userIsCoordinator = $userIsCoordinator;
        $this->isAdmin = $isAdmin;

        [$this->mainMenuIcons, $this->mainMenu] = $this->menuData();
    }

    /**
     * Get menu data configuration
     *
     * Returns the menu structure with icons, links, and access control settings.
     *
     * @return array{0:array<string,string>,1:array<string,array<int,SidebarItem>>}
     *         Tuple of (icon mapping, menu structure)
     */
    private function menuData(): array
    {
        // Main menu icons for each group
        $mainMenuIcons = [
            "Translations" => "bi-translate",
            "Pages"        => "bi-file-text",
            "Qids"         => "bi-database",
            "Users"        => "bi-people",
            "Others"       => "bi-three-dots",
            "Tools"        => "bi-tools",
        ];

        // Menu structure using SidebarItem objects
        $mainMenu = [
            "Translations" => [
                new SidebarItem(
                    id: "last",
                    href: "last",
                    title: "Recent",
                    icon: "bi-clock-history",
                    notForAdmin: true,
                ),
                new SidebarItem(
                    id: "last_coord",
                    href: "last_coord",
                    title: "Recent",
                    icon: "bi-clock-history",
                    forCoordinator: true,
                ),
                new SidebarItem(
                    id: "process",
                    href: "process",
                    title: "In Process",
                    icon: "bi-hourglass",
                    forAnyuser: true,
                ),
                new SidebarItem(
                    id: "process_total",
                    href: "process_total",
                    title: "In Process (Total)",
                    icon: "bi-hourglass-split",
                    forAnyuser: true,
                ),
                new SidebarItem(
                    id: "reports",
                    href: "reports",
                    title: "Publish Reports",
                    icon: "bi-file-earmark-text",
                    forCoordinator: true,
                ),
            ],
            "Pages"        => [
                new SidebarItem(
                    id: "tt_load",
                    href: "translate_type",
                    title: "Translate Type",
                    icon: "bi-translate",
                    forCoordinator: true,
                ),
                new SidebarItem(
                    id: "translated",
                    href: "translated",
                    title: "Translated Pages",
                    icon: "bi-check2-square",
                    forCoordinator: true,
                ),
                new SidebarItem(
                    id: "pages_users_to_main",
                    href: "pages_users_to_main",
                    title: "Pages to check",
                    icon: "bi-check",
                    forCoordinator: true,
                ),
                new SidebarItem(
                    id: "add",
                    href: "add",
                    title: "Add translations",
                    icon: "bi-plus-square",
                    forCoordinator: true,
                ),
                new SidebarItem(
                    id: "qidsload",
                    href: "qids",
                    title: "Qids",
                    icon: "bi-list-ul",
                    forCoordinator: true,
                ),
            ],
            "Users"        => [
                new SidebarItem(
                    id: "admins",
                    href: "admins",
                    title: "Coordinators",
                    icon: "bi-person-gear",
                    adminOnly: true,
                ),
                new SidebarItem(
                    id: "users",
                    href: "users",
                    title: "Emails",
                    icon: "bi-envelope",
                    forCoordinator: true,
                ),
                new SidebarItem(
                    id: "full_tr",
                    href: "full_translators",
                    title: "Full translators",
                    icon: "bi-person-check",
                    forCoordinator: true,
                ),
                new SidebarItem(
                    id: "user_inp",
                    href: "users_not_inprocess",
                    title: "Not in process",
                    icon: "bi-hourglass",
                    forCoordinator: true,
                ),
            ],
            "Others"       => [
                new SidebarItem(
                    id: "projects",
                    href: "projects",
                    title: "Projects",
                    icon: "bi-kanban",
                    forCoordinator: true,
                ),
                new SidebarItem(
                    id: "campaigns",
                    href: "campaigns",
                    title: "Campaigns",
                    icon: "bi-megaphone",
                    forCoordinator: true,
                ),
                new SidebarItem(
                    id: "settings",
                    href: "settings",
                    title: "Settings",
                    icon: "bi-gear",
                    forCoordinator: true
                ),
                new SidebarItem(
                    id: "categories",
                    href: "categories",
                    title: "Categories",
                    icon: "bi-tags",
                    forAnyuser: true,
                ),
            ],
            "Tools"        => [
                new SidebarItem(
                    id: "stat",
                    href: "stat",
                    title: "Status",
                    icon: "bi-graph-up",
                    forAnyuser: true,
                ),
                new SidebarItem(
                    id: "wikirefs_options",
                    href: "wikirefs_options",
                    title: "Fix refs (Options)",
                    icon: "bi-wrench-adjustable",
                    forCoordinator: true,
                ),
                new SidebarItem(
                    id: "fixwikirefs",
                    href: "/fixwikirefs.php",
                    title: "Fixwikirefs",
                    target: "_blank",
                    icon: "bi-wrench",
                    forAnyuser: true,
                ),
            ],
        ];

        return [$mainMenuIcons, $mainMenu];
    }

    /**
     * Build the list items (<li>) for a single menu group, applying
     * access control and active-state detection.
     *
     * @param array<int,SidebarItem> $items
     *
     * @return array{0:string,1:bool} Tuple of (list items HTML, whether the group is active)
     */
    private function buildGroupItems(array $items): array
    {
        $listItems = '';
        $groupIsActive = false;

        foreach ($items as $item) {
            $href = $item->href ?? '';

            // Check if this item is active
            if ($href === $this->currentType) {
                $groupIsActive = true;
            }

            if ($item->forAnyuser === false) {
                // Skip non-admin items for coordinators
                if ($item->notForAdmin === true && ($this->userIsCoordinator || $this->isAdmin)) {
                    continue;
                }
                // Skip items for non-coordinators
                if ($item->forCoordinator === true && !$this->userIsCoordinator && !$this->isAdmin) {
                    continue;
                }

                if ($item->adminOnly === true && !$this->isAdmin) {
                    continue;
                }
            }

            $class = ($this->currentType === $href) ? 'active' : '';

            $link = $item->generateListItem($this->filename);

            $listItems .= <<<HTML
                <li id='{$item->id}' class='{$class}'>
                    {$link}
                </li>
            HTML;
        }

        return [$listItems, $groupIsActive];
    }

    /**
     * Build the collapsible group markup (button + desktop/mobile lists).
     */
    private function buildGroup(string $groupKey, string $lis, bool $groupIsActive): string
    {
        $show = $groupIsActive ? 'show' : '';
        $expanded = $groupIsActive ? 'true' : 'false';

        $iconClass = $this->mainMenuIcons[$groupKey] ?? '';
        $iconHtml = (!empty($iconClass)) ? "<i class='bi {$iconClass} me-1'></i>" : '';

        $escapedKey = htmlspecialchars((string) $groupKey, ENT_QUOTES, 'UTF-8');

        return <<<HTML
            <li class="mb-1">
                <button class="btn btn-toggle align-items-center rounded" data-bs-toggle="collapse"
                    data-bs-target="#{$escapedKey}-collapse" aria-expanded="{$expanded}">
                    {$iconHtml}
                    <span class='hide-on-collapse-inline'>{$escapedKey}</span>
                </button>
                <div class="collapse {$show}" id="{$escapedKey}-collapse">
                    <div class="d-none d-md-inline">
                        <!-- desktop -->
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            {$lis}
                        </ul>
                    </div>
                    <div class="d-inline d-md-none">
                        <!-- mobile -->
                        <ul class="navbar-nav flex-row flex-wrap btn-toggle-nav-mobile list-unstyled fw-normal pb-1 small">
                            {$lis}
                        </ul>
                    </div>
                </div>
            </li>
            <li class="border-top my-1"></li>
        HTML;
    }

    /**
     * Create the complete sidebar navigation HTML
     *
     * Generates a hierarchical sidebar menu with proper styling, icons,
     * and access control. Admin items are hidden from non-coordinator users.
     *
     * @return string Complete HTML for the sidebar navigation
     *
     * @example
     * ```php
     * $sidebar = new SidebarMenu('/index.php', 'last', true);
     * echo $sidebar->render();
     * // Returns HTML with 'last' menu item marked as active
     * ```
     */
    public function render(): string
    {
        $sidebar = <<<HTML
            <ul class="list-unstyled">
        HTML;

        foreach ($this->mainMenu as $groupKey => $items) {
            [$lis, $groupIsActive] = $this->buildGroupItems($items);

            // Only render group if it has visible items
            if (!empty($lis)) {
                $sidebar .= $this->buildGroup((string) $groupKey, $lis, $groupIsActive);
            }
        }

        $sidebar .= "</ul>";

        return $sidebar;
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
