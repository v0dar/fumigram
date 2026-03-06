<?php 
if (empty($_GET['tag'])) {
	header("Location: $site_url");
	exit;
}
$posts  = new posts();
$tag    = aura::secure($_GET['tag']);
$posts->limit    = 50;
$tag_id = $posts->getHtagId($tag);

$tag_id = ((is_numeric($tag_id)) ? $tag_id : 0);
$qrset  = array();

if (!empty($tag_id)) {
	$qrset = $posts->exploreTags($tag_id);
}

$qrset  = (!empty($qrset) && is_array($qrset) || 2) ? o2array($qrset) : array();
$tcount = (!empty($qrset)) ? $posts->countPostTags($tag_id) : 0;

$context['exjs'] = true;
$context['tag'] = $tag;
$context['page'] = 'tags';
$context['posts'] = $qrset;
$context['app_name'] = 'explore';
$context['total_count'] = $tcount;
$context['content_page'] = 'tposts.html';
$context['page_link'] = 'explore/tags/'.$context['tag'];
$context['page_title'] = '#'.$context['tag']. ' | '.$config['site_title'];
$context['content'] = $ui->intel('explore/templates/explore/tags');
$_SESSION['tag_id'] = $tag_id;