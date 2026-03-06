<?php 
if (IS_LOGGED !== true) {
	header("Location: $site_url/home");
	exit;
}

$context['posts']  = array();
$posts             = new posts();
$posts->comm_limit = $config['comment_limit'];
$hashtags          = TimelineHastags();
$tlposts           = $posts->setUserById($me['user_id'])->TimelinePosts();
$boost             = $db->where('boosted',1)->orderBy('RAND()')->getOne(T_POSTS);
$boost_post = array();
if (!empty($boost)) {
	$posts->setPostId($boost->post_id);
	$boost_post = $posts->postData('');
}

$posts->setUserById($me['user_id']);
$total      = $posts->countPosts();
$tiles      = $posts->getTiles();
$followers  = $user->countFollowers();
$following  = $user->countFollowing();
$follow     = $user->followSuggestions();
$popular    = $user->followPopularUsers();
$random     = $user->followRandomUsers();
$trending = $posts->FeaturedTimelinePosts();
$upload = array(
	($config['upload_images'] == 'on'),
	($config['upload_videos'] == 'on'),
	($config['upload_tiles'] == 'on'),
	($config['import_videos'] == 'on'),
	($config['import_images'] == 'on'),
);

if (!empty($tlposts)) {
	$context['posts'] = o2array($tlposts);
}
if (!empty($boost_post) && !empty($context['posts'])) {
	array_unshift($context['posts'],o2array($boost_post));
}
elseif (empty($context['posts']) && !empty($boost_post)) {
	$context['posts'][] = o2array($boost_post);
}

$activities = $posts->Activities(0,11);
$activities = o2array($activities);
$tiles = (!empty($tiles)) ? o2array($tiles) : array();
$trending = (!empty($trending)) ? o2array($trending) : array();

$user = new users();
$context['exjs'] = true;
$config['footer'] = false;
$context['total'] = $total;
$context['tiles'] = $tiles;
$context['is_boosted'] = false;
// $context['stories'] = $stories;
$context['trending'] = $trending;
$context['hashtags'] = $hashtags;
$context['app_name'] = 'timeline';
$context['page_link'] = 'timeline';
$context['followers'] = $followers;
$context['following'] = $following;
$context['activities'] = $activities;
$context['random'] = o2array($random);
$context['posts'] = $context['posts'];
$context['follow'] = o2array($follow);
$context['popular'] = o2array($popular);
$context['upload'] = (in_array(true, $upload));
$context['page_title'] = $context['lang']['timeline'];
$context['sidebar_ad'] = $user->GetRandomAd('sidebar');
$context['content'] = $ui->intel('timeline/templates/timeline/index');
