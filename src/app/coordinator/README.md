# Coordinator - Admin Panel and Management Interface

## Project Overview

The `coordinator/` module is the **admin panel and management interface** for the Mdwiki Translation Dashboard Coordinator (TDC). It provides a comprehensive web interface for coordinators to manage the entire medical article translation workflow -- from tracking translations in progress, to managing users and campaigns, to sending follow-up emails to translators.

### Main Features

- **Translation monitoring**: View recent translations, in-process translations, and translation statistics per category
- **CRUD management**: Full create/read/update/delete interfaces for pages, users, campaigns, projects, QIDs, coordinators, translate types, and settings
- **Email system**: Rich-text email composition using Summernote WYSIWYG editor for sending follow-up suggestions to translators
- **Translation gap analysis**: View which articles in a category are translated vs. missing, with Wikidata sitelink cross-referencing
- **Namespace migration**: Tools to move translations from user namespace to main namespace
- **Publish reports**: Filterable reports with DataTables showing translation publishing activity
- **WikiRefs options**: Per-language configuration for reference fixing tools
- **Role-based access control**: Coordinator-only sections with redirect for unauthorized users
- **CSRF protection**: All POST handlers verify CSRF tokens

### Frameworks and Technologies

| Technology | Version | Purpose |
|---|---|---|
| PHP | ^8.2 | Runtime (vanilla, no framework) |
| MySQL/MariaDB | - | Database via PDO (Toolforge ToolsDB) |
| Bootstrap 5 | 5.3.7 | CSS framework (cards, forms, modals, responsive grid) |
| jQuery | 3.7.0 | DOM manipulation and AJAX |
| DataTables | 2.3.4 | Sortable, searchable, paginated tables |
| Bootstrap Selectpicker | - | Searchable dropdown selectors |
| Summernote | - | WYSIWYG email editor |
| Font Awesome / Bootstrap Icons | - | Icon sets |
| cURL | - | HTTP client for internal API and Wikimedia APIs |
| Wikidata REST API | - | Sitelink queries |
| Wikimedia Pageviews API | - | Page view statistics |

### PHP Version Requirement

`^8.2` (per parent project's `composer.json`)

---

## Project Structure

```
coordinator/
├── index.php                           # Entry point (includes tools/last.php)
├── 404.php                             # Static 404 error page
├── tools/                              # Coordinator tools (views and reports)
│   ├── index.php                       # Alias for tools/last.php
│   ├── last.php                        # Recent translations table (SQL-based)
│   ├── last1.php                       # Recent translations (API-based, alternative)
│   ├── process.php                     # In-process translations (AJAX/DataTables)
│   ├── process1.php                    # In-process translations (server-rendered)
│   ├── process_total.php              # Summary of users with in-process work
│   ├── stat.php                        # Category statistics (words, refs, views)
│   ├── categories.php                  # Translation categories per language
│   └── recent_helps.php               # Helper functions for recent views
└── admin/                              # Admin CRUD interfaces (coordinator-only)
    ├── index.php                       # Alias for tools/last.php
    ├── add/                            # Add translation records
    │   ├── index.php                   # Add form
    │   ├── post.php                    # POST handler
    │   └── add_post.php               # Database insert logic
    ├── admins/                         # Coordinator management
    │   ├── index.php                   # Coordinator CRUD form
    │   └── post.php                    # POST handler
    ├── Campaigns/                      # Campaign/category management
    │   ├── index.php                   # Campaign CRUD form
    │   └── post.php                    # POST handler
    ├── Emails/                         # User management and email system
    │   ├── index.php                   # User table with DataTables
    │   ├── msg.php                     # Email composition (Summernote)
    │   ├── post.php                    # User CRUD POST handler
    │   ├── sugust.php                  # Article suggestion engine
    │   └── edit_user.php              # Edit/add user form
    ├── pages_users_to_main/            # Namespace migration tools
    │   ├── index.php                   # Migration queue listing
    │   ├── fix_it.php                  # Edit form for migration
    │   └── fix_it_post.php            # Migration POST handler
    ├── projects/                       # Project management
    │   ├── index.php                   # Project CRUD form
    │   └── post.php                    # POST handler
    ├── qids/                           # Wikidata QID management
    │   ├── index.php                   # QID listing with filters
    │   ├── post.php                    # QID CRUD POST handler
    │   └── edit_qid.php              # Edit/add QID form
    ├── reports/                        # Publish reports
    │   ├── index.php                   # Reports viewer with filters
    │   └── index copy.php             # Backup/older copy
    ├── settings/                       # Application settings
    │   ├── index.php                   # Settings form
    │   └── post.php                    # Settings POST handler
    ├── translated/                     # Translated page management
    │   ├── index.php                   # Paginated page listing
    │   └── edit_page.php              # Edit/delete page form
    ├── tt/                             # Translate type management
    │   ├── index.php                   # Translate type listing
    │   ├── post.php                    # POST handler
    │   └── edit_translate_type.php    # Edit/add translate type
    ├── wikirefs_options/               # WikiRefs fix options
    │   ├── index.php                   # Options listing
    │   └── edit.php                    # Edit/add/delete form
    ├── full_translators/               # Full-article translator management
    │   ├── index.php                   # CRUD form
    │   └── post.php                    # POST handler
    ├── users_no_inprocess/             # Excluded users management
    │   ├── index.php                   # CRUD form
    │   └── post.php                    # POST handler
    └── last_coord/                     # Coordinator-specific recent view
        └── index.php                   # Recent translations with email/fixref columns
```

### Module Categories

| Category | Directories | Purpose |
|---|---|---|
| **Views** | `tools/` | Read-only views: recent translations, in-process, statistics, categories |
| **CRUD Admin** | `admin/add/`, `admin/admins/`, `admin/Campaigns/`, `admin/projects/`, `admin/settings/` | Create/Read/Update/Delete for core entities |
| **Content Management** | `admin/translated/`, `admin/qids/`, `admin/tt/`, `admin/wikirefs_options/` | Manage translated pages, QIDs, translate types, WikiRefs options |
| **User Management** | `admin/Emails/`, `admin/full_translators/`, `admin/users_no_inprocess/` | Manage users, email lists, translator permissions |
| **Migration** | `admin/pages_users_to_main/` | Move translations from user namespace to main namespace |
| **Reporting** | `admin/reports/` | Publish reports with filtering and export |

### Key Namespaces

| Namespace | Files | Responsibility |
|---|---|---|
| `Tools\RecentHelps` | `tools/recent_helps.php` | Helper functions for recent views |
| `Add\AddPost` | `admin/add/add_post.php` | Database insert logic for new pages |
| `Emails\Sugust` | `admin/Emails/sugust.php` | Article suggestion engine |

---

## Architecture & Code Quality Review

### Design Patterns

- **Front Controller**: `index.php` at the root routes to sub-modules based on the `ty` GET parameter
- **Template Composition**: Pages are assembled by including `header.php` (HTML + nav), adding content, then `footer.php` (JS init)
- **POST-Redirect-GET**: All `post.php` handlers process form submissions and redirect back to the listing page
- **CRUD Pattern**: Each admin module follows a consistent structure: `index.php` (list/form), `post.php` (handler), optional `edit_*.php` (edit form)
- **Role-Based Access Control**: `$GLOBALS['user_is_coordinator']` is checked at the top of every admin file
- **CSRF Protection**: All POST handlers use `generate_csrf_token()` and `verify_csrf_token()` from `TDWIKI\csrf`

### Code Organization

**Moderate**. The directory structure is logical and follows a consistent pattern. Each CRUD module has its own directory with index/post files. However, there is significant code duplication between alternative implementations (`last.php` vs `last1.php`, `process.php` vs `process1.php`).

### SOLID Principles Compliance

- **Single Responsibility**: Mostly adhered to -- each file handles one view or one form handler
- **Open/Closed**: New modules can be added by creating a new directory, but the routing in `index.php` requires modification
- **Liskov Substitution**: Not directly applicable (procedural)
- **Interface Segregation**: No interfaces; functions are loosely coupled
- **Dependency Inversion**: The module depends on concrete implementations from `backend/` and `utils/`

### Maintainability

**Moderate**. The consistent CRUD pattern makes it easy to understand each module. However, the code duplication between alternative implementations and the inline HTML generation (no template engine) make maintenance more difficult. The 47 files total approximately 4,700 lines.

### Readability

**Good**. The heredoc syntax provides readable HTML output. Function names are descriptive. The Bootstrap 5 UI components are consistent and well-structured.

### Scalability Considerations

- **DataTables with stateSave**: Client-side table state persistence reduces server load
- **AJAX loading**: `process.php` loads data via AJAX, reducing initial page load time
- **No pagination in some views**: Some listing pages load all records at once, which could be slow with large datasets

---

## Strengths

- **Consistent CSRF protection**: All POST handlers verify tokens, preventing cross-site request forgery
- **Role-based access control**: Coordinator-only sections are properly gated with redirect for unauthorized users
- **Comprehensive CRUD coverage**: Full management interface for all database entities
- **Bootstrap 5 responsive UI**: Mobile-friendly layout with responsive tables and sidebar
- **DataTables integration**: Sorting, searching, pagination, column toggling, and state persistence
- **Summernote email editor**: Rich-text email composition for translator follow-up
- **Dual data access**: Supports both SQL-based and API-based data retrieval
- **Dark mode support**: `data-bs-theme="auto"` enables automatic dark mode detection
- **Wikidata integration**: QID management with sitelink cross-referencing

## Weaknesses

- **Significant code duplication**: `last.php`/`last1.php` and `process.php`/`process1.php` contain near-identical logic
- **No template engine**: HTML is generated inline in PHP, mixing logic and presentation
- **Inconsistent escaping**: Some views use `htmlspecialchars()`, others do not
- **Global state dependency**: Authorization relies on `$GLOBALS['user_is_coordinator']`
- **Backup file in codebase**: `admin/reports/index copy.php` is a code smell
- **Hardcoded excluded user**: `$excludedUsers = ['Mr. Ibrahem']` in `last_coord/index.php` should be configurable
- **No unit tests**: No automated tests for any of the 47 files

---

## Critical Issues

### SQL Injection via `$_GET['table']` (HIGH)

In `admin/translated/edit_page.php`, the `$table` variable from `$_GET['table']` is interpolated directly into SQL:
```php
"DELETE FROM $table WHERE id = ?"
"SELECT * FROM $table WHERE id = ?"
```
While the calling code may validate this, the file itself does not sanitize the input.

### XSS in Multiple Views (HIGH)

Several views output user-supplied values without `htmlspecialchars()`:
- `tools/last.php`: `$user`, `$llang`, `$md_title` interpolated into HTML
- `admin/Emails/msg.php`: User values embedded in HTML email content
- Multiple admin forms: Form values echoed back without escaping on error

### Debug Mode Exposes SQL Queries (MEDIUM)

```php
// admin/add/add_post.php line 36
if (isset($_REQUEST['test'])) echo "$query1<br/>$query2";
```
Full SQL queries are displayed to anyone who adds `?test=1` to the URL.

### Authorization Relies Solely on Global Variable (MEDIUM)

Authorization is checked via `$GLOBALS['user_is_coordinator']`. If this global is incorrectly set or manipulated, all admin sections become accessible. There is no per-page session validation.

### Email Content Injection (MEDIUM)

`admin/Emails/msg.php` generates HTML email content with user-supplied values (`$user`, `$title`, `$target`) embedded without escaping. This could allow HTML injection in emails.

---

## Areas That Need Attention

- **Input validation**: Add `htmlspecialchars()` to all user-supplied output in views
- **Table name validation**: Add whitelist check for `$_GET['table']` in `edit_page.php`
- **Remove duplicate files**: Consolidate `last.php`/`last1.php` and `process.php`/`process1.php`
- **Remove backup files**: Delete `admin/reports/index copy.php`
- **Externalize configuration**: Move hardcoded excluded user to database settings
- **Add unit tests**: Test CRUD operations, form handlers, and authorization logic
- **Template engine**: Consider migrating to a template engine (Twig, Plates) to separate logic from presentation
- **Rate limiting**: Add rate limiting to POST endpoints to prevent abuse

---

## Improvement Plan

### Quick Fixes (1-2 days)

1. Add `htmlspecialchars()` to all unescaped output in `tools/last.php`
2. Add table name whitelist to `admin/translated/edit_page.php`
3. Gate debug SQL output behind `is_development()` check
4. Delete `admin/reports/index copy.php`
5. Escape email content in `admin/Emails/msg.php`

### Medium-Term Improvements (1-2 weeks)

6. Consolidate duplicate files (`last.php`/`last1.php`, `process.php`/`process1.php`)
7. Add CSRF token verification to all GET-based state-changing operations
8. Implement per-page session validation (don't rely solely on global variable)
9. Move hardcoded excluded user to database settings
10. Add input validation middleware for all form submissions

### Long-Term Refactoring (1-3 months)

11. Migrate to a template engine (Twig or Plates) to separate HTML from PHP logic
12. Implement a proper routing system (replace `ty` parameter dispatch)
13. Add comprehensive unit and integration tests
14. Implement a service layer to encapsulate business logic
15. Add API endpoints for CRUD operations (REST or GraphQL)

### Security Hardening

- Add `Content-Security-Policy` headers to all pages
- Implement rate limiting on POST endpoints
- Add per-page authorization checks (not just global variable)
- Sanitize all email content before sending
- Add audit logging for admin actions
- Implement session timeout and re-authentication for sensitive operations

### Performance Optimization

- Add database query caching for frequently-accessed data
- Implement pagination for all listing pages
- Use AJAX loading for large DataTables to reduce initial page load
- Add database indexes for commonly-queried columns

---

## Comprehensive Review

| Metric | Score | Notes |
|---|---|---|
| **Overall Rating** | 6/10 | Functional admin panel with good coverage, but security gaps |
| **Production Readiness** | Partial | Works in production but XSS and SQL injection risks need fixing |
| **Security Score** | 5/10 | CSRF is good, but XSS and SQL injection issues exist |
| **Technical Debt** | Medium | Code duplication, no tests, inline HTML generation |
| **Maintainability** | 6/10 | Consistent patterns but duplication and no tests |
| **Risk Assessment** | Medium | SQL injection and XSS are the primary risks |

---

## Setup & Usage

### Prerequisites

- PHP 8.2+ with PDO MySQL extension
- MySQL/MariaDB database (Toolforge ToolsDB or local equivalent)
- Composer (for dependencies)
- Web server (Apache/Nginx)

### Installation

```bash
cd /path/to/tdc
composer install
```

### Environment Configuration

Set the following environment variables (or use `load_env.php` for development):

```env
DB_HOST_TOOLS=localhost:3306
DB_NAME=mdwiki_db
TOOL_TOOLSDB_USER=root
TOOL_TOOLSDB_PASSWORD=your_password
COOKIE_KEY=your_encryption_key
TABLES_PATH=/path/to/tables
APP_ENV=development
```

### Local Development

```bash
# Start PHP built-in server
cd /path/to/tdc/src
php -S localhost:8080

# Access coordinator tools
# http://localhost:8080/index.php?ty=last
# http://localhost:8080/index.php?ty=Campaigns
# http://localhost:8080/index.php?ty=add
```

### Database Setup

The module requires the following database tables:
`pages`, `pages_users`, `pages_users_to_main`, `users`, `coordinators`, `categories`, `projects`, `qids`, `qids_others`, `translate_type`, `settings`, `language_settings`, `full_translators`, `users_no_inprocess`, `in_process`

### Access Control

- **Regular users**: Can view recent translations, in-process translations, and statistics
- **Coordinators**: Have access to all admin sections (CRUD, email, reports, settings)
- Authorization is determined by the `coordinators` database table

### Available Routes (`?ty=` parameter)

| Route | Access | Description |
|---|---|---|
| `last` | All users | Recent translations |
| `last_coord` | Coordinators | Recent translations with admin columns |
| `process` | All users | In-process translations |
| `process_total` | All users | In-process summary |
| `stat` | All users | Category statistics |
| `categories` | All users | Translation categories |
| `add` | Coordinators | Add translation records |
| `Campaigns` | Coordinators | Manage campaigns |
| `admins` | Coordinators | Manage coordinators |
| `Emails` | Coordinators | User management and email |
| `projects` | Coordinators | Manage projects |
| `qids` | Coordinators | Manage Wikidata QIDs |
| `reports` | Coordinators | Publish reports |
| `settings` | Coordinators | Application settings |
| `translated` | Coordinators | Manage translated pages |
| `tt` | Coordinators | Manage translate types |
| `wikirefs_options` | Coordinators | WikiRefs fix options |
| `full_translators` | Coordinators | Manage full translators |
| `users_no_inprocess` | Coordinators | Manage excluded users |
| `pages_users_to_main` | Coordinators | Namespace migration |
