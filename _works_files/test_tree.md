```
tests/
├── app/
│   ├── ApiClients/
│   │   ├── MdwikiApiTest.php
│   │   └── WikiApiTest.php
│   ├── Controllers/
│   │   └── AppRouterTest.php
│   ├── Coordinator/
│   │   ├── Admin/
│   │   │   ├── Add/
│   │   │   │   ├── AddIndexControllerTest.php
│   │   │   │   └── AddPostProcessorTest.php
│   │   │   ├── add/
│   │   │   │   └── PostTest.php
│   │   │   ├── Admins/
│   │   │   │   ├── AdminsIndexControllerTest.php
│   │   │   │   └── AdminsPostProcessorTest.php
│   │   │   ├── Campaigns/
│   │   │   │   ├── CampaignsIndexControllerTest.php
│   │   │   │   └── CampaignsPostProcessorTest.php
│   │   │   ├── Common/
│   │   │   │   ├── AbstractControllerNoPostTest.php
│   │   │   │   ├── AbstractControllerTest.php
│   │   │   │   ├── AbstractEditControllerTest.php
│   │   │   │   └── AbstractPostHandlerTest.php
│   │   │   ├── FullTranslators/
│   │   │   │   ├── FullTranslatorsIndexControllerTest.php
│   │   │   │   └── FullTranslatorsPostProcessorTest.php
│   │   │   ├── LastCoord/
│   │   │   │   └── LastCoordIndexControllerTest.php
│   │   │   ├── pages_users_to_main/
│   │   │   │   ├── FixItPostTest.php
│   │   │   │   ├── FixItTest.php
│   │   │   │   └── PagesUsersToMainIndexControllerTest.php
│   │   │   ├── PagesUsersToMain/
│   │   │   │   ├── FixItControllerTest.php
│   │   │   │   ├── FixItPostProcessorTest.php
│   │   │   │   └── PagesUsersToMainIndexControllerTest.php
│   │   │   ├── projects/
│   │   │   │   ├── PostTest.php
│   │   │   │   └── ProjectsIndexControllerTest.php
│   │   │   ├── Projects/
│   │   │   │   ├── ProjectsIndexControllerTest.php
│   │   │   │   └── ProjectsPostProcessorTest.php
│   │   │   ├── qids/
│   │   │   │   ├── EditQidTest.php
│   │   │   │   └── QidsIndexControllerTest.php
│   │   │   ├── Qids/
│   │   │   │   ├── EditQidControllerTest.php
│   │   │   │   ├── QidsIndexControllerTest.php
│   │   │   │   └── QidsPostProcessorTest.php
│   │   │   ├── Reports/
│   │   │   │   ├── ReportsIndex2ControllerTest.php
│   │   │   │   └── ReportsIndexControllerTest.php
│   │   │   ├── Settings/
│   │   │   │   ├── SettingsIndexControllerTest.php
│   │   │   │   └── SettingsPostProcessorTest.php
│   │   │   ├── translated/
│   │   │   │   └── EditPagePostHandlerTest.php
│   │   │   ├── Translated/
│   │   │   │   ├── EditPageControllerTest.php
│   │   │   │   ├── EditPagePostHandlerTest.php
│   │   │   │   └── TranslatedIndexControllerTest.php
│   │   │   ├── TranslateType/
│   │   │   │   ├── EditTranslateTypeControllerTest.php
│   │   │   │   ├── TranslateTypeIndexControllerTest.php
│   │   │   │   └── TtPostProcessorTest.php
│   │   │   ├── users/
│   │   │   │   ├── EditUserTest.php
│   │   │   │   ├── MsgTest.php
│   │   │   │   ├── PostTest.php
│   │   │   │   └── UsersIndexControllerTest.php
│   │   │   ├── Users/
│   │   │   │   ├── EditUserControllerTest.php
│   │   │   │   ├── EditUserPostProcessorTest.php
│   │   │   │   ├── MsgControllerTest.php
│   │   │   │   └── UsersIndexControllerTest.php
│   │   │   ├── UsersNotInprocess/
│   │   │   │   ├── UsersNotInprocessIndexControllerTest.php
│   │   │   │   └── UsersNotInprocessPostProcessorTest.php
│   │   │   ├── WikiRefsOptions/
│   │   │   │   ├── WikiRefsOptionsEditControllerTest.php
│   │   │   │   ├── WikiRefsOptionsEditPostHandlerTest.php
│   │   │   │   └── WikiRefsOptionsIndexControllerTest.php
│   │   │   └── AdminResolverTest.php
│   │   ├── Helpers/
│   │   │   ├── RecentHelpsTest.php
│   │   │   └── SugustTest.php
│   │   ├── Tools/
│   │   │   ├── CategoriesControllerTest.php
│   │   │   ├── LastControllerTest.php
│   │   │   ├── ProcessControllerDataTableTest.php
│   │   │   ├── ProcessControllerTest.php
│   │   │   ├── ProcessTotalControllerTest.php
│   │   │   └── StatControllerTest.php
│   │   └── RecentTranslationsTest.php
│   ├── Layout/
│   │   ├── PageFooterTest.php
│   │   ├── PageHeaderTest.php
│   │   ├── PageHeadTest.php
│   │   └── PageRunnerTest.php
│   ├── MdwikiSql/
│   │   ├── AddHelperTest.php
│   │   └── DatabaseTest.php
│   ├── Results27/
│   │   ├── GetCatsTest.php
│   │   └── GetResultsTest.php
│   ├── SQLorAPI/
│   │   ├── ApiOrSqlServiceTest.php
│   │   ├── BaseTableTest.php
│   │   ├── CategoriesTableTest.php
│   │   ├── InProcessTableTest.php
│   │   ├── LeaderboardTableTest.php
│   │   ├── PagesTableTest.php
│   │   ├── QidsTableTest.php
│   │   ├── RecentTableTest.php
│   │   ├── SettingsTableTest.php
│   │   ├── TitlesTableTest.php
│   │   ├── UsersTableTest.php
│   │   └── ViewsTableTest.php
│   ├── Tables/
│   │   ├── LangsTablesTest.php
│   │   └── MainTablesTest.php
│   ├── User/
│   │   ├── AccessKeyRepositoryTest.php
│   │   ├── CoordinatorRepositoryTest.php
│   │   ├── CurrentUserTest.php
│   │   ├── SessionManagerTest.php
│   │   └── UserCookieServiceTest.php
│   ├── Utils/
│   │   ├── Html2Test.php
│   │   ├── HtmlTest.php
│   │   ├── HtmlUrlsTest.php
│   │   └── SidebarMenuTest.php
│   ├── CSRFManagerTest.php
│   ├── LoggerTest.php
│   ├── NotFoundTest.php
│   └── SettingsTest.php
└── bootstrap.php

```