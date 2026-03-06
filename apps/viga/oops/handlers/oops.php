<?php
if (IS_LOGGED !== true) {
	header("Location: $site_url/home");
	exit;
}
$context['exjs'] = true;
$context['app_name'] = 'oops';
$context['page_link'] = 'oops';
$context['page_title'] = $context['lang']['oops_server_error'];
$context['content']    = $ui->intel('oops/templates/oops/index');
