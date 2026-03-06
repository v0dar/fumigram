<?php
if (IS_LOGGED != true) {
	header("Location: $site_url/home");
	exit;
}
if (!empty($_GET['user']) && users::userNameExists($_GET['user'])) {
	if (IS_ADMIN) {
		$user->setUserByName($_GET['user']);
		$me = $user->userData($user->getUser());
		$me = o2array($me);
	}
}

$page   = 'general';
$pages  = array(
	'store',
	'delete',
	'password',
	'account',
	'general',
	'profile',
	'privacy',
	'credits',
	'blocked',
	'requests',
	'verification',
	'my_affiliates',
	'notifications',
	'social_account',
	'administration',
	'password_reset',
	'manage_sessions',
	'business_account',
);

if (!empty($_GET['page']) && in_array($_GET['page'], $pages)) {
	$page = $_GET['page'];
}

if ($page == 'delete' && $config['delete_account'] != 'on') {
	$page = 'general';
}

if ($page == 'administration' && empty(IS_DEVEL)) {
	$page = 'general';
}

if ($page == 'verification' && $context['me']['can_verify'] != 1) {
	$page = 'general';
}

if ($page == 'account' && $context['me']['is_pro'] == 0) {
	$page = 'general';
}

if ($page == 'my_affiliates' && $config['affiliate_system'] != '1') {
	$page = 'general';
}


$followers       = $user->countFollowers();
$total_ref       = $user->CountUserAffiliates();
$context['page_title'] = lang('profile_settings');
$context['page'] = $page;
$context['me'] = $me;
$context['settings'] = $me;

if ($page == 'blocked') {
	$blocked = $user->getBlockedUsers();
	$blocked = (is_array($blocked) == true) ? $blocked : array();
	$context['blocked_users'] = o2array($blocked);
}
if ($page == 'verification') {
	$context['is_verified'] = $user->isVerificationRequested();
}
if ($page == 'manage_sessions') {
	$context['sessions'] = o2array($user->getUserSessions());
	
}
if ($page == 'requests') {
	$context['requests'] =  o2array($user->getUserRequests());
}
if ($page == 'my_affiliates') {
	$context['my_affiliates'] =  o2array($user->getUserAffiliates());
}
if ($page == 'partnership') {
	$context['page_title'] = lang('business_partnership');
	$context['p_requested'] = $user->isPartnershipRequested();
}
if ($page == 'business_account' && $context['me']['business_account'] == 0) {
	header("Location: $site_url/home");
	exit();
}
if ($page == 'business_account') {
	$context['page_title'] = lang('business_account');
}
$context['settings_page'] = $page;
$context['total_ref'] = $total_ref;
$context['followers'] = $followers;
$context['is_following'] = $is_following;
$context['page_link'] = 'settings/'.$page.'/'.$_GET['user'];
$context['app_name'] = 'settings';
$context['rita'] = "$site_url/aj/settings";
$context['content'] = $ui->intel('settings/templates/settings/index');