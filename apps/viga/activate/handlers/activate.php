<?php 
if (IS_LOGGED == false) {
	header("Location: $site_url/home");
	exit();
}

$user = $db->getOne(T_USERS);
if (empty($user)) {
    header("Location: $site_url/signup");
    exit();
}

$config['header'] = false;
$context['page'] = 'activate';
$context['app_name'] = 'login';
$context['page_title'] = lang('account_activation');
$context['rita'] = "$site_url/aj/$app";
$context['content'] = $ui->intel('activate/templates/activate/index');

