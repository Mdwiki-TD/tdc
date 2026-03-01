<?php

declare(strict_types=1);

/**
 * PHPUnit Bootstrap File
 * 
 * Sets up autoloading and mock functions for testing AdminPostHandler classes.
 */

// Mock the execute_query function for testing
namespace APICalls\MdwikiSql {
    /**
     * Mock execute_query function for testing.
     */
    function execute_query(string $query, array $params = [])
    {
        global $mockExecuteQueryResult, $mockExecuteQueryCalls;
        $mockExecuteQueryCalls[] = ['query' => $query, 'params' => $params];
        return $mockExecuteQueryResult ?? true;
    }

    /**
     * Mock check_one function for testing.
     */
    function check_one(string $field, string $column, string $value, string $table)
    {
        global $mockCheckOneResult;
        return $mockCheckOneResult ?? null;
    }

    /**
     * Mock sql_add_user function for testing.
     */
    function sql_add_user(string $user, string $email, string $wiki, string $project): bool
    {
        global $mockSqlAddUserCalls;
        $mockSqlAddUserCalls[] = compact('user', 'email', 'wiki', 'project');
        return true;
    }

    /**
     * Mock sql_update_user function for testing.
     */
    function sql_update_user(string $user, string $email, string $wiki, string $project, string $userId): bool
    {
        global $mockSqlUpdateUserCalls;
        $mockSqlUpdateUserCalls[] = compact('user', 'email', 'wiki', 'project', 'userId');
        return true;
    }
}

// Mock the div_alert function
namespace Utils\Html {
    /**
     * Mock div_alert function for testing.
     */
    function div_alert(array $messages, string $type = 'success'): string
    {
        if (empty($messages)) {
            return '';
        }
        return '<div class="alert alert-' . $type . '">' . implode('<br>', $messages) . '</div>';
    }
}

// Mock the CSRF verification
namespace TDWIKI\csrf {
    /**
     * Mock verify_csrf_token function for testing.
     */
    function verify_csrf_token(): bool
    {
        global $mockCsrfValid;
        return $mockCsrfValid ?? true;
    }
}

// Mock the add_pages_to_db function
namespace Add\AddPost {
    /**
     * Mock add_pages_to_db function for testing.
     */
    function add_pages_to_db($mdtitle, $type, $cat, $lang, $user, $target, $pupdate, $word)
    {
        global $mockAddPagesResult, $mockAddPagesCalls;
        $mockAddPagesCalls[] = compact('mdtitle', 'type', 'cat', 'lang', 'user', 'target', 'pupdate', 'word');
        return $mockAddPagesResult ?? true;
    }
}

// Now include the AdminPostHandler file and autoloader in global namespace
namespace {
    // Include Composer autoloader if available
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }

    require_once __DIR__ . '/../src/backend/api_calls/AdminPostHandler.php';
}
