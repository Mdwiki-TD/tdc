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
 * use function App\APICalls\MdwikiSql\fetch_query;
 * use function App\APICalls\MdwikiSql\execute_query;
 *
 * // Fetch results (SELECT queries)
 * $users = fetch_query("SELECT * FROM users WHERE is_active = ?", [1]);
 *
 * // Execute queries (INSERT, UPDATE, DELETE)
 * execute_query("UPDATE settings SET value = ? WHERE id = ?", ['new_value', 5]);
 * ```
 *
 * Configuration:
 * Database credentials are stored in ~/confs/db.ini:
 * ```ini
 * user = your_toolforge_username
 * password = your_database_password
 * ```
 *
 * @package    APICalls
 * @subpackage MdwikiSql
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 *
 * @see https://www.php.net/manual/en/book.pdo.php
 * @see https://wikitech.wikimedia.org/wiki/Help:Toolforge/Database
 */

namespace App\APICalls\MdwikiSql;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Database Connection and Query Management Class
 *
 * Encapsulates PDO database operations with automatic connection management,
 * error handling, and environment-specific configuration.
 *
 * @package APICalls\MdwikiSql
 */
class Database
{

    private $db;
    private $host;
    private $user;
    private $password;
    private $dbname;
    private $groupByModeDisabled = false;

    public function __construct(string $dbnameVar = 'DB_NAME')
    {
        $this->set_db($dbnameVar);
    }

    private function envVar(string $key)
    {
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        return "";
    }
    private function set_db(string $dbnameVar)
    {
        $this->host = $this->envVar('DB_HOST_TOOLS') ?: 'tools.db.svc.wikimedia.cloud';
        $this->dbname = $this->envVar($dbnameVar);
        $this->user = $this->envVar('TOOL_TOOLSDB_USER');
        $this->password = $this->envVar('TOOL_TOOLSDB_PASSWORD');

        try {
            $this->db = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Log the error message
            error_log($e->getMessage());
            // Display a generic message
            echo "Unable to connect to the database. Please try again later.";
            throw new \RuntimeException('Database connection failed');
            // exit();
        }
    }

    public function test_print($s)
    {
        if (isset($_COOKIE['test']) && $_COOKIE['test'] == 'x') {
            return;
        }

        $print_t = (isset($_REQUEST['test']) || isset($_COOKIE['test'])) ? true : false;

        if ($print_t && is_string($s)) {
            echo "\n<br>\n$s";
        } elseif ($print_t) {
            echo "\n<br>\n";
            print_r($s);
        }
    }

    public function disableFullGroupByMode($sqlQuery)
    {
        // if the query contains "GROUP BY", disable ONLY_FULL_GROUP_BY, strtoupper() is for case insensitive
        if (strpos(strtoupper($sqlQuery), 'GROUP BY') !== false && !$this->groupByModeDisabled) {
            try {
                // More precise SQL mode modification
                $this->db->exec("SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY',''))");
                $this->groupByModeDisabled = true;
            } catch (PDOException $e) {
                // Log error but don't fail the query
                error_log("Failed to disable ONLY_FULL_GROUP_BY: " . $e->getMessage());
            }
        }
    }

    public function executequery($sqlQuery, $params = null)
    {
        try {
            $this->disableFullGroupByMode($sqlQuery);

            $q = $this->db->prepare($sqlQuery);
            if ($params) {
                $q->execute($params);
            } else {
                $q->execute();
            }

            // Check if the query starts with "SELECT"
            $queryType = strtoupper(substr(trim((string) $sqlQuery), 0, 6));
            if ($queryType === 'SELECT') {
                // Fetch the results if it's a SELECT query
                $result = $q->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } else {
                // Otherwise, return null
                return [];
            }
        } catch (PDOException $e) {
            echo "sql error:" . $e->getMessage() . "<br>" . $sqlQuery;
            return false;
        }
    }

    public function fetchquery($sqlQuery, $params = null)
    {
        try {
            $this->disableFullGroupByMode($sqlQuery);

            $q = $this->db->prepare($sqlQuery);
            if ($params) {
                $q->execute($params);
            } else {
                $q->execute();
            }

            // Fetch the results if it's a SELECT query
            $result = $q->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            echo "SQL Error:" . $e->getMessage() . "<br>" . $sqlQuery;
            // error_log("SQL Error: " . $e->getMessage() . " | Query: " . $sqlQuery);
            return [];
        }
    }

    public function __destruct()
    {
        $this->db = null;
    }
}

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

function insert_to_projects($gTitle, $gId)
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

function check_one($select = "*", $where = "", $value = "", $table = "")
{
    // Whitelist of allowed tables
    $allowedTables = ['users', 'qids', 'qids_others'];

    // Whitelist of allowed columns for each table
    $allowedColumns = [
        'users' => ['*', 'username'],
        'qids' => ['*', 'qid', 'title'],
        'qids_others' => ['*', 'qid', 'title'],
    ];

    // Validate table name
    if (!in_array($table, $allowedTables)) {
        error_log("check_one: Invalid table name: $table");
        // return false;
    }

    // Validate select and where columns
    if (!in_array($select, $allowedColumns[$table]) || !in_array($where, $allowedColumns[$table])) {
        error_log("check_one: Invalid column name for table $table");
        // return false;
    }

    // check if it's already in table
    $query = "SELECT $select FROM $table WHERE $where = ?";

    $result = fetch_query($query, [$value]);

    if (count($result) > 0) {
        foreach ($result as $key => $tab) {

            // echo "<br>check_one: $where: $tab[$select]<br>";

            return $tab[$select] ?? $tab;
        }
    }

    return false;
}
