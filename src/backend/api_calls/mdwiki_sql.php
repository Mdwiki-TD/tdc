<?php

namespace APICalls\MdwikiSql;
/*
Usage:
use function APICalls\MdwikiSql\fetch_query;
use function APICalls\MdwikiSql\execute_query;
use function APICalls\MdwikiSql\sql_add_user;
use function APICalls\MdwikiSql\update_settings;
use function APICalls\MdwikiSql\update_settings_value;
use function APICalls\MdwikiSql\insert_to_translate_type;
use function APICalls\MdwikiSql\insert_to_projects;
use function APICalls\MdwikiSql\display_tables;
use function APICalls\MdwikiSql\check_one;
*/

if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
};
//---
use PDO;
use PDOException;

class Database
{

    private $db;
    private $host;
    private $home_dir;
    private $user;
    private $password;
    private $dbname;
    private $db_suffix;
    private $groupByModeDisabled = false;

    public function __construct($server_name, $db_suffix = 'mdwiki')
    {
        if (empty($db_suffix)) {
            $db_suffix = 'mdwiki';
        }
        // ---
        $this->home_dir = getenv("HOME") ?: 'I:/mdwiki/mdwiki';
        //---
        $this->db_suffix = $db_suffix;
        $this->set_db($server_name);
    }

    private function set_db($server_name)
    {
        // $ts_pw = posix_getpwuid(posix_getuid());
        // $ts_mycnf = parse_ini_file($ts_pw['dir'] . "/confs/db.ini");
        // ---
        $ts_mycnf = parse_ini_file($this->home_dir . "/confs/db.ini");
        // ---
        if ($server_name === 'localhost') {
            $this->host = 'localhost:3306';
            $this->dbname = $ts_mycnf['user'] . "__" . $this->db_suffix;
            $this->user = 'root';
            $this->password = 'root11';
        } else {
            $this->host = 'tools.db.svc.wikimedia.cloud';
            $this->dbname = $ts_mycnf['user'] . "__" . $this->db_suffix;
            $this->user = $ts_mycnf['user'];
            $this->password = $ts_mycnf['password'];
        }
        // unset($ts_mycnf, $ts_pw);
        unset($ts_mycnf);

        try {
            $this->db = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Log the error message
            error_log($e->getMessage());
            // Display a generic message
            echo "Unable to connect to the database. Please try again later.";
            exit();
        }
    }

    public function test_print($s)
    {
        if (isset($_COOKIE['test']) && $_COOKIE['test'] == 'x') {
            return;
        }

        $print_t = (isset($_REQUEST['test']) || isset($_COOKIE['test'])) ? true : false;

        if ($print_t && gettype($s) == 'string') {
            echo "\n<br>\n$s";
        } elseif ($print_t) {
            echo "\n<br>\n";
            print_r($s);
        }
    }

    public function disableFullGroupByMode($sql_query)
    {
        // if the query contains "GROUP BY", disable ONLY_FULL_GROUP_BY, strtoupper() is for case insensitive
        if (strpos(strtoupper($sql_query), 'GROUP BY') !== false && !$this->groupByModeDisabled) {
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

    public function executequery($sql_query, $params = null)
    {
        try {
            $this->disableFullGroupByMode($sql_query);

            $q = $this->db->prepare($sql_query);
            if ($params) {
                $q->execute($params);
            } else {
                $q->execute();
            }

            // Check if the query starts with "SELECT"
            $query_type = strtoupper(substr(trim((string) $sql_query), 0, 6));
            if ($query_type === 'SELECT') {
                // Fetch the results if it's a SELECT query
                $result = $q->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } else {
                // Otherwise, return null
                return [];
            }
        } catch (PDOException $e) {
            echo "sql error:" . $e->getMessage() . "<br>" . $sql_query;
            return false;
        }
    }

    public function fetchquery($sql_query, $params = null)
    {
        try {
            $this->test_print($sql_query);

            $this->disableFullGroupByMode($sql_query);

            $q = $this->db->prepare($sql_query);
            if ($params) {
                $q->execute($params);
            } else {
                $q->execute();
            }

            // Fetch the results if it's a SELECT query
            $result = $q->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            echo "SQL Error:" . $e->getMessage() . "<br>" . $sql_query;
            // error_log("SQL Error: " . $e->getMessage() . " | Query: " . $sql_query);
            return [];
        }
    }

    public function __destruct()
    {
        $this->db = null;
    }
}
function get_dbname($table_name)
{
    // Load from configuration file or define as class constant
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
        'mdwiki' => [] // default
    ];

    if ($table_name) {
        foreach ($table_db_mapping as $db => $tables) {
            if (in_array($table_name, $tables)) {
                return $db;
            }
        }
    }

    return 'mdwiki'; // default
}

function execute_query($sql_query, $params = null, $table_name = null)
{

    $dbname = get_dbname($table_name);

    // Create a new database object
    $db = new Database($_SERVER['SERVER_NAME'] ?? '', $dbname);

    // Execute a SQL query
    if ($params) {
        $results = $db->executequery($sql_query, $params);
    } else {
        $results = $db->executequery($sql_query);
    }

    // Print the results
    // foreach ($results as $row) echo $row['column1'] . " " . $row['column2'] . "<br>";

    // Destroy the database object
    $db = null;

    //---
    return $results;
};
function fetch_query($sql_query, $params = null, $table_name = null)
{

    $dbname = get_dbname($table_name);

    // Create a new database object
    $db = new Database($_SERVER['SERVER_NAME'] ?? '', $dbname);

    // Execute a SQL query
    if ($params) {
        $results = $db->fetchquery($sql_query, $params);
    } else {
        $results = $db->fetchquery($sql_query);
    }

    // Print the results
    // foreach ($results as $row) echo $row['column1'] . " " . $row['column2'] . "<br>";

    // Destroy the database object
    $db = null;

    //---
    return $results;
};

function sql_add_user($user_name, $email, $wiki, $project)
{
    // Create a new database object
    // Use a prepared statement for INSERT
    $qua = <<<SQL
        INSERT INTO users (username, email, wiki, user_group) SELECT ?, ?, ?, ?
        WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = ?)
    SQL;
    $params = [$user_name, $email, $wiki, $project, $user_name];

    // Prepare and execute the SQL query with parameter binding
    $results = execute_query($qua, $params = $params);

    return $results;
}

function sql_update_user($user_name, $email, $wiki, $project, $user_id)
{
    // Check if $user_id is set and not empty
    if (empty($user_id) || $user_id == 0 || $user_id == "0") {
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
    $params = [$user_name, $email, $project, $wiki, $user_id];

    // Prepare and execute the SQL query with parameter binding
    $results = execute_query($qua, $params = $params);

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
    // ---
    $query = <<<SQL
        UPDATE settings SET value = ? WHERE id = ?
    SQL;
    $params = [$value, $id];

    // Prepare and execute the SQL query with parameter binding
    $results = execute_query($query, $params);

    return $results;
}

function insert_to_translate_type($tt_title, $tt_lead, $tt_full, $tt_id = 0)
{
    //---
    $query = "UPDATE translate_type SET tt_lead = ?, tt_full = ? WHERE tt_id = ?";
    $params = [$tt_lead, $tt_full, $tt_id];
    //---
    if ($tt_id == 0 || $tt_id == '0' || empty($tt_id)) {
        $query = "INSERT INTO translate_type (tt_title, tt_lead, tt_full) SELECT ?, ?, ?";
        $params = [$tt_title, $tt_lead, $tt_full];
    };
    //---
    $result = execute_query($query, $params);
    //---
    return $result;
}

function insert_to_projects($g_title, $g_id)
{
    $query = "UPDATE projects SET g_title = ? WHERE g_id = ?";
    $params = [$g_title, $g_id];
    //---
    if ($g_id == 0 || $g_id == '0' || empty($g_id)) {
        $query = "INSERT INTO projects (g_title) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM projects WHERE g_title = ?)";
        $params = [$g_title, $g_title];
    };
    //---
    $result = execute_query($query, $params);
    //---
    return $result;
}

function check_one($select = "*", $where = "", $value = "", $table = "")
{
    // Whitelist of allowed tables
    $allowed_tables = ['users', 'qids', 'qids_others'];

    // Whitelist of allowed columns for each table
    $allowed_columns = [
        'users' => ['*', 'username'],
        'qids' => ['*', 'qid', 'title'],
        'qids_others' => ['*', 'qid', 'title'],
    ];

    // Validate table name
    if (!in_array($table, $allowed_tables)) {
        error_log("check_one: Invalid table name: $table");
        // return false;
    }

    // Validate select and where columns
    if (!in_array($select, $allowed_columns[$table]) || !in_array($where, $allowed_columns[$table])) {
        error_log("check_one: Invalid column name for table $table");
        // return false;
    }
    // ---
    // check if it's already in table
    $query = "SELECT $select FROM $table WHERE $where = ?";
    // ---
    $result = fetch_query($query, [$value]);
    //---
    if (count($result) > 0) {
        foreach ($result as $key => $tab) {
            // ---
            // echo "<br>check_one: $where: $tab[$select]<br>";
            // ---
            return $tab[$select] ?? $tab;
        }
    }
    //---
    return false;
}
