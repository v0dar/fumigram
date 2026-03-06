<?php
if ($config['upload_tiles'] == 'off') {
	header("Location: $site_url");
	exit;
}
$context['posts']  = array();
$posts             = new posts();
$posts->orderBy('post_id','DESC');
$posts->limit      = 100;
$query_posts       = $posts->exploreTiles();
$follow            = array();

if (IS_LOGGED) {
	$follow = $user->followSuggestions();
}

$boost_post = null;
if(isset($posts->exploreBoostedTiles()[0])){
	$boost_post = $posts->exploreBoostedTiles()[0];
}
if (!empty($query_posts)) {
	$context['posts'] = o2array($query_posts);
}
if (!empty($boost_post) && !empty($context['posts'])) {
	array_unshift($context['posts'],o2array($boost_post));
}
elseif (empty($context['posts']) && !empty($boost_post)) {
	$context['posts'][] = o2array($boost_post);
}

$context['is_boosted'] = false;
$follow = (!empty($follow) && is_array($follow)) ? o2array($follow) : array();

$context['page_link'] = 'tiles';
$context['exjs'] = true;
$context['app_name'] = 'tiles';
$context['page_title'] = $context['lang']['explore_tiles'];
$context['follow'] = $follow;
$context['content'] = $ui->intel('tiles/templates/tiles/index');
