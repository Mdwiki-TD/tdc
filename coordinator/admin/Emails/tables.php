<?php
//---
use function Actions\MdwikiSql\fetch_query;
// use function Actions\TDApi\get_td_api;
use function Actions\TDApi\compare_it;
//---
$last_qua = <<<SQL
	select DISTINCT p1.target, p1.title, p1.cat, p1.user, p1.pupdate, p1.lang
	from pages p1
	where target != ''
	and p1.pupdate = (select p2.pupdate from pages p2 where p2.user = p1.user ORDER BY p2.pupdate DESC limit 1)
	group by p1.user
	ORDER BY p1.pupdate DESC
SQL;
//---
if (isset($_GET['newsql'])) {
	$last_qua = <<<SQL
		WITH RankedPages AS (
			SELECT
				p1.target,
				p1.user,
				p1.pupdate,
				p1.lang,
				ROW_NUMBER() OVER (PARTITION BY p1.user ORDER BY p1.pupdate DESC) AS rn
			FROM pages p1
			WHERE p1.target != ''
		)
		SELECT target, user, pupdate, lang
		FROM RankedPages
		WHERE rn = 1
		ORDER BY pupdate DESC;
	SQL;
};
//---
$rr = fetch_query($last_qua);
//---
// $rr1 = get_td_api (array('get' => 'users_by_last_pupdate'));
//---
// compare_it($rr0, $rr1);
//---
$last_user_to_tab = array();
//---
foreach ($rr as $Key => $gg) {
	if (!in_array($gg['user'], $last_user_to_tab)) {
		$last_user_to_tab[$gg['user']] = $gg;
	}
};
//---
$live_pages = array();
//---
$q_live = "select DISTINCT user, count(target) as count from pages where target != '' group by user order by count desc";
//---
$result = fetch_query($q_live);
//---
// $result1 = get_td_api (array('get' => 'count_pages', 'target' => 'not_empty'));
//---
// compare_it($result, $result1);
//---
foreach ($result as $Key => $gg) {
	$live_pages[$gg['user']] = number_format($gg['count']);
};
//---
$users_done = array();
//---
$ddi = fetch_query("select user_id, username, email, wiki, user_group from users;");
//---
foreach ($ddi as $Key => $gk) {
	$users_done[$gk['username']] = $gk;
};
//---
$der = fetch_query("select DISTINCT user from pages WHERE NOT EXISTS (SELECT 1 FROM users WHERE user = username)");
//---
// $der1 = get_td_api (array('get' => 'pages', 'distinct' => 1, 'select' => 'user'));
//---
$der2 = [];
//---
foreach ($der as $Key => $gg) {
	if (!isset($users_done[$gg['user']])) {
		$der2[] = $gg;
	}
}
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
