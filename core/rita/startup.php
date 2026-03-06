<?php
if ($action == 'profile') {
	$data['status'] = 400;
	if (!empty($_FILES['photo']) && file_exists($_FILES['photo']['tmp_name'])) {
		$media = new Media();
		$media->setFile(array(
			'file' => $_FILES['photo']['tmp_name'],
			'name' => $_FILES['photo']['name'],
			'size' => $_FILES['photo']['size'],
			'type' => $_FILES['photo']['type'],
			'allowed' => 'jpeg,jpg,png',
			'crop' => array('height' => 400,'width' => 400),
			'avatar' => true
		));

		$upload = $media->uploadFile();
		if (!empty($upload)) { 
			$photo = $upload['filename'];
			$data['status']  = 200;
			$data['photo']  = Media::getMedia($photo);
			$user->updateStatic($me['user_id'],array( 'avatar' => $photo, 'startup_avatar' => 1));

			#Reward points to the user
			RecordAction('register', array('user_id' => $me['user_id']));
		}
	}
}

if ($action == 'info') {
	$data['status'] = 400;
	if (empty($_POST['country']) && empty($_POST['fname']) && empty($_POST['lname'])) {
		$data['message'] = lang('please_check_details');
	}
	elseif (!empty($_POST['fname']) && len($_POST['fname']) > 12) {
		$data['message'] = lang('fname_is_long');
	}
	elseif (!empty($_POST['lname']) && len($_POST['lname']) > 20) {
		$data['message'] = lang('lname_is_long');
	}
	else{
		if (!empty($_POST['country']) && in_array($_POST['country'], array_keys($cnames))) {
			$up_data['country_id'] = aura::secure($_POST['country']);
		}
		if (!empty($_POST['fname'])) {
			$up_data['fname'] = aura::secure($_POST['fname']);
		}
		if (!empty($_POST['lname'])) {
			$up_data['lname'] = aura::secure($_POST['lname']);
		}
		$up_data['startup_info'] = 1;
		$user->updateStatic($me['user_id'],$up_data);
		$data['status']  = 200;

		#Reward points to the user
		RecordAction('register', array('user_id' => $me['user_id']));
	}
}

if ($action == 'follow') {
	$data['status'] = 400;
	$ids = explode(',', $_POST['ids']);
	if (!empty($_POST['ids']) && is_array($ids)) {
		foreach ($ids as $key => $id) {
			if (!empty($id) && is_numeric($id)) {
				$follower_id  = $me['user_id'];
				$following_id = aura::secure($id);
				$notif        = new notify();
				$user->setUserById($follower_id);
				$status       = $user->follow($following_id);
				$data['status'] = 400;
				if ($status === 1) {
					$data['status'] = 200;
					$data['code'] = 1;

					#Notify post owner
					$notif_conf = $notif->notifSettings($following_id,'on_follow');
					if ($notif_conf) {
						$re_data = array(
							'notifier_id' => $me['user_id'],
							'recipient_id' => $following_id,
							'type' => 'followed_u',
							'url' => un2url($me['username']),
							'time' => time()
						);
						
						$notif->notify($re_data);
					}	
				}
			}
		}
		$user->updateStatic($me['user_id'],array('startup_follow' => 1));
		$data['status']  = 200;

		#Reward points to the user
		RecordAction('register', array('user_id' => $me['user_id']));
	}
}

if ($action == 'skip') {
	if ($me['startup_avatar'] == 0) {
		$user->updateStatic($me['user_id'],array(
			'startup_avatar' => 1
		));
	} elseif ($me['startup_info'] == 0) {
		$user->updateStatic($me['user_id'],array(
			'startup_info' => 1
		));
	} elseif ($me['startup_follow'] == 0) {
		$user->updateStatic($me['user_id'],array(
			'startup_follow' => 1
		));
	}
}