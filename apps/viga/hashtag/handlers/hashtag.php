<?php
if (empty($_GET['htag'])) {
	header("Location: $site_url");
	exit;
}

$posts  = new posts();
$posts->comm_limit = $config['comment_limit'];

$htag   = aura::secure($_GET['htag']);
$tag_id = $posts->getHtagId($htag);
$tag_id = ((is_numeric($tag_id)) ? $tag_id : 0);

$query    = array();
$hashtg   = HomeHastags();
$navtags  = RandomHastags('latest');
$random   = $user->followRandomUsers();
$upload   = array(($config['upload_images'] == 'on'),($config['upload_videos'] == 'on'),($config['import_videos'] == 'on'),($config['upload_tiles'] == 'on'),($config['import_images'] == 'on'));

if (!empty($tag_id)) {
	$query = $posts->HashtagPosts($tag_id);
}
$tcount = (!empty($query)) ? $posts->countPostTags($tag_id) : 0;
$query  = (!empty($query) && is_array($query) || 2) ? o2array($query) : array();

$user = new users();
$context['exjs'] = true;
$context['tag'] = $htag;
$context['posts'] = $query;
$context['count'] = $tcount;
$context['tag_id'] = $tag_id;
$context['navtags'] = $navtags;
$context['hashtags'] = $hashtg;
$context['app_name'] = 'hashtag';
$context['random'] = o2array($random);
$context['premium'] = $user->GetProUsers();
$context['upload'] = (in_array(true, $upload));
$context['page_link'] = 'hashtag/'.$context['tag'];
$context['sidebar_ad'] = $user->GetRandomAd('sidebar');
$context['page_title'] = '#'.$context['tag']. ' | '.$config['site_title'];
$context['content'] = $ui->intel('hashtag/templates/hashtag/index');
$_SESSION['tag_id'] = $tag_id;
