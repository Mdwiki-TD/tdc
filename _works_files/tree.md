```
src/
├── app/
│   ├── ApiClients/
│   │   ├── MdwikiApi.php
│   │   └── WikiApi.php
│   ├── Controllers/
│   │   └── AppRouter.php
│   ├── Coordinator/
│   │   ├── Admin/
│   │   │   ├── Add/
│   │   │   │   ├── AddIndexController.php
│   │   │   │   └── AddPostProcessor.php
│   │   │   ├── Admins/
│   │   │   │   ├── AdminsIndexController.php
│   │   │   │   └── AdminsPostProcessor.php
│   │   │   ├── Campaigns/
│   │   │   │   ├── CampaignsIndexController.php
│   │   │   │   └── CampaignsPostProcessor.php
│   │   │   ├── Common/
│   │   │   │   ├── AbstractController.php
│   │   │   │   ├── AbstractControllerNoPost.php
│   │   │   │   ├── AbstractEditController.php
│   │   │   │   └── AbstractPostHandler.php
│   │   │   ├── FullTranslators/
│   │   │   │   ├── FullTranslatorsIndexController.php
│   │   │   │   └── FullTranslatorsPostProcessor.php
│   │   │   ├── LastCoord/
│   │   │   │   └── LastCoordIndexController.php
│   │   │   ├── PagesUsersToMain/
│   │   │   │   ├── FixItController.php
│   │   │   │   ├── FixItPostProcessor.php
│   │   │   │   └── PagesUsersToMainIndexController.php
│   │   │   ├── Projects/
│   │   │   │   ├── ProjectsIndexController.php
│   │   │   │   └── ProjectsPostProcessor.php
│   │   │   ├── Qids/
│   │   │   │   ├── EditQidController.php
│   │   │   │   ├── QidsIndexController.php
│   │   │   │   └── QidsPostProcessor.php
│   │   │   ├── Reports/
│   │   │   │   ├── ReportsIndex2Controller.php
│   │   │   │   └── ReportsIndexController.php
│   │   │   ├── Settings/
│   │   │   │   ├── SettingsIndexController.php
│   │   │   │   └── SettingsPostProcessor.php
│   │   │   ├── Translated/
│   │   │   │   ├── EditPageController.php
│   │   │   │   ├── EditPagePostHandler.php
│   │   │   │   └── TranslatedIndexController.php
│   │   │   ├── TranslateType/
│   │   │   │   ├── EditTranslateTypeController.php
│   │   │   │   ├── TranslateTypeIndexController.php
│   │   │   │   └── TtPostProcessor.php
│   │   │   ├── Users/
│   │   │   │   ├── EditUserController.php
│   │   │   │   ├── EditUserPostProcessor.php
│   │   │   │   ├── MsgController.php
│   │   │   │   └── UsersIndexController.php
│   │   │   ├── UsersNotInprocess/
│   │   │   │   ├── UsersNotInprocessIndexController.php
│   │   │   │   └── UsersNotInprocessPostProcessor.php
│   │   │   ├── WikiRefsOptions/
│   │   │   │   ├── WikiRefsOptionsEditController.php
│   │   │   │   ├── WikiRefsOptionsEditPostHandler.php
│   │   │   │   └── WikiRefsOptionsIndexController.php
│   │   │   ├── AdminResolver.php
│   │   │   └── README.md
│   │   ├── Helpers/
│   │   │   ├── RecentHelps.php
│   │   │   └── Sugust.php
│   │   ├── Tools/
│   │   │   ├── CategoriesController.php
│   │   │   ├── LastController.php
│   │   │   ├── ProcessController.php
│   │   │   ├── ProcessControllerDataTable.php
│   │   │   ├── ProcessTotalController.php
│   │   │   └── StatController.php
│   │   ├── README.md
│   │   └── RecentTranslations.php
│   ├── Layout/
│   │   ├── PageFooter.php
│   │   ├── PageHead.php
│   │   ├── PageHeader.php
│   │   └── PageRunner.php
│   ├── MdwikiSql/
│   │   ├── AddHelper.php
│   │   └── Database.php
│   ├── Results27/
│   │   ├── getcats.php
│   │   ├── GetResults.php
│   │   └── README.md
│   ├── SQLorAPI/
│   │   ├── ApiOrSqlService.php
│   │   ├── BaseTable.php
│   │   ├── CategoriesTable.php
│   │   ├── InProcessTable.php
│   │   ├── LeaderboardTable.php
│   │   ├── PagesTable.php
│   │   ├── QidsTable.php
│   │   ├── RecentTable.php
│   │   ├── SettingsTable.php
│   │   ├── TitlesTable.php
│   │   ├── UsersTable.php
│   │   └── ViewsTable.php
│   ├── Tables/
│   │   ├── LangsTables.php
│   │   └── MainTables.php
│   ├── User/
│   │   ├── AccessKeyRepository.php
│   │   ├── CoordinatorRepository.php
│   │   ├── CurrentUser.php
│   │   ├── README.md
│   │   ├── SessionManager.php
│   │   └── UserCookieService.php
│   ├── Utils/
│   │   ├── Html.php
│   │   ├── Html2.php
│   │   ├── HtmlUrls.php
│   │   └── SidebarMenu.php
│   ├── autoload.php
│   ├── bootstrap.php
│   ├── CSRFManager.php
│   ├── Logger.php
│   ├── NotFound.php
│   ├── README.md
│   └── Settings.php
├── css/
│   ├── sidebar-desktop.css
│   └── sidebar-mobile.css
├── js/
│   ├── add_by_url.js
│   ├── autocomplate.js
│   ├── fix_u_targets.js
│   ├── footer.js
│   ├── reports-script.js
│   └── sidebar.js
├── bootstrap.php
├── index.php
├── sugust.php
└── tools.php

```