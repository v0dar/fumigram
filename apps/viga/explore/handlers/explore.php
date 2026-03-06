<?php

$context['posts']  = array();
$posts             = new posts();
$posts->orderBy('post_id','DESC');
$posts->limit      = 60;
$query_posts       = $posts->explorePosts();
$follow            = array();

if (IS_LOGGED) {
	$follow = $user->followSuggestions();
}

$boost_post = null;
if(isset($posts->exploreBoostedPosts()[0])){
	$boost_post = $posts->exploreBoostedPosts()[0];
}
if (!empty($query_posts)) {
	$context['posts'] = o2array($query_posts);
}
if (!empty($boost_post) && !empty($context['posts'])) {
	$boost_post->pid = $boost_post->post_id;
	array_unshift($context['posts'],o2array($boost_post));
}
elseif (empty($context['posts']) && !empty($boost_post)) {
	$context['posts'][] = o2array($boost_post);
}
$context['is_boosted'] = false;
$follow = (!empty($follow) && is_array($follow)) ? o2array($follow) : array();

$context['page_link'] = 'explore';
$context['exjs'] = true;
$context['app_name'] = 'explore';
$context['page_title'] = $context['lang']['explore_posts'];
$context['follow'] = $follow;
$context['content'] = $ui->intel('explore/templates/explore/index');