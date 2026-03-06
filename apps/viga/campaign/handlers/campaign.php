<?php
if (IS_LOGGED !== true || $config['campaign'] == 'off' || $context['me']['allow_ads'] == 0) {
	header("Location: $site_url/home");
	exit;
}

$uis = array('create','wallet');
if (!empty($_GET['ui']) && in_array($_GET['ui'], $uis) && $_GET['ui'] == 'create') {
	$context['exjs'] = true;
	$context['app_name'] = 'campaign';
	$context['page_link'] = 'campaign/create';
	$context['page_title'] = $context['lang']['start_campaign']." | ".$config['site_name'];
	$context['content'] = $ui->intel('campaign/templates/campaign/create');
} elseif (!empty($_GET['ui']) && in_array($_GET['ui'], $uis) && $_GET['ui'] == 'wallet') {
	$context['exjs'] = true;
	$context['app_name'] = 'campaign';
	$context['page_link'] = 'campaign/wallet';
	$context['page_title'] = $context['me']['username']. "'s ".$context['lang']['wallet']." | ".$config['site_title'];
	$context['content'] = $ui->intel('campaign/templates/campaign/wallet');
} else {
	$user = new users();
	$context['exjs'] = true;
	$context['app_name'] = 'campaign';
	$context['page_link'] = 'campaign';
	$context['campaigns'] = $user->GetUserAds();
	$context['page_title'] = $context['lang']['campaigns']." | ".$config['site_title'];
	$context['content'] = $ui->intel('campaign/templates/campaign/index');
}