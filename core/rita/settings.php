<?php
if ($action == 'account' && IS_LOGGED && !empty($_POST['user_id'])) {
	$error  = false;
	$post   = array();
	$post[] = (empty($_POST['username']));
	$post[] = (empty(is_numeric($_POST['user_id'])));

	if (in_array(true, $post)) {
		$error = lang('please_check_details');
	}
	else if(empty($user->isOwner($_POST['user_id'])) && IS_ADMIN == false){
		$error = lang('please_check_details');
	}
	else{
		$user_id   = aura::secure($_POST['user_id']);
		$user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $user_data;

		if (empty($user_data)) {
			$error = lang('user_not_exist');
		}
		if ($me->username != $_POST['username']) {
			if (users::userNameExists($_POST['username'])) {
				$error = lang('username_is_taken');
			}	
		}
		if(strlen($_POST['username']) < 4 || strlen($_POST['username']) > 32){
			$error = lang('username_characters_length');
		}
		if(preg_match('/[^\w]+/', $_POST['username'])){
			$error = lang('username_invalid_characters');
		}
	}

	if (empty($error)) {
		$up_data = array('username' => aura::secure($_POST['username']),'update_username' => 0,'username_timer' => time());
		$update  = $user->updateStatic($me->user_id,$up_data);
		$data['status'] = 200;
		$data['message'] = lang('changes_saved');	
	}
	else{
		$data['status']  = 400;
		$data['message'] = $error;
	}
}

if ($action == 'general' && IS_LOGGED && !empty($_POST['user_id'])) {

	$error  = false;
	$post   = array();
	$post[] = (empty($_POST['email']));
	$post[] = (empty($_POST['gender']) || empty(is_numeric($_POST['user_id'])));

	if (in_array(true, $post)) {
		$error = lang('please_check_details');
	}

	else if(empty($user->isOwner($_POST['user_id'])) && IS_ADMIN == false){
		$error = lang('please_check_details');
	}
	else{
		$user_id   = aura::secure($_POST['user_id']);
		$user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $user_data;
		if (empty($user_data)) {
			$error = lang('user_not_exist');
		}
		if($me->email != $_POST['email']){
			if (users::userEmailExists($_POST['email'])) {
				$error = lang('email_exists');
			}
		}
		if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
			$error = lang('email_invalid_characters');
		}
	}

	if (empty($error)) {
		$up_data = array(
			'email' => aura::secure($_POST['email']),
			'gender' => aura::secure($_POST['gender']),
		);
		$up_data['subscribe_price'] = 0;
		if (($config['private_photos'] == 'on' || $config['private_photos'] == 'on') && !empty($_POST['subscribe_price']) && is_numeric($_POST['subscribe_price']) && $_POST['subscribe_price'] > 0) {
			$up_data['subscribe_price'] = aura::secure($_POST['subscribe_price']);
		}
		if (!empty($_POST['country']) && in_array($_POST['country'], array_keys($cnames))) {
			$up_data['country_id'] = aura::secure($_POST['country']);
		}
		$update  = $user->updateStatic($me->user_id,$up_data);
		$data['status'] = 200;
		$data['message'] = lang('changes_saved');
		
	}else{
		$data['status']  = 400;
		$data['message'] = $error;
	}
}

else if ($action == 'profile' && IS_LOGGED && !empty($_POST['user_id'])) {

	$error     = false;
	$request   = array();
	$request[] = (isset($_POST['fname']) && isset($_POST['lname']));
	$request[] = (isset($_POST['about']) && isset($_POST['website']));
	$request[] = (isset($_POST['about']) && isset($_POST['web_name']));

	if (in_array(false, $request)) {
		$error = lang('please_check_details');
	}

	else if(empty($user->isOwner($_POST['user_id'])) && IS_ADMIN == false){
		$error = lang('unknown_error');
	}

	else{
		$user_id   = aura::secure($_POST['user_id']);
		$user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $user_data;
		if (empty($user_data)) {
			$error = lang('user_not_exist');
		}

		if (len($_POST['fname']) > 15) {
			$error = lang('fname_is_long');
		}

		if (len($_POST['lname']) > 20) {
			$error = lang('lname_is_long');
		}

		if (len($_POST['about']) > 150) {
			$error = lang('about_is_long');
		}

		if (len($_POST['website']) && empty(aura::isUrl($_POST['website']))) {
			$error = lang('invalid_webiste_url');
		}

		$profile = 1; //Default Profile
		if ($user_data->is_pro && $_POST['profile'] == 2) { //Nova Profile
			$profile = 2;
		}

		if ($user_data->is_pro && $_POST['profile'] == 3) { //Blue Tech Profile
			$profile = 3;
		}

		if ($user_data->is_pro && $_POST['profile'] == 4) { //Pathfinder Tech Profile
			$profile = 4;
		}

		if ($user_data->is_pro && $_POST['profile'] == 5) { //Oceana Profile
			$profile = 5;
		}

		if ($user_data->is_pro && $_POST['profile'] == 6) { // Hexagon Profile
			$profile = 6;
		}
	}

	if (empty($error)) {	
		$up_data = array(
			'fname' => ((len($_POST['fname'])) ? aura::secure($_POST['fname']) : ''),
			'lname' => ((len($_POST['lname'])) ? aura::secure($_POST['lname']) : ''),
			'about' => ((len($_POST['about'])) ? aura::secure($_POST['about']) : ''),
			'website' => ((len($_POST['website'])) ? aura::secure($_POST['website']) : ''),
			'web_name' => ((len($_POST['web_name'])) ? aura::secure($_POST['web_name']) : ''),
			'profile' => $profile,
		);

		$update          = $user->updateStatic($me->user_id,$up_data);
		$data['status']  = 200;
		$data['message'] = lang('changes_saved');	
	}
	else{	
		$data['status']  = 400;
		$data['message'] = $error;
	}
}

else if ($action == 'social_account' && IS_LOGGED && !empty($_POST['user_id'])) {

	$error     = false;
	$request   = array();

	$request[] = (isset($_POST['facebook']) && isset($_POST['google']) && isset($_POST['instagram']) && isset($_POST['discord']) && isset($_POST['deviantart']) && isset($_POST['github']) && isset($_POST['pinterest']) && isset($_POST['tiktok']) && isset($_POST['youtube']) && isset($_POST['spotify']) && isset($_POST['twitch']) && isset($_POST['patreon']));
	$request[] = (isset($_POST['twitter']) && is_numeric($_POST['user_id']));

	if (in_array(false, $request)) {
		$error = lang('please_check_details');
	}
	else if(empty($user->isOwner($_POST['user_id'])) && IS_ADMIN == false){
		$error = lang('unknown_error');
	}
	else{
		$user_id   = aura::secure($_POST['user_id']);
		$user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $user_data;
		if (empty($user_data)) {
			$error = lang('user_not_exist');
		}
		
		if (len($_POST['facebook']) && empty(aura::isUrl($_POST['facebook']))) {
			$error = lang('invalid_facebook_url');
		}		
		
		if (len($_POST['google']) && empty(aura::isUrl($_POST['google']))) {
			$error = lang('invalid_google_url');
		}		

		if (len($_POST['twitter']) && empty(aura::isUrl($_POST['twitter']))) {
			$error = lang('invalid_twitter_url');
		}

		if (len($_POST['instagram']) && empty(aura::isUrl($_POST['instagram']))) {
			$error = lang('invalid_instagram_url');
		}

		if (len($_POST['discord']) && empty(aura::isUrl($_POST['discord']))) {
			$error = lang('invalid_discord_url');
		}

		if (len($_POST['deviantart']) && empty(aura::isUrl($_POST['deviantart']))) {
			$error = lang('invalid_deviantart_url');
		}

		if (len($_POST['github']) && empty(aura::isUrl($_POST['github']))) {
			$error = lang('invalid_github_url');
		}

		if (len($_POST['pinterest']) && empty(aura::isUrl($_POST['pinterest']))) {
			$error = lang('invalid_pinterest_url');
		}

		if (len($_POST['tiktok']) && empty(aura::isUrl($_POST['tiktok']))) {
			$error = lang('invalid_tiktok_url');
		}

		if (len($_POST['youtube']) && empty(aura::isUrl($_POST['youtube']))) {
			$error = lang('invalid_youtube_url');
		}

		if (len($_POST['spotify']) && empty(aura::isUrl($_POST['spotify']))) {
			$error = lang('invalid_spotify_url');
		}

		if (len($_POST['twitch']) && empty(aura::isUrl($_POST['twitch']))) {
			$error = lang('invalid_twitch_url');
		}
		
		if (len($_POST['patreon']) && empty(aura::isUrl($_POST['patreon']))) {
			$error = lang('invalid_patreon_url');
		}
	}

	if (empty($error)) {	
		$up_data = array(	
			'facebook' => ((len($_POST['facebook'])) ? aura::secure($_POST['facebook']) : ''),
			'google' => ((len($_POST['google'])) ? aura::secure($_POST['google']) : ''),
			'twitter' => ((len($_POST['twitter'])) ? aura::secure($_POST['twitter']) : ''),
			'instagram' => ((len($_POST['instagram'])) ? aura::secure($_POST['instagram']) : ''),
			'discord' => ((len($_POST['discord'])) ? aura::secure($_POST['discord']) : ''),
			'deviantart' => ((len($_POST['deviantart'])) ? aura::secure($_POST['deviantart']) : ''),
			'github' => ((len($_POST['github'])) ? aura::secure($_POST['github']) : ''),
			'pinterest' => ((len($_POST['pinterest'])) ? aura::secure($_POST['pinterest']) : ''),
			'tiktok' => ((len($_POST['tiktok'])) ? aura::secure($_POST['tiktok']) : ''),
			'youtube' => ((len($_POST['youtube'])) ? aura::secure($_POST['youtube']) : ''),
			'spotify' => ((len($_POST['spotify'])) ? aura::secure($_POST['spotify']) : ''),
			'twitch' => ((len($_POST['twitch'])) ? aura::secure($_POST['twitch']) : ''),
			'patreon' => ((len($_POST['patreon'])) ? aura::secure($_POST['patreon']) : ''),
		);

		$update          = $user->updateStatic($me->user_id,$up_data);
		$data['status']  = 200;
		$data['message'] = lang('changes_saved');	
	}
	else{
		$data['status']  = 400;
		$data['message'] = $error;
	}
}

else if ($action == 'edit-avatar' && IS_LOGGED && !empty($_POST['user_id'])) {
	if(is_numeric($_POST['user_id']) && ($user->isOwner($_POST['user_id']) || IS_ADMIN)){
		$user_id   = aura::secure($_POST['user_id']);
		$user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $user_data;
		$data      = array('status' => 400);

		if (!empty($me)) {
			if ($me->is_pro) {
				if (!empty($_FILES['avatar']) && file_exists($_FILES['avatar']['tmp_name'])) {
					$media = new Media();
					if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
						$data['status']  = 400;
						$data['message'] = "Upload failed with error code " . $_FILES['avatar']['error'];
					}else{
						$info = getimagesize($_FILES['avatar']['tmp_name']);
						if ($info === FALSE) {
							$data['status']  = 400;
							$data['message'] = lang('cannot_determine_image');
						}else if ( ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG) && ($info[2] !== IMAGETYPE_GIF)) {
							$data['status']  = 400;
							$data['message'] = lang('image_not_valid');
						}else{
							$media->setFile(array(
								'file' => $_FILES['avatar']['tmp_name'],
								'name' => $_FILES['avatar']['name'],
								'size' => $_FILES['avatar']['size'],
								'type' => $_FILES['avatar']['type'],
								'allowed' => 'jpeg,jpg,png,gif',
								'crop' => array(
									'height' => 400,
									'width' => 400,
								),
								'avatar' => true
							));
			
							$upload = $media->uploadFile();
							if (!empty($upload)) { 
								if(isset($upload['error'])){
									$data['status']  = 400;
									$data['message'] = $upload['error'];
								}else{
									$avatar = $upload['filename'];
									$data['status']  = 200;
									$data['message'] = lang('ur_avatar_changed');
									$data['avatar']  = Media::getMedia($avatar);
									$user->updateStatic($me->user_id,array('avatar' => $avatar));
								}
							}
						}
					}
			    }
			} else {
				if (!empty($_FILES['avatar']) && file_exists($_FILES['avatar']['tmp_name'])) {
					$media = new Media();
					if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
						$data['status']  = 400;
						$data['message'] = "Upload failed with error code " . $_FILES['avatar']['error'];
					}else{
						$info = getimagesize($_FILES['avatar']['tmp_name']);
						if ($info === FALSE) {
							$data['status']  = 400;
							$data['message'] = lang('cannot_determine_image');
						}else if ( ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG)) {
							$data['status']  = 400;
							$data['message'] = lang('upgrade_your_account');
						}else{
							$media->setFile(array(
								'file' => $_FILES['avatar']['tmp_name'],
								'name' => $_FILES['avatar']['name'],
								'size' => $_FILES['avatar']['size'],
								'type' => $_FILES['avatar']['type'],
								'allowed' => 'jpeg,jpg,png,gif',
								'crop' => array(
									'height' => 400,
									'width' => 400,
								),
								'avatar' => true
							));
			
							$upload = $media->uploadFile();
							if (!empty($upload)) { 
								if(isset($upload['error'])){
									$data['status']  = 400;
									$data['message'] = $upload['error'];
								}else{
									$avatar = $upload['filename'];
									$data['status']  = 200;
									$data['message'] = lang('ur_avatar_changed');
									$data['avatar']  = Media::getMedia($avatar);
									$user->updateStatic($me->user_id,array('avatar' => $avatar));
								}
							}
						}
					}
			    }
			}
		}
	}
}

else if ($action == 'change-password' && IS_LOGGED && !empty($_POST['user_id'])) {
	if(is_numeric($_POST['user_id']) && ($user->isOwner($_POST['user_id']) || IS_ADMIN)){
		$user_id   = aura::secure($_POST['user_id']);
		$user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $user_data;
		$data      = array('status' => 400);
		$error     = false;
		if (!empty($me)) {
			$post   = array();
			$post[] = ((empty($_POST['old_password']) && IS_ADMIN == false));
			$post[] = (empty($_POST['new_password']) || empty($_POST['conf_password']));

			if (in_array(true, $post)) {
				$error = lang('please_check_details');
			} else{
				if (!HashPassword($_POST['old_password'], $user_data->password) && IS_ADMIN == false) {
					$error = lang('password_not_match');
				}
				if($_POST['new_password'] != $_POST['conf_password']){
					$error = lang('password_not_match');
				}
				if (strlen($_POST['conf_password']) < 4) {
					$error = lang('password_is_short');
				}
			}
			if (empty($error)) {
				$user->updateStatic($me->user_id,array('password' => password_hash($_POST['conf_password'], PASSWORD_DEFAULT)));
				$data['status']  = 200;
				$data['message'] = lang('changes_saved');
			} else{
				$data['message'] = $error;
			}
		}
	}
}

else if ($action == 'reset-password' && IS_LOGGED && !empty($_POST['user_id'])) {
	if(is_numeric($_POST['user_id']) && ($user->isOwner($_POST['user_id']))){
		$user_id   = aura::secure($_POST['user_id']);
		$user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $user_data;
		$data      = array('status' => 400);
		$error     = false;
		if (!empty($me)) {
			if (empty($_POST['email'])) {
				$error = lang('please_fill_fields');
			}
			if(!users::userEmailExists($_POST['email'])){
				$error = lang('unknown_email');
			}
			if (empty($error)) {
				$user = new users;
				$user_data = $user->setUserByEmail($_POST['email'])->getUser();
				$email_code = sha1(time() + rand(111,999));
				$update_data = array('email_code' => $email_code);
				$db->where('user_id', $user_data->user_id);
				$update = $db->update(T_USERS, $update_data);

				$body = "Hello {{NAME}},
				<br><br>".lang('v2_reset_password_msg')."
				<br>
				<a href=\"{{RESET_LINK}}\">".lang('v2_reset_password')."</a>
				<br><br>
				{{SITE_NAME}} Team.";

				$body = str_replace(
					array("{{NAME}}","{{SITE_NAME}}", "{{RESET_LINK}}"),
					array($user_data->name, $config['site_name'], $site_url . '/reset-password/' . $email_code),
					$body 
				);

				$email = array(
					'from_email' => $config['noreply_email'],
					'from_name' => $config['site_name'],
					'to_email' => $_POST['email'],
					'to_name' => $user_data->name,
					'subject' => lang('v2_reset_password'),
					'charSet' => 'UTF-8',
					'message_body' => $body,
					'is_html' => true
				);
				$send = aura::sendMail($email);
				if ($send) {
					$data['status'] = 200;
					$data['message'] = lang('instructions_sent');
				} else {
					$data['status'] = 400;
					$data['message'] = lang('unknown_error');
				}
			} else {
				$data['message'] = $error;
			}
		}

	}
}

else if ($action == 'delete-account' && IS_LOGGED && !empty($_POST['user_id'])) {
	if(is_numeric($_POST['user_id']) && ($user->isOwner($_POST['user_id']))){
		$user_id   = aura::secure($_POST['user_id']);
		$user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $user_data;
		//$code      = $_POST['deletecode'];
		$data      = array('status' => 400);
		$error     = false;
		if (!empty($me)) {
			if (!HashPassword($_POST['password'], $me->password)) {
				$error = lang('please_check_details');
			}
			if (empty($error)) {
				$user->setUserById($user_id)->delete();
			//	users::signoutUser();
				$data['status']  = 200;
				$data['message'] = lang('ur_account_deleted');
			} else{
				$data['message'] = $error;
			}
		}
	}
}

else if ($action == 'notifications' && IS_LOGGED && !empty($_POST['user_id'])) {
	if(is_numeric($_POST['user_id']) && ($user->isOwner($_POST['user_id']))){
		$user_id   = $user::secure($_POST['user_id']);
		$data      = array('status' => 400);
		$error     = false;
		$up_data   = array(
			'n_on_like' => ((!empty($_POST['on_like_post'])) ? '1' : '0'),
			'n_on_comment_like' => ((!empty($_POST['on_comment_like'])) ? '1' : '0'),
			'n_on_comment' => ((!empty($_POST['on_commnet_post'])) ? '1' : '0'),
			'n_on_follow' => ((!empty($_POST['on_follow'])) ? '1' : '0'),
			'n_on_mention' => ((!empty($_POST['on_mention'])) ? '1' : '0'),
			'n_on_comment_reply' => ((!empty($_POST['on_comment_reply'])) ? '1' : '0'),
			'n_on_post_saved' => ((!empty($_POST['on_post_saved'])) ? '1' : '0'),
			'n_on_post_shared' => ((!empty($_POST['on_post_shared'])) ? '1' : '0'),
			'n_on_accept_request' => ((!empty($_POST['on_accept_request'])) ? '1' : '0'),
			'n_on_rejected_request' => ((!empty($_POST['on_rejected_request'])) ? '1' : '0'),
			'n_on_points_earn' => ((!empty($_POST['on_points_earn'])) ? '1' : '0'),
			'n_on_points_loose' => ((!empty($_POST['on_points_loose'])) ? '1' : '0'),
			'n_on_follow_request' => ((!empty($_POST['on_follow_request'])) ? '1' : '0'),
		);
		$update = $user->updateStatic($user_id,$up_data);
		if (!empty($update)) {
			$data['status']  = 200;
			$data['message'] = lang('changes_saved');
		}
	}
}

else if ($action == 'administration' && IS_LOGGED && !empty($_POST['user_id'])) {
	if(is_numeric($_POST['user_id']) && ($user->isOwner($_POST['user_id']) || IS_DEV)){
		$user_id   = $user::secure($_POST['user_id']);
		$data      = array('status' => 400);
		$error     = false;
		$up_data = array(
			'admin' => ((isset($_POST['admin']) && $_POST['admin'] == 'on') ? '1' : '0'),
			'manager' => ((isset($_POST['manager']) && $_POST['manager'] == 'on') ? '1' : '0'),
			'moderator' => ((isset($_POST['moderator']) && $_POST['moderator'] == 'on') ? '1' : '0'),
			'graphics' => ((isset($_POST['graphics']) && $_POST['graphics'] == 'on') ? '1' : '0'),
			'translator' => ((isset($_POST['translator']) && $_POST['translator'] == 'on') ? '1' : '0'),
			'promoter' => ((isset($_POST['promoter']) && $_POST['promoter'] == 'on') ? '1' : '0')
		);
		$update = $user->updateStatic($user_id,$up_data);
		if (!empty($update)) {
			$data['status']  = 200;
			$data['message'] = lang('changes_saved');
		}
	}
}

else if ($action == 'privacy' && IS_LOGGED && !empty($_POST['user_id'])) {
	if(is_numeric($_POST['user_id']) && ($user->isOwner($_POST['user_id']))){
		$user_id = $user::secure($_POST['user_id']);
		$data    = array('status' => 400);
		$error   = false;
		if (isset($_POST['p_privacy']) && isset($_POST['c_privacy'])) {	
			$up_data = array(
				'p_privacy' => ((in_array($_POST['p_privacy'], array('0','1','2'))) ? $_POST['p_privacy'] : '2'),
				'c_privacy' => ((in_array($_POST['c_privacy'], array('1','2'))) ? $_POST['c_privacy'] : '1'),
				'joined_type' => ((in_array($_POST['joined_type'], array('0','1'))) ? $_POST['joined_type'] : '1'),
				'show_joined' => ((isset($_POST['show_joined']) && $_POST['show_joined'] == 'on') ? '1' : '0'),
				'auto_renew' => ((isset($_POST['auto_renew']) && $_POST['auto_renew'] == 'on') ? '1' : '0'),
				'search_engines' => ((isset($_POST['search_engines']) && $_POST['search_engines'] == 'on') ? '1' : '0'),
				'show_favorites' => ((isset($_POST['show_favorites']) && $_POST['show_favorites'] == 'on') ? '1' : '0'),
				'show_subscribers' => ((isset($_POST['show_subscribers']) && $_POST['show_subscribers'] == 'on') ? '1' : '0'),
			);
			$update = $user->updateStatic($user_id,$up_data);
			if (!empty($update)) {
				$data['status']  = 200;
				$data['message'] = lang('changes_saved');
			}
		}
	}
}

else if ($action == 'unblock-user' && IS_LOGGED && !empty($_POST['id'])) {
	if(is_numeric($_POST['id'])){
		$user_id = $user::secure($_POST['id']);
		$data    = array('status' => 304);
		$unblock = $user->unBlockUser($user_id);
		if (!empty($unblock)) {
			$data['status']  = 200;
			$data['message'] = lang('user_unblocked');
		}
	}
}

else if ($action == 'verify' && IS_LOGGED) {
	$data    = array('status' => 400);
	$me      = $user->user_data;
	if (!empty($_POST['name']) && !empty($_FILES['character']) && !empty($_FILES['photo'])) {
		$inserted_data = array();
		$is_ok = false;
		$media = new Media();
		$media->setFile(array(
			'file' => $_FILES['photo']['tmp_name'],
			'name' => $_FILES['photo']['name'],
			'size' => $_FILES['photo']['size'],
			'type' => $_FILES['photo']['type'],
			'allowed' => 'jpeg,jpg,png',
			'crop' => array(
				'height' => 600,
				'width' => 600,
			),
			'avatar' => true
		));

		$upload = $media->uploadFile();
		if (!empty($upload['filename'])) { 
			$is_ok = true;
			$inserted_data['photo'] = $upload['filename'];
		} else{
			$data['message'] = lang('your_photo_invalid');
		}

		if ($is_ok == true) {
			$media->setFile(array(
				'file' => $_FILES['character']['tmp_name'],
				'name' => $_FILES['character']['name'],
				'size' => $_FILES['character']['size'],
				'type' => $_FILES['character']['type'],
				'allowed' => 'jpeg,jpg,png',
				'crop' => array(
					'height' => 600,
					'width' => 600,
				),
				'avatar' => true
			));

			$upload = $media->uploadFile();
			if (!empty($upload['filename'])) { 
				$is_ok = true;
				$inserted_data['passport'] = $upload['filename'];
			}else{
				$is_ok = false;
				$data['message'] = lang('your_ip_invalid');
			}
		}

		if ($is_ok == true) {
			$inserted_data['time'] = $me->time;
			$inserted_data['gender'] = $me->gender;
			$inserted_data['user_id'] = $me->user_id;
			$inserted_data['name'] = aura::secure($_POST['name']);
			$inserted_data['message'] = !empty($_POST['message']) ? aura::secure($_POST['message']) : '';
			$id = $user->sendVerificationRequest($inserted_data);
			if ($id > 0) {
				$data['message'] = lang('request_done');
				$data['status'] = 200;
			}
			else{
				$data['message'] = lang('unknown_error');
			}
		}
	}
	else{
		$data['message'] = lang('please_check_details');
	}
}

elseif ($action == 'delete_session') {
	$data    = array('status' => 400);
	if (!empty($_POST['id'])) {
		$id = aura::secure($_POST['id']);
		$user->delete_session($id);
		$data['status'] = 200;
		$data['message'] = lang('session_delected');
	}
}
elseif ($action == 'change_profile') {
	if ($user->user_data->is_pro && $user->user_data->profile == 1) {
		$db->where('user_id',$user->user_data->user_id)->update(T_USERS,array('profile' => 2));
	} elseif ($user->user_data->is_pro && $user->user_data->profile == 2) {
		$db->where('user_id',$user->user_data->user_id)->update(T_USERS,array('profile' => 1));
	} elseif ($user->user_data->is_pro && $user->user_data->profile == 3) {
		$db->where('user_id',$user->user_data->user_id)->update(T_USERS,array('profile' => 4));
	} elseif ($user->user_data->is_pro && $user->user_data->profile == 4) {
		$db->where('user_id',$user->user_data->user_id)->update(T_USERS,array('profile' => 3));
	} elseif ($user->user_data->is_pro && $user->user_data->profile == 5) {
		$db->where('user_id',$user->user_data->user_id)->update(T_USERS,array('profile' => 6));
	} elseif ($user->user_data->is_pro && $user->user_data->profile == 6) {
		$db->where('user_id',$user->user_data->user_id)->update(T_USERS,array('profile' => 5));
	}
	$data['status'] = 200;
}

elseif ($action == 'switch') {
	$error     = false;
	$value     =  aura::secure($_POST['type']);
    if(empty($user->isOwner($_POST['user_id'])) && IS_ADMIN == false){
		$error = lang('unknown_error');
	} else{
		$user_id   = aura::secure($_POST['user_id']);
		$user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $user_data;
		if (empty($user_data)) {
			$error = lang('user_not_exist');
		}
		$profile = 1;
		if($value === 'nova') { //Nova Profile
			$profile = 2;
		}
		if($value === 'linear') { //Default Profile
			$profile = 1;
		}
		if($value === 'oceana') { //Oceana Profile
			$profile = 5;
		}
		if($value === 'hexagon') { // Hexagon Profile
			$profile = 6;
		}
	}
	if (empty($error)) {	
		$up_data = array('profile' => $profile);
		$update          = $user->updateStatic($me->user_id,$up_data);
		$data['status']  = 200;
		$data['message'] = lang('changes_saved');	
	}else{	
		$data['status']  = 400;
		$data['message'] = $error;
	}
}

else if ($action == 'business' && IS_LOGGED && $config['business_account'] == 'on') {
	if(is_numeric($_POST['user_id']) && ($user->isOwner($_POST['user_id']))){
		$user_id   = $user::secure($_POST['user_id']);
		$data      = array('status' => 400);
		$error     = false;
		$up_data = array(
			'b_site' => ((len($_POST['b_site'])) ? aura::secure($_POST['b_site']) : ''),
			'b_name' => ((len($_POST['b_name'])) ? aura::secure($_POST['b_name']) : ''),
			'b_email' => ((len($_POST['b_email'])) ? aura::secure($_POST['b_email']) : ''),
			'b_phone' => ((len($_POST['b_phone'])) ? aura::secure($_POST['b_phone']) : ''),
		);
		if (!empty($_POST['b_site_action']) && in_array($_POST['b_site_action'], array_keys($context['call_action']))) {
			$up_data['b_site_action'] = aura::secure($_POST['b_site_action']);
		}
		$update = $user->updateStatic($user_id,$up_data);
		if (!empty($update)) {
			$data['status']  = 200;
			$data['message'] = lang('changes_saved');
		}
    }
}

else if ($action == 'edit-banner' && IS_LOGGED && !empty($_POST['user_id'])) {
	if(is_numeric($_POST['user_id']) && ($user->isOwner($_POST['user_id']) || IS_ADMIN)){
		$user_id   = aura::secure($_POST['user_id']);
		$udata     = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $udata;
		$data      = array('status' => 400);
		if (!empty($me)) {
			if (!empty($_FILES['banner']) && file_exists($_FILES['banner']['tmp_name'])) {
				$media = new Media();
				if ($_FILES['banner']['error'] !== UPLOAD_ERR_OK) {
					$data['status']  = 400;
					$data['message'] = "Upload failed with error code " . $_FILES['banner']['error'];
				}else{
					$info = getimagesize($_FILES['banner']['tmp_name']);
					if ($info === FALSE) {
						$data['status']  = 400;
						$data['message'] = lang('cannot_determine_image');
					}else if ( ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG)) {
						$data['status']  = 400;
						$data['message'] = lang('banner_image_not_valid');
					}else{
						$media->setFile(array(
							'file' => $_FILES['banner']['tmp_name'],
							'name' => $_FILES['banner']['name'],
							'size' => $_FILES['banner']['size'],
							'type' => $_FILES['banner']['type'],
							'allowed' => 'jpeg,jpg,png',
							// 'crop' => array(
							// 	'height' => 762,
							// 	'width' => 1600,
							// ),
							'banner' => true
						));
		
						$upload = $media->uploadFile();
						if (!empty($upload)) { 
							if(isset($upload['error'])){
								$data['status']  = 400;
								$data['message'] = $upload['error'];
							}else{
								$banner = $upload['filename'];
								$data['status']  = 200;
								$data['message'] = lang('ur_banner_changed');
								$data['banner']  = Media::getMedia($banner);
								$user->updateStatic($me->user_id,array('banner' => $banner));
							}
						}
					}
				}
			}
		}
	}
}