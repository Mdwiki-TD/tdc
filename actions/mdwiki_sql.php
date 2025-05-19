<?php

namespace Actions\MdwikiSql;
/*
Usage:
use function Actions\MdwikiSql\fetch_query;
use function Actions\MdwikiSql\execute_query;
use function Actions\MdwikiSql\sql_add_user;
use function Actions\MdwikiSql\update_settings;
use function Actions\MdwikiSql\update_settings_value;
use function Actions\MdwikiSql\insert_to_translate_type;
use function Actions\MdwikiSql\insert_to_projects;
use function Actions\MdwikiSql\display_tables;
use function Actions\MdwikiSql\check_one;
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
    private $user;
    private $password;
    private $dbname;

    public function __construct($server_name)
    {
        if ($server_name === 'localhost' || !getenv('HOME')) {
            $this->host = 'localhost:3306';
            $this->dbname = 'mdwiki';
            $this->user = 'root';
            $this->password = 'root11';
        } else {
            $ts_pw = posix_getpwuid(posix_getuid());
            $ts_mycnf = parse_ini_file($ts_pw['dir'] . "/confs/db.ini");
            $this->host = 'tools.db.svc.wikimedia.cloud';
            $this->dbname = $ts_mycnf['db'];
            $this->user = $ts_mycnf['user'];
            $this->password = $ts_mycnf['password'];
            unset($ts_mycnf, $ts_pw);
        }

        try {
            $this->db = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e->getMessage();
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

    public function execute_query($sql_query, $params = null)
    {
        try {
            // إزالة ONLY_FULL_GROUP_BY مرة واحدة لكل جلسة
            $this->db->exec("SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY',''))");

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
            return [];
        }
    }

    public function fetch_query($sql_query, $params = null)
    {
        try {
            $this->test_print($sql_query);

            // إزالة ONLY_FULL_GROUP_BY مرة واحدة لكل جلسة
            $this->db->exec("SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY',''))");

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
            echo "sql error:" . $e->getMessage() . "<br>" . $sql_query;
            return [];
        }
    }

    public function __destruct()
    {
        $this->db = null;
    }
}

function execute_query($sql_query, $params = null)
{

    // Create a new database object
    $db = new Database($_SERVER['SERVER_NAME'] ?? '');

    // Execute a SQL query
    if ($params) {
        $results = $db->execute_query($sql_query, $params);
    } else {
        $results = $db->execute_query($sql_query);
    }

    // Print the results
    // foreach ($results as $row) echo $row['column1'] . " " . $row['column2'] . "<br>";

    // Destroy the database object
    $db = null;

    //---
    return $results;
};
function fetch_query($sql_query, $params = null)
{

    // Create a new database object
    $db = new Database($_SERVER['SERVER_NAME'] ?? '');

    // Execute a SQL query
    if ($params) {
        $results = $db->fetch_query($sql_query, $params);
    } else {
        $results = $db->fetch_query($sql_query);
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
