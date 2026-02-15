<?PHP

if ((getenv("HOME") ?: "") === '') {
    $new_home = 'I:/mdwiki/mdwiki';
    putenv('HOME=' . $new_home);
    $_ENV['HOME'] = $new_home;
}

include_once __DIR__ . '/csrf.php';
include_once __DIR__ . '/backend/infos/td_config.php';

foreach (glob(__DIR__ . "/utils/*.php") as $filename) {
    include_once $filename;
}
if (substr(__DIR__, 0, 2) == 'I:') {
    include_once 'I:/mdwiki/auth_repo/oauth/user_infos.php';
} else {
    include_once __DIR__ . '/../auth/oauth/user_infos.php';
}

foreach (glob(__DIR__ . "/backend/api_calls/*.php") as $filename) {
    include_once $filename;
}

foreach (glob(__DIR__ . "/backend/api_or_sql/*.php") as $filename) {
    include_once $filename;
}

// include_once __DIR__ . '/backend/api_or_sql/index.php';

foreach (glob(__DIR__ . "/backend/tables/*.php") as $filename) {
    if (basename($filename) == 'langcode.php') continue;
    include_once $filename;
}

include_once __DIR__ . '/backend/tables/langcode.php';

foreach (glob(__DIR__ . "/results/*.php") as $filename) {
    include_once $filename;
}

require_once __DIR__ . '/coordinator/tools/recent_helps.php';
