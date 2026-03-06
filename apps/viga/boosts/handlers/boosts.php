<?php
if ($config['pro_system'] == 'off') {
	header("Location: $site_url/home");
	exit;
}
$context['page_link'] = 'boosts';
$context['app_name'] = 'boosts';
$context['page_title'] = $context['me']['username'].' '.$context['lang']['r_boosts'];
$context['content'] = $ui->intel('boosts/templates/boosts/index');
