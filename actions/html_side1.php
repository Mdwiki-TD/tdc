<?php

namespace Actions\HtmlSide;
/*
Usage:
use function Actions\HtmlSide\create_side;
*/

function generateListItem($id, $href, $title, $filename, $ty, $icon, $target = '')
{
    // ---
    $class = $ty == $href ? 'active' : '';
    // ---
    // $icon = (!empty($icon)) ? "<i class='bi $icon me-1'></i>" : '';
    // ---
    if (!empty($icon)) {
        $title = "<i class='bi $icon me-1'></i>" . $title;
    }
    // ---
    $li1 = "<a class='linknave rounded' href='$filename?ty=%s'>%s</a>";
    $li2 = "<a target='_blank' class='linknave rounded' href='%s'>%s</a>";
    // ---
    $template = $target ? $li2 : $li1;
    // ---
    $link = sprintf($template, $href, $title);
    // ---
    $text = <<<HTML
        <li id='$id' class='$class'>
            $link
        </li>
    HTML;
    // ---
    return $text;
}

function generateSpan($filename, $text)
{
    return <<<HTML
		<span class='d-flex align-items-center pb-1 mb-1 text-decoration-none border-bottom'>
			<a class='nav-link' href='$filename'>
				<span id='Home' class='fs-5 fw-semibold'>$text</span>
			</a>
		</span>
	HTML;
}

function create_side($filename, $ty)
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
            ['id' => 'publish_reports', 'admin' => 0, 'href' => '/publish_reports', 'title' => 'Publish Reports', 'target' => '_blank', 'icon' => 'bi-file-earmark-text'],
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
            ['id' => 'wikirefs_options', 'admin' => 1, 'href' => 'wikirefs_options', 'title' => 'Fixwikirefs (Options)', 'icon' => 'bi-wrench-adjustable'],
            ['id' => 'fixwikirefs', 'admin' => 0, 'href' => '/fixwikirefs.php', 'title' => 'Fixwikirefs', 'target' => '_blank', 'icon' => 'bi-wrench'],
        ],
    ];

    $homeSpan = generateSpan($filename, 'Coordinator Tools');

    $sidebar = <<<HTML
        <!-- $homeSpan -->
        <div class="Dropdown_menu_toggle px-3">☰ Open list</div>
        <div class="div_menu navbar-collapse">
        <ul class="list-unstyled">
    HTML;

    foreach ($mainMenu as $key => $items) {
        $lis = '';
        // ---
        $is_active = false;
        // ---
        foreach ($items as $item) {
            $href = $item['href'] ?? '';
            // ---
            if ($href == $ty) {
                $is_active = true;
            }
            // ---
            $icon_1 = $item['icon'] ?? '';
            // ---
            $target = $item['target'] ?? '';
            $admin = $item['admin'] ?? 0;

            if ($admin == 1 && !user_in_coord) continue;

            $lis .= generateListItem($item['id'], $item['href'], $item['title'], $filename, $ty, $icon_1, $target);
        }

        if (!empty($lis)) {
            $show = $is_active ? 'show' : '';
            $expanded = $is_active ? 'true' : 'false';
            // ---
            $icon = $mainMenuIcons[$key] ?? '';
            $icon = (!empty($icon)) ? "<i class='bi $icon me-1'></i>" : '';
            // ---
            $sidebar .= <<<HTML
                <li class="mb-1">
                    <button class="btn btn-toggle align-items-center rounded" data-bs-toggle="collapse"
                        data-bs-target="#$key-collapse" aria-expanded="$expanded">
                        <!-- <i class="bi bi-chevron-right"></i>  -->
                        $icon
                        $key
                    </button>
                    <div class="collapse $show" id="$key-collapse">
                        <ul class="
                        navbar-nav flex-row flex-wrap
                        btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            $lis
                        </ul>
                    </div>
                </li>
                <li class="border-top my-1"></li>
            HTML;
        }
    }

    $sidebar .= "</ul></div>";
    return $sidebar;
}
