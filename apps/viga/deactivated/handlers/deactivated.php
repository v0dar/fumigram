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
if ($user->active == 0) {
    header("Location: $site_url/activate");
    exit();
}
$config['header'] = false;
$config['footer'] = false;
$context['page'] = 'deactivated';
$context['app_name'] = 'login';
$context['page_title'] = lang('account_deactivated');
$context['content'] = $ui->intel('deactivated/templates/deactivated/index');

