<?PHP
// has no include inside
include_once __DIR__ . '/csrf.php';
include_once __DIR__ . '/infos/td_config.php';

include_once __DIR__ . '/actions/html.php';
include_once __DIR__ . '/actions/html_side1.php';
include_once __DIR__ . '/actions/functions.php';

include_once __DIR__ . '/../auth/auth/user_infos.php';

// with include inside
include_once __DIR__ . '/actions/wiki_api.php';
include_once __DIR__ . '/actions/mdwiki_api.php';
include_once __DIR__ . '/actions/mdwiki_sql.php';
include_once __DIR__ . '/actions/td_api.php';

include_once __DIR__ . '/api_or_sql/include.php';

include_once __DIR__ . '/tablesd/tables_dir.php';
include_once __DIR__ . '/tablesd/sql_tables.php';
include_once __DIR__ . '/tablesd/tables.php';
include_once __DIR__ . '/tablesd/langcode.php';

include_once __DIR__ . '/results/get_results.php';
include_once __DIR__ . '/results/getcats.php';
require_once __DIR__ . '/coordinator/tools/recent_helps.php';
