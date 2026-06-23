# Backend - Data Access Layer

## Project Overview

The `backend/` module is the **data access layer** for the Mdwiki Translation Dashboard Coordinator (TDC). It provides a unified abstraction for retrieving translation data from either a direct SQL database or an external REST API, along with clients for Wikimedia services and in-memory lookup tables.

### Main Features

- **Dual data source strategy**: Fetch data from the Translation Dashboard API or directly from MySQL/MariaDB, controlled by a global flag
- **Wikimedia API integration**: Clients for MediaWiki API (MDWiki), Wikimedia REST Pageviews API, and Wikidata
- **PDO database abstraction**: Secure database access layer with prepared statements for the Toolforge MySQL database
- **In-memory static lookup tables**: Preloaded caches for article metadata, language codes, campaign mappings, and project data
- **Function-level memoization**: Per-request caching via static local variables to avoid redundant queries

### Frameworks and Technologies

| Technology | Version | Purpose |
|---|---|---|
| PHP | ^8.2 | Runtime language |
| PDO + pdo_mysql | - | MySQL/MariaDB database access |
| cURL | - | HTTP client for Wikimedia APIs |
| Composer | - | Dependency management |
| defuse/php-encryption | ^2.4 | AES-256 encryption (used by auth layer) |
| Wikimedia Toolforge | - | Production hosting platform |

### PHP Version Requirement

`^8.2` (per `composer.json`)

---

## Project Structure

```
backend/
├── api_calls/                  # Low-level API and database clients
│   ├── td_api.php             # Internal Translation Dashboard API client
│   ├── wiki_api.php           # Wikimedia Pageviews REST API client
│   ├── mdwiki_api.php         # MediaWiki API client (MDWiki)
│   └── mdwiki_sql.php         # PDO database abstraction layer
├── api_or_sql/                 # Data access abstraction layer
│   ├── index.php              # Core routing: super_function() chooses API vs SQL
│   ├── funcs.php              # 16+ data-fetching functions (categories, projects, settings, QIDs, users)
│   ├── process_data.php       # "In-process" translation queries
│   └── recent_data.php        # Recent translation queries and counts
└── tables/                     # In-memory lookup tables
    ├── tables.php             # Article metadata (pageviews, words, refs, assessments)
    ├── sql_tables.php         # Campaign/category and project mappings
    ├── langcode.php           # Language code normalization and name mapping
    └── lang_names.json        # Language autonym data
```

### Architecture Layers

```
Layer 3: tables/         ← In-memory static lookup caches
Layer 2: api_or_sql/     ← Data access abstraction (API or SQL routing)
Layer 1: api_calls/      ← Low-level communication (HTTP clients, DB driver)
```

### Key Namespaces

| Namespace | Files | Responsibility |
|---|---|---|
| `APICalls\TDApi` | `td_api.php` | Internal REST API client |
| `APICalls\WikiApi` | `wiki_api.php` | Wikimedia Pageviews API client |
| `APICalls\MdwikiApi` | `mdwiki_api.php` | MediaWiki API client |
| `APICalls\MdwikiSql` | `mdwiki_sql.php` | PDO database abstraction |
| `SQLorAPI\Get` | `index.php` | API/SQL routing logic |
| `SQLorAPI\Funcs` | `funcs.php` | General data-fetching functions |
| `SQLorAPI\Process` | `process_data.php` | In-process translation queries |
| `SQLorAPI\Recent` | `recent_data.php` | Recent translation queries |
| `Tables\Main\MainTables` | `tables.php` | Article metadata static store |
| `Tables\SqlTables\TablesSql` | `sql_tables.php` | Campaign/project static store |
| `Tables\Langs\LangsTables` | `langcode.php` | Language code static store |

### Database Tables Referenced

`pages`, `pages_users`, `pages_users_to_main`, `in_process`, `users`, `qids`, `qids_others`, `categories`, `projects`, `settings`, `translate_type`, `coordinators`, `full_translators`, `users_no_inprocess`, `language_settings`, `assessments`, `enwiki_pageviews`, `refs_counts`, `words`, `views_new_all`, `publish_reports`

---

## Architecture & Code Quality Review

### Design Patterns

- **Strategy Pattern**: `super_function()` in `index.php` implements a dual-source strategy, routing between API and SQL data retrieval
- **Gateway Pattern**: Each file in `api_calls/` acts as a gateway to an external service
- **Repository Pattern**: Functions in `funcs.php`, `recent_data.php`, `process_data.php` serve as repository methods for specific data entities
- **Static Singleton**: `MainTables`, `SqlTables`, `LangsTables` use static properties as global in-memory caches
- **Memoization**: Nearly every function uses `static` local variables for per-request caching

### Code Organization

The three-layer separation is clear and purposeful. Each layer has a distinct responsibility. The use of PHP namespaces provides logical grouping without the overhead of a full framework.

### SOLID Principles Compliance

- **Single Responsibility**: Mostly adhered to -- each function handles one data retrieval concern
- **Open/Closed**: The dual-source strategy allows extension, but adding new data sources requires modifying `super_function()`
- **Liskov Substitution**: Not directly applicable (procedural, not OOP)
- **Interface Segregation**: No interfaces used; functions are loosely coupled through namespaces
- **Dependency Inversion**: Partial -- the abstraction layer depends on concrete implementations rather than abstractions

### Maintainability

**Moderate**. The codebase is straightforward to read and navigate. The memoization pattern is applied consistently. However, the dual data source strategy adds complexity, and the `$use_td_api` global variable is currently hardcoded to `false` on line 21 of `index.php`, making the API path dead code.

### Readability

**Good**. Functions are named descriptively. The code uses consistent formatting and Arabic/English comments. The heredoc syntax for SQL queries is readable.

### Scalability Considerations

- **Connection per query**: Each `fetch_query()`/`execute_query()` call creates a new PDO connection and destroys it after one query. This is inefficient under load and risks connection exhaustion.
- **Static class caching**: All lookup tables are loaded at include-time, which is fast for single requests but consumes memory proportional to the dataset size.
- **No connection pooling**: Toolforge provides MySQL access, but the code does not reuse connections within a request.

---

## Strengths

- **Clean three-layer separation** between API clients, abstraction layer, and lookup tables
- **Consistent memoization** prevents redundant database calls within a single request
- **Prepared statements** used for most SQL queries, preventing SQL injection in parameterized queries
- **Environment detection** correctly distinguishes localhost from production
- **Comprehensive data model** covering translations, users, campaigns, QIDs, pageviews, and more
- **Dual CDN failover** for frontend assets (handled in the parent `head.php`)
- **Static lookup tables** provide fast in-memory access for frequently-read data

## Weaknesses

- **Dead API path**: `$use_td_api = false` is hardcoded, making the entire API-based data retrieval path unused code
- **Connection-per-query anti-pattern**: Each database call creates and destroys a PDO connection
- **Global variable coupling**: `$use_td_api` and `$GLOBALS` are used extensively for configuration and state
- **No PSR-4 autoloading**: Despite namespaces, files are manually included via `include.php`
- **Mixed data access strategies**: Some files use direct SQL, others use the API, with no clear guideline for when to use which

---

## Critical Issues

### SQL Injection Risks

1. **`recent_data.php` -- Unparameterized table/limit/offset interpolation**:
   ```php
   $query = "SELECT * FROM $table WHERE target != ''";
   $query = "select COUNT(*) AS count from $table where target != ''";
   ```
   The `$table`, `$limit`, and `$offset` variables are interpolated directly into SQL. While these may come from internal code rather than user input, this is a dangerous pattern.

2. **`mdwiki_sql.php` -- `check_one()` validation is ineffective**:
   The whitelist validation in `check_one()` logs errors but does NOT prevent query execution (the `return false` lines are commented out). This makes the security check purely decorative.

### Error Message Disclosure

`mdwiki_sql.php` echoes full SQL queries and error messages to the browser:
```php
echo "sql error:" . $e->getMessage() . "<br>" . $sql_query;
```
This leaks database structure, table names, and query logic to attackers.

### User-Controllable Debug Mode

Multiple files enable `display_errors=1` and `error_reporting(E_ALL)` when `$_REQUEST['test']` or `$_COOKIE['test']` is set. Any visitor can enable verbose error display by appending `?test=1` to a URL.

### XSS in `td_api.php`

The `post_url_mdwiki()` function embeds URLs directly into `<a>` tags without `htmlspecialchars()` escaping.

### GET Parameter Controls Data Source

`$_GET['use_td_api']` allows anyone to switch the data source between API and SQL, which could be used for reconnaissance.

---

## Areas That Need Attention

- **Missing input validation**: Table names, column names, and limits should be validated against whitelists before SQL interpolation
- **No connection pooling**: Each query creates a new PDO connection -- implement connection reuse
- **No tests**: No unit or integration tests exist for the backend module
- **Dead code**: The API-based data path (`$use_td_api = true`) is never activated
- **Error handling**: cURL errors and SQL exceptions should be logged, not echoed to users
- **Missing rate limiting**: API calls to Wikimedia services have no rate limiting or backoff
- **No Content-Security-Policy**: Missing CSP headers leave the application open to XSS if content is injected

---

## Improvement Plan

### Quick Fixes (1-2 days)

1. **Enable `check_one()` validation**: Uncomment the `return false` lines in `mdwiki_sql.php` to actually block invalid table/column names
2. **Suppress error output**: Replace `echo` error statements with proper logging (e.g., `error_log()`)
3. **Disable user-controllable debug mode**: Gate `?test` debug behind an environment check (`if (!is_development()) return;`)
4. **Add `htmlspecialchars()` to `post_url_mdwiki()`** in `td_api.php`

### Medium-Term Improvements (1-2 weeks)

5. **Implement connection pooling**: Create a singleton Database connection that is reused within a request
6. **Whitelist table/column names**: Add explicit whitelists for all dynamic SQL identifiers
7. **Add unit tests**: Test each function in `funcs.php`, `recent_data.php`, `process_data.php`
8. **Standardize data access**: Choose either API or SQL as the primary path and remove the dead code path

### Long-Term Refactoring (1-3 months)

9. **Introduce PSR-4 autoloading**: Configure Composer autoloading to replace manual includes
10. **Extract an interface**: Define a `DataSourceInterface` for the API/SQL strategy pattern
11. **Add prepared statement support for dynamic identifiers**: Use backtick-quoted identifiers with whitelisting
12. **Implement proper error handling**: Use exceptions with a global error handler instead of inline error echoing

### Security Hardening

- Add `Content-Security-Policy` headers
- Implement rate limiting for external API calls
- Add input validation middleware for all user-supplied parameters
- Rotate all credentials found in `load_env.php`
- Add `.gitignore` entry for `load_env.php`

### Performance Optimization

- Implement PDO connection reuse within a request lifecycle
- Add persistent connections for production (`PDO::ATTR_PERSISTENT`)
- Consider APCu or Redis for lookup table caching instead of static properties
- Add query result caching with TTL for frequently-accessed data

---

## Comprehensive Review

| Metric | Score | Notes |
|---|---|---|
| **Overall Rating** | 5/10 | Functional but has significant security gaps and no tests |
| **Production Readiness** | Partial | Works in production but error disclosure and SQL injection risks need fixing |
| **Security Score** | 4/10 | SQL injection vectors, error disclosure, user-controllable debug mode |
| **Technical Debt** | Medium | Dead API code path, connection-per-query, global variable coupling |
| **Maintainability** | 6/10 | Clear structure but no tests, no autoloading, inconsistent patterns |
| **Risk Assessment** | Medium-High | SQL injection and error disclosure are the primary risks |

---

## Setup & Usage

### Prerequisites

- PHP 8.2+
- MySQL/MariaDB with PDO extension
- cURL extension
- Composer

### Installation

```bash
cd /path/to/tdc
composer install
```

### Environment Configuration

Create a `.env` file or set environment variables:

```env
DB_HOST_TOOLS=localhost:3306
DB_NAME=mdwiki_db
TOOL_TOOLSDB_USER=root
TOOL_TOOLSDB_PASSWORD=your_password
COOKIE_KEY=your_encryption_key
TABLES_PATH=/path/to/tables
```

### Database Setup

The module connects to a MySQL/MariaDB database with the tables listed above. On Toolforge, the database is available at `tools.db.svc.wikimedia.cloud`.

### Local Development

When running on localhost, the module automatically:
- Uses `localhost:3306` as the database host
- Enables verbose error output (when `?test=1` is in the URL)
- Reads credentials from `load_env.php` (if `APP_ENV=development`)

### Integration

This module is loaded via `include.php` at the application root:
```php
require_once __DIR__ . '/backend/api_calls/mdwiki_sql.php';
require_once __DIR__ . '/backend/api_calls/wiki_api.php';
// ... etc
```
