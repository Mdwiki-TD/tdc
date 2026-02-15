<?php

declare(strict_types=1);

/**
 * Sidebar Navigation Generator Module
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
 * use function Utils\HtmlSide\create_side;
 * 
 * $sidebar = create_side($_SERVER['SCRIPT_NAME'], 'last');
 * echo $sidebar;
 * ```
 * 
 * @package    Utils
 * @subpackage HtmlSide
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

namespace Utils\HtmlSide;

/**
 * Get menu data configuration
 * 
 * Returns the menu structure with icons, links, and access control settings.
 * 
 * @return array{0:array<string,string>,1:array<string,array<int,array<string,mixed>>>}
 *         Tuple of (icon mapping, menu structure)
 */
function menu_data(): array
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
            ['id' => 'last', 'admin' => 0, 'href' => 'last', 'title' => 'Recent', 'icon' => 'bi-clock-history'],
            ['id' => 'process', 'admin' => 0, 'href' => 'process', 'title' => 'In Process', 'icon' => 'bi-hourglass'],
            ['id' => 'process_total', 'admin' => 0, 'href' => 'process_total', 'title' => 'In Process (Total)', 'icon' => 'bi-hourglass-split'],
            ['id' => 'reports', 'admin' => 0, 'href' => 'reports', 'title' => 'Publish Reports', 'icon' => 'bi-file-earmark-text'],
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
        ],
        'Users' => [
            ['id' => 'admins', 'admin' => 1, 'href' => 'admins', 'title' => 'Coordinators', 'icon' => 'bi-person-gear'],
            ['id' => 'Emails', 'admin' => 1, 'href' => 'Emails', 'title' => 'Emails', 'icon' => 'bi-envelope'],
            ['id' => 'full_tr', 'admin' => 1, 'href' => 'full_translators', 'title' => 'Full translators', 'icon' => 'bi-person-check'],
            ['id' => 'user_inp', 'admin' => 1, 'href' => 'users_no_inprocess', 'title' => 'Not in process', 'icon' => 'bi-hourglass'],
        ],
        'Others' => [
            ['id' => 'projects', 'admin' => 1, 'href' => 'projects', 'title' => 'Projects', 'icon' => 'bi-kanban'],
            ['id' => 'Campaigns', 'admin' => 1, 'href' => 'Campaigns', 'title' => 'Campaigns', 'icon' => 'bi-megaphone'],
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
 * @param string      $icon   Bootstrap icon class
 * @param bool|string $target Link target attribute
 * 
 * @return string HTML for the navigation item
 */
function generateListItem(string $href, string $title, string $icon, bool|string $target): string
{
    $icon_tag = (!empty($icon)) ? "<i class='bi {$icon} me-1'></i>" : "";
    $target_attr = ($target) ? "target='_blank'" : '';
    
    $escaped_href = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');
    $escaped_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    
    return <<<HTML
        <a {$target_attr} class='linknave rounded' href='{$escaped_href}' title='{$escaped_title}' data-bs-toggle='tooltip' data-bs-placement='right'>
            {$icon_tag}
            <span class='hide-on-collapse-inline'>{$escaped_title}</span>
        </a>
    HTML;
}

/**
 * Create the complete sidebar navigation HTML
 * 
 * Generates a hierarchical sidebar menu with proper styling, icons,
 * and access control. Admin items are hidden from non-coordinator users.
 * 
 * @param string $filename Current script filename (for building URLs)
 * @param string $ty       Current tool type (for active state highlighting)
 * 
 * @return string Complete HTML for the sidebar navigation
 * 
 * @example
 * ```php
 * $sidebar = create_side('/index.php', 'last');
 * // Returns HTML with 'last' menu item marked as active
 * ```
 */
function create_side(string $filename, string $ty): string
{
    [$mainMenuIcons, $mainMenu] = menu_data();
    
    $sidebar = '<ul class="list-unstyled">';
    
    foreach ($mainMenu as $groupKey => $items) {
        $lis = '';
        $group_is_active = false;
        
        foreach ($items as $item) {
            $href = $item['href'] ?? '';
            
            // Check if this item is active
            if ($href === $ty) {
                $group_is_active = true;
            }
            
            $icon = $item['icon'] ?? '';
            $target = $item['target'] ?? '';
            $admin = $item['admin'] ?? 0;
            
            // Skip admin items for non-coordinators
            if ($admin === 1 && !defined('user_in_coord')) {
                continue;
            }
            if ($admin === 1 && !user_in_coord) {
                continue;
            }
            
            $class = ($ty === $href) ? 'active' : '';
            
            // Build full URL (unless external link)
            $href_full = ($target) ? $href : "{$filename}?ty={$href}";
            
            $id = $item['id'] ?? '';
            $link = generateListItem($href_full, $item['title'] ?? '', $icon, $target);
            
            $lis .= <<<HTML
                <li id='{$id}' class='{$class}'>
                    {$link}
                </li>
            HTML;
        }
        
        // Only render group if it has visible items
        if (!empty($lis)) {
            $show = $group_is_active ? 'show' : '';
            $expanded = $group_is_active ? 'true' : 'false';
            
            $icon_class = $mainMenuIcons[$groupKey] ?? '';
            $icon_html = (!empty($icon_class)) ? "<i class='bi {$icon_class} me-1'></i>" : '';
            
            $escaped_key = htmlspecialchars($groupKey, ENT_QUOTES, 'UTF-8');
            
            $sidebar .= <<<HTML
                <li class="mb-1">
                    <button class="btn btn-toggle align-items-center rounded" data-bs-toggle="collapse"
                        data-bs-target="#{$escaped_key}-collapse" aria-expanded="{$expanded}">
                        {$icon_html}
                        <span class='hide-on-collapse-inline'>{$escaped_key}</span>
                    </button>
                    <div class="collapse {$show}" id="{$escaped_key}-collapse">
                        <div class="d-none d-md-inline">
                            <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                                {$lis}
                            </ul>
                        </div>
                        <div class="d-inline d-md-none">
                            <ul class="navbar-nav flex-row flex-wrap btn-toggle-nav-mobile list-unstyled fw-normal pb-1 small">
                                {$lis}
                            </ul>
                        </div>
                    </div>
                </li>
                <li class="border-top my-1"></li>
            HTML;
        }
    }
    
    $sidebar .= "</ul>";
    
    return $sidebar;
}

/**
 * Get flat list of all available menu items
 * 
 * Useful for generating breadcrumbs or search indexes.
 * 
 * @return array<string,string> Item ID => Title mapping
 */
function get_all_menu_items(): array
{
    [, $mainMenu] = menu_data();
    $items = [];
    
    foreach ($mainMenu as $groupItems) {
        foreach ($groupItems as $item) {
            $id = $item['id'] ?? '';
            $title = $item['title'] ?? '';
            if (!empty($id) && !empty($title)) {
                $items[$id] = $title;
            }
        }
    }
    
    return $items;
}
