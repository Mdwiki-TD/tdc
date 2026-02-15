<?php

declare(strict_types=1);

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
 * use function APICalls\MdwikiSql\fetch_query;
 * use function APICalls\MdwikiSql\execute_query;
 * 
 * // Fetch results (SELECT queries)
 * $users = fetch_query("SELECT * FROM users WHERE active = ?", [1]);
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

namespace APICalls\MdwikiSql;

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
    /**
     * PDO database connection instance
     * 
     * @var PDO|null
     */
    private ?PDO $db = null;

    /**
     * Database server hostname
     * 
     * @var string
     */
    private string $host;

    /**
     * Home directory path for configuration files
     * 
     * @var string
     */
    private string $home_dir;

    /**
     * Database username
     * 
     * @var string
     */
    private string $user;

    /**
     * Database password
     * 
     * @var string
     */
    private string $password;

    /**
     * Database name
     * 
     * @var string
     */
    private string $dbname;

    /**
     * Database suffix for multi-database support
     * 
     * @var string
     */
    private string $db_suffix;

    /**
     * Flag indicating if ONLY_FULL_GROUP_BY has been disabled
     * 
     * @var bool
     */
    private bool $groupByModeDisabled = false;

    /**
     * Initialize database connection for specified environment
     * 
     * Automatically detects the server environment and configures
     * the connection appropriately:
     * - localhost: Uses local MySQL with root credentials from config
     * - production: Uses Wikimedia Toolforge database credentials
     * 
     * @param string $server_name The server hostname (e.g., 'localhost' or production domain)
     * @param string $db_suffix   Database suffix for multi-database setups (default: 'mdwiki')
     * 
     * @throws RuntimeException If database connection fails
     * 
     * @example
     * ```php
     * // Create connection for current server
     * $db = new Database($_SERVER['SERVER_NAME'] ?? '', 'mdwiki');
     * ```
     */
    public function __construct(string $server_name, string $db_suffix = 'mdwiki')
    {
        if (empty($db_suffix)) {
            $db_suffix = 'mdwiki';
        }
        
        $this->home_dir = getenv("HOME") ?: self::getDefaultHomeDir();
        $this->db_suffix = $db_suffix;
        $this->initializeConnection($server_name);
    }

    /**
     * Get default home directory for configuration files
     * 
     * SECURITY FIX: Configuration path should be loaded from environment
     * or a central configuration, not hardcoded.
     * 
     * @return string Default home directory path
     */
    private static function getDefaultHomeDir(): string
    {
        // Use environment variable if available
        $home = getenv('TDC_CONFIG_DIR');
        if ($home !== false && is_dir($home)) {
            return $home;
        }
        
        // Fallback for development (should be configured properly)
        return 'I:/mdwiki/mdwiki';
    }

    /**
     * Initialize database connection with environment-specific settings
     * 
     * @param string $server_name Server hostname for environment detection
     * 
     * @return void
     * @throws RuntimeException If connection fails
     */
    private function initializeConnection(string $server_name): void
    {
        $credentials = $this->loadCredentials();
        
        if ($server_name === 'localhost') {
            $this->host = 'localhost:3306';
            $this->dbname = $credentials['user'] . "__" . $this->db_suffix;
            $this->user = $credentials['local_user'] ?? 'root';
            $this->password = $credentials['local_password'] ?? '';
        } else {
            $this->host = 'tools.db.svc.wikimedia.cloud';
            $this->dbname = $credentials['user'] . "__" . $this->db_suffix;
            $this->user = $credentials['user'];
            $this->password = $credentials['password'];
        }

        // Clear credentials from memory after use
        unset($credentials);

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->db = new PDO($dsn, $this->user, $this->password, $options);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new RuntimeException('Unable to connect to the database. Please try again later.');
        }
    }

    /**
     * Load database credentials from configuration file
     * 
     * SECURITY: Credentials are loaded from external file, never hardcoded.
     * 
     * @return array<string, string> Database credentials array
     * @throws RuntimeException If configuration file cannot be read
     */
    private function loadCredentials(): array
    {
        $configFile = $this->home_dir . "/confs/db.ini";
        
        if (!file_exists($configFile)) {
            throw new RuntimeException("Database configuration file not found: {$configFile}");
        }
        
        $credentials = parse_ini_file($configFile);
        
        if ($credentials === false) {
            throw new RuntimeException("Failed to parse database configuration file");
        }
        
        return $credentials;
    }

    /**
     * Print debug information when test mode is enabled
     * 
     * @param mixed $s Value to print for debugging
     * 
     * @return void
     */
    public function test_print(mixed $s): void
    {
        if (isset($_COOKIE['test']) && $_COOKIE['test'] === 'x') {
            return;
        }

        $print_t = (isset($_REQUEST['test']) || isset($_COOKIE['test']));

        if ($print_t && is_string($s)) {
            echo "\n<br>\n" . htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        } elseif ($print_t) {
            echo "\n<br>\n<pre>";
            print_r($s);
            echo "</pre>";
        }
    }

    /**
     * Disable ONLY_FULL_GROUP_BY SQL mode for legacy query compatibility
     * 
     * Some legacy queries use non-aggregated columns in GROUP BY queries.
     * This method disables the strict mode for those queries.
     * 
     * @param string $sql_query The SQL query to check
     * 
     * @return void
     */
    public function disableFullGroupByMode(string $sql_query): void
    {
        if (stripos($sql_query, 'GROUP BY') !== false && !$this->groupByModeDisabled) {
            try {
                $this->db?->exec("SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY',''))");
                $this->groupByModeDisabled = true;
            } catch (PDOException $e) {
                error_log("Failed to disable ONLY_FULL_GROUP_BY: " . $e->getMessage());
            }
        }
    }

    /**
     * Execute a SQL query and return results for SELECT queries
     * 
     * @param string              $sql_query The SQL query to execute
     * @param array<string,mixed>|null $params   Parameters for prepared statement
     * 
     * @return array<int,array<string,mixed>>|false Query results or false on error
     */
    public function executequery(string $sql_query, ?array $params = null): array|false
    {
        try {
            $this->disableFullGroupByMode($sql_query);

            $q = $this->db?->prepare($sql_query);
            if ($q === null) {
                return false;
            }
            
            if (!empty($params)) {
                $q->execute($params);
            } else {
                $q->execute();
            }

            $query_type = strtoupper(substr(trim($sql_query), 0, 6));
            
            if ($query_type === 'SELECT') {
                return $q->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return [];
        } catch (PDOException $e) {
            error_log("SQL error: " . $e->getMessage() . " | Query: " . $sql_query);
            return false;
        }
    }

    /**
     * Execute a SQL query and fetch all results
     * 
     * @param string                   $sql_query The SQL query to execute
     * @param array<string,mixed>|null $params    Parameters for prepared statement
     * 
     * @return array<int,array<string,mixed>> Query results
     */
    public function fetchquery(string $sql_query, ?array $params = null): array
    {
        try {
            $this->test_print($sql_query);
            $this->disableFullGroupByMode($sql_query);

            $q = $this->db?->prepare($sql_query);
            if ($q === null) {
                return [];
            }
            
            if (!empty($params)) {
                $q->execute($params);
            } else {
                $q->execute();
            }

            return $q->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error: " . $e->getMessage() . " | Query: " . $sql_query);
            return [];
        }
    }

    /**
     * Clean up database connection on object destruction
     */
    public function __destruct()
    {
        $this->db = null;
    }
}

/**
 * Get the appropriate database name for a given table
 * 
 * Maps table names to their corresponding database based on configuration.
 * This allows for multi-database setups where different tables may reside
 * in different databases.
 * 
 * @param string|null $table_name The table name to look up
 * 
 * @return string The database name (default: 'mdwiki')
 * 
 * @example
 * ```php
 * $dbName = get_dbname('missing'); // Returns 'mdwiki_new'
 * $dbName = get_dbname('users');   // Returns 'mdwiki'
 * ```
 */
function get_dbname(?string $table_name): string
{
    $table_db_mapping = [
        'mdwiki_new' => [
            "missing",
            "missing_by_qids",
            "exists_by_qids",
            "publish_reports",
            "login_attempts",
            "logins",
            "publish_reports_stats",
            "all_qids_titles"
        ],
        'mdwiki' => []
    ];

    if ($table_name !== null && $table_name !== '') {
        foreach ($table_db_mapping as $db => $tables) {
            if (in_array($table_name, $tables, true)) {
                return $db;
            }
        }
    }

    return 'mdwiki';
}

/**
 * Execute a SQL query (INSERT, UPDATE, DELETE) and return results
 * 
 * For SELECT queries, use fetch_query() instead.
 * Creates a new database connection, executes the query, and closes the connection.
 * 
 * @param string                   $sql_query  The SQL query to execute
 * @param array<string,mixed>|null $params     Parameters for prepared statement
 * @param string|null              $table_name Table name for database selection
 * 
 * @return array<int,array<string,mixed>>|false Query results or false on error
 * 
 * @example
 * ```php
 * // Update a record
 * execute_query(
 *     "UPDATE settings SET value = ? WHERE id = ?",
 *     ['new_value', 5]
 * );
 * 
 * // Insert with parameters
 * execute_query(
 *     "INSERT INTO users (username, email) VALUES (?, ?)",
 *     ['john_doe', 'john@example.com']
 * );
 * ```
 */
function execute_query(string $sql_query, ?array $params = null, ?string $table_name = null): array|false
{
    $dbname = get_dbname($table_name);
    $db = new Database($_SERVER['SERVER_NAME'] ?? '', $dbname);

    $results = (!empty($params)) 
        ? $db->executequery($sql_query, $params) 
        : $db->executequery($sql_query);

    return $results;
}

/**
 * Execute a SELECT query and return all matching rows
 * 
 * Creates a new database connection, executes the query, and closes the connection.
 * Always returns an array (empty array on error).
 * 
 * @param string                   $sql_query  The SELECT query to execute
 * @param array<string,mixed>|null $params     Parameters for prepared statement
 * @param string|null              $table_name Table name for database selection
 * 
 * @return array<int,array<string,mixed>> Array of result rows (empty on error)
 * 
 * @example
 * ```php
 * // Fetch all active users
 * $users = fetch_query("SELECT * FROM users WHERE active = ?", [1]);
 * 
 * // Fetch with multiple parameters
 * $pages = fetch_query(
 *     "SELECT * FROM pages WHERE lang = ? AND user = ?",
 *     ['en', 'john_doe']
 * );
 * ```
 */
function fetch_query(string $sql_query, ?array $params = null, ?string $table_name = null): array
{
    $dbname = get_dbname($table_name);
    $db = new Database($_SERVER['SERVER_NAME'] ?? '', $dbname);

    $results = (!empty($params)) 
        ? $db->fetchquery($sql_query, $params) 
        : $db->fetchquery($sql_query);

    return $results;
}

/**
 * Add a new user to the database if they don't already exist
 * 
 * @param string $user_name The username
 * @param string $email     User's email address
 * @param string $wiki      Associated wiki
 * @param string $project   User's project/group
 * 
 * @return array<int,array<string,mixed>>|false Query result or false on error
 */
function sql_add_user(string $user_name, string $email, string $wiki, string $project): array|false
{
    $query = <<<SQL
        INSERT INTO users (username, email, wiki, user_group) 
        SELECT ?, ?, ?, ?
        WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = ?)
    SQL;
    
    $params = [$user_name, $email, $wiki, $project, $user_name];

    return execute_query($query, $params);
}

/**
 * Update an existing user's information
 * 
 * @param string      $user_name The username
 * @param string      $email     User's email address
 * @param string      $wiki      Associated wiki
 * @param string      $project   User's project/group
 * @param int|null    $user_id   User's ID (required for update)
 * 
 * @return array<int,array<string,mixed>>|false Query result or false on error
 */
function sql_update_user(string $user_name, string $email, string $wiki, string $project, ?int $user_id): array|false
{
    if (empty($user_id) || $user_id === 0) {
        return false;
    }
    
    $query = <<<SQL
        UPDATE users SET
            username = ?,
            email = ?,
            user_group = ?,
            wiki = ?
        WHERE user_id = ?
    SQL;
    
    $params = [$user_name, $email, $project, $wiki, $user_id];

    return execute_query($query, $params);
}

/**
 * Update or insert a settings record
 * 
 * @param int|string|null $id        Setting ID (null or 0 for insert)
 * @param string          $title     Setting title/key
 * @param string          $displayed Display name
 * @param string          $value     Setting value
 * @param string          $type      Setting type
 * 
 * @return array<int,array<string,mixed>>|false Query result or false on error
 */
function update_settings(int|string|null $id, string $title, string $displayed, string $value, string $type): array|false
{
    if (empty($id) || $id === 0 || $id === '0') {
        $query = "INSERT INTO settings (id, title, displayed, Type, value) 
                  SELECT ?, ?, ?, ?, ? 
                  WHERE NOT EXISTS (SELECT 1 FROM settings WHERE title = ?)";
        $params = [$id, $title, $displayed, $type, $value, $title];
    } else {
        $query = "UPDATE settings SET title = ?, displayed = ?, Type = ?, value = ? WHERE id = ?";
        $params = [$title, $displayed, $type, $value, $id];
    }

    return execute_query($query, $params);
}

/**
 * Update a setting's value by ID
 * 
 * @param int    $id    Setting ID
 * @param string $value New value
 * 
 * @return array<int,array<string,mixed>>|false Query result or false on error
 */
function update_settings_value(int $id, string $value): array|false
{
    if (empty($id) || $id === 0) {
        return false;
    }
    
    $query = "UPDATE settings SET value = ? WHERE id = ?";
    $params = [$value, $id];

    return execute_query($query, $params);
}

/**
 * Insert or update a translate type record
 * 
 * @param string   $tt_title Title
 * @param int      $tt_lead  Lead word count
 * @param int      $tt_full  Full word count
 * @param int|null $tt_id    ID for update (null or 0 for insert)
 * 
 * @return array<int,array<string,mixed>>|false Query result or false on error
 */
function insert_to_translate_type(string $tt_title, int $tt_lead, int $tt_full, ?int $tt_id = null): array|false
{
    if (empty($tt_id) || $tt_id === 0) {
        $query = "INSERT INTO translate_type (tt_title, tt_lead, tt_full) SELECT ?, ?, ?";
        $params = [$tt_title, $tt_lead, $tt_full];
    } else {
        $query = "UPDATE translate_type SET tt_lead = ?, tt_full = ? WHERE tt_id = ?";
        $params = [$tt_lead, $tt_full, $tt_id];
    }

    return execute_query($query, $params);
}

/**
 * Insert or update a project record
 * 
 * @param string   $g_title Project title
 * @param int|null $g_id    Project ID for update (null or 0 for insert)
 * 
 * @return array<int,array<string,mixed>>|false Query result or false on error
 */
function insert_to_projects(string $g_title, ?int $g_id = null): array|false
{
    if (empty($g_id) || $g_id === 0) {
        $query = "INSERT INTO projects (g_title) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM projects WHERE g_title = ?)";
        $params = [$g_title, $g_title];
    } else {
        $query = "UPDATE projects SET g_title = ? WHERE g_id = ?";
        $params = [$g_title, $g_id];
    }

    return execute_query($query, $params);
}

/**
 * Check for a single value in a whitelisted table
 * 
 * SECURITY: Only allows queries on predefined tables and columns
 * to prevent SQL injection through dynamic table/column names.
 * 
 * @param string $select Column to select
 * @param string $where  Column for WHERE clause
 * @param string $value  Value to search for
 * @param string $table  Table name (must be in whitelist)
 * 
 * @return string|false The selected value, or false if not found
 */
function check_one(string $select = "*", string $where = "", string $value = "", string $table = ""): string|false
{
    $allowed_tables = ['users', 'qids', 'qids_others'];

    $allowed_columns = [
        'users' => ['*', 'username'],
        'qids' => ['*', 'qid', 'title'],
        'qids_others' => ['*', 'qid', 'title'],
    ];

    // SECURITY FIX: Enforce validation
    if (!in_array($table, $allowed_tables, true)) {
        error_log("check_one: Invalid table name: $table");
        return false;
    }

    if (!isset($allowed_columns[$table]) || 
        !in_array($select, $allowed_columns[$table], true) || 
        !in_array($where, $allowed_columns[$table], true)) {
        error_log("check_one: Invalid column name for table $table");
        return false;
    }

    $query = "SELECT $select FROM $table WHERE $where = ?";
    $result = fetch_query($query, [$value]);

    if (!empty($result) && isset($result[0][$select])) {
        return $result[0][$select];
    }

    return false;
}
