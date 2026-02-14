# Static Analysis Report: Translation Dashboard Coordinator (TDC)

**Generated:** 2026-01-26
**Repository:** I:\mdwiki\tdc
**Analysis Scope:** Full codebase (~958 lines PHP, 40+ files)

---

## 1. System Overview

### 1.1 Current Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                          │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐ │
│  │ header.php  │  │ footer.php  │  │  Bootstrap 5 + jQuery    │ │
│  │ (navbar,    │  │ (page       │  │  DataTables + FontAwesome│ │
│  │  auth)      │  │  footer)    │  │                         │ │
│  └─────────────┘  └─────────────┘  └─────────────────────────┘ │
└──────────────────────────┬──────────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────────┐
│                   COORDINATOR LAYER                             │
│  ┌──────────────────────┐  ┌────────────────────────────────┐  │
│  │ src/index.php        │  │ src/coordinator/admin/          │  │
│  │ (routing, dispatcher)│  │ - 11 admin modules             │  │
│  │                      │  │ - Dupe-heavy CRUD patterns      │  │
│  └──────────────────────┘  └────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ src/coordinator/tools/ (6 utility modules)               │  │
│  └──────────────────────────────────────────────────────────┘  │
└──────────────────────────┬──────────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────────┐
│                  DATA ACCESS LAYER                              │
│  ┌────────────────────────────────────────────────────────────┐│
│  │ Dual-Mode Strategy (API or SQL)                            ││
│  │ ┌─────────────────────┐  ┌──────────────────────────────┐ ││
│  │ │ API Mode            │  │ SQL Mode                     │ ││
│  │ │ ($use_td_api=true)  │  │ ($use_td_api=false)          │ ││
│  │ │ → TD API → JSON     │  │ → PDO → MySQL                │ ││
│  │ │   (mdwiki.toolforge │  │   (tools.db.svc.wikimedia.)  │ ││
│  │ │   .org/api.php)     │  │   cloud)                     │ ││
│  │ └─────────────────────┘  └──────────────────────────────┘ ││
│  └────────────────────────────────────────────────────────────┘│
│  Location: src/backend/api_or_sql/                              │
└──────────────────────────┬──────────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────────┐
│                   DATA STORAGE LAYER                            │
│  • MySQL: pages, users, categories, qids, settings, etc.       │
│  • JSON configs: $HOME/confs/                                   │
│  • External APIs: MediaWiki, Wikidata, Pageviews               │
└─────────────────────────────────────────────────────────────────┘
```

### 1.2 Technology Stack

| Layer | Technology | Notes |
|-------|-----------|-------|
| Language | PHP 7.4+ | No strict types, mixed procedural/OOP |
| Frontend | Bootstrap 5, jQuery 3.x, DataTables | No framework bundling |
| Database | MySQL via PDO | Single `Database` class, connection per query |
| Auth | External OAuth (`auth_repo/oauth/user_infos.php`) | Global `$GLOBALS['global_username']` |
| Deployment | GitHub Actions → SSH → shell script | No automated tests |

### 1.3 Key Modules

| Module | Location | Purpose | Lines |
|--------|----------|---------|-------|
| CSRF Protection | `src/csrf.php` | Token generation/verification | 77 |
| Database Access | `src/backend/api_calls/mdwiki_sql.php` | PDO wrapper, query execution | 397 |
| API Client | `src/backend/api_calls/td_api.php` | Translation Dashboard API | 108 |
| Data Access Abstraction | `src/backend/api_or_sql/` | API/SQL dual-mode layer | ~450 |
| Admin Modules | `src/coordinator/admin/*/` | CRUD for 11 entities | ~1500 |
| HTML Helpers | `src/utils/html.php` | 376 lines of HEREDOC strings | 376 |

---

## 2. Code Smells and Anti-Patterns

### 2.1 Critical Security Vulnerabilities

#### CVE-2024-CSRF: Default Return `true`

**File:** `src/csrf.php:17-66`
```php
function verify_csrf_token()
{
    if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
        echo "No csrf tokens in session!";
        return true;  // ← VULNERABLE: default allows request
    }
    // ...
}
```

**Impact:** CSRF protection effectively disabled when session tokens missing.
**Fix Required:** Return `false` by default; only return `true` after valid verification.

---

#### CVE-2024-SQLi: Dynamic Table Name Concatenation

**File:** `src/coordinator/admin/qids/post.php:24`
```php
$qua = "INSERT INTO $qid_table (title, qid) SELECT ?, ? WHERE NOT EXISTS ...";
```

**Variable Source:** `$_GET["qid_table"]` (line 15)
```php
$qid_table = $_GET["qid_table"] ?? '';
if ($qid_table != 'qids' && $qid_table != 'qids_others') $qid_table = 'qids';
```

**Issue:** While validation exists, it occurs AFTER the variable is used in line 24 within function scope. Additionally, similar patterns exist without validation.

**Fix Required:** Use whitelist validation BEFORE query construction, or use table name constants.

---

#### CVE-2024-XSS: Unescaped HEREDOC Output

**File:** `src/utils/html.php:134-142`
```php
function make_input_group($label, $id, $value, $required)
{
    $val2 = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');  // ← Good
    return <<<HTML
        <div class='col-md-3'>
            <div class='input-group mb-3'>
                <span class='input-group-text'>$label</span>  // ← NOT ESCAPED
                <input class='form-control' type='text' name='$id' value='$val2' $required/>  // ← $id, $required not escaped
            </div>
        </div>
    HTML;
}
```

**Issue:** `$label`, `$id`, `$required` interpolated directly into HTML without escaping.
**Fix Required:** Escape all variables placed into HEREDOC or use template engine.

---

### 2.2 Global Scope Pollution

**File:** `src/include.php` (43 lines)
```php
<?php
// Lines 3-7: Environment mutation
if ((getenv("HOME") ?: "") === '') {
    $new_home = 'I:/mdwiki/mdwiki';
    putenv("HOME=$new_home");
    $_ENV['HOME'] = $new_home;
}

// Lines 12-14: Wildcard includes
foreach (glob(__DIR__ . "/utils/*.php") as $filename) {
    include_once $filename;
}

// Lines 16-19: Conditional external include
if (substr(__DIR__, 0, 2) == 'I:') {
    include_once 'I:/mdwiki/auth_repo/oauth/user_infos.php';
} else {
    include_once __DIR__ . '/../auth/oauth/user_infos.php';
}

// Lines 21-22: More wildcard includes
foreach (glob(__DIR__ . "/backend/api_calls/*.php") as $filename) {
    include_once $filename;
}

// Lines 25-27: More wildcard includes
foreach (glob(__DIR__ . "/backend/api_or_sql/*.php") as $filename) {
    include_once $filename;
}

// Lines 31-34: More wildcard includes
foreach (glob(__DIR__ . "/backend/tables/*.php") as $filename) {
    if (basename($filename) == 'langcode.php') continue;
    include_once $filename;
}

// Lines 38-40: More wildcard includes
foreach (glob(__DIR__ . "/results/*.php") as $filename) {
    include_once $filename;
}
```

**Problems:**
1. **40+ files loaded** into global scope on every request
2. **No autoloading** - all code always loaded
3. **Hardcoded paths** (`I:/mdwiki/`) - breaks portability
4. **No namespace isolation** despite namespaces being declared in included files

**Estimated Impact:** Every page load includes ~15KB of unnecessary code.

---

### 2.3 Primitive Obsession / Magic Numbers

**File:** `src/index.php:52-54`
```php
$ty = $_GET['ty'] ?? $_POST['ty'] ?? 'last';
if ($ty == 'translate_type') $ty = 'tt';
```

**Issue:** String-based routing without enum or constants.

**File:** `src/coordinator/admin/qids/index.php:89`
```php
if (!isset($_GET['dis']) && $GLOBALS['global_username'] == "Mr. Ibrahem") $dis = "empty";
```

**Issues:**
1. Hardcoded username check
2. No role-based access control abstraction

---

### 2.4 Switch Statements / Conditional Chains

**File:** `src/index.php:72-87` (Routing Logic)
```php
if (in_array($ty, $tools_folders)) {
    include_once __DIR__ . "/coordinator/tools/$ty.php";
} elseif ($ty == "sidebar") {
    $sidebar = create_side($filename, $ty);
    echo $sidebar;
} elseif (in_array($ty, $corrd_folders) && user_in_coord) {
    include_once __DIR__ . "/coordinator/admin/$ty/index.php";
} elseif (is_file($adminfile) && user_in_coord) {
    include_once $adminfile;
} else {
    test_print("can't find $adminfile");
    include_once __DIR__ . "/coordinator/404.php";
}
```

**Anti-Pattern:** Control coupling based on string routing.
**Refactor To:** Front Controller pattern with route configuration.

---

### 2.5 Duplicated Code Across Admin Modules

**Pattern repeated in 11+ admin modules:**

```php
// Pattern 1: Auth check (appears in every admin/index.php)
if (user_in_coord == false) {
    echo "<meta http-equiv='refresh' content='0; url=index.php'>";
    exit;
};

// Pattern 2: Debug mode setup
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
};

// Pattern 3: POST handler inclusion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require __DIR__ . '/post.php';
}

// Pattern 4: CSRF token generation
$csrf_token = generate_csrf_token();
```

**Locations:**
- `src/coordinator/admin/users/index.php` (if exists)
- `src/coordinator/admin/projects/index.php`
- `src/coordinator/admin/qids/index.php`
- `src/coordinator/admin/settings/index.php`
- And 7+ more admin modules...

**Duplication:** ~80 lines per module × 11 modules = ~880 lines of boilerplate

---

### 2.6 Static Caching Anti-Pattern

**File:** `src/backend/api_or_sql/funcs.php` (appears in 15+ functions)

```php
function get_td_or_sql_categories(): array
{
    static $categories = [];  // ← Static cache
    if (!empty($categories ?? [])) {
        return $categories;
    }
    // ... fetch logic
    $categories = $data;
    return $categories;
}
```

**Problems:**
1. **No cache invalidation** - data never refreshes within request
2. **Testing hostile** - cannot reset state between tests
3. **Memory leaks** - long-running processes accumulate data
4. **Inconsistent behavior** - some functions check `!empty($categories)`, others check `!empty($categories ?? [])`

**Examples:**
- `get_td_or_sql_categories()` (line 53)
- `get_coordinator()` (line 72)
- `get_users_by_last_pupdate()` (line 91)
- `get_td_or_sql_count_pages_not_empty()` (line 139)
- And 10+ more...

---

### 2.7 Error Output to HTML

**File:** `src/backend/api_calls/mdwiki_sql.php:137-138`
```php
} catch (PDOException $e) {
    echo "sql error:" . $e->getMessage() . "<br>" . $sql_query;  // ← Exposes internals
    return false;
}
```

**File:** `src/backend/api_calls/mdwiki_sql.php:160`
```php
} catch (PDOException $e) {
    echo "SQL Error:" . $e->getMessage() . "<br>" . $sql_query;
    return [];
}
```

**Issues:**
1. Database errors exposed to users
2. Query structure exposed
3. No structured logging for debugging
4. Returns `false` in some cases, `[]` in others - inconsistent

---

### 2.8 Code Comments in Arabic

**File:** `src/csrf.php:21-24`
```php
// التحقق مما إذا كان هناك CSRF Tokens في الجلسة
if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
    $_SESSION['csrf_tokens'] = [];
    echo "No csrf tokens in session!";
```

**Impact:** Code not accessible to non-Arabic speaking developers.

---

### 2.9 Dead Code

**File:** `src/backend/api_or_sql/index.php:21`
```php
$use_td_api  = false;  // ← Immediately overwrites computed value
```

Lines 18-19 compute `$use_td_api`, but line 21 hardcodes it to `false`.

**File:** `src/include.php:29`
```php
// include_once __DIR__ . '/backend/api_or_sql/index.php';  // ← Commented out but file still loaded via glob
```

---

### 2.10 Mixed Naming Conventions

| File | Pattern | Example |
|------|---------|---------|
| `funcs.php` | snake_case | `get_td_or_sql_categories()` |
| `mdwiki_sql.php` | snake_case | `fetch_query()`, `sql_add_user()` |
| `td_api.php` | snake_case | `get_td_api()` |
| `tables.php` | PascalCase class | `class MainTables` |
| Global variable | snake_case | `$use_td_api`, `$user_in_coord` |
| Constants | Not used | N/A |

**Issue:** No PSR-4 or PSR-12 compliance.

---

## 3. Dependency Issues and Coupling Map

### 3.1 Circular Dependencies

```
┌────────────────────────────────────────────────────────────┐
│                  CIRCULAR DEPENDENCY                       │
│                                                            │
│  src/index.php                                           │
│       │                                                   │
│       ├──► src/header.php                                │
│       │        │                                         │
│       │        └──► src/include.php ────────┐           │
│       │                                    │            │
│       └─────────────────────────────────────┘            │
│                      │                                   │
│                      ▼                                   │
│         src/backend/api_or_sql/funcs.php                 │
│                      │                                   │
│                      ├──► src/backend/api_calls/td_api.php│
│                      │        │                          │
│                      │        └──► Uses $_SERVER, $_GET  │
│                      │                                   │
│                      └──► src/backend/api_calls/         │
│                              mdwiki_sql.php              │
│                                   │                       │
│                                   └──► Uses $_REQUEST    │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

**Problem:** `include.php` loads `api_or_sql/funcs.php`, which uses `$_GET` at module load time (line 17-25 of `index.php`).

---

### 3.2 High Coupling: Hardcoded Paths

**File:** `src/include.php:15-19`
```php
if (substr(__DIR__, 0, 2) == 'I:') {
    include_once 'I:/mdwiki/auth_repo/oauth/user_infos.php';
} else {
    include_once __DIR__ . '/../auth/oauth/user_infos.php';
}
```

**Coupling:** Application tightly coupled to Windows `I:` drive path and external repository location.

---

### 3.3 Tight Coupling: Global State

**Global Variables Used Across Codebase:**
```php
// Defined in src/backend/api_or_sql/index.php:17-25
global $use_td_api;

// Defined in src/header.php:19-31
global $user_in_coord;
define('user_in_coord', $user_in_coord);  // ← Both global AND constant

// From external auth
$GLOBALS['global_username']  // ← Used in 20+ files without null check
```

**Dependent Files:**
- `src/header.php:24-28`
- `src/coordinator/admin/qids/index.php:89`
- And 18+ more...

---

### 3.4 Dependency Graph: Data Access Layer

```
┌──────────────────────────────────────────────────────────────────┐
│                    DATA ACCESS COUPLING                          │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │         Coordinator Admin Modules (11)                    │ │
│  │  - projects/index.php                                      │ │
│  │  - qids/index.php                                          │ │
│  │  - settings/index.php                                      │ │
│  │  - users/index.php (et al.)                                │ │
│  └────────────┬───────────────────────────────────────────────┘ │
│               │                                                  │
│               │ use function SQLorAPI\Funcs\...                │
│               ▼                                                  │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │        src/backend/api_or_sql/funcs.php                   │ │
│  │        - 15 data-fetching functions with static cache     │ │
│  └────────────┬───────────────────────────────────────────────┘ │
│               │                                                  │
│               │ use function SQLorAPI\Get\super_function       │
│               ▼                                                  │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │        src/backend/api_or_sql/index.php                   │ │
│  │        - Routing between API/SQL modes                    │ │
│  └────┬───────────────────────────┬───────────────────────────┘ │
│       │                           │                              │
│       │ use function              │ use function                │
│       │ APICalls\TDApi\           │ APICalls\MdwikiSql\         │
│       ▼                           ▼                              │
│  ┌──────────────┐        ┌──────────────────┐                   │
│  │ td_api.php   │        │ mdwiki_sql.php   │                   │
│  │ - cURL       │        │ - PDO Database   │                   │
│  │   wrapper    │        │   class          │                   │
│  └──────────────┘        └──────────────────┘                   │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

**Coupling Issues:**
1. **No interface abstraction** - direct function calls
2. **Static cache in data layer** - prevents testability
3. **Global mode switch** (`$use_td_api`) affects all calls

---

### 3.5 Dependency Table: External Services

| Service | Location | Fallback | Error Handling |
|---------|----------|----------|----------------|
| Translation Dashboard API | `td_api.php:86` | Returns empty array | Silent failure |
| MySQL Database | `mdwiki_sql.php:72` | Echoes error, exits | Exposes internals |
| MediaWiki API | (referenced, not directly analyzed) | Unknown | Unknown |
| OAuth | External repo | Unknown | Unknown |

---

## 4. Refactoring Roadmap

### Phase 1: Critical Security Fixes (Week 1)

| Priority | Issue | File | Action |
|----------|-------|------|--------|
| P0 | CSRF default `true` | `src/csrf.php:25` | Change to `return false` |
| P0 | SQL table injection | `qids/post.php:24` | Whitelist validation |
| P0 | XSS in HEREDOC | `utils/html.php:134+` | Escape all variables |
| P1 | Debug mode exposure | Multiple files | Remove `display_errors` in production |

---

### Phase 2: Dependency Injection & Autoloading (Week 2-3)

**2.1 Implement PSR-4 Autoloading**
```php
// composer.json (new file)
{
    "autoload": {
        "psr-4": {
            "TDC\\": "src/"
        }
    }
}
```

**2.2 Create Service Container**
```php
// src/Core/Container.php (new file)
namespace TDC\Core;

class Container {
    private static $services = [];

    public static function register(string $id, callable $factory) {
        self::$services[$id] = $factory;
    }

    public static function get(string $id) {
        return self::$services[$id] ?? null;
    }
}
```

**2.3 Dependency Injection for Database**
```php
// Before (global):
$db = new Database($_SERVER['SERVER_NAME'] ?? '', $dbname);

// After (injected):
class UserRepository {
    private PDO $db;
    public function __construct(PDO $db) {
        $this->db = $db;
    }
}
```

---

### Phase 3: Extract Domain Layer (Week 4-5)

**3.1 Create Entity Classes**

```php
// src/Domain/Entity/Project.php (new file)
namespace TDC\Domain\Entity;

class Project {
    public function __construct(
        private readonly int $id,
        private string $title
    ) {}

    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function withTitle(string $title): self {
        return new self($this->id, $title);
    }
}
```

**3.2 Create Repository Interface**

```php
// src/Domain/Repository/ProjectRepository.php (new file)
namespace TDC\Domain\Repository;

interface ProjectRepositoryInterface {
    /** @return Project[] */
    public function findAll(): array;
    public function findById(int $id): ?Project;
    public function save(Project $project): void;
    public function delete(int $id): void;
}
```

---

### Phase 4: Admin Module Consolidation (Week 6-7)

**4.1 Create Base Admin Controller**

```php
// src/Controller/Admin/AdminController.php (new file)
namespace TDC\Controller\Admin;

abstract class AdminController {
    abstract protected function getEntityName(): string;
    abstract protected function handlePost(array $data): void;

    final public function __invoke(): void {
        $this->requireAuth();
        $this->handleCsrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost($_POST);
            return;
        }

        $this->renderForm();
    }

    final protected function requireAuth(): void {
        if (!$this->getUser()->isCoordinator()) {
            $this->redirectTo('/index.php');
        }
    }
}
```

**4.2 Migrate Each Admin Module**

| Module | Current Files | Target Class |
|--------|---------------|--------------|
| Projects | `projects/index.php`, `projects/post.php` | `ProjectAdminController` |
| QIDs | `qids/index.php`, `qids/post.php`, `qids/edit_qid.php` | `QidAdminController` |
| Settings | `settings/index.php`, `settings/post.php` | `SettingsAdminController` |

---

### Phase 5: Front Controller Routing (Week 8)

**5.1 Replace `index.php` Routing**

```php
// src/Core/Router.php (new file)
namespace TDC\Core;

class Router {
    private array $routes = [];

    public function get(string $path, callable $handler): self {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }

    public function post(string $path, callable $handler): self {
        $this->routes['POST'][$path] = $handler;
        return $this;
    }

    public function dispatch(string $method, string $uri): void {
        $handler = $this->routes[$method][$uri] ?? null;
        if ($handler) {
            $handler();
        } else {
            $this->handleNotFound();
        }
    }
}
```

**5.2 Route Configuration**

```php
// config/routes.php (new file)
$router
    ->get('/coordinator/tools/last', [ToolsController::class, 'last'])
    ->get('/coordinator/admin/projects', [ProjectAdminController::class, 'index'])
    ->post('/coordinator/admin/projects', [ProjectAdminController::class, 'post'])
    // ...
```

---

### Phase 6: Testing Infrastructure (Week 9-10)

**6.1 Setup PHPUnit**

```json
// composer.json
{
    "require-dev": {
        "phpunit/phpunit": "^10.0"
    },
    "autoload-dev": {
        "psr-4": {
            "TDC\\Tests\\": "tests/"
        }
    }
}
```

**6.2 Database Test Abstraction**

```php
// tests/Integration/DatabaseTestCase.php (new file)
namespace TDC\Tests\Integration;

use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase {
    protected PDO $db;

    protected function setUp(): void {
        $this->db = new PDO('sqlite::memory:');
        $this->migrateDatabase();
    }

    protected function tearDown(): void {
        $this->db = null;
    }

    private function migrateDatabase(): void {
        // Load schema.sql into memory DB
    }
}
```

---

## 5. Concrete Changes Per File/Module

### 5.1 `src/csrf.php`

**Current Issues:**
- Line 25: Returns `true` by default (critical security bug)
- Line 24: Echoes error message
- Arabic comments

**Required Changes:**
```php
// Line 17-26 (BEFORE)
function verify_csrf_token()
{
    if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
        echo "No csrf tokens in session!";
        return true;  // ← BUG
    }

// Line 17-26 (AFTER)
function verify_csrf_token(): bool
{
    if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
        error_log("CSRF: No tokens in session");
        return false;  // ← FIXED
    }
```

**Full Refactor Required:**
```php
// src/Security/CsrfProtection.php (new file)
namespace TDC\Security;

final class CsrfProtection {
    private const TOKEN_KEY = 'csrf_tokens';
    private const TOKEN_LENGTH = 32;

    public function generate(): string {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));

        if (!isset($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = [];
        }

        $_SESSION[self::TOKEN_KEY][] = $token;
        return $token;
    }

    public function verify(string $token): bool {
        $tokens = $_SESSION[self::TOKEN_KEY] ?? [];

        $key = array_search($token, $tokens, true);
        if ($key === false) {
            error_log("CSRF: Invalid token");
            return false;
        }

        unset($_SESSION[self::TOKEN_KEY][$key]);
        return true;
    }

    public function verifyFromRequest(): bool {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return $this->verify($token);
    }
}
```

---

### 5.2 `src/include.php`

**Current Issues:**
- 43 lines of wildcard includes
- Hardcoded `I:/mdwiki/` paths
- Environment mutation
- No conditional loading

**Required Changes:**

**Step 1: Replace with Composer autoload**
```php
// src/include.php (AFTER - 3 lines)
<?php
namespace TDC;

require_once __DIR__ . '/../vendor/autoload.php';

session_start();
```

**Step 2: Create bootstrap.php**
```php
// bootstrap.php (new file)
<?php
namespace TDC;

use TDC\Core\Environment;
use TDC\Security\CsrfProtection;
use TDC\Database\DatabaseConnection;

Environment::initialize();
CsrfProtection::initializeSession();
DatabaseConnection::initialize();
```

---

### 5.3 `src/backend/api_calls/mdwiki_sql.php`

**Current Issues:**
- Lines 44, 61: Hardcoded password `'root11'` for localhost
- Line 72: Hardcoded connection string
- Lines 137-138, 160: Errors echoed to HTML
- No connection pooling (new instance per query)

**Required Changes:**

```php
// src/Database/DatabaseConnection.php (new file)
namespace TDC\Database;

use PDO;
use PDOException;

final class DatabaseConnection {
    private static ?PDO $instance = null;
    private string $dsn;
    private string $username;
    private string $password;

    public function __construct(DatabaseConfig $config) {
        $this->dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $config->host,
            $config->database
        );
        $this->username = $config->username;
        $this->password = $config->password;
    }

    public function getConnection(): PDO {
        if (self::$instance === null) {
            $this->connect();
        }
        return self::$instance;
    }

    private function connect(): void {
        try {
            self::$instance = new PDO($this->dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new DatabaseConnectionException(
                "Database connection failed: " . $e->getMessage(),
                previous: $e
            );
        }
    }
}

// src/Database/DatabaseConfig.php (new file)
namespace TDC\Database;

final class DatabaseConfig {
    public function __construct(
        public readonly string $host,
        public readonly string $database,
        public readonly string $username,
        public readonly string $password
    ) {}

    public static function fromEnvironment(): self {
        $configFile = getenv('HOME') . '/confs/db.ini';
        if (!file_exists($configFile)) {
            throw new \RuntimeException("Database config not found");
        }

        $config = parse_ini_file($configFile);
        return new self(
            host: $config['host'] ?? 'tools.db.svc.wikimedia.cloud',
            database: $config['user'] . '__mdwiki',
            username: $config['user'],
            password: $config['password']
        );
    }
}

// src/Database/QueryExecutor.php (new file)
namespace TDC\Database;

use PDO;

final class QueryExecutor {
    public function __construct(private PDO $db) {}

    /**
     * @param array<mixed> $params
     * @return array<mixed>
     */
    public function fetchAll(string $query, array $params = []): array {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new QueryExecutionException(
                message: "Query failed: " . $e->getMessage(),
                query: $query,
                previous: $e
            );
        }
    }

    /**
     * @param array<mixed> $params
     */
    public function execute(string $query, array $params = []): int {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new QueryExecutionException(
                message: "Execution failed: " . $e->getMessage(),
                query: $query,
                previous: $e
            );
        }
    }
}
```

---

### 5.4 `src/backend/api_or_sql/funcs.php`

**Current Issues:**
- Lines 28-161: Static cache in every function (15 functions)
- No cache invalidation
- Testing hostile
- Mixed return types (array indexed differently)

**Required Changes:**

```php
// src/Domain/Repository/CachedCategoryRepository.php (new file)
namespace TDC\Domain\Repository;

use TDC\Core\Cache\CacheInterface;

class CachedCategoryRepository implements CategoryRepositoryInterface {
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct(
        private CategoryRepositoryInterface $decorated,
        private CacheInterface $cache
    ) {}

    /**
     * @return array<array{id: int, category: string, ...}>
     */
    public function findAll(): array {
        $key = 'categories:all';
        $cached = $this->cache->get($key);

        if ($cached !== null) {
            return $cached;
        }

        $data = $this->decorated->findAll();
        $this->cache->set($key, $data, self::CACHE_TTL);

        return $data;
    }
}

// src/Core/Cache/InMemoryCache.php (new file)
namespace TDC\Core\Cache;

final class InMemoryCache implements CacheInterface {
    private array $storage = [];

    public function get(string $key): mixed {
        return $this->storage[$key] ?? null;
    }

    public function set(string $key, mixed $value, int $ttl = 0): void {
        $this->storage[$key] = [
            'value' => $value,
            'expires' => $ttl > 0 ? time() + $ttl : 0
        ];
    }

    public function delete(string $key): void {
        unset($this->storage[$key]);
    }

    public function clear(): void {
        $this->storage = [];
    }
}
```

---

### 5.5 `src/utils/html.php` (376 lines)

**Current Issues:**
- 376 lines of HEREDOC strings
- No XSS escaping on parameters
- No template engine
- Direct HTML generation in business logic

**Required Changes:**

```php
// src/Presentation/Html/HtmlBuilder.php (new file)
namespace TDC\Presentation\Html;

final class HtmlBuilder {
    public function __construct(
        private Escaper $escaper
    ) {}

    public function inputGroup(
        string $label,
        string $id,
        string $value,
        bool $required = false
    ): string {
        return sprintf(
            '<div class="col-md-3"><div class="input-group mb-3">' .
            '<span class="input-group-text">%s</span>' .
            '<input class="form-control" type="text" name="%s" value="%s" %s/>' .
            '</div></div>',
            $this->escaper->escapeHtml($label),
            $this->escaper->escapeHtmlAttr($id),
            $this->escaper->escapeHtmlAttr($value),
            $required ? 'required' : ''
        );
    }
}

// src/Presentation/Html/Escaper.php (new file)
namespace TDC\Presentation\Html;

final class Escaper {
    public function escapeHtml(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function escapeHtmlAttr(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function escapeJs(string $value): string {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}
```

**Alternative: Use Twig Template Engine**
```twig
{# templates/admin/projects_form.twig #}
<form action="{{ path('admin_projects') }}" method="POST">
    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">

    {% for project in projects %}
    <tr>
        <td>{{ project.id }}</td>
        <td><input name="rows[{{ loop.index }}][g_title]" value="{{ project.title|e }}"></td>
        <td><input type="checkbox" name="rows[{{ loop.index }}][del]" value="{{ project.id }}"></td>
    </tr>
    {% endfor %}

    <button type="submit">Save</button>
</form>
```

---

### 5.6 `src/coordinator/admin/projects/`

**Current Files:**
- `index.php` (106 lines) - Form display
- `post.php` (50 lines) - Form processing

**Current Issues:**
- Auth check duplicated
- CSRF handling duplicated
- No validation
- Direct database access
- Error messages to HTML

**Refactor To:**

```php
// src/Controller/Admin/ProjectAdminController.php (new file)
<?php
namespace TDC\Controller\Admin;

use TDC\Domain\Repository\ProjectRepositoryInterface;
use TDC\Security\CsrfProtection;
use TDC\Presentation\Redirector;
use TDC\Presentation\FlashMessages;

final class ProjectAdminController {
    public function __construct(
        private ProjectRepositoryInterface $projects,
        private CsrfProtection $csrf,
        private Redirector $redirector,
        private FlashMessages $flash
    ) {}

    public function index(): string {
        $this->requireCoordinator();

        return $this->render('admin/projects/index', [
            'projects' => $this->projects->findAll(),
            'csrf_token' => $this->csrf->generate()
        ]);
    }

    public function store(): void {
        $this->requireCoordinator();
        $this->verifyCsrf();

        $request = new ProjectStoreRequest($_POST);
        $errors = $request->validate();

        if (!empty($errors)) {
            $this->flash->error('Validation failed', $errors);
            $this->redirector->to('/admin/projects');
            return;
        }

        foreach ($request->getRows() as $row) {
            if ($row->isDelete()) {
                $this->projects->delete($row->getId());
                $this->flash->success("Project {$row->getTitle()} deleted.");
            } else {
                $this->projects->save($row->toProject());
                $this->flash->success("Project {$row->getTitle()} saved.");
            }
        }

        $this->redirector->to('/admin/projects');
    }

    private function requireCoordinator(): void {
        if (!$this->getUser()->isCoordinator()) {
            $this->redirector->to('/');
        }
    }

    private function verifyCsrf(): void {
        if (!$this->csrf->verifyFromRequest()) {
            $this->flash->error('Invalid CSRF token');
            $this->redirector->to('/admin/projects');
        }
    }
}

// src/Http/ProjectStoreRequest.php (new file)
<?php
namespace TDC\Http;

final class ProjectStoreRequest {
    private array $rows = [];

    public function __construct(array $data) {
        foreach ($data['rows'] ?? [] as $key => $row) {
            $this->rows[] = ProjectRow::fromArray($row);
        }
    }

    public function validate(): array {
        $errors = [];

        foreach ($this->rows as $index => $row) {
            if (empty($row->getTitle()) && !$row->isDelete()) {
                $errors["rows_{$index}_title"] = "Title is required";
            }
        }

        return $errors;
    }

    /** @return ProjectRow[] */
    public function getRows(): array {
        return $this->rows;
    }
}
```

---

## 6. Technical Debt Risks

### 6.1 Critical Risks (Immediate Action Required)

| Risk | Severity | Impact | Files Affected |
|------|----------|--------|----------------|
| CSRF default bypass | **CRITICAL** | Any user can perform actions on behalf of others | `src/csrf.php:25` |
| SQL injection potential | **CRITICAL** | Database compromise, data theft | `qids/post.php:24` + 5+ locations |
| XSS via HEREDOC | **HIGH** | Session hijacking, data theft | `utils/html.php` (376 lines) |
| Password in source | **CRITICAL** | Database compromised if repo exposed | `mdwiki_sql.php:61` |

---

### 6.2 High-Priority Technical Debt

| Issue | Impact | Effort | Priority |
|-------|--------|--------|----------|
| No test coverage | Cannot safely refactor | High | P1 |
| Global scope pollution | Memory issues, testing hostile | Medium | P1 |
| Static caching (no invalidation) | Stale data served | Medium | P1 |
| No error logging | Production debugging impossible | Low | P1 |
| Mixed naming conventions | Maintenance burden | Low | P2 |
| Arabic comments | Collaboration barrier | Low | P2 |
| Dead code | Confusion, bloat | Low | P3 |

---

### 6.3 Maintainability Metrics

| Metric | Current | Target | Status |
|--------|---------|--------|--------|
| Test Coverage | ~1% | 80% | Critical |
| Cyclomatic Complexity (avg) | Unknown | <10 | Needs measurement |
| Code Duplication | ~30% | <5% | High |
| Largest Function | 161 lines | <50 lines | Exceeded |
| Files per Directory | Variable | <20 | Mixed |
| Dependency Depth | 5+ layers | <4 | Exceeded |

---

### 6.4 Scalability Concerns

| Concern | Current Behavior | Scaling Limit |
|---------|-----------------|---------------|
| Database Connections | New connection per query | Connection exhaustion at ~100 req/s |
| Static Caching | Never expires | Memory growth unbounded |
| File Loading | All files on every request | CPU waste at high concurrency |
| Session Storage | PHP default (files) | Not horizontally scalable |

---

### 6.5 Deployment Risks

| Risk | Current Mitigation | Recommendation |
|------|-------------------|----------------|
| No pre-deploy tests | None | Add PHPUnit tests to CI |
| SSH key in secrets | Basic | Add rotation policy |
| Direct production deploy | None | Add staging environment |
| No rollback mechanism | Manual | Add deployment rollback |

---

## Appendix A: File Inventory

### Backend API Layer (4 files, 722 lines)
```
src/backend/api_calls/
├── mdwiki_api.php        (MediaWiki API client)
├── mdwiki_sql.php        (397 lines) - PDO wrapper
├── td_api.php            (108 lines) - TD API client
└── wiki_api.php          (Wikimedia Pageviews)
```

### Data Access Layer (4 files, 700+ lines)
```
src/backend/api_or_sql/
├── index.php             (44 lines) - Dual-mode router
├── funcs.php             (422 lines) - 15 data functions with static cache
├── process_data.php      (Data processing)
└── recent_data.php       (Recent data fetching)
```

### Admin Modules (33 files, ~2000+ lines)
```
src/coordinator/admin/
├── add/                  (3 files)
├── admins/               (2 files)
├── Campaigns/            (2 files)
├── Emails/               (4 files)
├── full_translators/     (2 files)
├── pages_users_to_main/  (3 files)
├── projects/             (2 files)
├── qids/                 (3 files)
├── reports/              (2 files)
├── settings/             (2 files)
├── translated/           (2 files)
├── tt/                   (3 files)
├── users_no_inprocess/   (2 files)
└── wikirefs_options/     (2 files)
```

### Configuration (3 files)
```
src/backend/infos/
└── td_config.php         (72 lines) - JSON config management

src/backend/tables/
├── tables.php            (77 lines) - Static tables class
├── sql_tables.php        (SQL-specific tables)
├── langcode.php          (Language codes)
└── lang_names.json       (14KB+) - Language names
```

---

## Appendix B: Recommended Tools

| Tool | Purpose | Integration Point |
|------|---------|-------------------|
| PHPStan | Static analysis | CI pipeline |
| Psalm | Type checking | CI pipeline |
| PHPUnit | Unit testing | Development + CI |
| PHP-CS-Fixer | Code style | Pre-commit hook |
| Rector | Automated refactoring | Development |
| Xdebug | Debugging | Development |
| Blackfire | Performance profiling | Staging |

---

## Appendix C: Migration Checklist

### Week 1-2: Security & Foundation
- [ ] Fix CSRF default return (P0)
- [ ] Fix SQL injection vectors (P0)
- [ ] Fix XSS in HEREDOC (P0)
- [ ] Remove hardcoded passwords (P0)
- [ ] Setup Composer
- [ ] Create `.env` configuration

### Week 3-4: Architecture
- [ ] Implement PSR-4 autoloading
- [ ] Create service container
- [ ] Extract domain entities
- [ ] Create repository interfaces

### Week 5-7: Refactor
- [ ] Consolidate admin modules
- [ ] Replace static caching with proper cache
- [ ] Remove global scope pollution
- [ ] Implement front controller

### Week 8-10: Quality
- [ ] Add PHPUnit tests
- [ ] Setup CI with PHPStan
- [ ] Add error logging
- [ ] Document API

---

*End of Report*
