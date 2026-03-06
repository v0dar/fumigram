<?php 
if (IS_LOGGED !== true) {
	header("Location: $site_url/login");
	exit;
}

$context['uis'] = 'startup';
$context['app_name'] = 'startup';
$context['page_title'] = "Get Started";

$uis = 'image';
if ($context['user']['startup_avatar'] == 0) {
	$uis = 'image';
}
elseif ($context['user']['startup_info'] == 0) {
	$uis = 'info';
}
elseif ($context['user']['startup_follow'] == 0) {
	$uis = 'follow';
	$follow   = $user->followNewMembers();
	$ids = array();
	foreach ($follow as $key => $value) {
		$ids[]= $value->user_id;
	}
	$context['follow'] = o2array($follow);
	$context['ids'] = implode(',', $ids);
}
else{
	header("Location: $site_url/login");
	exit;
}
$context['content'] = $ui->intel('startup/templates/startup/'.$uis);