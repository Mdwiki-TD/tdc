<?php

namespace AdminPost;

/*
Usage:
use AdminPost\AdminPostHandler;
use AdminPost\UserTableHandler;
use AdminPost\ProjectsHandler;
use AdminPost\CampaignsHandler;
use AdminPost\SettingsHandler;
use AdminPost\TranslateTypeHandler;
use AdminPost\QidsHandler;
use AdminPost\EmailsHandler;
use AdminPost\AddPagesHandler;

// For user-based tables (admins, full_translators, users_no_inprocess):
$handler = new UserTableHandler('coordinator');
$handler->handleRequest($_POST);
$handler->render();

// For projects:
$handler = new ProjectsHandler();
$handler->handleRequest($_POST);
$handler->render();

// For qids:
$handler = new QidsHandler($_GET["qid_table"] ?? 'qids');
$handler->handleRequest($_POST);
$handler->render();
*/

use function APICalls\MdwikiSql\execute_query;
use function Utils\Html\div_alert;
use function TDWIKI\csrf\verify_csrf_token;

/**
 * Abstract base class for handling admin POST requests.
 * Provides common functionality for CSRF verification, row processing,
 * and success/error message handling.
 */
abstract class AdminPostHandler
{
    protected array $errors = [];
    protected array $texts = [];
    protected string $tableName;

    public function __construct(string $tableName = '')
    {
        $this->tableName = $tableName;
    }

    /**
     * Handle the POST request if CSRF token is valid.
     */
    public function handleRequest(array $postData): bool
    {
        if (!verify_csrf_token()) {
            return false;
        }

        $rows = $postData['rows'] ?? [];
        foreach ($rows as $key => $row) {
            $this->processRow($row);
        }

        return true;
    }

    /**
     * Process a single row of data.
     * Must be implemented by child classes.
     */
    abstract protected function processRow(array $row): void;

    /**
     * Render success and error messages.
     */
    public function render(): void
    {
        echo div_alert($this->texts, 'success');
        echo div_alert($this->errors, 'danger');
    }

    /**
     * Add a success message.
     */
    protected function addSuccess(string $message): void
    {
        $this->texts[] = $message;
    }

    /**
     * Add an error message.
     */
    protected function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * Delete a record by ID.
     */
    protected function deleteById(string $id, string $identifier = ''): bool
    {
        $query = "DELETE FROM {$this->tableName} WHERE id = ?";
        $result = execute_query($query, [$id]);

        if ($result === false) {
            $this->addError("Failed to delete {$identifier}.");
            return false;
        }

        $this->addSuccess("{$identifier} deleted.");
        return true;
    }

    /**
     * Get all error messages.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get all success messages.
     */
    public function getTexts(): array
    {
        return $this->texts;
    }
}

/**
 * Handler for user-based tables (coordinator, full_translators, users_no_inprocess).
 * Handles insert/update/delete operations with active status tracking.
 */
class UserTableHandler extends AdminPostHandler
{
    protected function processRow(array $row): void
    {
        $id = $row['id'] ?? '';
        $del = $row['del'] ?? '';
        $user = trim($row['user'] ?? '');

        // Handle deletion
        if (!empty($del) && !empty($id)) {
            $this->deleteById($id, "User $user");
            return;
        }

        // Check for active status changes
        $active = $row['active'] ?? '';
        $activeOriginal = $row['active_orginal_value'] ?? '';

        // Skip if no change and existing record
        if ($active === $activeOriginal && !empty($id)) {
            return;
        }

        // Insert or update user
        if (!empty($user)) {
            $this->upsertUser($user, $active, $id);
        }
    }

    /**
     * Insert or update a user record.
     */
    protected function upsertUser(string $user, string $active, string $id): void
    {
        $query = <<<SQL
            INSERT INTO {$this->tableName} (user, active)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE
                active = VALUES(active)
        SQL;

        $result = execute_query($query, [$user, $active]);

        if ($result === false) {
            $this->addError("Failed to add user $user.");
        } else {
            $message = empty($id) ? "User $user Added." : "User $user Updated.";
            $this->addSuccess($message);
        }
    }
}

/**
 * Handler for projects table.
 */
class ProjectsHandler extends AdminPostHandler
{
    public function __construct()
    {
        parent::__construct('projects');
    }

    protected function processRow(array $row): void
    {
        $gId = $row['g_id'] ?? '';
        $del = $row['del'] ?? '';
        $gTitle = trim($row['g_title'] ?? '');

        // Handle deletion
        if (!empty($del) && !empty($gId)) {
            $query = "DELETE FROM projects WHERE g_id = ?";
            $result = execute_query($query, [$gId]);
            $this->addSuccess("Project $gTitle deleted.");
            return;
        }

        if (empty($gTitle)) {
            return;
        }

        // Insert or update project
        $this->upsertProject($gTitle, $gId);
    }

    /**
     * Insert or update a project.
     */
    protected function upsertProject(string $gTitle, string $gId): void
    {
        if (empty($gId)) {
            $query = "INSERT INTO projects (g_title) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM projects WHERE g_title = ?)";
            $params = [$gTitle, $gTitle];
            $message = "Project $gTitle Added.";
        } else {
            $query = "UPDATE projects SET g_title = ? WHERE g_id = ?";
            $params = [$gTitle, $gId];
            $message = "Project $gTitle Updated.";
        }

        execute_query($query, $params);
        $this->addSuccess($message);
    }
}

/**
 * Handler for campaigns/categories table.
 */
class CampaignsHandler extends AdminPostHandler
{
    private string $defaultCat = '';

    public function __construct()
    {
        parent::__construct('categories');
    }

    /**
     * Handle the POST request for campaigns.
     */
    public function handleRequest(array $postData): bool
    {
        if (!verify_csrf_token()) {
            return false;
        }

        $this->defaultCat = $postData['default_cat'] ?? '';

        // Process existing rows
        $rows = $postData['rows'] ?? [];
        foreach ($rows as $key => $row) {
            $this->processRow($row);
        }

        // Process new rows
        $newRows = $postData['new'] ?? [];
        foreach ($newRows as $key => $row) {
            $this->processNewRow($row);
        }

        return true;
    }

    protected function processRow(array $row): void
    {
        $id = $row['id'] ?? '';
        if (empty($id)) {
            return;
        }

        $del = $row['del'] ?? '';

        // Handle deletion
        if (!empty($del) && $del !== "0") {
            $query = "DELETE FROM categories WHERE id = ?";
            execute_query($query, [$del]);
            return;
        }

        $this->updateCategory($row, $id);
    }

    /**
     * Process a new category row.
     */
    protected function processNewRow(array $row): void
    {
        $id = $row['id'] ?? '';
        $camp = $row['camp'];
        $cat1 = $row['cat1'];
        $cat2 = $row['cat2'];
        $dep = $row['dep'];
        $def = ($this->defaultCat == $id) ? 1 : 0;

        $query = "INSERT INTO categories (category, campaign, depth, def, category2) SELECT ?, ?, ?, ?, ?";
        $params = [$cat1, $camp, $dep, $def, $cat2];

        execute_query($query, $params);
    }

    /**
     * Update an existing category.
     */
    protected function updateCategory(array $row, string $id): void
    {
        $camp = $row['camp'];
        $cat1 = $row['cat1'];
        $cat2 = $row['cat2'];
        $dep = $row['dep'];
        $def = ($this->defaultCat == $id) ? 1 : 0;

        $query = <<<SQL
            UPDATE categories
            SET
                campaign = ?,
                category = ?,
                category2 = ?,
                depth = ?,
                def = ?
            WHERE
                id = ?
        SQL;

        $params = [$camp, $cat1, $cat2, $dep, $def, $id];
        execute_query($query, $params);
    }
}

/**
 * Handler for settings table.
 */
class SettingsHandler extends AdminPostHandler
{
    public function __construct()
    {
        parent::__construct('settings');
    }

    protected function processRow(array $row): void
    {
        $id = $row['id'] ?? '';
        $value = $row['value'] ?? '';

        if (empty($id) || $value === "") {
            return;
        }

        $query = "UPDATE settings SET value = ? WHERE id = ?";
        execute_query($query, [$value, $id]);
    }
}

/**
 * Handler for translate_type table.
 */
class TranslateTypeHandler extends AdminPostHandler
{
    private bool $showCloseButton = true;
    private bool $hideNav = true;

    public function __construct()
    {
        parent::__construct('translate_type');
    }

    /**
     * Set whether to show navigation hiding script.
     */
    public function setHideNav(bool $hide): self
    {
        $this->hideNav = $hide;
        return $this;
    }

    /**
     * Set whether to show close button.
     */
    public function setShowCloseButton(bool $show): self
    {
        $this->showCloseButton = $show;
        return $this;
    }

    protected function processRow(array $row): void
    {
        $title = trim($row['title'] ?? '');
        $lead = $row['lead'] ?? 0;
        $full = $row['full'] ?? 0;
        $id = $row['id'] ?? '';

        if (empty($title)) {
            $this->addError("Title is required.");
            return;
        }

        $result = $this->upsertTranslateType($title, $lead, $full, $id);

        if ($result === false) {
            $this->addError("Failed to add translate type, title: $title.");
        } else {
            $this->addSuccess("Translate type added successfully, title: $title.");
        }
    }

    /**
     * Insert or update a translate type.
     */
    protected function upsertTranslateType(string $title, $lead, $full, string $id)
    {
        if (empty($id)) {
            $query = "INSERT INTO translate_type (tt_title, tt_lead, tt_full) SELECT ?, ?, ?";
            $params = [$title, $lead, $full];
        } else {
            $query = "UPDATE translate_type SET tt_lead = ?, tt_full = ? WHERE tt_id = ?";
            $params = [$lead, $full, $id];
        }

        return execute_query($query, $params);
    }

    /**
     * Render with optional navigation hiding and close button.
     */
    public function render(): void
    {
        if ($this->hideNav) {
            echo '</div><script>
                $("#mainnav").hide();
                $("#maindiv").hide();
            </script>';
        }

        parent::render();

        if ($this->showCloseButton) {
            echo <<<HTML
                <div class="aligncenter">
                    <a class="btn btn-outline-primary" onclick="window.close()">Close</a>
                </div>
            HTML;
        }
    }
}

/**
 * Handler for qids and qids_others tables.
 */
class QidsHandler extends AdminPostHandler
{
    private bool $showCloseButton = true;
    private bool $hideNav = true;

    public function __construct(string $qidTable = 'qids')
    {
        // Validate table name to prevent SQL injection
        if ($qidTable !== 'qids' && $qidTable !== 'qids_others') {
            $qidTable = 'qids';
        }
        parent::__construct($qidTable);
    }

    /**
     * Set whether to show navigation hiding script.
     */
    public function setHideNav(bool $hide): self
    {
        $this->hideNav = $hide;
        return $this;
    }

    /**
     * Set whether to show close button.
     */
    public function setShowCloseButton(bool $show): self
    {
        $this->showCloseButton = $show;
        return $this;
    }

    protected function processRow(array $row): void
    {
        $title = trim($row['title'] ?? '');
        $qid = trim($row['qid'] ?? '');
        $id = $row['id'] ?? '';

        // Validate required fields
        if (empty($title)) {
            $this->addError("Title is required. qid=($qid)");
            return;
        }

        if (empty($qid)) {
            $this->addError("Qid is required. title=($title)");
            return;
        }

        // Check for duplicate qid
        if (!$this->validateQidNotDuplicate($qid, $id, $title)) {
            return;
        }

        // Check for duplicate title
        if (!$this->validateTitleNotDuplicate($title, $id, $qid)) {
            return;
        }

        // Insert or update
        if (empty($id)) {
            $this->addNewQid($qid, $title);
        } else {
            $this->updateExistingQid($qid, $id, $title);
        }
    }

    /**
     * Validate that the QID is not already used by another record.
     */
    protected function validateQidNotDuplicate(string $qid, string $id, string $title): bool
    {
        $txTab = \APICalls\MdwikiSql\check_one("*", "qid", $qid, $this->tableName);

        if ($txTab) {
            $txId = $txTab['id'];
            $titleOfQid = $txTab['title'];

            if (!empty($id) && $txId != $id) {
                $this->addError("Qid:($qid) already used in database with with id:($txId).");
                return false;
            }

            if (!empty($titleOfQid) && empty($id) && $titleOfQid != $title) {
                $this->addError("Qid:($qid) already used in database with title:($titleOfQid).");
                return false;
            }
        }

        return true;
    }

    /**
     * Validate that the title is not already used by another record.
     */
    protected function validateTitleNotDuplicate(string $title, string $id, string $qid): bool
    {
        $ttTab = \APICalls\MdwikiSql\check_one("*", "title", $title, $this->tableName);

        if ($ttTab) {
            $qidOfTitle = $ttTab['qid'];
            $ttId = $ttTab['id'];

            if (!empty($id) && $ttId != $id) {
                $this->addError("Title:($title) already used in database with qid:($qidOfTitle), new qid:($qid)");
                return false;
            }

            if (empty($id) && !empty($qidOfTitle) && $qidOfTitle != $qid) {
                $this->addError("Title:($title) already used in database with qid:($qidOfTitle), new qid:($qid)");
                return false;
            }
        }

        return true;
    }

    /**
     * Add a new QID record.
     */
    protected function addNewQid(string $qid, string $title): void
    {
        $this->insertOrUpdateQid('', $title, $qid);

        $qidOfTitle = \APICalls\MdwikiSql\check_one("qid", "title", $title, $this->tableName);

        if (!empty($qidOfTitle) && $qidOfTitle == $qid) {
            $this->addSuccess("Qid added successfully for title: $title.");
        } else {
            $this->addError("Failed to add Qid for title: $title. qid_of_title:$qidOfTitle");
        }
    }

    /**
     * Update an existing QID record.
     */
    protected function updateExistingQid(string $qid, string $id, string $title): void
    {
        $this->insertOrUpdateQid($id, $title, $qid);

        $qidOfTitle = \APICalls\MdwikiSql\check_one("qid", "title", $title, $this->tableName);

        if (!empty($qidOfTitle) && $qidOfTitle == $qid) {
            $this->addSuccess("Data Changes successfully of title: $title, Qid: $qid");
        } else {
            $this->addError("Failed to chanhe data of title: $title, Qid: $qid. Found: qid in db:$qidOfTitle");
        }
    }

    /**
     * Insert or update a QID record in the database.
     */
    protected function insertOrUpdateQid(string $id, string $title, string $qid): void
    {
        $tableName = $this->tableName;

        $query = "INSERT INTO $tableName (title, qid) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM $tableName WHERE (title = ? OR qid = ?))";
        $params = [$title, $qid, $title, $qid];

        if (!empty($id)) {
            $query = "UPDATE $tableName SET title = ?, qid = ? WHERE id = ?";
            $params = [$title, $qid, $id];
        }

        execute_query($query, $params);

        // Also update any records with matching title but no qid
        if (!empty($qid)) {
            $query2 = "UPDATE $tableName SET qid = ? WHERE title = ? and (qid = '' OR qid IS NULL)";
            execute_query($query2, [$qid, $title]);
        }
    }

    /**
     * Render with table info, navigation hiding, and close button.
     */
    public function render(): void
    {
        if ($this->hideNav) {
            echo '</div><script>
                $("#mainnav").hide();
                $("#maindiv").hide();
            </script>';
        }

        // Add table name to messages
        if (!empty($this->texts)) {
            $this->texts[] = "table:({$this->tableName})";
        } elseif (!empty($this->errors)) {
            $this->errors[] = "table:({$this->tableName})";
        }

        parent::render();

        if ($this->showCloseButton) {
            echo <<<HTML
                <div class="aligncenter">
                    <a class="btn btn-outline-primary" onclick="window.close()">Close</a>
                </div>
            HTML;
        }
    }
}

/**
 * Handler for emails/users table.
 */
class EmailsHandler extends AdminPostHandler
{
    private bool $showCloseButton = true;
    private bool $hideNav = true;
    protected string $rowsKey = 'emails';

    public function __construct()
    {
        parent::__construct('users');
    }

    /**
     * Set whether to show navigation hiding script.
     */
    public function setHideNav(bool $hide): self
    {
        $this->hideNav = $hide;
        return $this;
    }

    /**
     * Set whether to show close button.
     */
    public function setShowCloseButton(bool $show): self
    {
        $this->showCloseButton = $show;
        return $this;
    }

    /**
     * Handle the POST request if CSRF token is valid.
     * Overridden to use 'emails' key instead of 'rows'.
     */
    public function handleRequest(array $postData): bool
    {
        if (!isset($postData[$this->rowsKey]) || !verify_csrf_token()) {
            return false;
        }

        $rows = $postData[$this->rowsKey] ?? [];
        foreach ($rows as $key => $row) {
            $this->processRow($row);
        }

        return true;
    }

    protected function processRow(array $row): void
    {
        $user = $row['username'] ?? '';
        $email = $row['email'] ?? '';
        $wiki = $row['wiki'] ?? '';
        $project = $row['project'] ?? '';
        $userId = $row['user_id'] ?? '';

        // Validate required fields
        if (empty($user)) {
            $this->addError("Username is required.");
            return;
        }

        $user = trim($user);
        $email = trim($email);

        // Validate email format if not empty
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError("Invalid Email format");
            $email = '';
        }

        $wiki = trim($wiki);
        $project = trim($project);

        // Check for duplicate user
        if (!$this->validateUserNotDuplicate($user, $userId)) {
            return;
        }

        // Insert or update user
        if (empty($userId)) {
            \APICalls\MdwikiSql\sql_add_user($user, $email, $wiki, $project);
            $this->addSuccess("User:($user) added successfully.");
        } else {
            \APICalls\MdwikiSql\sql_update_user($user, $email, $wiki, $project, $userId);
            $this->addSuccess("User:($user) updated successfully.");
        }
    }

    /**
     * Validate that the user is not already in the database.
     */
    protected function validateUserNotDuplicate(string $user, string $userId): bool
    {
        $ttTab = \APICalls\MdwikiSql\check_one("*", "username", $user, "users");

        if ($ttTab) {
            $ttUsername = $ttTab['username'];
            $ttId = $ttTab['user_id'];

            if (!empty($userId) && $ttId != $userId) {
                $this->addError("User:($user) already in database with user_id:($ttId).");
                return false;
            }

            if (empty($userId) && !empty($ttUsername)) {
                $this->addError("User:($user) already in database with user_id:($ttId).");
                return false;
            }
        }

        return true;
    }

    /**
     * Render with navigation hiding and close button.
     */
    public function render(): void
    {
        if ($this->hideNav) {
            echo '</div><script>
                $("#mainnav").hide();
                $("#maindiv").hide();
            </script>';
        }

        parent::render();

        if ($this->showCloseButton) {
            echo <<<HTML
                <div class="aligncenter">
                    <a class="btn btn-outline-primary" onclick="window.close()">Close</a>
                </div>
            HTML;
        }
    }
}

/**
 * Handler for adding pages.
 * Requires add_post.php to be included before use.
 */
class AddPagesHandler extends AdminPostHandler
{
    public function __construct()
    {
        parent::__construct('pages');
    }

    protected function processRow(array $row): void
    {
        $mdtitle = $row['mdtitle'] ?? '';
        $cat = rawurldecode($row['cat'] ?? '');
        $type = $row['type'] ?? '';
        $user = rawurldecode($row['user'] ?? '');
        $lang = $row['lang'] ?? '';
        $target = $row['target'] ?? '';
        $pupdate = $row['pupdate'] ?? '';
        $word = $row['word'] ?? '';

        // Validate required fields
        if (!empty($mdtitle) && !empty($lang) && !empty($user)) {
            $result = \Add\AddPost\add_pages_to_db($mdtitle, $type, $cat, $lang, $user, $target, $pupdate, $word);

            if ($result === false) {
                $this->addError("Failed to add translations.");
            } else {
                $this->addSuccess("Translations added successfully.");
            }
        } else {
            $this->addError("Failed to add translations. Missing required fields.");
        }
    }
}
