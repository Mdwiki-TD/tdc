# TDC - Project Audit Report

**Project**: Mdwiki Translation Dashboard Coordinator (TDC)
**Repository**: [Mdwiki-TD/tdc](https://github.com/Mdwiki-TD/tdc)
**Platform**: Wikimedia Toolforge (mdwiki.toolforge.org)
**Date**: 2026-05-27
**Scope**: Full codebase audit of `src/` directory (6 modules, ~70 PHP files, ~8,000 lines)

---

## Executive Summary

The **Translation Dashboard Coordinator (TDC)** is a PHP web application for the WikiProject Medicine community. It manages the translation of medical articles from MDWiki (mdwiki.org) into multiple languages via Wikipedia volunteers. Coordinators use it to track translations, manage users/campaigns, send follow-up emails, monitor page views, and manage Wikidata QIDs.

### Technology Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.2+ (vanilla, no framework) |
| Database | MySQL/MariaDB via PDO (Toolforge ToolsDB) |
| Frontend | Bootstrap 5.3.7, jQuery 3.7.0, DataTables 2.3.4 |
| Hosting | Wikimedia Toolforge |
| External APIs | MediaWiki API, Wikidata REST API, Wikimedia Pageviews API |
| Encryption | defuse/php-encryption ^2.4 (AES-256) |
| Build | Composer, PHPStan ^2.1, PHPUnit ^10.5 |

### Architecture Overview

Traditional server-rendered PHP multi-page application (MPA) with a three-layer architecture:

```
Presentation Layer    →  src/coordinator/, src/utils/, src/header.php, src/footer.php
Business Logic Layer  →  src/results/, src/backend/api_or_sql/, src/coordinator/tools/
Data Access Layer     →  src/backend/api_calls/, src/backend/tables/
Bootstrap/Entry       →  src/include.php, src/index.php
```

Every request flows through `include.php` (centralized bootstrap) → `header.php` (HTML + nav) → page content → `footer.php` (JS init). Routing is handled by the `ty` GET parameter dispatching to sub-modules.

---

## Project Health Assessment

### Overall Code Quality: 5/10

The codebase is functional and serves its purpose, but suffers from inconsistent practices. PHP namespaces provide logical grouping, heredoc syntax keeps HTML readable, and function naming is descriptive. However, the code lacks a framework, has no automated tests, mixes logic with presentation, and contains significant duplication.

### Maintainability: 5/10

| Factor | Assessment |
|---|---|
| Code duplication | `last.php`/`last1.php`, `process.php`/`process1.php` are near-identical |
| No template engine | HTML generated inline in PHP, tightly coupling logic and presentation |
| No autoloading | Despite namespaces, all files are manually included via `include.php` |
| No tests | Zero unit or integration tests across all modules |
| Backup files | `admin/reports/index copy.php` left in codebase |
| Global state | `$GLOBALS` used extensively for configuration and authorization |

### Scalability: 4/10

- **Connection per query**: Each `fetch_query()`/`execute_query()` creates a new PDO connection, executes one query, then destroys it. This is the single biggest scalability bottleneck.
- **No connection pooling**: No persistent connections or connection reuse.
- **Static class caching**: All lookup tables loaded at include-time into static properties. Fast for single requests but memory-proportional to dataset size.
- **No query caching**: No APCu, Redis, or file-based caching for frequently-accessed data.
- **Some pagination missing**: Several listing pages load all records at once.

### Security Posture: 4/10

| Area | Status |
|---|---|
| CSRF protection | **Good** -- all POST handlers verify tokens via `TDWIKI\csrf` |
| Session security | **Good** -- httponly, SameSite=Strict, secure flag, strict mode |
| Cookie encryption | **Good** -- AES-256 via defuse/php-encryption |
| XSS prevention | **Poor** -- inconsistent `htmlspecialchars()` usage, multiple unescaped outputs |
| SQL injection | **Poor** -- dynamic table/column interpolation, validation bypassed |
| Debug mode | **Critical** -- `?test=1` enables `display_errors=1` in production |
| Credential management | **Critical** -- hardcoded credentials in `load_env.php` committed to repo |
| Security headers | **Missing** -- no CSP, no X-Frame-Options, no X-Content-Type-Options |

### Production Readiness: Partial

The application is deployed and functional on Toolforge. It handles real translation coordination work. However, the security gaps (SQL injection vectors, XSS, debug mode exposure) represent real risks that should be addressed before any public-facing expansion.

---

## Cross-Project Analysis

### Shared Architectural Patterns

All modules follow consistent patterns:

1. **Bootstrap via `include.php`**: Every entry point starts by including the centralized bootstrap, which loads Composer autoloader, CSRF module, utilities, API clients, data layer, and lookup tables in a fixed order.

2. **Template composition**: Pages are assembled as `header.php` + content + `footer.php`. The header outputs `<html>` through `<main>`, the footer closes everything and initializes JavaScript.

3. **CRUD module pattern**: Each admin module follows `index.php` (list/form) + `post.php` (handler) + optional `edit_*.php` (edit form). POST handlers validate CSRF, process data, and redirect.

4. **Namespace-based organization**: PHP namespaces group related functions (`Utils\Html`, `SQLorAPI\Funcs`, `APICalls\MdwikiSql`, etc.) without OOP class overhead.

5. **Static memoization**: Functions in the data layer use `static` local variables for per-request caching.

6. **Dual CDN failover**: `head.php` probes `tools-static.wmflabs.org/cdnjs` and falls back to `cdnjs.cloudflare.com`.

### Repeated Weaknesses

The following issues appear across multiple modules:

| Weakness | Modules Affected | Severity |
|---|---|---|
| **User-controllable debug mode** (`?test=1`) | backend, coordinator, results, utils | Critical |
| **Inconsistent `htmlspecialchars()` usage** | coordinator, utils, backend | High |
| **No Content-Security-Policy headers** | All modules | High |
| **Global state via `$GLOBALS`** | All modules | Medium |
| **No automated tests** | All modules | Medium |
| **Hardcoded configuration values** | coordinator, backend | Medium |
| **Mixed Arabic/English comments** | post_test, backend | Low |

### Common Technical Debt

1. **Dead code path**: `$use_td_api = false` is hardcoded in `backend/api_or_sql/index.php`, making the entire API-based data retrieval path unused. Yet `last1.php` and `process1.php` duplicate logic to use the API path.

2. **No PSR-4 autoloading**: `composer.json` has empty `psr-4` and `files` arrays. All files are manually included in `include.php`, which is fragile and order-dependent.

3. **Inline HTML generation**: All HTML is generated in PHP using heredoc syntax. No template engine is used, making it difficult to separate logic from presentation.

4. **`$_REQUEST` usage**: Multiple files use `$_REQUEST` (which combines GET, POST, and COOKIE), making it harder to reason about input source and potentially enabling CSRF via GET for actions that should only accept POST.

5. **`ONLY_FULL_GROUP_BY` disabled globally**: `mdwiki_sql.php` disables this SQL mode as a workaround for poorly written queries rather than fixing the queries.

### Dependency Issues

| Issue | Details |
|---|---|
| **External CSS/JS paths** | References to `/Translation_Dashboard/` paths suggest dependencies on a sibling project not in this repo |
| **No lock file verification** | `composer.lock` exists but no CI pipeline to verify integrity |
| **Beta dependency** | Bootstrap Select `1.14.0-beta3` is used in production |
| **Single production dependency** | Only `defuse/php-encryption` -- the rest is hand-rolled |

### Integration Concerns

- **Cross-project file dependencies**: `header.php` references CSS/JS files from `/Translation_Dashboard/` which is a separate project. If that project changes paths or removes files, TDC breaks silently.
- **Shared database**: Multiple tools likely share the same Toolforge ToolsDB database. Schema changes could affect other tools.
- **API dependency**: The application depends on `mdwiki.org/w/api.php` for category member fetching. If MDWiki is down, the results module returns empty data (graceful degradation via cache).

---

## Critical Findings

### High-Risk Issues

#### 1. Hardcoded Credentials in Version Control (CRITICAL)

**File**: `src/load_env.php`

```php
putenv("TOOL_TOOLSDB_PASSWORD=root11");
putenv("COOKIE_KEY=def0000096f...");
```

Database password and encryption key are committed to the repository. Even though this file is gated by `APP_ENV=development`, it is accessible to anyone with repo access. The credentials should be rotated immediately and the file added to `.gitignore`.

#### 2. User-Controllable Debug Mode in Production (CRITICAL)

**Files**: `backend/api_calls/mdwiki_sql.php`, `backend/api_calls/td_api.php`, `backend/tables/tables.php`, `utils/functions.php`, `results/getcats.php`

```php
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
```

Any visitor can enable verbose error display by appending `?test=1` to any URL. This leaks file paths, database structure, stack traces, and SQL queries. This pattern is repeated in at least 5 files across 4 modules.

#### 3. SQL Injection via Dynamic Table/Column Names (HIGH)

**Files**: `backend/api_calls/recent_data.php`, `backend/api_calls/mdwiki_sql.php`, `coordinator/admin/translated/edit_page.php`

```php
// recent_data.php
$query = "SELECT * FROM $table WHERE target != ''";
$query = "select COUNT(*) AS count from $table where target != ''";

// mdwiki_sql.php - check_one() validation is bypassed
// The return false lines are commented out, so invalid table/column names still execute

// edit_page.php
"DELETE FROM $table WHERE id = ?"  // $table from $_GET['table']
```

While some of these have whitelist checks, the `check_one()` function in `mdwiki_sql.php` has its validation enforcement commented out -- it logs errors but still executes the query.

#### 4. XSS via Unescaped Output (HIGH)

**Files**: `utils/html.php`, `coordinator/tools/last.php`, `coordinator/admin/Emails/msg.php`

```php
// html.php - banner_alert()
// $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');  // COMMENTED OUT
HTML.="$text";  // Raw output

// html.php - make_modal_fade()
// $label, $text, $button are NOT escaped

// last.php
// $user, $llang, $md_title interpolated into HTML without escaping
```

The `banner_alert()` function has XSS escaping explicitly commented out. The `make_modal_fade()` function does not escape any of its parameters.

#### 5. Error Message Disclosure (HIGH)

**File**: `backend/api_calls/mdwiki_sql.php`

```php
echo "sql error:" . $e->getMessage() . "<br>" . $sql_query;
echo "SQL Error:" . $e->getMessage() . "<br>" . $sql_query;
```

Full SQL queries and error messages are echoed to the browser, leaking database table names, column names, and query structure.

### Security Vulnerabilities Summary

| ID | Vulnerability | Severity | Files |
|---|---|---|---|
| V-01 | Hardcoded credentials in repo | Critical | `load_env.php` |
| V-02 | User-controllable debug mode | Critical | 5+ files across 4 modules |
| V-03 | SQL injection (dynamic identifiers) | High | `recent_data.php`, `mdwiki_sql.php`, `edit_page.php` |
| V-04 | XSS (unescaped output) | High | `html.php`, `last.php`, `msg.php` |
| V-05 | Error message disclosure | High | `mdwiki_sql.php` |
| V-06 | CSRF via GET for debug mode | Medium | All files with `?test` check |
| V-07 | No Content-Security-Policy | Medium | All pages |
| V-08 | Username in URL without URL-encoding | Low | `header.php` |
| V-09 | Unbounded CSRF token pool | Low | `post_test/u.php` |
| V-10 | Potential path traversal | Low | `open_td_tables_file()` |

### Performance Bottlenecks

1. **Connection per query**: Each database call creates a new PDO connection. Under load with 20+ queries per page, this causes significant overhead and risks connection exhaustion.

2. **No query result caching**: Frequently-accessed data (categories, settings, language codes) is queried from the database on every request, with only static-variable memoization within a single request.

3. **Synchronous API calls**: `fetch_category_members()` makes paginated API calls to `mdwiki.org` synchronously. Large categories with 500+ pages require multiple sequential HTTP requests.

4. **Full table scans**: Several queries use `SELECT * FROM table WHERE target != ''` without indexes on the `target` column.

### Stability Concerns

- **No error recovery**: cURL errors and SQL exceptions are echoed to users rather than handled gracefully.
- **No circuit breaker**: If `mdwiki.org` is slow or down, the results module blocks until cURL times out.
- **No health checks**: No endpoint to verify database connectivity, API availability, or cache freshness.
- **No logging**: Debug output goes to the browser via `test_print()`. No file-based or structured logging exists.

### Missing Infrastructure

| Missing | Impact |
|---|---|
| CI/CD pipeline | No automated testing, linting, or deployment |
| Automated tests | Zero test coverage across all modules |
| Static analysis | PHPStan is a dev dependency but no configuration to run it |
| Logging system | No structured logging for production debugging |
| Monitoring | No health checks, error tracking, or performance monitoring |
| `.gitignore` for secrets | `load_env.php` is committed with credentials |
| CSP headers | No Content-Security-Policy on any page |

---

## Strengths

### Strong Engineering Decisions

1. **Consistent CSRF protection**: Every POST handler across all modules uses `generate_csrf_token()` and `verify_csrf_token()` from `TDWIKI\csrf`. This is the most consistently applied security measure in the codebase.

2. **Secure session configuration**: Session cookies are configured with `httponly`, `SameSite=Strict`, `secure` flag (on HTTPS), and `session.use_strict_mode`. This is better than many production applications.

3. **Cryptographic token generation**: CSRF tokens use `random_bytes(32)` with `bin2hex()` encoding -- cryptographically secure with 256 bits of entropy.

4. **Cookie encryption**: Production authentication uses `defuse/php-encryption` (AES-256) for cookie values, with timing-safe comparison via `hash_equals()`.

5. **Dual CDN failover**: The `head.php` CDN probe checks `tools-static.wmflabs.org` availability at runtime and falls back to `cdnjs.cloudflare.com`. The result is cached for the request lifetime.

### Reusable Components

1. **`Utils\Html` component library**: 18+ HTML generation functions provide consistent Bootstrap 5 components (cards, modals, alerts, dropdowns, inputs) used across all views.

2. **`Utils\HtmlSide` sidebar**: Role-based sidebar navigation with automatic coordinator/admin filtering, responsive desktop/mobile layouts, and Bootstrap Icons integration.

3. **`backend/api_or_sql/` abstraction layer**: The `super_function()` pattern allows switching between API and SQL data sources, providing a clean abstraction for data access.

4. **`backend/tables/` lookup caches**: Static class properties provide fast in-memory access to frequently-read data (pageviews, word counts, language codes, campaign mappings).

5. **`Results\GetCats` category traversal**: Clean BFS implementation for recursive subcategory traversal with cache-first data access.

### Well-Structured Modules

- **`backend/`**: Clear three-layer separation (API clients → abstraction → lookup tables)
- **`results/`**: Two files with distinct responsibilities (retrieval vs. analysis)
- **`utils/`**: Clean separation of concerns (debug, HTML, navigation, data loading)
- **`coordinator/admin/`**: Consistent CRUD pattern (index + post + edit) across all modules

### Good Development Practices

- PHP namespaces provide logical grouping
- Composer for dependency management
- PHPStan and PHPUnit configured (though not used in CI)
- Environment-based configuration (localhost vs. Toolforge)
- `robots: noindex` meta tag prevents indexing of internal tools
- Heredoc syntax keeps HTML output readable

---

## Improvement Roadmap

### Immediate Fixes (1-3 days)

These address critical security vulnerabilities:

| Priority | Action | Files |
|---|---|---|
| P0 | **Rotate all credentials** in `load_env.php` (database password, COOKIE_KEY) | `load_env.php` |
| P0 | **Add `load_env.php` to `.gitignore`** and remove from git history | `.gitignore` |
| P0 | **Gate debug mode** behind `is_development()` check in all 5+ files | `mdwiki_sql.php`, `td_api.php`, `tables.php`, `functions.php`, `getcats.php` |
| P0 | **Enable `check_one()` validation** -- uncomment `return false` lines | `mdwiki_sql.php` |
| P0 | **Replace error echoing** with `error_log()` | `mdwiki_sql.php`, `wiki_api.php`, `td_api.php` |
| P1 | **Uncomment `htmlspecialchars()`** in `banner_alert()` | `html.php` |
| P1 | **Add `htmlspecialchars()`** to `make_modal_fade()` parameters | `html.php` |
| P1 | **Escape output** in `tools/last.php` (`$user`, `$llang`, `$md_title`) | `last.php` |
| P1 | **Add table name whitelist** to `edit_page.php` | `edit_page.php` |

### Short-Term Improvements (1-2 weeks)

| Priority | Action |
|---|---|
| P2 | Implement PDO connection reuse (singleton pattern within request) |
| P2 | Add `Content-Security-Policy` headers in `header.php` |
| P2 | Consolidate `last.php`/`last1.php` and `process.php`/`process1.php` |
| P2 | Delete `admin/reports/index copy.php` |
| P2 | Escape email content in `admin/Emails/msg.php` |
| P2 | Add `rawurlencode()` to username in URL (`header.php`) |
| P2 | Configure PHPStan and add to pre-commit hook |
| P2 | Write `.gitignore` entry for `load_env.php` and `.env` |

### Medium-Term Improvements (2-4 weeks)

| Priority | Action |
|---|---|
| P3 | Add unit tests for core modules (target: 60% coverage) |
| P3 | Implement PSR-4 autoloading in `composer.json` to replace manual includes |
| P3 | Add structured logging (Monolog or custom logger) to replace `test_print()` |
| P3 | Implement query result caching with APCu (TTL: 5 minutes for lookup tables) |
| P3 | Add rate limiting to POST endpoints |
| P3 | Add input validation middleware for all user-supplied parameters |
| P3 | Move hardcoded excluded user (`Mr. Ibrahem`) to database settings |
| P3 | Add per-page authorization checks (not just global variable) |

### Long-Term Strategic Refactoring (1-3 months)

| Priority | Action |
|---|---|
| P4 | Migrate to a template engine (Twig or Plates) to separate HTML from PHP logic |
| P4 | Implement a proper routing system (replace `ty` parameter dispatch) |
| P4 | Extract a service layer to encapsulate business logic |
| P4 | Implement API endpoints for CRUD operations (REST) |
| P4 | Add CI/CD pipeline (GitHub Actions): lint → test → deploy |
| P4 | Implement health check endpoint for monitoring |
| P4 | Add audit logging for admin actions |
| P4 | Consider migrating to a lightweight framework (Slim, Lumen) for routing, middleware, and dependency injection |

### Security Hardening Priorities

| Priority | Action |
|---|---|
| S1 | Rotate all credentials and remove from version control |
| S2 | Disable user-controllable debug mode in production |
| S3 | Add `Content-Security-Policy` headers |
| S4 | Whitelist all dynamic SQL identifiers |
| S5 | Escape all HTML output consistently |
| S6 | Add `SameSite=Strict` to all cookies (already done for session) |
| S7 | Implement rate limiting on authentication and POST endpoints |
| S8 | Add audit logging for sensitive operations |
| S9 | Implement session timeout and re-authentication for admin actions |
| S10 | Add `X-Frame-Options: DENY` and `X-Content-Type-Options: nosniff` headers |

### DevOps and Testing Recommendations

| Area | Recommendation |
|---|---|
| **CI Pipeline** | GitHub Actions: `composer install` → `phpstan analyse` → `phpunit` → deploy |
| **Unit Tests** | PHPUnit for `backend/`, `results/`, `utils/` functions (target: 60% coverage) |
| **Integration Tests** | Test database queries against a test database |
| **Static Analysis** | PHPStan level 5+ with baseline for existing errors |
| **Code Style** | PHP-CS-Fixer with PSR-12 rules |
| **Dependency Scanning** | `composer audit` in CI pipeline |
| **Monitoring** | Health check endpoint, error tracking (Sentry or similar), uptime monitoring |
| **Logging** | Structured logging to file (JSON format) with log levels |

---

## Final Evaluation

### Overall Project Score: 5/10

The TDC application is a functional, purpose-built tool that serves its community well. It has consistent CSRF protection, secure session management, and a clean module structure. However, it suffers from critical security gaps (hardcoded credentials, user-controllable debug mode, SQL injection vectors, XSS), no automated tests, significant code duplication, and missing DevOps infrastructure.

### Risk Level: HIGH

The combination of SQL injection vectors, XSS vulnerabilities, and exposed debug mode in a publicly-accessible web application creates a high-risk profile. The hardcoded credentials in version control are the most urgent issue.

### Technical Debt Level: MEDIUM-HIGH

| Category | Assessment |
|---|---|
| Code duplication | Medium -- `last.php`/`last1.php`, `process.php`/`process1.php` |
| Missing tests | High -- zero test coverage |
| Architecture | Medium -- procedural with namespaces, no framework |
| Configuration | Medium -- global variables, hardcoded values |
| Documentation | Low -- README files now generated |

### Estimated Production Readiness: 60%

The application works in production and handles real coordination work. However, the security gaps, missing tests, and lack of monitoring infrastructure mean it is not ready for:
- Public-facing deployment beyond the current Toolforge context
- Handling sensitive user data (PII, credentials)
- Scaling beyond the current user base
- Compliance with security best practices

### Recommended Next Steps

1. **Immediate (this week)**: Rotate credentials, gate debug mode, add `.gitignore` entry for `load_env.php`
2. **This sprint**: Fix XSS and SQL injection issues, add CSP headers, consolidate duplicate files
3. **Next month**: Add unit tests, implement PSR-4 autoloading, set up CI pipeline
4. **Next quarter**: Migrate to template engine, implement proper routing, add structured logging

The application has a solid foundation and serves a real need. Addressing the security issues and adding basic test coverage would significantly improve its reliability and maintainability.
