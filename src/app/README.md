```
app/
├── backend/
│   ├── api_calls/
│   │   ├── mdwiki_api.php
│   │   └── wiki_api.php
│   ├── bootstrap.php
│   └── README.md
├── Controllers/
│   └── AppRouter.php
├── coordinator/
│   ├── admin/
│   │   ├── add/
│   │   │   ├── add_post.php
│   │   │   └── index.php
│   │   ├── admins/
│   │   │   ├── admins_post.php
│   │   │   └── index.php
│   │   ├── campaigns/
│   │   │   ├── campaigns_post.php
│   │   │   └── index.php
│   │   ├── common/
│   │   │   ├── AbstractController.php
│   │   │   ├── AbstractControllerNoPost.php
│   │   │   ├── AbstractEditController.php
│   │   │   └── AbstractPostHandler.php
│   │   ├── full_translators/
│   │   │   ├── full_translators_post.php
│   │   │   └── index.php
│   │   ├── last_coord/
│   │   │   └── index.php
│   │   ├── pages_users_to_main/
│   │   │   ├── fix_page.php
│   │   │   ├── fix_page_post.php
│   │   │   └── index.php
│   │   ├── projects/
│   │   │   ├── index.php
│   │   │   └── projects_post.php
│   │   ├── qids/
│   │   │   ├── edit_qid.php
│   │   │   ├── edit_qid_post.php
│   │   │   └── index.php
│   │   ├── reports/
│   │   │   ├── index.php
│   │   │   └── index2.php
│   │   ├── settings/
│   │   │   ├── index.php
│   │   │   └── settings_post.php
│   │   ├── translated/
│   │   │   ├── edit_page.php
│   │   │   ├── edit_page_post.php
│   │   │   └── index.php
│   │   ├── tt/
│   │   │   ├── edit_translate_type.php
│   │   │   ├── edit_tt_post.php
│   │   │   └── index.php
│   │   ├── users/
│   │   │   ├── edit_user.php
│   │   │   ├── edit_user_post.php
│   │   │   ├── index.php
│   │   │   └── msg.php
│   │   ├── users_not_inprocess/
│   │   │   ├── index.php
│   │   │   └── users_not_inprocess_post.php
│   │   ├── wikirefs_options/
│   │   │   ├── index.php
│   │   │   ├── wikirefs_options_edit.php
│   │   │   └── wikirefs_options_edit_post.php
│   │   ├── index.php
│   │   └── README.md
│   ├── helpers/
│   │   ├── bootstrap.php
│   │   ├── index.php
│   │   ├── recent_helps.php
│   │   └── sugust_helper.php
│   ├── admin.7z
│   ├── bootstrap.php
│   ├── index.php
│   ├── README.md
│   └── RecentTranslations.php
├── MdwikiSql/
│   ├── add_helper.php
│   ├── Database.php
│   └── mdwiki_sql.php
├── Results27/
│   ├── bootstrap.php
│   ├── get_results.php
│   ├── getcats.php
│   └── README.md
├── SQLorAPI/
│   ├── ApiOrSqlService.php
│   ├── BaseTable.php
│   ├── bootstrap.php
│   ├── CategoriesTable.php
│   ├── InProcessTable.php
│   ├── LeaderboardTable.php
│   ├── PagesTable.php
│   ├── QidsTable.php
│   ├── RecentTable.php
│   ├── SettingsTable.php
│   ├── TitlesTable.php
│   ├── UsersTable.php
│   └── ViewsTable.php
├── Tables/
│   ├── lang_names.json
│   ├── langcode.php
│   ├── sql_tables.php
│   └── tables.php
├── tools/
│   ├── bootstrap.php
│   ├── categories.php
│   ├── index.php
│   ├── last.php
│   ├── process.php
│   ├── process1.php
│   ├── process_total.php
│   └── stat.php
├── User/
│   ├── AccessKeyRepository.php
│   ├── bootstrap.php
│   ├── CoordinatorRepository.php
│   ├── CurrentUser.php
│   ├── README.md
│   ├── SessionManager.php
│   └── UserCookieService.php
├── Utils/
│   ├── html.php
│   ├── html2.php
│   ├── htmlUrls.php
│   ├── README.md
│   └── SidebarMenu.php
├── 404.php
├── bootstrap.php
├── CSRFManager.php
├── Logger.php
├── README.md
└── Settings.php

```