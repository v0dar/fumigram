<?php
if ($config['point_system'] == 'off') {
	header("Location: $site_url/home");
	exit;
}
$context['page_link'] = 'loose_points';
$context['app_name'] = 'loose_points';
$context['page_title'] = $context['me']['username'].' '.$context['lang']['lost_points'];
$context['content'] = $ui->intel('loose_points/templates/loose_points/index');
