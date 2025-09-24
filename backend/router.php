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

// Get the list of available tools and admin sections
$tools_folders = array_map(fn ($file) => basename($file, '.php'), glob(__DIR__ . '/coordinator/tools/*.php'));
$corrd_folders = array_map('basename', glob(__DIR__ . '/coordinator/admin/*', GLOB_ONLYDIR));

// Include the new controller
include_once __DIR__ . '/controllers/LastEditsController.php';
include_once __DIR__ . '/controllers/StatController.php';

// Route the request to the appropriate handler
if ($action === 'last') {
    $params = [
        'last_table' => $_GET['last_table'] ?? 'pages',
        'lang' => $_GET['lang'] ?? 'All',
    ];
    $response['data'] = \Controllers\LastEditsController::getLastEdits($params);
    $response['success'] = true;
    $response['message'] = "Successfully retrieved last edits.";

} elseif ($action === 'stat') {
    $params = [
        'cat' => $_GET['cat'] ?? 'RTT',
    ];
    $response['data'] = \Controllers\StatController::getStats($params);
    $response['success'] = true;
    $response['message'] = "Successfully retrieved stats.";

} elseif (in_array($action, $tools_folders)) {
    // For now, just confirm which file would be included
    $response['success'] = true;
    $response['message'] = "Action '{$action}' routed to tools.";
    $response['data'] = "Would include: " . __DIR__ . "/coordinator/tools/{$action}.php";
    // include_once __DIR__ . "/coordinator/tools/{$action}.php";

} elseif ($action == "sidebar") {
    // The sidebar logic will be handled differently in the new architecture
    $response['success'] = true;
    $response['message'] = "Sidebar action is not an API endpoint.";

} elseif (in_array($action, $corrd_folders) && $user_in_coord) {
    // For now, just confirm which file would be included
    $response['success'] = true;
    $response['message'] = "Action '{$action}' routed to admin section.";
    $response['data'] = "Would include: " . __DIR__ . "/coordinator/admin/{$action}/index.php";
    // include_once __DIR__ . "/coordinator/admin/{$action}/index.php";

} else {
    $adminfile = __DIR__ . "/coordinator/admin/{$action}.php";
    if (is_file($adminfile) && $user_in_coord) {
        $response['success'] = true;
        $response['message'] = "Action '{$action}' routed to admin file.";
        $response['data'] = "Would include: {$adminfile}";
        // include_once $adminfile;
    } else {
        $response['message'] = "Action '{$action}' not found.";
        // include_once __DIR__ . "/coordinator/404.php";
    }
}

// Send the JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
