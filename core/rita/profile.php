<?php

if ($action == 'load-user-followers') {
	$vl1 = (!empty($_GET['offset']) && is_numeric($_GET['offset']));
	$vl2 = (!empty($_GET['user_id']) && is_numeric($_GET['user_id']));

	if ($vl1 && $vl2) {
		$offset  = $_GET['offset'];
		$user_id = $_GET['user_id'];

		$user->setUserById($user_id);
		$following_ls   = $user->getFollowers($offset,50);
		$data['status'] = 404;

		if (!empty($following_ls)) {
			$data['html']   = "";
			$data['status'] = 200;
			$following_ls   = o2array($following_ls);
			foreach ($following_ls as $udata) {
				$data['html']    .= $ui->intel('profile/templates/profile/includes/followers-ls-item');
			}
		}
	}
}

if($action == 'add_views' && !empty($_POST['user_id'])) {
	if (is_numeric($_POST['user_id'])) {
		$data    = array('status' => 400);

		$user   = new users();
		$user_id = $_POST['user_id'];
		$count   = $user->setUserById($user_id)->add_visit();

		if (!empty($count)) {
			$data['status']  = 200;
			$data['count']    = $count;
		}
	}
}

if ($action == 'load-user-following') {
	$vl1 = (!empty($_GET['offset']) && is_numeric($_GET['offset']));
	$vl2 = (!empty($_GET['user_id']) && is_numeric($_GET['user_id']));

	if ($vl1 && $vl2) {
		$offset  = $_GET['offset'];
		$user_id = $_GET['user_id'];

		$user->setUserById($user_id);
		$following_ls   = $user->getFollowing($offset,50);
		$data['status'] = 404;

		if (!empty($following_ls)) {
			$data['html']   = "";
			$data['status'] = 200;
			$following_ls   = o2array($following_ls);
			foreach ($following_ls as $udata) {
				$data['html']    .= $ui->intel('profile/templates/profile/includes/following-ls-item');
			}
		}
	}
}

if ($action == 'load-user-subscriptions') {
	$data['status'] = 404;
	$vl1 = (!empty($_GET['offset']) && is_numeric($_GET['offset']));
	$vl2 = (!empty($_GET['user_id']) && is_numeric($_GET['user_id']));

	if ($vl1 && $vl2) {
		$offset  = $_GET['offset'];
		$user_id = $_GET['user_id'];
		$month = 60 * 60 * 24 * 30;
		$subscriptions = $db->where('subscriber_id',$user_id)->where('id',$offset,'<')->where('time',(time() - $month),'>=')->orderBy("id","DESC")->get(T_SUBSCRIBERS,50);
		$context['subscriptions'] = array();
		if (!empty($subscriptions)) {
			$data['html']   = "";
			$data['status'] = 200;
			foreach ($subscriptions as $key => $value) {
				$user_info = $user->setUserById($value->user_id)->getUser();
				$user_info->sub_id = $value->id;
				$udata = o2array($user_info);
				$data['html']    .= $ui->intel('profile/templates/profile/includes/subscriptions_list');
			}
		}
	}
}

if ($action == 'create_ad') {
	$bidding_array = array('clicks','views');
	$appears_array = array('post','sidebar');
	$data['status'] = 400;
	if (empty($_POST['company']) || empty($_POST['url']) || empty($_POST['title']) || empty($_POST['location']) || empty($_POST['description']) || empty($_POST['bidding']) || !in_array($_POST['bidding'], $bidding_array) || empty($_POST['appears']) || !in_array($_POST['appears'], $appears_array) || empty($_FILES['image']) || empty($_POST['country']) || empty($_POST['gender'])) {
		$data['message'] = lang('please_check_details');
	}
	elseif (!aura::isUrl($_POST['url'])) {
		$data['message'] = lang('url_invalid');
	}
	elseif ($me['balance'] < 5) {
		$data['message'] = lang('top_wallet');
	}
	else{
		$media  = new Media();
		$media->setFile(array(
			'file' => $_FILES['image']['tmp_name'],
			'name' => $_FILES['image']['name'],
			'size' => $_FILES['image']['size'],
			'type' => $_FILES['image']['type'],
			'allowed' => 'jpeg,jpg,png,webp,gif',
		));
		$image = $media->uploadFile();
		if (!empty($image['filename'])) {
			$country = '';
			if (!empty($_POST['country'])) {
				$country = aura::secure('{'.implode('},{', $_POST['country']).'}');
			}
			$insert_array = array(
			'name' => aura::secure($_POST['company']),
		    'url'  => aura::secure($_POST['url']),
		    'headline' => aura::secure($_POST['title']),
		    'location' => aura::secure($_POST['location']),
		    'appears'  => aura::secure($_POST['appears']),
		    'bidding'  => aura::secure($_POST['bidding']),
		    'audience' => $country,
		    'gender'   => aura::secure($_POST['gender']),
		    'description' => aura::secure($_POST['description']),
		    'posted' => time(),
			'status' => '0',
		    'user_id'  => $me['user_id'],
			'edited'   => '0',
		    'ad_media' => $image['filename']);
			$db->insert(T_ADS,$insert_array);
			$data['status'] = 200;
			$data['message'] = lang('ad_created');

			#Reward points to the user
			RecordAction('create_ads', array('user_id' => $me['user_id']));
		}
		else{
			$data['message'] = lang('media_not_supported');
		}
	}
}

if ($action == 'edit_ad') {
	$bidding_array = array('clicks','views');
	$appears_array = array('post','sidebar');
	$data['status'] = 400;
	if (empty($_POST['company']) || empty($_POST['url']) || empty($_POST['title']) || empty($_POST['location']) || empty($_POST['description']) || empty($_POST['bidding']) || !in_array($_POST['bidding'], $bidding_array) || empty($_POST['appears']) || !in_array($_POST['appears'], $appears_array) || empty($_POST['country']) || empty($_POST['gender']) || empty($_POST['id'])) {
		$data['message'] = lang('please_check_details');
	}elseif (!aura::isUrl($_POST['url'])) {
		$data['message'] = lang('url_invalid');
	}
	elseif ($me['balance'] < 5) {
		$data['message'] = lang('top_wallet');
	}else{
		$user = new users();
		$ad = $user->GetAdByID($_POST['id']);
		if (!empty($ad) && $ad->user_id == $me['user_id']) {
			$country = '';
			if (!empty($_POST['country'])) {
				$country = aura::secure('{'.implode('},{', $_POST['country']).'}');
			}
			$insert_array = array(
			'name' => aura::secure($_POST['company']),
		    'url'  => aura::secure($_POST['url']),
		    'headline' => aura::secure($_POST['title']),
		    'location' => aura::secure($_POST['location']),
		    'appears'  => aura::secure($_POST['appears']),
		    'bidding'  => aura::secure($_POST['bidding']),
		    'audience' => $country,
		    'gender'   => aura::secure($_POST['gender']),
		    'description'   => aura::secure($_POST['description']),
		    'posted'   => time(),
		    'user_id'  => $me['user_id'],
			'status'   => '0',
			'edited'   => '1');
			$db->where('id',$ad->id)->update(T_ADS,$insert_array);
			$data['status'] = 200;
			$data['message'] = lang('ad_edited');
		}
		else{
			$data['message'] = lang('ad_not_found');
		}
	}
}

if ($action == 'delete_ad') {
	$data['status'] = 400;
	if (empty($_POST['id'])) {
		$data['message'] = lang('please_check_details');
	}else{
		$user = new users();
		$media = new Media();
		$ad = $user->GetAdByID($_POST['id']);
		if (!empty($ad) && $ad->user_id == $me['user_id']) {
			$db->where('id',$ad->id)->delete(T_ADS);
			$photo_file = $ad->ad_media;
			if (file_exists($photo_file)) {
	            @unlink(trim($photo_file));
	        }
	        else if($config['amazone_s3'] == 1 || $config['ftp_upload'] == 1 || $config['google_cloud_storage'] == 1){
	            $media->deleteFromFTPorS3($photo_file);
	        }
			$data['status'] = 200;
			$data['message'] = lang('campaign_deleted_success');
		}else{
			$data['message'] = lang('ad_not_found');
		}
	}
}

if ($action == 'ad_click') {
	$data['status'] = 400;
	if (empty($_POST['id'])) {
		$data['message'] = lang('please_check_details');
	}else{
		$user = new users();
		$ad = $user->GetAdByID($_POST['id']);
		$ads_array = array();
		if (!empty($_SESSION['ads'])) {
			$ads_array = explode(',', $_SESSION['ads']);
		}
		if (!empty($ad) && $ad->bidding == 'clicks' && !in_array($ad->id, $ads_array)) {
			$db->where('id', $ad->id)->update(T_ADS,array('clicks' => $db->inc(1)));
			$db->where('user_id', $ad->user_id)->update(T_USERS,array('balance' => $db->dec($config['ad_c_price'])));
			$ad_user = $db->where('user_id', $ad->user_id)->getOne(T_USERS);
			$user_wallet = $ad_user->balance - $config['ad_c_price'];
			if ($user_wallet < $config['ad_c_price']) {
				$db->where('id', $ad->id)->update(T_ADS,array('status' => 0));
			}
			$ads_array[] = $ad->id;
			$_SESSION['ads'] = implode(',', $ads_array);
		}
		$data['status'] = 200;
	}
}

if ($action == 'popover' && !empty($_GET['user_id'])) {
	$html  = '';
	$data['status'] = 400;

	$user       = new users();
	$user_id    = $_GET['user_id'];
	$query      = $user->getUserDataById($user_id);

	$is_owner      = false;
	$is_following  = false;
	
	if (IS_LOGGED && ($me['user_id'] == $user_id)) {
		$is_owner = true;
	}
	if (IS_LOGGED) {
		$chat_privacy  = $user->chatPrivacy($user_id);
		$is_following  = $user->isFollowing($user_id);
	}	

	$following = $db->where('follower_id',$user_id)->where('type',1)->getValue(T_CONNECTIV,"COUNT(`id`)");
	$followers = $db->where('following_id',$user_id)->where('type',1)->getValue(T_CONNECTIV,"COUNT(`id`)");

	if (!empty($query)) {
		$udata  = o2array($query);
		$context['is_owner']  = $is_owner;
		$context['followers'] = $followers;
		$context['following'] = $following;
		$context['chat_privacy'] = $chat_privacy;
		$context['is_following'] = $is_following;
		$html  .= $ui->intel('popover/templates/popover/index');
	}

	$data['status'] = 200;
	$data['html']   = $html;
}