<?php
//---
use function Actions\MdwikiSql\fetch_query;
use function SQLorAPI\Get\get_td_or_sql_page_user_not_in_users;
//---
$users_done = array();
//---
$ddi = fetch_query("select user_id, username, email, wiki, user_group from users;");
//---
foreach ($ddi as $Key => $gk) {
	$users_done[$gk['username']] = $gk;
};
//---
$der = get_td_or_sql_page_user_not_in_users();
//---
foreach ($der as $d => $tat) if (!in_array($tat['user'], $users_done)) {
	$users_done[$tat['user']] = array('user_id' => 0, 'username' => $tat['user'], 'email' => '', 'wiki' => '', 'user_group' => '');
}
//---
$sorted_array = array();
//---
foreach ($users_done as $u => $tab) {
	$sorted_array[$u] = $live_pages[$u] ?? 0;
};
//---
arsort($sorted_array);
//---
