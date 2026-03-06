<?php
if (IS_LOGGED || $config['signup_system'] != 'on') {
	header("Location: $site_url");
	exit;
}

$config['header'] = false;
$config['footer'] = false;
$context['page_title'] = lang('signup');
$context['app_name'] = 'signup';
$context['page'] = 'signup';
$context['rita'] = "$site_url/aj/$app";
$context['content'] = $ui->intel('signup/templates/signup/index');