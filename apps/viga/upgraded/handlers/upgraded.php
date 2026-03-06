<?php
if (IS_LOGGED !== true) {
	header("Location: $site_url/home");
	exit;
}

$user = $db->getOne(T_USERS);
if ($user->is_pro == 0) {
	header("Location: $site_url/home");
	exit;
}

$context['page_link'] = 'upgraded';
$context['exjs'] = true;
$context['app_name'] = 'upgraded';
$context['page_title'] = $context['lang']['upgraded'];
$context['content'] = $ui->intel('upgraded/templates/upgraded/index');
