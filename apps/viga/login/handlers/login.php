<?php
if (IS_LOGGED) {
	header("Location: $site_url");
	exit;
}

$config['header'] = false;
$context['app_name'] = 'login';
$context['page_title'] = lang('login');
$context['rita'] = "$site_url/aj/signin";
$context['content'] = $ui->intel('login/templates/login/index');