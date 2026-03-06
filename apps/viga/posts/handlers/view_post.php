<?php

if (empty($_GET['pid']) || !is_numeric($_GET['pid'])) {
	header("Location: $site_url/404");
	exit;
}

$post_id           = $_GET['pid'];
$posts             = new posts();
$post_data         = null;
$fetched_data      = $posts->setPostId($post_id)->postData();
$is_owner          = false;
$is_following      = false;
$follow   = $user->followRandomUsers();
if (!empty($fetched_data)) {
	$post_data = o2array($fetched_data);
} else{
	header("Location: $site_url/404");
	exit;
}
if (IS_LOGGED && ($me['user_id'] == $post_data['user_id'])) {
	$is_owner = true;
}
if (IS_LOGGED) {
	$is_following = $user->isFollowing($post_data['user_id']);
}

$user_id = $post_data['user_id'];
$posts->setUserById($user_id);
$posts->limit = 6;
$user_posts   = $posts->getMoreFromUserPosts();
$context['posts'] = o2array($user_posts);
if (empty($post_data['description'])){
	$context['page_title'] = lang('fumigram_photo_by') . ' ' . $post_data['username'] . ' • ' . time2str($post_data['time']);
} else {
	$context['page_title'] = $post_data['username'] . ' ' . lang('on_fumigram') . ' • ' . ' ' . $post_data['description'] ;
}

$context['post_data'] = $post_data;
$context['is_owner'] = $is_owner;
$context['follow'] = o2array($follow);
$context['is_following'] = $is_following;
$context['exjs'] = true;
$context['app_name'] = 'posts';
$context['page_link'] = 'post/'.$post_id;
if (isset($_GET['type']) && $_GET['type'] == 'embed') {
	$config['header'] = false;
	$config['footer'] = false;
	$context['app_name'] = 'embed';
	$context['content'] = $ui->intel('posts/templates/posts/embed_post');
} else{
	$context['content'] = $ui->intel('posts/templates/posts/view-post');
}