<?php
if ($config['point_system'] == 'off') {
	header("Location: $site_url/home");
	exit;
}
$context['page_link'] = 'points';
$context['app_name'] = 'points';
$context['page_title'] = $context['me']['username'].' '.$context['lang']['c_points'];
$context['content'] = $ui->intel('points/templates/points/index');
