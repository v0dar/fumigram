<?php
if (IS_LOGGED) {
	header("Location: $site_url");
	exit;
}

$config['header'] = false;
$config['footer'] = false;
$context['app_name'] = 'login';
$context['page_title'] = lang('reset_password');
$context['rita'] = "$site_url/aj/signin";
$context['content'] = $ui->intel('login/templates/login/forgot');
