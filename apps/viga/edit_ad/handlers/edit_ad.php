<?php
if (IS_LOGGED !== true || empty($_GET['id']) || $config['campaign'] != 'on') {
	header("Location: $site_url/home");
	exit;
}

$user = new users();
$context['campaign'] = $user->GetAdByID($_GET['id']);
$context['page_link'] = 'edit_ad/'.$_GET['id'];
$context['exjs'] = true;
$context['app_name'] = 'edit_ad';
$context['page_title'] =  $context['lang']['edit_campaign'];
$context['content'] = $ui->intel('edit_ad/templates/edit_ad/index');