<?php
// Include the bootstrap file to load all necessary dependencies
include_once __DIR__ . '/bootstrap.php';

// Get the requested action from the query parameters
$action = $_GET['action'] ?? '';

// A simple response structure
$response = [
    'success' => false,
    'message' => 'Invalid action.',
    'data' => null
];

// ---
// At this stage, we will not handle authentication.
// We will assume the user has the necessary permissions.
$user_in_coord = true;
// ---

// Define the routes
$routes = [
    'last' => ['\Controllers\LastEditsController', 'getLastEdits', 'params' => ['last_table', 'lang']],
    'stat' => ['\Controllers\StatController', 'getStats', 'params' => ['cat']],
    'process' => ['\Controllers\ProcessController', 'getProcessData', 'params' => []],
    'reports_stats' => ['\Controllers\Admin\ReportsController', 'getReportsStats', 'params' => []],
    'reports_data' => ['\Controllers\Admin\ReportsController', 'getReportsData', 'params' => ['year', 'month', 'user', 'lang', 'result']],
];

// Check if the requested action is in our defined routes
if (array_key_exists($action, $routes)) {
    $route = $routes[$action];
    $controller = $route[0];
    $method = $route[1];
    $param_keys = $route['params'];

    $params = [];
    foreach ($param_keys as $key) {
        if (isset($_GET[$key])) {
            $params[$key] = $_GET[$key];
        }
    }

    $response['data'] = call_user_func([$controller, $method], $params);
    $response['success'] = true;
    $response['message'] = "Successfully executed action: {$action}.";

} else {
    // Fallback to the old routing mechanism for non-refactored parts
    $tools_folders = array_map(fn ($file) => basename($file, '.php'), glob(__DIR__ . '/coordinator/tools/*.php'));
    $corrd_folders = array_map('basename', glob(__DIR__ . '/coordinator/admin/*', GLOB_ONLYDIR));

    if (in_array($action, $tools_folders)) {
        $response['success'] = true;
        $response['message'] = "Action '{$action}' routed to legacy tools.";
        $response['data'] = "Would include: " . __DIR__ . "/coordinator/tools/{$action}.php";

    } elseif ($action == "sidebar") {
        $response['success'] = true;
        $response['message'] = "Sidebar action is not an API endpoint.";

    } elseif (in_array($action, $corrd_folders) && $user_in_coord) {
        $response['success'] = true;
        $response['message'] = "Action '{$action}' routed to legacy admin section.";
        $response['data'] = "Would include: " . __DIR__ . "/coordinator/admin/{$action}/index.php";

    } else {
        $adminfile = __DIR__ . "/coordinator/admin/{$action}.php";
        if (is_file($adminfile) && $user_in_coord) {
            $response['success'] = true;
            $response['message'] = "Action '{$action}' routed to legacy admin file.";
            $response['data'] = "Would include: {$adminfile}";
        } else {
            $response['message'] = "Action '{$action}' not found.";
        }
    }
}

// Send the JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
