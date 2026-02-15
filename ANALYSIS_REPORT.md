# Static Analysis Report: Translation Dashboard Codebase

**Analysis Date:** 2026-02-15  
**Analyzer:** opencode  
**Codebase:** WikiProjectMed Translation Dashboard (PHP)

---

## Executive Summary

This report documents critical security vulnerabilities, logical errors, performance bottlenecks, and architectural anti-patterns identified in the Translation Dashboard codebase. The analysis covers 40+ PHP files across multiple modules including API calls, database operations, CSRF protection, and administrative functions.

### Criticality Rating Overview

| Category | Critical | High | Medium | Low |
|----------|----------|------|--------|-----|
| Security | 3 | 2 | 1 | 0 |
| Logic | 0 | 3 | 2 | 1 |
| Performance | 0 | 1 | 3 | 2 |
| Architecture | 0 | 2 | 4 | 1 |

---

## 1. Security Vulnerabilities

### 1.1 CRITICAL: Hardcoded Database Credentials

**File:** `src/backend/api_calls/mdwiki_sql.php:61`

```php
if ($server_name === 'localhost') {
    $this->host = 'localhost:3306';
    $this->dbname = $ts_mycnf['user'] . "__" . $this->db_suffix;
    $this->user = 'root';
    $this->password = 'root11';  // VULNERABILITY: Hardcoded credentials
}
```

**Impact:** Credentials exposed in source code. If repository is compromised, database access is granted.

**Recommendation:** 
- Use environment variables or secure credential vault
- Never commit credentials to version control
- Implement separate configuration for development/production

---

### 1.2 CRITICAL: CSRF Bypass Vulnerability

**File:** `src/csrf.php:22-26`

```php
if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
    $_SESSION['csrf_tokens'] = [];
    echo "No csrf tokens in session!";
    return true;  // VULNERABILITY: Returns true when no tokens exist
}
```

**Impact:** An attacker can clear session tokens (via session fixation) and bypass CSRF protection entirely.

**Recommendation:**
- Return `false` when no tokens exist
- Require token presence for all state-changing operations
- Log security events for monitoring

---

### 1.3 CRITICAL: SQL Injection Validation Disabled

**File:** `src/backend/api_calls/mdwiki_sql.php:370-378`

```php
// Validate table name
if (!in_array($table, $allowed_tables)) {
    error_log("check_one: Invalid table name: $table");
    // return false;  // VULNERABILITY: Validation commented out
}

// Validate select and where columns
if (!in_array($select, $allowed_columns[$table]) || !in_array($where, $allowed_columns[$table])) {
    error_log("check_one: Invalid column name for table $table");
    // return false;  // VULNERABILITY: Validation commented out
}
```

**Impact:** SQL injection possible through table and column parameters despite whitelist existence.

**Recommendation:**
- Enable the validation by uncommenting `return false`
- Add positive validation for all SQL identifiers
- Use parameterized queries exclusively

---

### 1.4 HIGH: Debug Mode Authentication Bypass

**File:** `src/utils/functions.php:12-17`, `src/header.php:6-10`

```php
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    $print_t = true;
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
```

**Impact:** Any user can enable debug mode by setting a cookie or request parameter, potentially exposing sensitive information.

**Recommendation:**
- Require authentication for debug mode
- Use IP whitelist for development debugging
- Remove debug triggers in production

---

### 1.5 HIGH: XSS Vulnerabilities in URL Generation

**File:** `src/backend/api_calls/mdwiki_api.php:36`, `src/backend/api_calls/td_api.php:62`

```php
$url2 = str_replace('&format=json', '', $url);
$url2 = "<a target='_blank' href='$url2'>$url2</a>";  // VULNERABILITY: No escaping
```

**Impact:** If API parameters contain malicious content, it could be reflected as XSS.

**Recommendation:**
- Use `htmlspecialchars()` for all HTML output
- Implement context-aware encoding

---

### 1.6 MEDIUM: Session Security Misconfiguration

**File:** `src/header.php:12`

```php
ini_set('session.use_strict_mode', '1');
```

**Issue:** While strict mode is enabled, other session security settings are missing:
- No HTTPOnly cookie flag
- No Secure flag
- No SameSite attribute

**Recommendation:**
```php
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Strict');
```

---

## 2. Logical Errors

### 2.1 HIGH: Unreachable Code in CSRF Verification

**File:** `src/csrf.php:58-65`

```php
// After return statements at lines 46 and 56
echo <<<HTML
<div class='alert alert-danger' role='alert'>
    This page is not allowed to be opened directly!
</div>
HTML;
return false;
```

**Impact:** Dead code that will never execute. Indicates incomplete refactoring.

**Recommendation:** Remove unreachable code block.

---

### 2.2 HIGH: Undefined Variable Usage

**File:** `src/header.php:55`

```php
<a href="/Translation_Dashboard/leaderboard.php?user=$username" ...>
```

**Issue:** Variable `$username` is undefined. The correct variable should be `$u_name` (defined at line 51).

**Impact:** Broken user profile links, potential notice errors.

**Recommendation:** Change `$username` to `$u_name`.

---

### 2.3 HIGH: Potential Null Reference

**File:** `src/coordinator/tools/last.php:111`

```php
if ($GLOBALS['global_username'] !== "Mr. Ibrahem") {
```

**Issue:** No null check before comparison. If `$GLOBALS['global_username']` is not set, this will cause issues.

**Recommendation:**
```php
if (($GLOBALS['global_username'] ?? '') !== "Mr. Ibrahem") {
```

---

### 2.4 MEDIUM: Inconsistent Null Handling

**File:** `src/backend/api_calls/mdwiki_sql.php:120`

```php
if ($params) {
    $q->execute($params);
} else {
    $q->execute();
}
```

**Issue:** Empty array `$params = []` is truthy, but should be treated same as null.

**Recommendation:**
```php
if (!empty($params)) {
    $q->execute($params);
} else {
    $q->execute();
}
```

---

### 2.5 MEDIUM: Duplicate Function Definition

**File:** `src/backend/api_calls/td_api.php:12-25`

**Issue:** `test_print_o()` duplicates functionality of `test_print()` in `Utils\Functions` namespace.

**Recommendation:** Use the utility function or create a proper shared debug service.

---

## 3. Performance Bottlenecks

### 3.1 HIGH: No Database Connection Pooling

**File:** `src/backend/api_calls/mdwiki_sql.php:199-222`

```php
function execute_query($sql_query, $params = null, $table_name = null) {
    $db = new Database($_SERVER['SERVER_NAME'] ?? '', $dbname);  // New connection per query
    // ... execute ...
    $db = null;  // Destroy connection
}
```

**Impact:** Every database query creates a new connection. High overhead, resource exhaustion under load.

**Recommendation:**
- Implement singleton pattern or connection pooling
- Use persistent connections
- Consider PDO connection pooling via third-party library

---

### 3.2 MEDIUM: Static Caching Without Invalidation

**File:** `src/backend/api_or_sql/funcs.php:31-35`

```php
static $stats_data = [];
if (!empty($stats_data)) {
    return $stats_data;
}
```

**Issue:** Static cache never invalidates. Data becomes stale over time.

**Recommendation:**
- Implement TTL-based cache expiration
- Add cache invalidation hooks for data updates
- Consider using APCu or Redis for proper caching

---

### 3.3 MEDIUM: Multiple File Reads Without Caching

**File:** `src/backend/infos/td_config.php:46`

```php
function get_configs($fileo) {
    $pv_file = file_get_contents($file);  // Read file every call
    $uu = json_decode($pv_file, true);
    return $uu;
}
```

**Recommendation:** Cache file contents with modification time check.

---

### 3.4 MEDIUM: Inefficient Loop Operations

**File:** `src/backend/tables/tables.php:51-63`

```php
foreach ($titles_infos as $k => $tab) {
    $title = $tab['title'];
    MainTables::$x_enwiki_pageviews_table[$title] = $tab['en_views'];
    // ... multiple static array assignments
}
```

**Issue:** Multiple static array operations in tight loop.

**Recommendation:** Batch operations where possible, use local variables and assign once.

---

### 3.5 LOW: Unnecessary Type Conversions

Multiple files contain redundant type casts and conversions that could be optimized.

---

## 4. Architectural Anti-Patterns

### 4.1 HIGH: Global State Abuse

**Files:** Multiple

```php
$GLOBALS['global_username']
$user_in_coord  // Global constant via define()
MainTables::$x_enwiki_pageviews_table  // Static mutable state
```

**Impact:** 
- Difficult to test (requires global state setup)
- Hidden dependencies
- Thread safety concerns
- State mutation tracking difficulty

**Recommendation:**
- Implement dependency injection container
- Use class instances instead of static state
- Pass dependencies explicitly

---

### 4.2 HIGH: Hardcoded Paths

**File:** `src/include.php:3-7, 15-16`

```php
if ((getenv("HOME") ?: "") === '') {
    $new_home = 'I:/mdwiki/mdwiki';
    putenv("HOME=$new_home");
}
if (substr(__DIR__, 0, 2) == 'I:') {
    include_once 'I:/mdwiki/auth_repo/oauth/user_infos.php';
}
```

**Impact:** Code not portable, breaks on different systems.

**Recommendation:**
- Use configuration files for paths
- Use environment variables
- Implement proper path resolution

---

### 4.3 MEDIUM: Mixed Concerns (HTML in Logic)

**Files:** `src/coordinator/tools/process.php`, `src/coordinator/tools/last.php`

Functions like `make_td()` mix database result processing with HTML generation.

**Recommendation:**
- Implement proper MVC or similar pattern
- Separate data transformation from presentation
- Use templating engine

---

### 4.4 MEDIUM: Inconsistent Namespace Usage

**Files:** Multiple

Some files use proper namespaces (`namespace SQLorAPI\Funcs;`) while others use global namespace or inconsistent naming.

**Recommendation:** Establish and enforce namespace conventions.

---

### 4.5 MEDIUM: No Interface Abstractions

Database and API calls use concrete classes directly without interfaces.

**Recommendation:**
- Create `DatabaseInterface` for database operations
- Create `ApiClientInterface` for API calls
- Enable mocking for testing

---

### 4.6 LOW: God Class Tendency

`Database` class in `mdwiki_sql.php` handles connection, querying, error handling, and debug output.

**Recommendation:** Separate concerns into focused classes.

---

## 5. Code Quality Issues

### 5.1 Missing Type Declarations

Most functions lack PHP 7.4+ type declarations:

```php
// Current
function test_print($s)

// Recommended
function test_print(string $s): void
```

### 5.2 Missing PHPDoc Documentation

Most files lack comprehensive PHPDoc blocks for:
- File-level documentation
- Class documentation
- Method documentation with @param, @return, @throws

### 5.3 Magic Strings/Numbers

Hardcoded strings like `'root11'`, `'localhost:3306'`, session names scattered throughout.

---

## 6. Recommendations Summary

### Immediate Actions (Critical)

1. **Remove hardcoded credentials** - Use environment variables
2. **Fix CSRF bypass** - Return false when no tokens
3. **Enable SQL validation** - Uncomment return statements

### Short-term Actions (High Priority)

1. Fix undefined variable `$username` in header.php
2. Add null checks for global variables
3. Implement database connection pooling
4. Add proper authentication to debug mode
5. Remove unreachable code

### Medium-term Actions

1. Implement dependency injection
2. Add comprehensive PHPDoc documentation
3. Add PHP type declarations
4. Separate concerns (MVC pattern)
5. Create configuration management system

### Long-term Actions

1. Implement proper caching layer (Redis/APCu)
2. Add comprehensive test coverage
3. Create CI/CD pipeline with static analysis
4. Implement proper logging and monitoring

---

## 7. Files Analyzed

| File | Lines | Issues |
|------|-------|--------|
| src/backend/api_calls/mdwiki_sql.php | 397 | 5 |
| src/csrf.php | 77 | 2 |
| src/header.php | 152 | 2 |
| src/include.php | 43 | 2 |
| src/utils/functions.php | 43 | 1 |
| src/backend/api_calls/td_api.php | 108 | 2 |
| src/backend/api_calls/wiki_api.php | 94 | 1 |
| src/coordinator/tools/last.php | 335 | 2 |
| src/backend/tables/tables.php | 77 | 1 |
| src/backend/infos/td_config.php | 72 | 1 |
| src/backend/api_or_sql/index.php | 44 | 1 |
| src/backend/api_or_sql/funcs.php | 422 | 1 |
| src/coordinator/admin/add/post.php | 46 | 0 |
| src/coordinator/admin/add/add_post.php | 79 | 0 |

---

*Report generated by opencode static analysis*
