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
        // Map route aliases
        $aliasesMap = [
            "qids/edit_qid" => "edit_qid",
            "tt" => "translate_type",
            "users_no_inprocess" => "users_not_inprocess",
            "Campaigns" => "campaigns",

            "Emails" => "users",

            "pages_users_to_main/fix_it" => "fix_page",
            "pages_users_to_main/fix_page" => "fix_page",

            "translated/edit_page" => "edit_page",

            "users/edit_user" => "edit_user",
            "Emails/edit_user" => "edit_user",

            "users/msg" => "msg",
            "Emails/msg" => "msg",

            "wikirefs_options/wikirefs_options_edit" => "wikirefs_options_edit",
            "wikirefs_options/edit" => "wikirefs_options_edit",

            "tt/edit_translate_type" => "edit_translate_type",
        ];

        return (isset($aliasesMap[$rawTy])) ? $aliasesMap[$rawTy] : $rawTy;
    }
    /**
     * Dispatches the request to the target script or view based on routes and permissions.
     */
    public function dispatchObject(string $ty): bool
    {
        // String mapping with fully qualified namespace
        // Array mapping string keys to fully qualified class names
        $Controllers = [
            "add"                     => "App\\Coordinator\\Admin\\Add\\AddIndexController",
            "admins"                  => "App\\Coordinator\\Admin\\Admins\\AdminsIndexController",
            "campaigns"               => "App\\Coordinator\\Admin\\Campaigns\\CampaignsIndexController",
            "full_translators"        => "App\\Coordinator\\Admin\\FullTranslators\\FullTranslatorsIndexController",
            "last_coord"              => "App\\Coordinator\\Admin\\LastCoord\\LastCoordIndexController",
            "pages_users_to_main"     => "App\\Coordinator\\Admin\\PagesUsersToMain\\PagesUsersToMainIndexController",
            "projects"                => "App\\Coordinator\\Admin\\Projects\\ProjectsIndexController",
            "qids"                    => "App\\Coordinator\\Admin\\Qids\\QidsIndexController",
            "reports"                 => "App\\Coordinator\\Admin\\Reports\\ReportsIndexController",
            "settings"                => "App\\Coordinator\\Admin\\Settings\\SettingsIndexController",
            "translate_type"          => "App\\Coordinator\\Admin\\TranslateType\\TranslateTypeIndexController",
            "tt"                      => "App\\Coordinator\\Admin\\TranslateType\\TranslateTypeIndexController",
            "translated"              => "App\\Coordinator\\Admin\\Translated\\TranslatedIndexController",
            "users"                   => "App\\Coordinator\\Admin\\Users\\UsersIndexController",
            "users_not_inprocess"     => "App\\Coordinator\\Admin\\UsersNotInprocess\\UsersNotInprocessIndexController",
            "wikirefs_options"        => "App\\Coordinator\\Admin\\WikiRefsOptions\\WikiRefsOptionsIndexController",

            "edit_page"               => "App\\Coordinator\\Admin\\Translated\\EditPageController",
            "edit_qid"                => "App\\Coordinator\\Admin\\Qids\\EditQidController",
            "edit_translate_type"     => "App\\Coordinator\\Admin\\TranslateType\\EditTranslateTypeController",
            "edit_user"               => "App\\Coordinator\\Admin\\Users\\EditUserController",
            "fix_page"                => "App\\Coordinator\\Admin\\PagesUsersToMain\\FixItController",
            "msg"                     => "App\\Coordinator\\Admin\\Users\\MsgController",
            "wikirefs_options_edit"   => "App\\Coordinator\\Admin\\WikiRefsOptions\\WikiRefsOptionsEditController",
        ];
        if (isset($Controllers[$ty])) {
            $className = $Controllers[$ty];
            $controller = new $className();
            $controller->handleRequest();
            return true;
        }
        return false;
    }

    public function dispatch(string $ty): void
    {
        if (!$this->isCoordinator) {
            include_once dirname(dirname(__DIR__)) . "/404.php";
            return;
        }

        if ($this->dispatchObject($ty)) {
            return;
        }

        // Fallback for missing or unauthorized routes
        TestPrinter::testPrint("can't find {$ty}");
        include_once dirname(dirname(__DIR__)) . "/404.php";
    }
}
