<?php

if ($action == 'follow' && IS_LOGGED) {
	if (!empty($_GET['user_id']) && is_numeric($_GET['user_id'])) {
		$follower_id  = $me['user_id'];
		$following_id = aura::secure($_GET['user_id']);

		$db->where('following_id', $follower_id);
		$db->where('follower_id', $following_id);
		$request = $db->getOne(T_CONNECTIV);
		$udata = new users();
		$follower = $udata->getUserDataById($request->follower_id);

		$notif        = new notify();
		$user->setUserById($follower_id);
		$status       = $user->follow($following_id);
		$data['status'] = 400;
		if ($status === 1) {
			$data['status'] = 200;
			$data['code'] = 1;
			$data['message']   = lang('started_following');
			$data['message2']  = lang('started_following') . ' ' . $follower->name;

			#Notify the user
			$notif_conf = $notif->notifSettings($following_id, 'on_follow');
			if ($notif_conf) {
				$re_data = array(
					'notifier_id' => $me['user_id'],
					'recipient_id' => $following_id,
					'type' => 'followed_u',
					'url' => un2url($me['username']),
					'time' => time(),
				);
				$notif->notify($re_data);
			}
			#Reward points to the user
			RecordAction('follow_users', array('user_id' => $me['user_id']));
		} else if ($status === -1) {
			$data['status'] = 200;
			$data['code'] = 0;
			$data['message']   =  lang('you_unfollow');
			$data['message2']  =  lang('you_unfollow') . ' ' . $follower->name;

			#Remove points from the user
			RecordAction('unfollow_users', array('user_id' => $me['user_id']));
		} else if ($status === 2) {
			$data['status'] = 200;
			$data['code'] = 2;
			$data['message']   =  lang('u_sent_follow_request');
			$data['message2']  =  lang('u_sent_follow_request') . ' ' . $follower->name;
		}
		exit();
	}
}

elseif ($action == 'notify_follow' && IS_LOGGED && !empty($_POST['notifier_id'])) {
	$follower_id  = $me['user_id'];
	$following_id = aura::secure($_POST['notifier_id']);

	$db->where('following_id', $follower_id);
	$db->where('follower_id', $following_id);
	$request = $db->getOne(T_CONNECTIV);
	$user = new users();
	$follower = $user->getUserDataById($request->follower_id);

	$notif  = new notify();
	$user->setUserById($follower_id);
	$status = $user->follow($following_id);
	$db->where('recipient_id', $me['user_id'])->where('follow', 0)->update(T_NOTIF, array('follow' => 1));

	$data['status'] = 400;
	$data['message'] = lang('system_error_failed');
	if ($status === 1) {
		$data['status'] = 200;
		$data['message'] =  lang('started_following') . ' ' . $follower->name;
		$data['code'] = 1;

		#Notify the user
		$notif_conf = $notif->notifSettings($following_id, 'on_follow');
		if ($notif_conf) {
			$re_data = array(
				'notifier_id' => $me['user_id'],
				'recipient_id' => $following_id,
				'type' => 'followed_u',
				'url' => un2url($me['username']),
				'time' => time(),
			);
			$notif->notify($re_data);
		}
		#Reward points to the user
		RecordAction('follow_users', array('user_id' => $me['user_id']));
	} else if ($status === -1) {
		$data['status'] = 200;
		$data['code'] = 0;
		#Remove points from the user
		RecordAction('unfollow_users', array('user_id' => $me['user_id']));
	}
	exit();
} 

elseif ($action == 'get_notif' && IS_LOGGED) {
	$notif = new notify();
	$data  = array();

	$notif->setUserById($me['user_id']);
	$notif->type    = 'all';
	$notif->limit   = 1000;
	$queryset       = $notif->getNotifications();

	if (!empty($queryset) && is_array($queryset)) {
		$new_notif      = o2array($queryset);
		$context['notifications'] = $new_notif;
		$data['html']    = $ui->intel('main/templates/header/notifications');
		$data['status'] = 200;
	} else {
		$data['status']  = 304;
		$data['message'] = lang('u_dont_have_notif');
	}
} 

elseif ($action == 'click_notif' && IS_LOGGED && !empty($_POST['id'])) {
	$notif = new notify();
	$data  = array();

	$notif->notify_id = $_POST['id'];
	$notif->setUserById($me['user_id']);
	$queryset  = $notif->ReadNotifications();

	if (!empty($queryset)) {
		$data['status'] = 200;
	} else {
		$data['status']  = 304;
	}
} 

elseif ($action == 'mark_read' && IS_LOGGED) {
	$notif = new notify();
	$data  = array();

	$notif->setUserById($me['user_id']);
	$queryset  = $notif->MarkReadNotifications();

	if (!empty($queryset)) {
		$data['status'] = 200;
		$data['message'] = lang('notify_marked_as_read');
	} else {
		$data['status']  = 304;
		$data['message'] = lang('failed_to_mark_notify');
	}
} 

elseif ($action == 'accept_requests' && IS_LOGGED) {
	$data['status'] = 400;
	if (!empty($_POST['user_id']) && is_numeric($_POST['user_id']) && $_POST['user_id'] > 0) {
		$db->where('following_id', $me['user_id']);
		$db->where('follower_id', aura::secure($_POST['user_id']));
		$db->where('type', 2);
		$request = $db->getOne(T_CONNECTIV);
		$user = new users();
		$follower = $user->getUserDataById($request->follower_id);
		if (!empty($request) && !empty($follower)) {
			$db->where('id', $request->id)->update(T_CONNECTIV, array('type' => 1, 'active' => 1));
			$db->where('recipient_id', $me['user_id'])->where('follow', 0)->update(T_NOTIF, array('follow' => 1));
			$notif        = new notify();
			$notif_conf = $notif->notifSettings($follower->user_id, 'on_accept_request');
			if ($notif_conf) {
				$re_data = array(
					'notifier_id' => $me['user_id'],
					'recipient_id' => $follower->user_id,
					'type' => 'accept_request',
					'url' => un2url($me['username']),
					'time' => time()
				);
				$notif->notify($re_data);
			}
			$data['status'] = 200;
			$data['message'] = $follower->name . ' ' . lang('is_following_you');
		} else {
			$data['message'] = lang('please_check_details');
		}
	} else {
		$data['message'] = lang('please_check_details');
	}
} 

elseif ($action == 'delete_requests' && IS_LOGGED) {
	$data['status'] = 400;
	if (!empty($_POST['user_id']) && is_numeric($_POST['user_id']) && $_POST['user_id'] > 0) {
		$db->where('following_id', $me['user_id']);
		$db->where('follower_id', aura::secure($_POST['user_id']));
		$db->where('type', 2);
		$request = $db->getOne(T_CONNECTIV);
		$user = new users();
		$follower = $user->getUserDataById($request->follower_id);

		$notif        = new notify();
		$notif_conf = $notif->notifSettings($follower->user_id, 'on_rejected_request');
		if ($notif_conf) {
			$re_data = array(
				'notifier_id' => $me['user_id'],
				'recipient_id' => $follower->user_id,
				'type' => 'rejected_request',
				'url' => un2url($me['username']),
				'time' => time()
			);
			$notif->notify($re_data);
		}
		$db->where('following_id', $me['user_id']);
		$db->where('follower_id', aura::secure($_POST['user_id']));
		$db->where('type', 2);
		$request = $db->delete(T_CONNECTIV);
		$db->where('recipient_id', $me['user_id'])->where('follow', 0)->update(T_NOTIF, array('follow' => 1));

		$data['status'] = 200;
		$data['message'] = lang('following_request_rejected');
	} else {
		$data['message'] = lang('please_check_details');
	}
} 

elseif ($action == 'update-data' && IS_LOGGED) {
	if ($config['private_videos'] == 'on' || $config['private_photos'] == 'on') {
		$month = 60 * 60 * 24 * 30;
		$expired_subscribed = $db->where('time', (time() - $month), '<')->get(T_SUBSCRIBERS);
		if (!empty($expired_subscribed)) {
			foreach ($expired_subscribed as $key => $value) {
				$user_data = $db->where('user_id', $value->user_id)->getOne(T_USERS, array('subscribe_price', 'username'));
				if (!empty($user_data) && $user_data->subscribe_price > 0) {
					$subscribe_data = $db->where('user_id', $value->subscriber_id)->getOne(T_USERS, array('credits', 'username', 'auto_renew'));
					if (!empty($subscribe_data) && $subscribe_data->credits > 0 && $subscribe_data->credits >= $user_data->subscribe_price) {
						$amount = $user_data->subscribe_price;
						$admin_com = 0;
						if ($config['monthly_subscribers_commission'] > 0) {
							$admin_com = ($config['monthly_subscribers_commission'] * $amount) / 100;
							$amount = $amount - $admin_com;
						}
						if ($subscribe_data->auto_renew > 0) {
							$credits = $subscribe_data->credits - $user_data->subscribe_price;
							$db->insert(T_SUBSCRIBERS, array('user_id' => $value->user_id, 'subscriber_id' => $value->subscriber_id, 'time' => time()));
							$user = new users();
							$user->updateStatic($value->subscriber_id, array('credits' => $credits));
							$db->where('user_id', $value->user_id)->update(T_USERS, array('credits' => $db->inc($amount)));
							$notif   = new notify();

							$re_data = array(
								'notifier_id' => $value->subscriber_id,
								'recipient_id' => $value->user_id,
								'type' => 'renewed_his_subscription',
								'url' => $config['site_url'] . "/" . $subscribe_data->username,
								'time' => time(),
								'credits' => $amount,
								'ajax_url' => 'load.php?app=profile&apph=profile&uname=' . $subscribe_data->username,
							);
							$notif->notify($re_data);

							$re_data = array(
								'notifier_id' => $value->user_id,
								'recipient_id' => $value->subscriber_id,
								'type' => 'subscription_has_been_renewed',
								'url'  =>  $config['site_url'] . '/settings/credits/' . $user_data->username,
								'time' => time(),
								'credits' => $amount,
								'ajax_url' => 'load.php?app=settings&apph=settings&user=' . $user_data->username . '&page=credits'
							);
							$notif->notify($re_data);
						}
					} else {
						$notif   = new notify();
						$re_data = array(
							'notifier_id' => $value->user_id,
							'recipient_id' => $value->subscriber_id,
							'type' => 'your_subscription_has_been_expired',
							'url' => $config['site_url'] . "/" . $user_data->username,
							'time' => time(),
							'ajax_url' => 'load.php?app=profile&apph=profile&uname=' . $user_data->username,
						);
						$notif->notify($re_data);
					}
					$db->where('id', $value->id)->delete(T_SUBSCRIBERS);
				}
			}
		}
	}

	$data  = array();
	$notif = new notify();
	$notif->setUserById($me['user_id']);
	$notif->type    = 'new';
	$new_notif      = $notif->getNotifications();
	$data['notif']  = (is_numeric($new_notif)) ? $new_notif : 0;

	if (!empty($_GET['new_messages'])) {
		$messages     = new messages();
		$messages->setUserById($me['user_id']);
		$new_messages = $messages->countNewMessages();
		$data['new_messages'] = $new_messages;
	}
	if (!empty($_GET['new_points'])) {
		$new = $db->where('user_id', $me['user_id'])->getValue(T_USERS, "points");
		$data['points'] = $new;
	}
	if (!empty($_GET['new_credits'])) {
		$new = $db->where('user_id', $me['user_id'])->getValue(T_USERS, "credits");
		$data['credits'] = $new;
	}
} 

elseif ($action == 'explore-people' && IS_LOGGED) {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		$user->limit = 100;
		$offset      = $_GET['offset'];
		$users       = $user->explorePeople($offset);
		$data        = array('status' => 404);
		if (!empty($users)) {
			$users = o2array($users);
			$html  = "";
			foreach ($users as $udata) {
				$html    .= $ui->intel('explore/templates/explore/includes/row');
			}
			$data = array('status' => 200,'html' => $html);
		}
	}
} 

elseif ($action == 'pro-people' && IS_LOGGED) {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		// $user->limit = 100;
		// $offset      = $_GET['offset'];
		// $users       = $user->exploreProUsers($offset);
		// $data        = array('status' => 404);

		// if (!empty($users)) {
		// 	$users = o2array($users);
		// 	$html  = "";

		// 	foreach ($users as $udata) {
		// 		$html    .= $ui->intel('pro_members/templates/home/includes/list');
		// 	}
		// 	$data = array(
		// 		'status' => 200,
		// 		'html' => $html
		// 	);
		// }
	}
} 

elseif ($action == 'report-profile' && IS_LOGGED && !empty($_POST['id'])) {
	if (is_numeric($_POST['id']) && !empty($_POST['t'])) {
		$user_id = $_POST['id'];
		$type    = $_POST['t'];
		$report  = $_POST['m'];
		$data    = array('status' => 304);
		$code = $user->reportUser($user_id, $type, $report);
		$code = ($code == -1) ? 0 : 1;
		$data = array('status' => 200, 'code' => $code,);
		if ($code == 1) {
			$data['message'] = lang('report_sent');
		}
	}
} 

elseif ($action == 'block-user' && IS_LOGGED && !empty($_POST['id'])) {
	if (is_numeric($_POST['id'])) {
		$user_id = $_POST['id'];
		$data    = array('status' => 304);
		$notif   = new notify();
		$code    = $user->blockUser($user_id);
		$code    = ($code == -1) ? 0 : 1;

		if (in_array($code, array(0, 1))) {
			$data    = array('status' => 200, 'code' => $code);

			if ($code == 0) {
				$data['message'] = lang('user_unblocked');
			} else if ($code == 1) {
				$data['message']    = lang('user_blocked');
				$notif->notifier_id = $user_id;
				$notif->setUserById($me['user_id'])->clearNotifications();
			}
		}
	}
} 

elseif ($action == 'share-post') {
	$post_id = 0;
	$data['status'] = 400;
	if ((!empty($_GET['post_id']) && is_numeric($_GET['post_id']))) {
		$post_id = aura::secure($_GET['post_id']);
	}
	$posts   = new posts();
	$posts->setPostId($post_id);
	$post_data = $posts->postData();
	if (!empty($post_data)) {
		$posts->setPostId($post_id);
		$post_data    = o2array($post_data);
		$caption = '';
		if (!empty($_GET['caption'])) {
			$caption = aura::secure($_GET['caption']);
		} else {
			$caption = $post_data['description'];
		}
		$user_id = $post_data['user_id'];
		if ($user_id <> $me['user_id']) {
			$re_data = array(
				'user_id' 		=> $me['user_id'],
				'description' 	=> $caption,
				'link'			=> $post_data['link'],
				'youtube'		=> $post_data['youtube'],
				'vimeo'			=> $post_data['vimeo'],
				'dailymotion'	=> $post_data['dailymotion'],
				'mp4'			=> $post_data['mp4'],
				'type'			=> $post_data['type'],
				'registered'	=> sprintf('%s/%s', date('Y'), date('n')),
				'time' 			=> time()
			);
			$pid = $db->insert(T_POSTS, $re_data);
			if (is_numeric($pid) && $pid > 0) {
				foreach ($post_data['media_set'] as $id => $file) {
					$thumb = '';
					$type = $post_data['type'];
					$extra = media($file['extra']);
					$fullpath = media($file['file']);
					if (in_array($type, array('youtube','gif','video','tile','vimeo','dailymotion','mp4','fetched'))) {
						if(!empty($extra)){
							$thumb = $extra;
						}else{
							if($type == 'youtube'){
								$thumb = 'https://i3.ytimg.com/vi/'.$file['youtube'].'/maxresdefault.jpg';
							}
						}
					} else {
						$thumb = media($file['file']);
					}
				}
				$db->insert(T_MEDIA, array('user_id' => $me['user_id'],'post_id' => $pid,'file' => $fullpath,'extra' => $thumb));
				$notif   = new notify();
				$notif_conf = $notif->notifSettings($user_id,'on_post_shared');
				if ($notif_conf) {
					$re_notify = array(
						'notifier_id' => $me['user_id'],
						'recipient_id' => $user_id,
						'type' => 'shared_your_post',
						'url' => $config['site_url'] . "/post/" . $pid,
						'time' => time(),
						'ftype' => $type,
						'thumb' => $extra,
						'post_id' => $pid,
						'file' => $fullpath
					); $notif->notify($re_notify);
				}
				$db->where('post_id', $post_id)->update(T_POSTS, array('shares' => $post_data['shares'] + 1));
				$db->insert(T_ACTIVITIES, array('user_id' => $me['user_id'], 'post_id' => $pid, 'type' => 'share_post', 'time' => time()));
				#Reward points to the user
		        RecordAction('share_post', array('user_id' => $user_id));
				$data['status']  = 200;
				$data['message'] = lang('post_shared_success');
			}
		} else {
			$data['status'] = 400;
			$data['message'] = lang('cant_share_own');
		}
	}
} 



elseif ($action == 'share-modal') {
	$postid = 0;
	if ((!empty($_GET['post_id']) && is_numeric($_GET['post_id']))) {
		$post_id = aura::secure($_GET['post_id']);
	}
	$posts   = new posts();
	$posts->setPostId($post_id);
	$post_data = $posts->postData();

	$data['status'] = 400;
	$data['html']   = "";

	if (!empty($post_data)) {
		$posts->setPostId($post_id);
		$post_data      = o2array($post_data);

		$description  = $posts->likifyMentions($post_data['description']);
		$description  = $posts->tagifyHTags($post_data['description']);
		$description  = $posts->linkifyHTags($post_data['description']);
		$description  = $posts->obsceneWords($post_data['description']);
		$description  = htmlspecialchars_decode($post_data['description']);

		$context['post_data']     = $post_data;
		$context['s_user']        = $post_data['username'];
		$context['t_title']       = strip_tags($description);
		$context['t_thumbnail']   =  $post_data['avatar'];
		$context['t_url']         = urlencode($config['site_url'] . '/post/' . $post_data['post_id']);
		$context['t_url_original'] = 'post/' . $post_data['post_id'];
		$data['html']    = $ui->intel('main/templates/includes/share-post');
		$data['status'] = 200;
	}
} 

elseif ($action == 'search-users' && !empty($_POST['kw'])) {
	if (len($_POST['kw']) >= 0) {
		$kword    = $_POST['kw'];
		$data     = array('status' => 304);
		$user     = new users();
		$query    = $user->seachUsers($kword);
		$html     = "";
		if (substr($kword, 0, 1) == '#') {
			if (len($kword) >= 0) {
				$kword    = $_POST['kw'];
				$data     = array('status' => 304);
				$posts    = new posts();
				$query    = $posts->SearchTags($kword);
				$html     = "";
				if (!empty($query)) {
					$query = o2array($query);
					foreach ($query as $htag) {
						$htag['url']     = sprintf('%s/explore/tags/%s', $site_url, $htag['tag']);
						$context['htag'] = $htag;
						$html            .= $ui->intel('main/templates/header/search-posts');
					}
					$data['status'] = 200;
				    $data['html'] = $html;
				}
			}
		} else {
			if (!empty($query)) {
				$query = o2array($query);
				foreach ($query as $udata) {
					$html .= $ui->intel('main/templates/header/search-users');
				}
				$data['status'] = 200;
				$data['html'] = $html;
			}
		}
	}
} 

elseif ($action == 'search-posts' && !empty($_POST['kw'])) {
	if (len($_POST['kw']) >= 0) {
		$posts    = new posts();
		$kword    = $_POST['kw'];
		$data     = array('status' => 304);
		$query    = $posts->SearchTags($kword);
		$html     = "";
		if (!empty($query)) {
			$query = o2array($query);
			foreach ($query as $htag) {
				$htag['url']     = sprintf('%s/explore/tags/%s', $site_url, $htag['tag']);
				$context['htag'] = $htag;
				$html            .= $ui->intel('main/templates/header/search-posts');
			}
			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
} 

elseif ($action == 'contact_us') {
	$data['status'] = 400;
	if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['message'])) {
		$data['message'] = lang('please_check_details');
	} else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$data['message'] = lang('email_invalid_characters');
	} else {
		$first_name        = aura::secure($_POST['first_name']);
		$last_name         = aura::secure($_POST['last_name']);
		$email             = aura::secure($_POST['email']);
		$message           = aura::secure($_POST['message']);
		$name              = $first_name . ' ' . $last_name;
		$message_text = "<p><strong>Name</strong> : {$name}</p>
						 <br>
						 <p><strong>Email</strong> : {$email}</p>
						 <br>
						 <p><strong>Message</strong> : {$message}</p>
						 ";

		$send_email_data = array(
			'from_email' => $email,
			'from_name' => $name,
			'reply-to' => $email,
			'to_email' => $config['site_email'],
			'to_name' => $user->user_data->name,
			'subject' => 'A New Message!',
			'charSet' => 'UTF-8',
			'message_body' => $message_text,
			'is_html' => true
		);
		$send_message = aura::sendMail($send_email_data);
		if ($send_message) {
			$data['status'] = 200;
			$data['message'] = lang('email_sent');
		} else {
			$data['message'] = lang('unknown_error');
		}
	}
} 

elseif ($action == 'change-mode') {
	if ($_COOKIE['mode'] == 'day') {
		setcookie("mode", 'night', time() + (10 * 365 * 24 * 60 * 60), "/");
		$data = array( 'status' => 200, 'type' => 'night', 'link' => $config['site_url'] . '/apps/' . $config['theme'] . '/main/static/css/styles.master.night.css');
		$update = $user->updateStatic($me['user_id'], array('mode' => 'night'));
	} else {
		setcookie("mode", 'day', time() + (10 * 365 * 24 * 60 * 60), "/");
		$data = array('status' => 200,'type' => 'day');
		$update = $user->updateStatic($me['user_id'], array('mode' => 'day'));
	}
} 

elseif ($action == 'activities') {
	$data = array('status' => 400);
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$html = '';
		$posts  = new posts();
		$offset = aura::secure($_POST['id']);
		$activities = $posts->Activities($offset, 10);
		$activities = o2array($activities);
		if (!empty($activities)) {
			foreach ($activities as $key => $value) {
				$context['activity'] = $value;
				$html    .= $ui->intel('home/templates/home/includes/activity');
			}
			$data = array('status' => 200,'html'   => $html);
		} else {
			$data['status'] = 300;
			$data['text'] = lang('no_more_activities');
		}
	}
} 

elseif ($action == 'session-status') {
	if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
		$db->where('session_id', $_SESSION['user_id'])->update(T_SESSIONS, array('time' => time()));
	} else if (!empty($_COOKIE['user_id']) && !empty($_COOKIE['user_id'])) {
		$db->where('session_id', $_COOKIE['user_id'])->update(T_SESSIONS, array('time' => time()));
	}
	$data = array('status' => 200);
} 

elseif ($action == 'payment-methods') {
	$pay_type = array('wallet');
	if (!empty($_POST['type']) && in_array($_POST['type'], $pay_type)) {
		$context['pay_type'] = $_POST['type'];
	}
	$html    = $ui->intel('main/templates/modals/payment');
	$data = array('status' => 200, 'html' => $html);
}

elseif ($action == 'unsubscribe') {
	$data['status']     = 400;
	if (!empty($_GET['user_id']) && is_numeric($_GET['user_id']) && $_GET['user_id'] > 0) {
		$user_id = aura::secure($_GET['user_id']);
		$user_data = $db->where('user_id', $user_id)->getOne(T_USERS);
		if (!empty($user_data)) {
			$db->where('user_id', $user_data->user_id)->where('subscriber_id', $me['user_id'])->delete(T_SUBSCRIBERS);
			$data['status']     = 200;
			$data['uname']      = $user_data->username;
			$data['url']        = $config['site_url'] . '/' . $user_data->username;
		} else {
			$data['message'] = lang("something_went_wrong_please_try_again_later_");
		}
	} else {
		$data['message'] = lang("something_went_wrong_please_try_again_later_");
	}
}