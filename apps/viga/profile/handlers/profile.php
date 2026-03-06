<?php 
if (empty($_GET['uname'])) {
	header("Location: $site_url");
	exit;
}

$page  = (!empty($_GET['page'])) ? $_GET['page'] : 'posts';
try {
	$user->setUserByName($_GET['uname']);
	$user_data = $user->userData($user->getUser());
	$user_data = o2array($user_data);
} catch (Exception $e) {
	header("Location: $site_url");
	exit;
}

$context['posts']  = array();
$posts             = new posts();
$is_owner          = false;
$is_following      = false;
$is_reported       = false;
$is_blocked        = false;
$visits            = $user_data['visits'];
$user_id           = $user_data['user_id'];

$posts->setUserById($user_id);
$total_posts       = $posts->countPosts();
$user_tiles        = $posts->countTiles();
$user_followers    = $user->countFollowers();
$user_following    = $user->countFollowing();
$profile_privacy   = $user->profilePrivacy($user_id);
$chat_privacy      = $user->chatPrivacy($user_id);


if (IS_LOGGED && ($me['user_id'] == $user_id)) {
	$is_owner = true;
}

if (IS_LOGGED) {
	$is_following = $user->isFollowing($user_id);
	$is_reported  = $user->isUserRepoted($user_id);
	$is_blocked   = $user->isBlocked($user_id);
	$ami_blocked  = $user->isBlocked($user_id,true);
	if ($ami_blocked) {
		header("Location: $site_url");
		exit;
	}
}

$navbar = ($profile_privacy && empty($is_blocked));

$context['exjs'] = true;
$context['page'] = $page;
$context['navbar'] = $navbar;
$context['app_name'] = 'profile';
$context['is_owner'] = $is_owner;
$context['user_visits'] = $visits;
$context['user_data'] = $user_data;
$context['is_blocked'] = $is_blocked;
$context['user_tiles'] = $user_tiles;
$context['posts'] = $context['posts'];
$context['page_link'] = $_GET['uname'];
$context['is_reported'] = $is_reported;
$context['total_posts'] = $total_posts;
$context['rita'] = "$site_url/aj/posts";
$context['content_page'] = 'posts.html';
$context['is_following'] = $is_following;
$context['p_privacy'] = $profile_privacy;
$context['chat_privacy'] = $chat_privacy;
$context['page_title'] = $user_data['name'];
$context['user_followers'] = $user_followers;
$context['user_following'] = $user_following;
$context['boosted_count'] = $posts->countBoostedPosts();

$context['subscriptions_count'] = 0;
if ($config['private_photos'] == 'on' || $config['private_videos'] == 'on') {
	$month = 60 * 60 * 24 * 30;
	$context['subscriptions_count'] = $db->where('subscriber_id',$user_id)->where('time',(time() - $month),'>=')->getValue(T_SUBSCRIBERS,'COUNT(*)');
}

if ($page == 'following' && $navbar) {
	$user->setUserById($user_id);
	$following_ls = $user->getFollowing(false,50);
	$context['content_page'] = "following.html";
	$context['following_ls'] =	o2array($following_ls);
	$context['page_link'] = $_GET['uname'].'/following';
}

elseif ($page == 'followers' && $navbar) {
	$user->setUserById($user_id);
	$followers_ls = $user->getFollowers(false,50);
	$context['content_page'] = "followers.html";	
	$context['followers_ls'] =	o2array($followers_ls);
	$context['page_link'] = $_GET['uname'].'/followers';
}

elseif ($page == 'favourites' && ($context['user_data']['show_favorites'] == 1 || ($context['user_data']['show_favorites'] != 1 && $is_owner))) {
	$user->setUserById($me['user_id']);
	$context['content_page']   = "favourites.html";
	$context['favorite_posts'] = array();
	$favorite_posts = $posts->getSavedPosts();

	if (!empty($favorite_posts)) {
		$context['favorite_posts'] = o2array($favorite_posts);
	}
	$context['page_link'] = $_GET['uname'].'/favourites';
}

elseif ($page == 'boosted_posts' && $is_owner) {
	$user->setUserById($me['user_id']);
	$context['content_page']   = "boosted_posts.html";
	$context['boosted_posts'] = array();
	$boosted_posts = $posts->getBoostedPosts();

	if (!empty($boosted_posts)) {
		$context['boosted_posts'] = o2array($boosted_posts);
	}
	$context['page_link'] = $_GET['uname'].'/boosted_posts';
}

elseif ($page == 'tiles' && $navbar ) {
	$posts->setUserById($user_id);
	$posts->limit = 6;
	$user_posts   = $posts->getUserTiles();
	$context['posts'] = o2array($user_posts);
	$context['page_link'] = $_GET['uname'].'/tiles';

}

elseif ($page == 'subscriptions' && ($config['private_photos'] == 'on' || $config['private_videos'] == 'on') && ($context['user_data']['show_subscribers'] == 1 || ($context['user_data']['show_subscribers'] != 1 && $is_owner))) {
	$month = 60 * 60 * 24 * 30;
	$subscriptions = $db->where('subscriber_id',$user_id)->where('time',(time() - $month),'>=')->orderBy("id","DESC")->get(T_SUBSCRIBERS,50);
	$context['subscriptions'] = array();
	if (!empty($subscriptions)) {
		foreach ($subscriptions as $key => $value) {
			$user_info = $user->setUserById($value->user_id)->getUser();
			$user_info->sub_id = $value->id;
			$context['subscriptions'][] = $user_info;
		}
	}
	$context['content_page'] = "subscriptions.html";	
	$context['subscriptions'] =	o2array($context['subscriptions']);
	$context['page_link'] = $_GET['uname'].'/subscriptions';
} else{
	if ($navbar || empty(IS_LOGGED)) {
		$posts->setUserById($user_id);
		$posts->limit = 12;
		$user_posts   = $posts->getUserPosts();
		$context['posts'] = o2array($user_posts);
	}
	$context['page'] = 'posts';
}

if ($context['user_data']['show_favorites'] == 1 || ($context['user_data']['show_favorites'] != 1 && $is_owner === true)){
	$favourites = $posts->countSavedPosts();
	$context['favourites'] = $favourites;
}

$profile = 'index'; //Linear Profile
if ($user_data['profile'] == 2 && $user_data['is_pro']) { //Nove Profile
	$profile = 'index_2';
}
if ($user_data['profile'] == 3 && $user_data['is_pro']) { //Blue Tech Profile
	$profile = 'index_3';
}
if ($user_data['profile'] == 4 && $user_data['is_pro']) { //Pathfinder Profile
	$profile = 'index_4';
}
if ($user_data['profile'] == 5 && $user_data['is_pro']) { //Oceana Profile
	$profile = 'index_5';
}
if ($user_data['profile'] == 6 && $user_data['is_pro']) { //Hexagon Profile
	$profile = 'index_6';
}

$context['content'] = $ui->intel('profile/templates/profile/'.$profile);
