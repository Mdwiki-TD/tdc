# Results - Translation Gap Analysis Engine

## Project Overview

The `results/` module is the **translation gap analysis engine** for the Mdwiki Translation Dashboard Coordinator (TDC). It determines which medical articles in a given MDWiki category have been translated and which are still missing, enabling coordinators to prioritize translation efforts.

### Main Features

- **Category member retrieval**: Fetch all pages in an MDWiki category, with recursive subcategory traversal up to configurable depth
- **Cache-first data access**: Local JSON file cache with live MediaWiki API fallback
- **Gap analysis**: Compare category members against a list of already-translated pages to identify missing translations
- **Breadth-first subcategory traversal**: Iterative BFS algorithm walks subcategory trees efficiently
- **Content filtering**: Automatically excludes `Category:`, `File:`, `Template:`, `User:` namespaces and disambiguation pages

### Frameworks and Technologies

| Technology | Purpose |
|---|---|
| PHP 8.2+ | Runtime language |
| MediaWiki API | Live category member fetching from mdwiki.org |
| cURL | HTTP transport (via `mdwiki_api.php`) |
| JSON files | Local caching layer for category members and translation data |
| Composer | Dependency management |

### PHP Version Requirement

`^8.2` (per parent project's `composer.json`)

---

## Project Structure

```
results/
├── get_results.php     # Gap analysis: compares category members vs translated pages
└── getcats.php         # Category member retrieval with caching and recursive traversal
```

### Data Flow

```
get_cat_exists_and_missing(cat, depth, code)
├── get_mdwiki_cat_members(cat, use_cache, depth)
│   └── get_category_members(cat, use_cache)
│       ├── get_category_from_cache(cat)     ← reads JSON from $tables_path/cats_cash/
│       └── fetch_category_members(cat)       ← calls MediaWiki API (paginated)
├── open_td_tables_file(cash_exists/$code.json)  ← reads translated pages list
└── Compute set difference → return { len_of_exists, missing }
```

### Key Functions

| Function | File | Purpose |
|---|---|---|
| `get_cat_exists_and_missing()` | `get_results.php` | Main entry point: gap analysis |
| `get_mdwiki_cat_members()` | `getcats.php` | Recursive category traversal with BFS |
| `get_category_members()` | `getcats.php` | Cache-or-API orchestrator |
| `fetch_category_members()` | `getcats.php` | Live MediaWiki API caller |
| `get_category_from_cache()` | `getcats.php` | JSON cache reader |

### Namespaces

| Namespace | File |
|---|---|
| `Results\GetResults` | `get_results.php` |
| `Results\GetCats` | `getcats.php` |

---

## Architecture & Code Quality Review

### Design Patterns

- **Read-Through Cache**: `get_category_members()` tries the local JSON cache first, falls back to the live API
- **Repository Pattern**: Each function acts as a repository method for a specific data entity
- **Breadth-First Traversal**: `get_mdwiki_cat_members()` uses iterative BFS (not recursion) to walk subcategory trees
- **Set-Difference Gap Analysis**: `get_cat_exists_and_missing()` computes missing items by comparing two lists

### Code Organization

**Good**. Two files with clear responsibilities: `getcats.php` handles data retrieval, `get_results.php` handles analysis. The BFS implementation is clean and well-structured.

### SOLID Principles Compliance

- **Single Responsibility**: Each function has one clear purpose
- **Separation of Concerns**: Data retrieval (`getcats.php`) is cleanly separated from analysis (`get_results.php`)

### Maintainability

**Good**. The code is short (~190 lines total), well-organized, and uses consistent patterns. Functions are small and focused.

### Readability

**Good**. Descriptive function names, clear control flow, and consistent formatting. The BFS implementation uses a straightforward while loop.

### Scalability Considerations

- **Recursive depth**: The `$depth` parameter limits subcategory traversal, preventing runaway API calls
- **Cache dependency**: Performance depends on cache freshness; stale caches may return outdated data
- **No pagination limits**: `fetch_category_members()` paginates until all members are fetched, which could be slow for very large categories

---

## Strengths

- **Clean cache-first architecture**: Reduces API load and improves response times
- **Robust BFS implementation**: Handles arbitrary subcategory depth with deduplication
- **Content filtering**: Automatically excludes non-article namespaces and disambiguation pages
- **Graceful fallback**: If cache is empty and API fails, returns empty array instead of crashing
- **Environment-driven configuration**: `TABLES_PATH` is read from environment variables, supporting dev/production separation
- **Consistent use of shared utilities**: Uses `test_print()`, `open_td_tables_file()`, `start_with()` from the utils layer

## Weaknesses

- **No rate limiting on API calls**: Rapid pagination requests to mdwiki.org could trigger throttling
- **Cache staleness**: No TTL or freshness check on cached data
- **No error propagation**: API errors are silently swallowed (logged via `test_print` only)
- **File path construction**: `$tables_path` concatenation has no path sanitization
- **No input validation on category names**: Passed directly to API requests without length or format checks

---

## Critical Issues

### Debug Mode Exposure (MEDIUM)

Both files activate `display_errors=1` when `$_REQUEST['test']` is set:
```php
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
```
Any visitor can enable verbose error display by appending `?test=1` to a URL.

### No Rate Limiting on API Calls (LOW)

`fetch_category_members()` makes paginated API calls in a loop with no delay or rate limiting. Large categories could trigger Wikimedia API throttling.

### File Path from Environment Variable (LOW)

`$tables_path` from `TABLES_PATH` env var is concatenated directly into file paths. While `$category` comes from internal logic, a compromised env var could enable path traversal.

---

## Areas That Need Attention

- **Cache TTL**: Add time-based cache invalidation to prevent stale data
- **Rate limiting**: Add delays between paginated API calls
- **Error handling**: Propagate errors to callers instead of silently returning empty arrays
- **Input validation**: Validate category name format and length before API requests
- **Missing tests**: No unit tests for the gap analysis logic
- **Logging**: Replace `test_print()` with proper logging for production debugging

---

## Improvement Plan

### Quick Fixes

1. Gate debug mode behind `is_development()` check
2. Add `usleep()` or rate limiter between paginated API calls
3. Add category name validation (length, format)

### Medium-Term

4. Implement cache TTL (e.g., 24 hours) with file modification time checks
5. Add proper error logging (replace `test_print` with `error_log`)
6. Write unit tests for `get_cat_exists_and_missing()` and `get_mdwiki_cat_members()`
7. Add path sanitization for `$tables_path` construction

### Long-Term

8. Implement background cache refresh (cron job to update JSON caches)
9. Add API response caching (APCu or Redis) for frequently-accessed categories
10. Consider implementing a queue-based approach for large category traversals

---

## Comprehensive Review

| Metric | Score | Notes |
|---|---|---|
| **Overall Rating** | 7/10 | Clean, focused module with good architecture |
| **Production Readiness** | Yes | Functional and deployed, minor improvements needed |
| **Security Score** | 7/10 | Debug mode exposure is the main concern |
| **Technical Debt** | Low | Small codebase, clear structure |
| **Maintainability** | 8/10 | Short files, clear responsibilities, consistent patterns |
| **Risk Assessment** | Low | Read-only module with no write operations |

---

## Setup & Usage

### Prerequisites

- PHP 8.2+ with cURL extension
- Access to the Toolforge MySQL database (or local equivalent)
- JSON cache files in the `TABLES_PATH` directory

### Environment Variables

```env
TABLES_PATH=/path/to/tables
```

### Directory Structure for Cache Files

```
$TABLES_PATH/
├── cats_cash/              # Category member caches
│   ├── Diseases.json
│   ├── Symptoms.json
│   └── ...
└── cash_exists/            # Translation existence caches
    ├── ar.json             # Arabic translations
    ├── fr.json             # French translations
    └── ...
```

### Usage Example

```php
use Results\GetResults\get_cat_exists_and_missing;

// Get missing translations for "Diseases" category in Arabic
$result = get_cat_exists_and_missing('Diseases', 2, 'ar');

echo "Translated: " . $result['len_of_exists'] . "\n";
echo "Missing: " . count($result['missing']) . "\n";
foreach ($result['missing'] as $page) {
    echo "  - $page\n";
}
```

### Cache Bypass

To force live API data instead of cache:
```php
$result = get_cat_exists_and_missing('Diseases', 2, 'ar', use_cache: false);
```
