<?php
// src/app/utils/SidebarMenu.php

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
 * @package    Utils
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

namespace App\Utils;

class SidebarMenu
{
    private string $filename;
    private string $currentType;
    private bool $userIsCoordinator;

    private array $mainMenuIcons;
    private array $mainMenu;

    /**
     * @param string $filename          Current script filename (for building URLs)
     * @param string $ty                Current tool type (for active state highlighting)
     * @param bool   $userIsCoordinator Whether the current user is a coordinator
     */
    public function __construct(string $filename, string $ty, bool $userIsCoordinator)
    {
        $this->filename          = $filename;
        $this->currentType       = $ty;
        $this->userIsCoordinator = $userIsCoordinator;

        [$this->mainMenuIcons, $this->mainMenu] = $this->menuData();
    }

    /**
     * Get menu data configuration
     *
     * Returns the menu structure with icons, links, and access control settings.
     *
     * @return array{0:array<string,string>,1:array<string,array<int,array<string,mixed>>>}
     *         Tuple of (icon mapping, menu structure)
     */
    private function menuData(): array
    {
        // Main menu icons for each group
        $mainMenuIcons = [
            "Translations" => "bi-translate",
            "Pages" => "bi-file-text",
            "Qids" => "bi-database",
            "Users" => "bi-people",
            "Others" => "bi-three-dots",
            "Tools" => "bi-tools",
        ];

        // Menu structure: [id, admin-required, href, title, icon, optional target]
        $mainMenu = [
            'Translations' => [
                ['id' => 'last', 'admin' => 0, 'no_admin' => 1,  'href' => 'last', 'title' => 'Recent', 'icon' => 'bi-clock-history'],
                ['id' => 'last_coord', 'admin' => 1, 'href' => 'last_coord', 'title' => 'Recent', 'icon' => 'bi-clock-history'],
                ['id' => 'process', 'admin' => 0, 'href' => 'process', 'title' => 'In Process', 'icon' => 'bi-hourglass'],
                ['id' => 'process_total', 'admin' => 0, 'href' => 'process_total', 'title' => 'In Process (Total)', 'icon' => 'bi-hourglass-split'],
                ['id' => 'reports', 'admin' => 1, 'href' => 'reports', 'title' => 'Publish Reports', 'icon' => 'bi-file-earmark-text'],
            ],
            'Pages' => [
                ['id' => 'tt_load', 'admin' => 1, 'href' => 'tt', 'title' => 'Translate Type', 'icon' => 'bi-translate'],
                ['id' => 'translated', 'admin' => 1, 'href' => 'translated', 'title' => 'Translated Pages', 'icon' => 'bi-check2-square'],
                ['id' => 'pages_users_to_main', 'admin' => 1, 'href' => 'pages_users_to_main', 'title' => 'Pages to check', 'icon' => 'bi-check'],
                ['id' => 'add', 'admin' => 1, 'href' => 'add', 'title' => 'Add translations', 'icon' => 'bi-plus-square'],
                ['id' => 'qidsload', 'admin' => 1, 'href' => 'qids', 'title' => 'Qids', 'icon' => 'bi-list-ul'],
            ],
            'Qids' => [
                // Reserved for future QID-related tools
                // ['id' => 'qids_othersload', 'admin' => 1, 'href' => 'qids&qid_table=qids_others', 'title' => 'Qids Others', 'icon' => ''],
            ],
            'Users' => [
                ['id' => 'admins', 'admin' => 1, 'href' => 'admins', 'title' => 'Coordinators', 'icon' => 'bi-person-gear'],
                ['id' => 'users', 'admin' => 1, 'href' => 'users', 'title' => 'Emails', 'icon' => 'bi-envelope'],
                ['id' => 'full_tr', 'admin' => 1, 'href' => 'full_translators', 'title' => 'Full translators', 'icon' => 'bi-person-check'],
                ['id' => 'user_inp', 'admin' => 1, 'href' => 'users_not_inprocess', 'title' => 'Not in process', 'icon' => 'bi-hourglass'],
            ],
            'Others' => [
                ['id' => 'projects', 'admin' => 1, 'href' => 'projects', 'title' => 'Projects', 'icon' => 'bi-kanban'],
                ['id' => 'campaigns', 'admin' => 1, 'href' => 'campaigns', 'title' => 'Campaigns', 'icon' => 'bi-megaphone'],
                ['id' => 'settings', 'admin' => 1, 'href' => 'settings', 'title' => 'Settings', 'icon' => 'bi-gear'],
                ['id' => 'categories', 'admin' => 0, 'href' => 'categories', 'title' => 'Categories', 'icon' => 'bi-tags'],
            ],
            'Tools' => [
                ['id' => 'stat', 'admin' => 0, 'href' => 'stat', 'title' => 'Status', 'icon' => 'bi-graph-up'],
                ['id' => 'wikirefs_options', 'admin' => 1, 'href' => 'wikirefs_options', 'title' => 'Fix refs (Options)', 'icon' => 'bi-wrench-adjustable'],
                ['id' => 'fixwikirefs', 'admin' => 0, 'href' => '/fixwikirefs.php', 'title' => 'Fixwikirefs', 'target' => '_blank', 'icon' => 'bi-wrench'],
            ],
        ];

        return [$mainMenuIcons, $mainMenu];
    }

    /**
     * Generate a single navigation list item
     *
     * @param string      $href   Link URL
     * @param string      $title  Link title/tooltip
     * @param string|null $icon   Bootstrap icon class
     * @param string|null $target Link target attribute
     *
     * @return string HTML for the navigation item
     */
    private function generateListItem(string $href, string $title, ?string $icon, ?string $target): string
    {
        $iconTag = (!empty($icon)) ? "<i class='bi {$icon} me-1'></i>" : "";
        $targetAttr = (!empty($target)) ? "target='_blank'" : '';

        $escapedHref = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');
        $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        return <<<HTML
            <a {$targetAttr} class='linknave rounded' href='{$escapedHref}' title='{$escapedTitle}' data-bs-toggle='tooltip' data-bs-placement='right'>
                {$iconTag}
                <span class='hide-on-collapse-inline'>{$escapedTitle}</span>
            </a>
        HTML;
    }

    /**
     * Build the list items (<li>) for a single menu group, applying
     * access control and active-state detection.
     *
     * @param array<int,array<string,mixed>> $items
     *
     * @return array{0:string,1:bool} Tuple of (list items HTML, whether the group is active)
     */
    private function buildGroupItems(array $items): array
    {
        $lis = '';
        $groupIsActive = false;

        foreach ($items as $item) {
            $href = isset($item['href']) && is_string($item['href']) ? $item['href'] : '';

            // Check if this item is active
            if ($href === $this->currentType) {
                $groupIsActive = true;
            }

            $icon = isset($item['icon']) && is_string($item['icon']) ? $item['icon'] : null;
            $target = isset($item['target']) && is_string($item['target']) ? $item['target'] : null;
            $admin = (int)($item['admin'] ?? 0);
            $noAdmin = (int)($item['no_admin'] ?? 0);

            // Skip non-admin items for coordinators
            if ($noAdmin === 1 && $this->userIsCoordinator) {
                continue;
            }
            // Skip admin items for non-coordinators
            if ($admin === 1 && !$this->userIsCoordinator) {
                continue;
            }

            $class = ($this->currentType === $href) ? 'active' : '';

            // Build full URL (unless external link)
            $hrefFull = ($target !== null && $target !== '') ? $href : "{$this->filename}?ty={$href}";

            $id = isset($item['id']) && is_string($item['id']) ? $item['id'] : '';
            $title = isset($item['title']) && is_string($item['title']) ? $item['title'] : '';
            $link = $this->generateListItem($hrefFull, $title, $icon, $target);

            $lis .= <<<HTML
                <li id='{$id}' class='{$class}'>
                    {$link}
                </li>
            HTML;
        }

        return [$lis, $groupIsActive];
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

        $escapedKey = htmlspecialchars((string)$groupKey, ENT_QUOTES, 'UTF-8');

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
                $sidebar .= $this->buildGroup((string)$groupKey, $lis, $groupIsActive);
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
