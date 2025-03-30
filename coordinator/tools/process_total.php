<?PHP
//---
use function SQLorAPI\Process\get_users_process;
use function SQLorAPI\Process\get_users_process_new;

//---
echo <<<HTML
    <div class='card-header'>
        <h4>Translations in process</h4>
    </div>
    <div class='card-body'>
HTML;
//---
/*

صفحات مكررة

SELECT A.id as id1, A.title as title1, A.user as user1, A.target as target1,
B.id as id2, B.title as title2, B.user as user2, B.target as target2
 from pages A, pages B
where (A.target = '' OR A.target IS NULL)
and A.lang = B.lang
and A.title = B.title
and B.target != ''
;

للحذف:
SELECT A.id from pages A, pages B where (A.target = '' OR A.target IS NULL) and A.lang = B.lang and A.title = B.title and B.target != '';
*/
//---
$text = <<<HTML
<table class='table table-striped compact soro table-mobile-responsive table-mobile-sided'>
    <thead>
        <tr>
            <th>#</th>
            <th class='spannowrap'>User</th>
            <th>Articles</th>
        </tr>
    </thead>
    <tbody>

HTML;
//---
// $user_process_tab = get_users_process();
$user_process_tab = get_users_process_new();
// sort user_process_tab by value
arsort($user_process_tab);
//---
$n = 0;
//---
foreach ($user_process_tab as $user => $count) {
    // ---
    if ($user != 'test' && !empty($user) && $count > 0) {
        //---
        $n++;
        //---
        $use = rawurlEncode($user);
        $use = str_replace('+', '_', $use);
        //---
        $text .= <<<HTML
        <tr>
            <td data-content='#'>
                $n
            </td>
            <td data-content='User'>
                <a href='/Translation_Dashboard/leaderboard.php?user=$use'>$user</a>
            </td>
            <td data-content='Articles'>
                $count
            </td>
        </tr>
        HTML;
    };
};
//---
$text .= <<<HTML
	</tbody>
	</table>
HTML;
//---
echo $text;
//---
