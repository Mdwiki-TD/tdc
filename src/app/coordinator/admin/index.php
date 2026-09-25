<?php
// src/app/coordinator/admin/index.php

namespace App\Coordinator\Admin;

use App\Utils\TestPrinter;


class AdminResolver
{
    private bool $isCoordinator;

    public function __construct(bool $isCoordinator)
    {
        $this->isCoordinator = $isCoordinator;
    }

    /**
     * Determines, maps, and sanitizes the requested route key ('ty').
     */
    public function resolveTy(string $rawTy): string
    {
        $preDefinedTy = [
            "add",
            "admins",
            "campaigns",
            "users",
            "users/edit_user",
            "users/msg",
            "full_translators",
            "last_coord",
            "pages_users_to_main",
            "projects",
            "qids",
            "reports",
            "settings",
            "translated",
            "tt",
            "users_not_inprocess",
            "wikirefs_options",

            "pages_users_to_main/fix_page",
            "qids/edit_qid",
            "translated/edit_page",
            "tt/edit_translate_type",
            "wikirefs_options/wikirefs_options_edit",
        ];
        if (in_array($rawTy, $preDefinedTy)) {
            return $rawTy;
        }
        // Map route aliases
        $aliasesMap = [
            "translate_type" => "tt",
            "users_no_inprocess" => "users_not_inprocess",                            // new
            "Campaigns" => "campaigns",
            "Emails" => "users",
            "pages_users_to_main/fix_it" => "pages_users_to_main/fix_page",                                // new
            "fix_page" => "pages_users_to_main/fix_page",                                // new

            "edit_page" => "translated/edit_page",                                    // new
            "edit_user" => "users/edit_user",                                        // new
            "Emails/edit_user" => "users/edit_user",

            "Emails/msg" => "users/msg",
            "wikirefs_options_edit" => "wikirefs_options/wikirefs_options_edit",    // new
            "wikirefs_options/edit" => "wikirefs_options/wikirefs_options_edit",
            "edit_translate_type" => "tt/edit_translate_type",                        // new
        ];
        if (isset($aliasesMap[$rawTy])) {
            return $aliasesMap[$rawTy];
        }

        // Sanitize parameter to prevent directory traversal
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $rawTy);
    }
    /**
     * Dispatches the request to the target script or view based on routes and permissions.
     */
    public function dispatchObject(string $ty): bool {
        // String mapping with fully qualified namespace
        // Array mapping string keys to fully qualified class names
        $map = [
            "add"                  => "App\\Coordinator\\Admin\\Add\\AddIndexController",
            "admins"               => "App\\Coordinator\\Admin\\Admins\\AdminsIndexController",
            "campaigns"            => "App\\Coordinator\\Admin\\Campaigns\\CampaignsIndexController",
            "full_translators"     => "App\\Coordinator\\Admin\\FullTranslators\\FullTranslatorsIndexController",
            "last_coord"           => "App\\Coordinator\\Admin\\LastCoord\\LastCoordIndexController",
            "pages_users_to_main"  => "App\\Coordinator\\Admin\\PagesUsersToMain\\PagesUsersToMainIndexController",
            "projects"             => "App\\Coordinator\\Admin\\Projects\\ProjectsIndexController",
            "qids"                 => "App\\Coordinator\\Admin\\Qids\\QidsIndexController",
            "reports"              => "App\\Coordinator\\Admin\\Reports\\ReportsIndexController",
            "settings"             => "App\\Coordinator\\Admin\\Settings\\SettingsIndexController",
            "translated"           => "App\\Coordinator\\Admin\\Translated\\TranslatedIndexController",
            "tt"                   => "App\\Coordinator\\Admin\\TranslateType\\TranslateTypeIndexController",
            "users"                => "App\\Coordinator\\Admin\\Users\\UsersIndexController",
            "users_not_inprocess"  => "App\\Coordinator\\Admin\\UsersNotInprocess\\UsersNotInprocessIndexController",
            "wikirefs_options"     => "App\\Coordinator\\Admin\\WikiRefsOptions\\WikiRefsOptionsIndexController",
        ];
        if (isset($map[$ty])) {
            $controller = new $map[$ty]();
            $controller->handleRequest();
            return true;
        }
        return false;
    }

    public function dispatch(string $ty): void
    {
        if ($this->dispatchObject($ty)) {
            return;
        }

        $coordFolders = $this->getCoordinatorFolders();
        $adminFile = __DIR__ . "/{$ty}.php";

        if ($this->isCoordinator && in_array($ty, $coordFolders, true)) {
            include_once __DIR__ . "/{$ty}/index.php";
            return;
        }

        if ($this->isCoordinator && is_file($adminFile)) {
            include_once $adminFile;
            return;
        }

        // Fallback for missing or unauthorized routes
        TestPrinter::testPrint("can't find {$adminFile}");
        include_once dirname(dirname(__DIR__)) . "/404.php";
    }


    /**
     * Fetches existing directory names under coordinator/admin folder.
     */
    private function getCoordinatorFolders(): array
    {
        $directories = glob(__DIR__ . '/*', GLOB_ONLYDIR);
        if ($directories === false) {
            return [];
        }

        return array_map('basename', $directories);
    }
}
