<?PHP

if ((getenv("HOME") ?: "") === '') {
    $new_home = 'I:/mdwiki/mdwiki';
    putenv('HOME=' . $new_home);
    $_ENV['HOME'] = $new_home;
}

$env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'development');

if ($env === 'development' && file_exists(__DIR__ . '/load_env.php')) {
    include_once __DIR__ . '/load_env.php';
}

include_once __DIR__ . '/csrf.php';
include_once __DIR__ . '/backend/infos/td_config.php';

include_once __DIR__ . '/utils/functions.php';
include_once __DIR__ . '/utils/html_side1.php';
include_once __DIR__ . '/utils/html.php';
include_once __DIR__ . '/utils/tables_dir.php';

include_once __DIR__ . '/userinfos_wrap.php';

include_once __DIR__ . '/backend/api_calls/mdwiki_api.php';
include_once __DIR__ . '/backend/api_calls/mdwiki_sql.php';
include_once __DIR__ . '/backend/api_calls/td_api.php';
include_once __DIR__ . '/backend/api_calls/wiki_api.php';

include_once __DIR__ . '/backend/api_or_sql/funcs.php';
include_once __DIR__ . '/backend/api_or_sql/index.php';
include_once __DIR__ . '/backend/api_or_sql/process_data.php';
include_once __DIR__ . '/backend/api_or_sql/recent_data.php';

include_once __DIR__ . '/backend/tables/sql_tables.php';
include_once __DIR__ . '/backend/tables/tables.php';

include_once __DIR__ . '/backend/tables/langcode.php';

include_once __DIR__ . '/results/get_results.php';
include_once __DIR__ . '/results/getcats.php';

require_once __DIR__ . '/coordinator/tools/recent_helps.php';
