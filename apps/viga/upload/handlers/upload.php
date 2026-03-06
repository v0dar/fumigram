<?php
$uis = array('tile','video');
if (!empty($_GET['ui']) && in_array($_GET['ui'], $uis) && $_GET['ui'] == 'tile') {
	if ($config['upload_tiles'] == 'off') {
		header("Location: $site_url/home");
		exit();
	}
	$context['app_name'] = 'upload';
	$context['page_link'] = 'upload/tile';
	$context['rita'] = "$site_url/aj/upload";
	$context['page_title'] = $context['lang']['upload_tile']." | ".$config['site_name'];
	$context['content'] = $ui->intel('upload/templates/upload/tile');
} elseif (!empty($_GET['ui']) && in_array($_GET['ui'], $uis) && $_GET['ui'] == 'video') {
	if ($config['upload_videos'] == 'off') {
		header("Location: $site_url/home");
		exit();
	}
	$context['app_name'] = 'upload';
	$context['page_link'] = 'upload/video';
	$context['rita'] = "$site_url/aj/upload";
	$context['page_title'] = $context['lang']['upload_a_vid']." | ".$config['site_name'];
	$context['content'] = $ui->intel('upload/templates/upload/video');
} else {
	$context['page_link'] = 'upload';
	$context['app_name'] = 'upload';
	$context['page_title'] = $context['lang']['upload']." | ".$config['site_title'];
	$context['content'] = $ui->intel('upload/templates/upload/index');
}
