# Utils - Utility and Presentation Layer

## Project Overview

The `utils/` module is the **utility and presentation layer** for the Mdwiki Translation Dashboard Coordinator (TDC). It provides general-purpose helper functions, HTML component generators, sidebar navigation, and JSON data loading capabilities used across the entire application.

### Main Features

- **Debug utilities**: Conditional debug output, environment detection, and error display control
- **HTML component library**: 18+ functions generating Bootstrap 5 cards, modals, alerts, dropdowns, inputs, and links
- **Sidebar navigation**: Role-based sidebar menu with coordinator/admin access control and responsive desktop/mobile layouts
- **JSON data loader**: File-based data access with graceful error handling and diagnostic logging

### Frameworks and Technologies

| Technology | Purpose |
|---|---|
| PHP 8.2+ | Runtime language |
| Bootstrap 5 | UI framework (cards, modals, alerts, collapse, grid, tooltips) |
| Bootstrap Icons | Icon library (`bi-*` classes) |
| Composer | Dependency management |
| defuse/php-encryption ^2.4 | Encryption (used by auth layer, not directly in utils) |

### PHP Version Requirement

`^8.2` (per `composer.json`)

---

## Project Structure

```
utils/
├── functions.php       # Debug output, string helpers, environment detection
├── html.php            # HTML component generators (18+ functions)
├── html_side1.php      # Sidebar navigation with role-based access control
└── tables_dir.php      # JSON file reader with error handling
```

### Key Functions by File

#### `functions.php` (Namespace: `Utils\Functions`)

| Function | Purpose |
|---|---|
| `test_print($s)` | Conditional debug output (activated via `?test=1` or `test` cookie) |
| `start_with($haystack, $needle)` | String prefix check |
| `is_development(): bool` | Detects localhost/development environment |

#### `html.php` (Namespace: `Utils\Html`)

| Function | Purpose |
|---|---|
| `banner_alert($text)` | Bootstrap danger alert banner |
| `make_modal_fade($label, $text, $id, $button)` | Bootstrap modal dialog |
| `make_mail_icon_new($tab, $func_name)` | Email icon button |
| `make_project_to_user($project)` | Project dropdown `<option>` elements |
| `make_input_group($label, $id, $value, $required)` | Labeled text input with column wrapper |
| `make_input_group_no_col($label, $id, $value, $required)` | Labeled text input without wrapper |
| `makeDropdown($tab, $cat, $id, $add)` | `<select>` dropdown from array |
| `makeCard($title, $table)` | Bootstrap card component |
| `makeColSm4($title, $table, $numb, $table2, $title2)` | Card in responsive column |
| `make_col_sm_body($title, $subtitle, $table, $numb)` | Card with subtitle in header |
| `make_drop($uxutable, $code)` | `<option>` elements from associative array |
| `make_datalist_options($hyh)` | HTML5 `<datalist>` options |
| `make_mdwiki_title($title)` | Link to MDWiki article |
| `make_cat_url($category)` | Link to MDWiki category page |
| `make_talk_url($lang, $user)` | Link to Wikipedia user talk page |
| `make_mdwiki_user_url($user)` | Link to MDWiki user page |
| `make_target_url($target, $lang, $name, $deleted)` | Link to Wikipedia article |
| `div_alert($texts, $type)` | Bootstrap alert with type whitelist |
| `make_edit_icon_new($target, $edit_params, $text)` | Edit button with popup window |

#### `html_side1.php` (Namespace: `Utils\HtmlSide`)

| Function | Purpose |
|---|---|
| `menu_data(): array` | Returns icon mappings and hierarchical menu structure |
| `generateListItem($href, $title, $icon, $target): string` | Single navigation link with Bootstrap icon |
| `create_side($filename, $ty): string` | Complete sidebar HTML with role-based filtering |

#### `tables_dir.php` (Namespace: `Utils\TablesDir`)

| Function | Purpose |
|---|---|
| `open_td_tables_file($file_path): array` | Reads JSON file with error handling and diagnostics |

---

## Architecture & Code Quality Review

### Design Patterns

- **Component Pattern**: Each HTML function generates a self-contained UI component (card, modal, alert, etc.)
- **Heredoc Templates**: All HTML uses PHP 7.3+ heredoc syntax (`<<<HTML ... HTML`) for readable multi-line templates
- **Role-Based Access Control**: Sidebar filters menu items based on `$GLOBALS['user_is_coordinator']`
- **Graceful Degradation**: `open_td_tables_file()` returns empty array on any failure instead of crashing

### Code Organization

**Good**. Four files with clear, distinct responsibilities. The separation between general utilities, HTML components, navigation, and data loading is clean and logical.

### SOLID Principles Compliance

- **Single Responsibility**: Each function handles one concern (one UI component, one utility operation)
- **Open/Closed**: New components can be added as new functions without modifying existing ones

### Maintainability

**Good**. Functions are small, well-named, and use consistent patterns. The heredoc syntax makes HTML output readable. However, the lack of a template engine means HTML is tightly coupled to PHP.

### Readability

**Very Good**. The heredoc syntax provides excellent readability for HTML output. Function names are descriptive and follow a consistent naming convention (`make_*` for generators, `div_*` for containers).

### Scalability Considerations

- **Static lookup tables**: `html.php` reads `TablesSql::$s_projects_title_to_id` at call time, which is fast but couples the module to the data layer
- **No caching**: HTML components are generated fresh on each call (acceptable for server-rendered pages)

---

## Strengths

- **Consistent XSS prevention**: Most functions use `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` for output escaping
- **URL encoding**: External URLs use `rawurlencode()` with space-to-underscore conversion for MediaWiki compatibility
- **Alert type whitelist**: `div_alert()` validates Bootstrap alert types against a whitelist, preventing CSS class injection
- **Responsive sidebar**: Separate desktop and mobile layouts with Bootstrap collapse and toggle components
- **Role-based filtering**: Sidebar automatically hides admin-only items from regular users and non-admin items from coordinators
- **Graceful error handling**: `open_td_tables_file()` handles file-not-found, read failure, and JSON decode failure without crashing
- **Clean heredoc templates**: HTML output is readable and maintainable

## Weaknesses

- **Commented-out XSS escaping**: `banner_alert()` has `htmlspecialchars()` commented out on line 60
- **Missing escaping in `make_modal_fade()`**: `$label`, `$text`, and `$button` are not escaped
- **Inconsistent input escaping**: `make_input_group()` escapes `$value` but not `$label`; `$id` is never escaped
- **Global state coupling**: Sidebar reads `$GLOBALS['user_is_coordinator']` directly
- **No template engine**: HTML is generated in PHP, mixing logic and presentation
- **Debug mode exposed to all users**: `?test=1` activates debug output in production

---

## Critical Issues

### XSS in `banner_alert()` (HIGH)

```php
// $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');  // COMMENTED OUT
HTML.="$text";  // Raw, unescaped output
```

If `$text` contains user-controlled content, this is a reflected/stored XSS vulnerability.

### XSS in `make_modal_fade()` (HIGH)

`$label`, `$text`, and `$button` are inserted directly into the HTML heredoc without escaping. If any caller passes user-derived content, this is an XSS vector.

### Debug Mode Exposed to All Users (MEDIUM)

```php
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
```

Any visitor can enable verbose error display by appending `?test=1` to a URL. In production, this leaks internal paths and error details.

### Potential Path Traversal in `open_td_tables_file()` (LOW)

The function accepts any `$file_path` and reads it with `file_get_contents()`. If the path is derived from user input elsewhere, this could allow reading arbitrary files.

---

## Areas That Need Attention

- **Uncomment XSS escaping**: Restore `htmlspecialchars()` in `banner_alert()` and add escaping to `make_modal_fade()`
- **Escape all HTML attributes**: `$id` parameters should be escaped in all `make_input_group*()` functions
- **Gate debug mode**: Restrict `?test=1` to development environments only
- **Add Content-Security-Policy headers**: Restrict script sources to prevent XSS exploitation
- **Path validation**: Add directory restriction to `open_td_tables_file()` to prevent path traversal
- **Missing tests**: No unit tests for HTML generation or utility functions

---

## Improvement Plan

### Quick Fixes

1. Uncomment `htmlspecialchars()` in `banner_alert()` line 60
2. Add `htmlspecialchars()` to `$label`, `$text`, `$button` in `make_modal_fade()`
3. Escape `$id` in `make_input_group()` and `make_input_group_no_col()`
4. Gate debug mode behind `is_development()` check

### Medium-Term

5. Add unit tests for all HTML component functions
6. Implement a simple template engine or use PHP's `include` with variable scoping
7. Add path validation to `open_td_tables_file()` (restrict to `$TABLES_PATH` directory)
8. Add CSP headers in `header.php`

### Long-Term

9. Migrate to a proper template engine (Twig, Plates, or Blade) to separate logic from presentation
10. Create a component class hierarchy for type-safe HTML generation
11. Implement a proper logging system to replace `test_print()`
12. Add input validation middleware for all user-supplied parameters

---

## Comprehensive Review

| Metric | Score | Notes |
|---|---|---|
| **Overall Rating** | 6/10 | Functional with good structure, but XSS gaps need fixing |
| **Production Readiness** | Partial | Works in production but XSS vulnerabilities exist |
| **Security Score** | 5/10 | Good escaping in most places, but critical exceptions in `banner_alert()` and `make_modal_fade()` |
| **Technical Debt** | Low-Medium | Small codebase, consistent patterns, minor escaping issues |
| **Maintainability** | 7/10 | Clean separation, readable heredoc templates, no tests |
| **Risk Assessment** | Medium | XSS vulnerabilities are the primary risk |

---

## Setup & Usage

### Prerequisites

- PHP 8.2+
- Composer (for autoloading and dependencies)

### Installation

```bash
cd /path/to/tdc
composer install
```

### Integration

This module is loaded via `include.php` at the application root:
```php
require_once __DIR__ . '/utils/functions.php';
require_once __DIR__ . '/utils/html_side1.php';
require_once __DIR__ . '/utils/html.php';
require_once __DIR__ . '/utils/tables_dir.php';
```

### Usage Examples

#### HTML Components

```php
use Utils\Html;

// Create a Bootstrap card
echo Html::makeCard('Translation Stats', '<p>42 translations</p>');

// Create a dropdown
$options = ['ar' => 'Arabic', 'fr' => 'French', 'de' => 'German'];
echo Html::makeDropdown($options, '', 'lang_select', 'Select Language');

// Create an alert
echo Html::div_alert(['Operation completed successfully'], 'success');

// Create a link to a translated article
echo Html::make_target_url('Diabetes', 'ar', 'Diabetes (Arabic)', false);
```

#### Sidebar Navigation

```php
use Utils\HtmlSide;

// Generate sidebar (reads $GLOBALS['user_is_coordinator'] for access control)
$sidebar = HtmlSide::create_side('last.php', 'last');
echo $sidebar;
```

#### Debug Output

```php
use Utils\Functions\test_print;

// Only prints when ?test=1 is in the URL or test cookie is set
test_print(['key' => 'value', 'count' => 42]);
test_print("Debug message");
```

#### JSON Data Loading

```php
use Utils\TablesDir\open_td_tables_file;

$data = open_td_tables_file('/path/to/data.json');
if (!empty($data)) {
    // Process data
}
```
