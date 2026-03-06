<?php
if ($config['point_system'] == 'off') {
	header("Location: $site_url/home");
	exit;
}
$context['page_link'] = 'tokens';
$context['app_name'] = 'tokens';
$context['page_title'] = $context['me']['username'].' '.$context['lang']['t_tokens'];
$context['content'] = $ui->intel('tokens/templates/tokens/index');
