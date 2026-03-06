<?php
if (IS_LOGGED !== true || $config['story_system'] == 'off') {
	header("Location: $site_url/home");
	exit;
}

$uis = array('create');
if (!empty($_GET['ui']) && in_array($_GET['ui'], $uis) && $_GET['ui'] == 'create') {
	$context['exjs'] = true;
	$context['app_name'] = 'stories';
	$context['page_link'] = 'stories/create';
	$context['page_title'] = $context['lang']['create_story']." | ".$config['site_name'];
	$context['content'] = $ui->intel('stories/templates/stories/create');
} else {
	$user = new users();
	$context['exjs'] = true;
	$context['app_name'] = 'stories';
	$context['page_link'] = 'stories';
	$context['page_title'] = 'Stories';
	$context['content'] = $ui->intel('stories/templates/stories/index');
}