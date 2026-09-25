<?php

/**
 * Database Abstraction Layer for MDWiki SQL Operations
 *
 * Provides a secure, PDO-based database abstraction layer for interacting
 * with MySQL/MariaDB databases in the Translation Dashboard application.
 * Supports both local development and Wikimedia Toolforge environments.
 *
 * Features:
 * - Automatic environment detection (localhost vs production)
 * - Prepared statement support for SQL injection prevention
 * - Configurable database suffix for multi-database support
 * - Automatic SQL mode adjustment for GROUP BY compatibility
 * - Secure credential management via external configuration
 *
 * Security Considerations:
 * - Credentials are loaded from external configuration file, never hardcoded
 * - All queries use prepared statements
 * - Error messages are logged, not displayed in production
 * - Database connections are properly closed after use
 *
 * Usage Example:
 * ```php
 * use function App\MdwikiSql\fetch_query;
 * use function App\MdwikiSql\execute_query;
 *
 * // Fetch results (SELECT queries)
 * $users = fetch_query("SELECT * FROM users WHERE is_active = ?", [1]);
 *
 * // Execute queries (INSERT, UPDATE, DELETE)
 * execute_query("UPDATE settings SET value = ? WHERE id = ?", ['new_value', 5]);
 * ```
 *
 * @package    MdwikiSql
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 *
 * @see https://www.php.net/manual/en/book.pdo.php
 * @see https://wikitech.wikimedia.org/wiki/Help:Toolforge/Database
 */

namespace App\MdwikiSql;

use App\MdwikiSql\Database;

function execute_query(string $sqlQuery, $params = null)
{
    // Create a new database object
    $db = new Database('DB_NAME');

    // Execute a SQL query
    if ($params) {
        $results = $db->executequery($sqlQuery, $params);
    } else {
        $results = $db->executequery($sqlQuery);
    }

    // Print the results
    // foreach ($results as $row) echo $row['column1'] . " " . $row['column2'] . "<br>";

    // Destroy the database object
    $db = null;

    return $results;
};
function fetch_query(string $sqlQuery, $params = null, $noprint = false)
{
    // Create a new database object
    $db = new Database('DB_NAME');

    if ($noprint == false) {
        $db->test_print($sqlQuery);
    }

    // Execute a SQL query
    if ($params) {
        $results = $db->fetchquery($sqlQuery, $params);
    } else {
        $results = $db->fetchquery($sqlQuery, null);
    }

    // Print the results
    // foreach ($results as $row) echo $row['column1'] . " " . $row['column2'] . "<br>";

    // Destroy the database object
    $db = null;

    return $results;
};

function sql_add_user($userName, $email, $wiki, $project)
{
    // Create a new database object
    // Use a prepared statement for INSERT
    $qua = <<<SQL
        INSERT INTO users (username, email, wiki, user_group) SELECT ?, ?, ?, ?
        WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = ?)
    SQL;
    $params = [$userName, $email, $wiki, $project, $userName];

    // Prepare and execute the SQL query with parameter binding
    $results = execute_query($qua, $params);

    return $results;
}

function sql_update_user($userName, $email, $wiki, $project, $userId)
{
    // Check if $userId is set and not empty
    if (empty($userId) || $userId == 0 || $userId == "0") {
        return;
    }
    // Use a prepared statement for UPDATE
    $qua = <<<SQL
        UPDATE users SET
            username = ?,
            email = ?,
            user_group = ?,
            wiki = ?
        WHERE user_id = ?
    SQL;
    $params = [$userName, $email, $project, $wiki, $userId];

    // Prepare and execute the SQL query with parameter binding
    $results = execute_query($qua, $params);

    return $results;
}

function update_settings($id, $title, $displayed, $value, $type)
{
    // Create a new database object

    $query = <<<SQL
        UPDATE settings SET title = ?, displayed = ?, Type = ?, value = ? WHERE id = ?
    SQL;
    $params = [$title, $displayed, $type, $value, $id];

    // Define the SQL query using a prepared statement
    if ($id == 0 || $id == '0' || empty($id)) {
        $query = "INSERT INTO settings (id, title, displayed, Type, value) SELECT ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM settings WHERE title = ?)";
        $params = [$id, $title, $displayed, $type, $value, $title];
    }

    // Prepare and execute the SQL query with parameter binding
    $results = execute_query($query, $params);

    return $results;
}

function update_settings_value($id, $value)
{
    // Create a new database object
    if ($id == 0 || $id == '0' || empty($id)) {
        return;
    }

    $query = <<<SQL
        UPDATE settings SET value = ? WHERE id = ?
    SQL;
    $params = [$value, $id];

    // Prepare and execute the SQL query with parameter binding
    $results = execute_query($query, $params);

    return $results;
}

function insert_to_translate_type($ttTitle, $ttLead, $ttFull, $ttId = 0)
{

    $query = "UPDATE translate_type SET tt_lead = ?, tt_full = ? WHERE tt_id = ?";
    $params = [$ttLead, $ttFull, $ttId];

    if ($ttId == 0 || $ttId == '0' || empty($ttId)) {
        $query = "INSERT INTO translate_type (tt_title, tt_lead, tt_full) SELECT ?, ?, ?";
        $params = [$ttTitle, $ttLead, $ttFull];
    };

    $result = execute_query($query, $params);

    return $result;
}

/**
 * Fetches a single user record by user_id.
 *
 * @param string $userId
 * @return array<string, mixed>|null
 */
function get_user_by_id(string $userId): ?array
{
    if (empty($userId)) {
        return null;
    }
    $result = fetch_query("SELECT * FROM users WHERE user_id = ?", [$userId]);
    return $result[0] ?? null;
}

/**
 * Fetches a single user record by username.
 *
 * @param string $username
 * @return array<string, mixed>|null
 */
function get_user_by_username(string $username): ?array
{
    if (empty($username)) {
        return null;
    }
    $result = fetch_query("SELECT * FROM users WHERE username = ?", [$username]);
    return $result[0] ?? null;
}

/**
 * Fetches a single qid row by column ('qid' or 'title') from 'qids' or 'qids_others'.
 *
 * @param string $column 'qid' or 'title'
 * @param string $value
 * @param string $table 'qids' or 'qids_others'
 * @return array<string, mixed>|null
 */
function get_qid_row(string $column, string $value, string $table = 'qids'): ?array
{
    $allowedColumns = ['qid', 'title'];
    $allowedTables = ['qids', 'qids_others'];

    if (!in_array($column, $allowedColumns, true) || !in_array($table, $allowedTables, true) || empty($value)) {
        return null;
    }

    $result = fetch_query("SELECT * FROM {$table} WHERE {$column} = ?", [$value]);
    return $result[0] ?? null;
}

/**
 * Fetches qid string value by title from 'qids' or 'qids_others'.
 *
 * @param string $title
 * @param string $table 'qids' or 'qids_others'
 * @return string|null
 */
function get_qid_by_title(string $title, string $table = 'qids'): ?string
{
    $row = get_qid_row('title', $title, $table);
    return isset($row['qid']) ? (string) $row['qid'] : null;
}

function insert_to_projects($gTitle, $gId): bool
{
    $query = "UPDATE projects SET g_title = ? WHERE g_id = ?";
    $params = [$gTitle, $gId];

    if ($gId == 0 || $gId == '0' || empty($gId)) {
        $query = "INSERT INTO projects (g_title) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM projects WHERE g_title = ?)";
        $params = [$gTitle, $gTitle];
    };

    $result = execute_query($query, $params);

    return $result;
}

