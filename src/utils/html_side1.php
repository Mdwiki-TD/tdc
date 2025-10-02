<?php

namespace Utils\HtmlSide;
/*
Usage:
use function Utils\HtmlSide\create_side;
*/

function menu_data()
{

    $mainMenuIcons = [
        "Translations" => "bi-translate",
        "Pages" => "bi-file-text",
        "Qids" => "bi-database",
        "Users" => "bi-people",
        "Others" => "bi-three-dots",
        "Tools" => "bi-tools",
    ];
    // ---
    $mainMenu = [
        'Translations' => [
            ['id' => 'last', 'admin' => 0, 'href' => 'last', 'title' => 'Recent', 'icon' => 'bi-clock-history'],
            // ['id' => 'last_users', 'admin' => 0, 'href' => 'last_users', 'title' => 'Recent in User space', 'icon' => 'bi-person-workspace'],
            ['id' => 'process', 'admin' => 0, 'href' => 'process', 'title' => 'In Process', 'icon' => 'bi-hourglass'],
            ['id' => 'process_total', 'admin' => 0, 'href' => 'process_total', 'title' => 'In Process (Total)', 'icon' => 'bi-hourglass-split'],
            // ['id' => 'publish_reports', 'admin' => 0, 'href' => '/publish_reports', 'title' => 'Publish Reports', 'target' => '_blank', 'icon' => 'bi-file-earmark-text'],
            ['id' => 'reports', 'admin' => 0, 'href' => 'reports', 'title' => 'Publish Reports', 'icon' => 'bi-file-earmark-text'],
        ],
        'Pages' => [
            ['id' => 'tt_load', 'admin' => 1, 'href' => 'tt', 'title' => 'Translate Type', 'icon' => 'bi-translate'],
            ['id' => 'translated', 'admin' => 1, 'href' => 'translated', 'title' => 'Translated Pages', 'icon' => 'bi-check2-square'],
            ['id' => 'pages_users_to_main', 'admin' => 1, 'href' => 'pages_users_to_main', 'title' => 'Pages to check', 'icon' => 'bi-check'],
            ['id' => 'add', 'admin' => 1, 'href' => 'add', 'title' => 'Add translations', 'icon' => 'bi-plus-square'],
            ['id' => 'qidsload', 'admin' => 1, 'href' => 'qids', 'title' => 'Qids', "icon" => "bi-list-ul"],
        ],
        'Qids' => [
            // ['id' => 'qids_othersload', 'admin' => 1, 'href' => 'qids&qid_table=qids_others', 'title' => 'Qids Others', 'icon' => ''],
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
        ],
        'Tools' => [
            ['id' => 'stat', 'admin' => 0, 'href' => 'stat', 'title' => 'Status', 'icon' => 'bi-graph-up'],
            ['id' => 'wikirefs_options', 'admin' => 1, 'href' => 'wikirefs_options', 'title' => 'Fix refs (Options)', 'icon' => 'bi-wrench-adjustable'],
            ['id' => 'fixwikirefs', 'admin' => 0, 'href' => '/fixwikirefs.php', 'title' => 'Fixwikirefs', 'target' => '_blank', 'icon' => 'bi-wrench'],
        ],
    ];
    return [$mainMenuIcons, $mainMenu];
}

function generateListItem($href, $title, $icon, $target)
{
    // ---
    // $icon = (!empty($icon)) ? "<i class='bi $icon me-1'></i>" : '';
    // ---
    $icon_tag = (!empty($icon)) ? "<i class='bi $icon me-1'></i>" : "";
    // ---
    $target_attr = ($target) ? "target='_blank'" : '';
    // ---
    $link = <<<HTML
        <a $target_attr class='linknave rounded' href='$href' title='$title' data-bs-toggle='tooltip' data-bs-placement='right'>
            $icon_tag
            <span class='hide-on-collapse-inline'>$title</span>
        </a>
    HTML;
    // ---
    return $link;
}

function create_side($filename, $ty)
{

    [$mainMenuIcons, $mainMenu] = menu_data();

    $sidebar = <<<HTML
        <ul class="list-unstyled">
    HTML;

    foreach ($mainMenu as $key => $items) {
        $lis = '';
        // ---
        $group_is_active = false;
        // ---
        foreach ($items as $item) {
            $href = $item['href'] ?? '';
            // ---
            if ($href == $ty) {
                $group_is_active = true;
            }
            // ---
            $icon_1 = $item['icon'] ?? '';
            // ---
            $target = $item['target'] ?? '';
            $admin = $item['admin'] ?? 0;

            if ($admin == 1 && !user_in_coord) continue;

            $class = $ty == $href ? 'active' : '';
            // ---
            $href_full = ($target) ? $href : "$filename?ty=$href";
            // ---
            $id = $item['id'];
            // ---
            $link = generateListItem($href_full, $item['title'], $icon_1, $target);
            // ---
            $lis .= <<<HTML
                <li id='$id' class='$class'>
                    $link
                </li>
            HTML;
            // ---
        }

        if (!empty($lis)) {
            $show = $group_is_active ? 'show' : '';
            $expanded = $group_is_active ? 'true' : 'false';
            // ---
            $icon = $mainMenuIcons[$key] ?? '';
            $icon = (!empty($icon)) ? "<i class='bi $icon me-1'></i>" : '';
            // ---
            $sidebar .= <<<HTML
                <li class="mb-1">
                    <button class="btn btn-toggle align-items-center rounded" data-bs-toggle="collapse"
                        data-bs-target="#$key-collapse" aria-expanded="$expanded">
                        $icon
                        <span class='hide-on-collapse-inline'>$key</span>
                    </button>
                    <div class="collapse $show" id="$key-collapse">
                        <div class="d-none d-md-inline">
                            <!-- desktop -->
                            <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                                $lis
                            </ul>
                        </div>
                        <div class="d-inline d-md-none">
                            <!-- mobile -->
                            <ul class="navbar-nav flex-row flex-wrap btn-toggle-nav-mobile list-unstyled fw-normal pb-1 small">
                                $lis
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
