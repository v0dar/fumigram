<?php 
$user->limit = 120;
$follow = $user->explorePeople();
$users  = (!empty($follow) && is_array($follow)) ? o2array($follow) : array();

$context['exjs'] = true;
$context['users'] = $users;
$context['app_name'] = 'explore';
$context['page'] = 'explore-people';
$context['page_link'] = 'explore/people';
$context['page_title'] = $context['lang']['explore_people'];
$context['content'] = $ui->intel('explore/templates/explore/people');
