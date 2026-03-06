<?php
require_once 'core/vera/cpanel.php';
$cpanel = new cpanel();
require_once 'core/sync/functions/function.php';
if ($config['last_clean_db'] <= (time() - 604800)) {
	$cpanel->updateSettings(array('last_clean_db' => time()));
	$cpanel::$db->where('click', '0', '>')->delete(T_NOTIF);
	$cpanel::$db->where('deleted_fs1', '1');
	$cpanel::$db->where('deleted_fs2', '1')->delete(T_MESSAGES);
}

// Premium
if ($config['pro_system'] == 'on') {
	ExpiredPremium();
}

$storyids = array();
$stories  = $cpanel::$db->where('time', (time() - 86400), '<')->get(T_STORY, null, array('id', 'media_file'));
if (!empty($stories) && is_array($stories)) {
	foreach ($stories as $story_data) {
		$storyids[] = $story_data->id;
		if (file_exists($story_data->media_file)) {
			$del = new Media();
			$del->deleteFromFTPorS3($story_data->media_file);
			@unlink($story_data->media_file);
		}
	}
	if (!empty($storyids) && len($storyids)) {
		$cpanel::$db->where('id', $storyids, 'IN')->delete(T_STORY);
	}
}

// USERNAME TIMER
$expired_usernames = $cpanel::$db->where('update_username', 0)->where('username_timer', strtotime("-" . $config['username_timeout']), '<')->get(T_USERS);
if (!empty($expired_usernames)) {
	foreach ($expired_usernames as $key => $value) {
		$user_data = $cpanel::$db->where('user_id', $value->user_id)->where('update_username', 0)->getOne(T_USERS, array('update_username', 'username_timer', 'username'));
		if ($user_data->username_timer < strtotime("-" . $config['username_timeout'])) {
			$update = $cpanel::$db->where('user_id', $value->user_id)->where('update_username', 0)->update(T_USERS, array('update_username' => 1, 'username_timer' => null));
			$notif   = new notify();
			$re_data = array(
				'notifier_id' => $me['user_id'],
				'recipient_id' => $value->user_id,
				'type' => 'username_timer_up',
				'url'  =>  $config['site_url'] . '/settings/account/' . $user_data->username,
				'time' => time(),
				'ajax_url' => 'load.php?app=settings&apph=settings&user=' . $user_data->username . '&page=account'
			);
			$notif->notify($re_data);
		}
	}
}

// BOOSTED POST TIMER
$expired_boost = $cpanel::$db->where('boosted', 1)->where('boost_time', strtotime("-" . $config['boost_timer']. ' '.'days'), '<')->get(T_POSTS);
if (!empty($expired_boost)) {
	foreach ($expired_boost as $key => $value) {
		$posts   = new posts();
		$posts->setPostId($value->post_id);
		$post_data = o2array($posts->postData());
		foreach ($post_data['media_set'] as $id => $file) {
			$thumb = '';
			$type = $post_data['type'];
			$extra = media($file['extra']);
			$fullpath = media($file['file']);
			if (in_array($type, array('youtube', 'gif', 'video', 'tile', 'vimeo', 'dailymotion', 'playtube', 'mp4', 'fetched'))) {
				if (!empty($extra)) {
					$thumb = $extra;
				} else {
					if ($type == 'youtube') {
						$thumb = 'https://i3.ytimg.com/vi/' . $file['youtube'] . '/maxresdefault.jpg';
					}
				}
			} else {
				$thumb = media($file['file']);
			}
		}
		$timer      = $cpanel::$db->where('user_id', $value->user_id)->getValue(T_USERS, 'boost_timer');
		$is_boosted = $cpanel::$db->where('user_id', $value->user_id)->where('post_id', $value->post_id)->where('boosted', 1)->getOne(T_POSTS);
		if ($is_boosted->boost_time < strtotime("-" . $timer. ' '.'days')) {
			$update = $cpanel::$db->where('user_id', $value->user_id)->where('post_id', $value->post_id)->update(T_POSTS, array('boosted' => 0, 'boost_time' => null));
			if ($update) {
				$notif = new notify();
				$re_data = array(
					'notifier_id' => $me['user_id'],
					'recipient_id' => $value->user_id,
					'post_id' => $value->post_id,
					'type' => 'boosted_post_time_up',
					'url' => pid2url($value->post_id),
					'time' => time(),
					'ftype' => $type,
					'thumb' => $thumb,
					'file' => $fullpath
				); $notif->notify($re_data);
			}
		}
	}
}
