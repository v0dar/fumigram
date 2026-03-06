<?php 

$context['posts']  = array();
$posts             = new posts();
$posts->comm_limit = $config['comment_limit'];
$hashtags          = HomeHastags();
$feedposts         = $posts->FeedPosts();

if (IS_LOGGED) {
	$boost = $db->where('boosted',1)->orderBy('RAND()')->getOne(T_POSTS);
	$boost_post = array();
	if (!empty($boost)) {
		$posts->setPostId($boost->post_id);
		$boost_post = $posts->postData('');
	}
}

$tiles    = $posts->getTiles();
$follow   = $user->followSuggestions();
$popular  = $user->followPopularUsers();
$random   = $user->followRandomUsers();
$trending = $posts->FeaturedPosts();
$upload = array(
	($config['upload_images'] == 'on'),
	($config['upload_videos'] == 'on'),
	($config['import_videos'] == 'on'),
	($config['upload_tiles'] == 'on'),
	($config['import_images'] == 'on'),
);

if (!empty($feedposts)) {
	$context['posts'] = o2array($feedposts);
}
if (!empty($boost_post) && !empty($context['posts'])) {
	array_unshift($context['posts'],o2array($boost_post));
}
elseif (empty($context['posts']) && !empty($boost_post)) {
	$context['posts'][] = o2array($boost_post);
}
$context['is_boosted'] = false;
$activities = $posts->Activities(0,9);
$activities = o2array($activities);
$tiles = (!empty($tiles)) ? o2array($tiles) : array();
$trending = (!empty($trending)) ? o2array($trending) : array();

// Everybody
$user->limit = 150;
$people = $user->everyBody();
$everyBody  = (!empty($people) && is_array($people)) ? o2array($people) : array();

$user = new users();
$context['exjs'] = true;
$config['footer'] = false;
$context['tiles'] = $tiles;
$context['page_link'] = '';
$context['app_name'] = 'home';
$context['trending'] = $trending;
$context['hashtags'] = $hashtags;
$context['everyBody'] = $everyBody;
$context['activities'] = $activities;
$context['random'] = o2array($random);
$context['posts'] = $context['posts'];
$context['follow'] = o2array($follow);
$context['popular'] = o2array($popular);
$context['premium'] = $user->GetProUsers();
$context['upload'] = (in_array(true, $upload));
$context['sidebar_ad'] = $user->GetRandomAd('sidebar');
$context['page_title'] = $context['lang']['home_page'];
$context['content'] = $ui->intel('home/templates/home/index');
