```
Admin/
├── Add/
│   ├── AddIndexController.php
│   └── AddPostProcessor.php
├── Admins/
│   ├── AdminsIndexController.php
│   └── AdminsPostProcessor.php
├── Campaigns/
│   ├── CampaignsIndexController.php
│   └── CampaignsPostProcessor.php
├── Common/
│   ├── AbstractController.php
│   ├── AbstractControllerNoPost.php
│   ├── AbstractEditController.php
│   ├── AbstractPostHandler.php
│   ├── AbstractUserListIndexController.php
│   ├── AbstractUserListPostHandler.php
│   └── BaseController.php
├── FullTranslators/
│   ├── FullTranslatorsIndexController.php
│   └── FullTranslatorsPostProcessor.php
├── LastCoord/
│   └── LastCoordIndexController.php
├── PagesUsersToMain/
│   ├── FixItController.php
│   ├── FixItPostProcessor.php
│   └── PagesUsersToMainIndexController.php
├── Projects/
│   ├── ProjectsIndexController.php
│   └── ProjectsPostProcessor.php
├── Qids/
│   ├── EditQidController.php
│   ├── QidsIndexController.php
│   └── QidsPostProcessor.php
├── Reports/
│   ├── ReportsIndex2Controller.php
│   └── ReportsIndexController.php
├── Settings/
│   ├── EditSettingsController.php
│   ├── EditSettingsProcessor.php
│   ├── SettingsIndexController.php
│   └── SettingsPostProcessor.php
├── Translated/
│   ├── EditPageController.php
│   ├── EditPagePostHandler.php
│   └── TranslatedIndexController.php
├── TranslateType/
│   ├── EditTranslateTypeController.php
│   ├── TranslateTypeIndexController.php
│   └── TtPostProcessor.php
├── Users/
│   ├── EditUserController.php
│   ├── EditUserPostProcessor.php
│   ├── MsgController.php
│   └── UsersIndexController.php
├── UsersNotInprocess/
│   ├── UsersNotInprocessIndexController.php
│   └── UsersNotInprocessPostProcessor.php
├── WikiRefsOptions/
│   ├── WikiRefsOptionsEditController.php
│   ├── WikiRefsOptionsEditPostHandler.php
│   └── WikiRefsOptionsIndexController.php
├── AdminResolver.php
└── README.md
```

## Shared Base Classes

The `Common/` directory provides base classes to eliminate duplicate code across controllers and post handlers:

- **`AbstractPostHandler`**: Base class for all POST processing handlers. Provides error/success message tracking, CSRF verification, `deleteById()`, and `reportResult()`.
- **`AbstractUserListPostHandler`**: Extends `AbstractPostHandler`. Handles simple user/admin list table operations (insert, update `is_active`, delete row) for tables with `(id, username/user, is_active)`.
- **`AbstractUserListIndexController`**: Extends `AbstractController`. Handles rendering editable tables and new row controls for simple user/admin list tables.
- **`AbstractEditController`**: Base class for single-record add/edit controllers. Provides HTML component helpers `inputGroup()` and `switchField()` for generating Bootstrap form inputs.

## How to Add a Simple User/Admin List CRUD Page

1. **Create the POST Processor** extending `AbstractUserListPostHandler`:
   ```php
   namespace App\Coordinator\Admin\MyFeature;

   use App\Coordinator\Admin\Common\AbstractUserListPostHandler;

   class MyFeaturePostProcessor extends AbstractUserListPostHandler
   {
       protected function getTableName(): string { return 'my_table'; }
       protected function getUsernameColumn(): string { return 'user'; }
       protected function getRecords(): array { return $this->usersTable->getMyTableRecords(); }
   }
   ```

2. **Create the Index Controller** extending `AbstractUserListIndexController`:
   ```php
   namespace App\Coordinator\Admin\MyFeature;

   use App\Coordinator\Admin\Common\AbstractUserListIndexController;

   class MyFeatureIndexController extends AbstractUserListIndexController
   {
       protected function createPostProcessor(): object { return new MyFeaturePostProcessor(); }
       protected function getTyName(): string { return 'my_feature'; }
       protected function getCardTitle(): string { return 'My Feature Title:'; }
       protected function getUsersData(): array { return $this->usersTable->getMyTableRecords(); }
   }
   ```

3. **Register Route in `AdminResolver.php`**:
   Add `'my_feature' => 'App\\Coordinator\\Admin\\MyFeature\\MyFeatureIndexController'` to the `$Controllers` map in `AdminResolver.php`.
