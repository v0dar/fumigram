<?php
if (IS_LOGGED) {
	header("Location: $site_url");
	exit;
}

if (empty($_GET['code'])) {
	header("Location: $site_url/404");
	exit;
}

$user_id = users::validateCode($_GET['code']);
if (!$user_id) {
	header("Location: $site_url/404");
	exit;
}

$config['header'] = false;
$config['footer'] = false;
$context['app_name'] = 'login';
$context['page_title'] = lang('reset');
$context['rita'] = "$site_url/aj/signin";
$context['code'] = aura::secure($_GET['code']);
$context['content'] = $ui->intel('login/templates/login/reset');
