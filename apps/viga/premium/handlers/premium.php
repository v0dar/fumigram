<?php
if ($config['pro_system'] == 'off') {
	header("Location: $site_url/home");
	exit;
}

$user->limit = 60;
$members = $user->PremiumUsers();
$users   = (!empty($members) && is_array($members)) ? o2array($members) : array();

$context['users'] = $users;
$context['page_link'] = 'premium';
$context['app_name'] = 'premium';
$context['page_title'] = $context['lang']['upgrade_to_pro'];
$context['content'] = $ui->intel('premium/templates/premium/index');
