<?php 
use Aws\S3\S3Client;
use Google\Cloud\Storage\StorageClient;

if ($action == 'general-settings' && !empty($_POST)) {
	$cpanel  = new cpanel();
	$update = $_POST;
	
	$data   = array('status' => 304);
	$error  = false;

	if (!empty($_POST['import_videos']) && $_POST['import_videos'] == 'on' && empty($config['yt_api'])) {
		$error = "Youtube api key is reqired to import videos";
	}
	if (!empty($_POST['import_images']) && $_POST['import_images'] == 'on' && empty($config['giphy_api'])) {
		$error = "Giphy api key is reqired to import images/gifs";
	}
	if (empty($error)) {
		$query  = $cpanel->updateSettings($update);
		if ($query == true) {
			$data['status'] = 200;
		}
	}else{
		$data['status'] = 400;
		$data['error']  = $error;
	}
}
elseif ($action == 'ad-settings' && !empty($_POST)) {
	$cpanel  = new cpanel();
	$update = array();	
	$data   = array('status' => 304);
	$error  = false;
    
    if (ISSET($_POST['ad1'])) {
    	if (!empty($_POST['ad1'])) {
    		$update['ad1'] = base64_decode($_POST['ad1']);
    	}else{
    	    $update['ad1'] = '';
    	}
    }
    if (ISSET($_POST['ad2'])) {
    	if (!empty($_POST['ad2'])) {
    		$update['ad2'] = base64_decode($_POST['ad2']);
    	}else{
    	    $update['ad2'] = '';
    	}
    }
    if (ISSET($_POST['ad3'])) {
    	if (!empty($_POST['ad3'])) {
    		$update['ad3'] = base64_decode($_POST['ad3']);
    	}else{
    	    $update['ad3'] = '';
    	}
    }
	if (empty($error)) {
		$query  = $cpanel->updateSettings($update);
		if ($query == true) {
			$data['status'] = 200;
		}
	}
}
elseif ($action == 'site-settings' && !empty($_POST)) {
	$update = $_POST;	
	$cpanel  = new cpanel();
	$data   = array('status' => 304);
	$error  = false;

	if (!empty($update['google_analytics'])) {
		$update['google_analytics'] = $cpanel::encode($update['google_analytics']);
	}
	if (empty($error)) {
		$query  = $cpanel->updateSettings($update);
		if ($query == true) {
			$data['status'] = 200;
		}
	}else{
		$data['status'] = 400;
		$data['error']  = $error;
	}
}

elseif ($action == 'email-settings' && !empty($_POST)) {
	$update = $_POST;
	$cpanel  = new cpanel();
	$data   = array('status' => 304);
	$error  = false;

	if (empty($error)) {
		$query  = $cpanel->updateSettings($update);
		foreach ($_POST as $key => $value) {
			if ($key == 'smtp_password') {
				$value = openssl_encrypt($value, "AES-128-ECB", 'mysecretkey1234');
				$cpanel->updateSettings(array('smtp_password' => $value));
			}
			if ($key == 'hello_password') {
				$value = openssl_encrypt($value, "AES-128-ECB", 'mysecretkey1234');
				$cpanel->updateSettings(array('hello_password' => $value));
			}
		}
		if ($query == true) {
			$data['status'] = 200;
		}
	}else{
		$data['status'] = 400;
		$data['error']  = $error;
	}
}
elseif ($action == 'storeg-settings' && !empty($_POST)) {
	$update = $_POST;
	$cpanel  = new cpanel();
	$data   = array('status' => 304);
	$error  = false;

    $ftp_upload = (ISSET($_POST['ftp_upload']) ? $_POST['ftp_upload'] : '');
    $amazone_s3 = (ISSET($_POST['amazone_s3']) ? $_POST['amazone_s3'] : '');
	$digital_ocean = (ISSET($_POST['digital_ocean']) ? $_POST['digital_ocean'] : '');
	$google_cloud_storage = (ISSET($_POST['google_cloud_storage']) ? $_POST['google_cloud_storage'] : '');
    if( $ftp_upload == 1 ){
        $cpanel->updateSettings(array('amazone_s3' => 0,'digital_ocean' => 0,'google_cloud_storage' => 0));	
    }
    if( $amazone_s3 == 1 ){
		$cpanel->updateSettings(array('ftp_upload' => 0,'digital_ocean' => 0,'google_cloud_storage' => 0));
	}
	if( $digital_ocean == 1 ){
		$cpanel->updateSettings(array('ftp_upload' => 0,'amazone_s3' => 0,'google_cloud_storage' => 0));
	}
	if( $google_cloud_storage == 1 ){
		$cpanel->updateSettings(array('ftp_upload' => 0,'amazone_s3' => 0,'digital_ocean' => 0));
	}
	
	$query  = $cpanel->updateSettings($update);
	if ($query == true) {
		$data['status'] = 200;
	}else{
	    $data['status'] = 400;
	    $data['error']  = "";
	}
                
}
elseif ($action == 'login-settings' && !empty($_POST)) {
	$update = $_POST;
	$cpanel  = new cpanel();
	$data   = array('status' => 304);
	$error  = false;

	$en_fb  = (!empty($_POST['fb_login']) && $_POST['fb_login'] == 'on');
	$en_tw  = (!empty($_POST['tw_login']) && $_POST['tw_login'] == 'on');
	$en_gl  = (!empty($_POST['gl_login']) && $_POST['gl_login'] == 'on');

	if (empty($error)) {
		$query  = $cpanel->updateSettings($update);
		if ($query == true) {
			$data['status'] = 200;
		}
	}else{
		$data['status'] = 400;
		$data['error']  = $error;
	}
}
elseif ($action == 'delete_multi_users') {
    if (!empty($_POST['ids']) && !empty($_POST['type']) && in_array($_POST['type'], array('Activate','Deactivate','Delete','free'))) {
        foreach ($_POST['ids'] as $key => $value) {
            if (is_numeric($value) && $value > 0) {
            	$value = $user::secure($value);
                if ($_POST['type'] == 'Delete') {
                	$user->setUserById($value)->delete();
                }
                elseif ($_POST['type'] == 'Activate') {
                    $db->where('user_id', $value);
                    $update_data = array('active' => '1','email_code' => '');
                    $update = $db->update(T_USERS, $update_data);
                }
                elseif ($_POST['type'] == 'Deactivate') {
                    $db->where('user_id', $value);
                    $update_data = array('active' => 2,'email_code' => '');
                    $update = $db->update(T_USERS, $update_data);
                }
                elseif ($_POST['type'] == 'free') {
                	$db->where('user_id', $value);
                    $update_data = array('is_pro' => 0,'pro_type' => 0,'pro_time' => 0,'banner_key' => 0,'switch' => 0,'can_verify' => 0,'update_username' => 0);
                    $update = $db->update(T_USERS, $update_data);
                }
            }
        }
        $data = ['status' => 200];
    }
}

elseif ($action == 'deactivate-account' && !empty($_POST['id']) && is_numeric($_POST['id'])) {

	$cpanel    = new cpanel();	
	$user_id = $user::secure($_POST['id']);

	$cpanel::$db->where('user_id',$user_id);
	$update = $cpanel::$db->update(T_USERS,array('active' => '2','email_code' => ''));

	if ($update) {
		$data['status'] = 200;
		$data['message'] = "Account has been Deactivated!";
	} else {
		$data['status'] = 400;
		$data['error'] = "Failed to deactivate this user. Try later!";
	}
}

elseif ($action == 'activate-account' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	$cpanel    = new cpanel();	
	$user_id = $user::secure($_POST['id']);

	$cpanel::$db->where('user_id',$user_id);
	$update = $cpanel::$db->update(T_USERS,array('active' => '1','email_code' => ''));

	if ($update) {
		$data['status'] = 200;
		$data['message'] = "Account has been Activated!";
	} else {
		$data['status'] = 400;
		$data['error'] = "Failed to activate this user. Try later!";
	}
}
elseif ($action == 'verify-account' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	$cpanel    = new cpanel();	
	$user_id = $user::secure($_POST['id']);

	$cpanel::$db->where('user_id',$user_id);
	$update = $cpanel::$db->update(T_USERS,array('verified' => '1'));

	$user = new users();
	$user_data = $user->getUserDataById($user_id);

	if ($update) {
		$data['status'] = 200;
		$data['message'] = "Account has been Verified!";

		$notif   = new notify();
		$re_data = array(
			'notifier_id' => $me['user_id'],
			'recipient_id' => $user_id,
			'type' => 'we_verified_ur_account',
			'url'  =>  $config['site_url'].'/settings/verification/'.$user_data->username,
			'time' => time(),
			'ajax_url' => 'load.php?app=settings&apph=settings&user='.$user_data->username.'&page=verification'
			);
		$notif->notify($re_data);
	} else {
		$data['status'] = 400;
		$data['error'] = "Failed to verify this user. Try later!";
	}
}

elseif ($action == 'un-verify-account' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	$cpanel    = new cpanel();	
	$user_id = $user::secure($_POST['id']);

	$cpanel::$db->where('user_id',$user_id);
	$update = $cpanel::$db->update(T_USERS,array('verified' => '0'));

	$user = new users();
	$user_data = $user->getUserDataById($user_id);

	if ($update) {
		$data['status'] = 200;
		$data['message'] = "Account has been Un-Verified!";

		$notif   = new notify();
		$re_data = array(
			'notifier_id' => $me['user_id'],
			'recipient_id' => $user_id,
			'type' => 'we_removed_ur_verification',
			'url'  =>  $config['site_url'].'/settings/verification/'.$user_data->username,
			'time' => time(),
			'ajax_url' => 'load.php?app=settings&apph=settings&user='.$user_data->username.'&page=verification'
			);
		$notif->notify($re_data);
	} else {
		$data['status'] = 400;
		$data['error'] = "Failed to Un-verify this user. Try later!";
	}
}

elseif ($action == 'send-credits' && !empty($_POST['id']) && is_numeric($_POST['id']) && !empty($_POST['credits'])) {
	$cpanel    = new cpanel();	
	$user_id = $user::secure($_POST['id']);
	$credits = $user::secure($_POST['credits']);

	$cpanel::$db->where('user_id',$user_id);
	$update = $cpanel::$db->update(T_USERS,array('credits' => $db->inc($credits)));

	$user = new users();
	$user_data = $user->getUserDataById($user_id);

	if ($update) {
		$data['status'] = 200;
		$data['message'] = "Credits added successfully!";
		
		$notif   = new notify();
		$re_data = array(
			'notifier_id' => $me['user_id'],
			'recipient_id' => $user_id,
			'type' => 'ur_ak_has_been_credited_with_credits',
			'url'  =>  $config['site_url'].'/settings/credits/'.$user_data->username,
			'time' => time(),
			'credits' => $credits,
			'ajax_url' => 'load.php?app=settings&apph=settings&user='.$user_data->username.'&page=credits'
			);
		$notif->notify($re_data);
	} else {
		$data['status'] = 400;
		$data['error'] = "Failed to add credits to this user!";
	}
}

elseif ($action == 'cancel-membership' && !empty($_POST['id']) && is_numeric($_POST['id'])) {

	$cpanel    = new cpanel();	
	$user_id = $user::secure($_POST['id']);

	$cpanel::$db->where('user_id',$user_id);
	$update = $cpanel::$db->update(T_USERS,array('is_pro' => 0, 'business_account' => 0));

	if ($update) {
		$data['status'] = 200;
		$data['message'] = "Membership plan canceled successfully!";
		#Notify the user
		$user = new users();
		$user_data = $user->getUserDataById($user_id);
		$notif   = new notify();
		$re_data = array(
			'notifier_id' => $me['user_id'],
			'recipient_id' => $user_id,
			'type' => 'membership_canceled',
			'url' => $site_url.'/'.$user_data->username,
			'time' => time()
			);
		$notif->notify($re_data);
	} else {
		$data['status'] = 400;
		$data['error'] = "Failed to cancel Membership!";
	}
}

elseif ($action == 'send-code' && !empty($_POST['id']) && is_numeric($_POST['id'])) {

	$cpanel    = new cpanel();	
	$user_id = $user::secure($_POST['id']);
	$email_code = rand(100000,999999);

	$cpanel::$db->where('user_id',$user_id);
	$update = $cpanel::$db->update(T_USERS,array('ac_code' => $email_code));

	$user = new users();
	$user_data = $user->getUserDataById($user_id);

	if ($config['email_validation'] == 'on') {
		$message_body = $config['block_email_code'];
		$message_body = str_replace(
			array("{{NAME}}", "{{DESCRIPTION}}", "{{SITE_NAME}}", "{{EMAIL_CODE}}"),
			array($user_data->username, $config['email_code_description'], $config['site_name'], $email_code),
		$message_body 
		);
		$email_data = array(
			'from_email' => $config['noreply_email'],
			'from_name' => $config['site_name'],
			'to_email' => aura::secure($user_data->email),
			'to_name' => aura::secure($user_data->username),
			'subject' => '📢 Account Not Activated',
			'charSet' => 'UTF-8',
			'message_body' => $message_body,
			'is_html' => true
		);
		$send_message = aura::sendMail($email_data);
		if ($send_message) {
			$data['status'] = 200;
			$data['message'] = "Activation code sent successfully!";
		} else {
			$data['status'] = 400;
			$data['error'] = "Failed to send code to this user!";
		}
	}
}

elseif ($action == 'activate-ad' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	$user      = new users();
	$media     = new Media();
	$cpanel    = new cpanel();	
	$ad_id     = $user::secure($_POST['id']);

	$ad        = $user->GetAdByID($ad_id);
	$photo     = $ad->ad_media;
	$thumb     = media($photo);
	$user_id   = $ad->user_id;

	$cpanel::$db->where('id',$ad_id);
	$update = $cpanel::$db->update(T_ADS,array('status' => '1'));
	
	if ($update) {
		$data['status'] = 200;
		$data['message'] = "Campaign activated successfully!";

		$notif   = new notify();
		$re_data = array(
			'notifier_id' => $me['user_id'],
			'recipient_id' => $user_id,
			'type' => 'we_approved_ur_campaign',
			'url'  =>  $config['site_url'].'/campaign',
			'time' => time(),
			'file' => $thumb
			);
		$notif->notify($re_data);
	} else {
		$data['status'] = 400;
		$data['error'] = "Failed to activate this ad. Try later!";
	}
}

elseif ($action == 'deactivate-ad' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	$user      = new users();
	$media     = new Media();
	$cpanel    = new cpanel();	
	$ad_id     = $user::secure($_POST['id']);

	$ad        = $user->GetAdByID($ad_id);
	$photo     = $ad->ad_media;
	$thumb     = media($photo);
	$user_id   = $ad->user_id;

	$cpanel::$db->where('id',$ad_id);
	$update = $cpanel::$db->update(T_ADS,array('status' => '0'));
	
	if ($update) {
		$data['status'] = 200;
		$data['message'] = "Campaign deactivated successfully!";

		$notif   = new notify();
		$re_data = array(
			'notifier_id' => $me['user_id'],
			'recipient_id' => $user_id,
			'type' => 'we_deactivated_ur_campaign',
			'url'  =>  $config['site_url'].'/campaign',
			'time' => time(),
			'file' => $thumb
			);
		$notif->notify($re_data);
	} else {
		$data['status'] = 400;
		$data['error'] = "Failed to deactivate this ad. Try later!";
	}
}

elseif ($action == 'update_multi_users') {
    if (!empty($_POST['ids']) && !empty($_POST['type']) && in_array($_POST['type'], array('Activate','Deactivate','Pro','Free', 'Business'))) {
        foreach ($_POST['ids'] as $key => $value) {
            if (is_numeric($value) && $value > 0) {
            	$value = $user::secure($value);
				if ($_POST['type'] == 'Free') {
                	$db->where('user_id', $value);
                    $update_data = array('business_account' => 0, 'is_pro' => 0,'pro_type' => 0,'pro_time' => 0,'banner_key' => 0,'switch' => 0,'can_verify' => 0,'update_username' => 0);
                    $update = $db->update(T_USERS, $update_data);
                }elseif ($_POST['type'] == 'Pro') {
                	$db->where('user_id', $value);
                    $update_data = array('business_account' => 1, 'is_pro' => 1,' pro_type' => 2, 'pro_time' => time(), 'banner_key' => 1, 'switch' => 1, 'can_verify' => 1, 'business_account' => 1, 'update_username' => 1, 'username_timer' => null);
                    $update = $db->update(T_USERS, $update_data);
                }elseif ($_POST['type'] == 'Business') {
                	$db->where('user_id', $value);
                    $update_data = array('business_account' => 1);
                    $update = $db->update(T_USERS, $update_data);
                }elseif ($_POST['type'] == 'Activate') {
                    $db->where('user_id', $value);
                    $update_data = array('active' => '1','email_code' => '');
                    $update = $db->update(T_USERS, $update_data);
                }elseif ($_POST['type'] == 'Deactivate') {
                    $db->where('user_id', $value);
                    $update_data = array('active' => 2,'email_code' => '');
                    $update = $db->update(T_USERS, $update_data);
                }
            }
        }
        $data = ['status' => 200];
    }
}

elseif ($action == 'update-post_reports') {
    if (!empty($_POST['ids']) && !empty($_POST['type']) && in_array($_POST['type'], array('mark_safe','delete_post'))) {
        foreach ($_POST['ids'] as $key => $value) {
            if (is_numeric($value) && $value > 0) {
            	$value = $user::secure($value);
            	$report = $cpanel::$db->where('id', $value)->getOne(T_POST_REPORTS);
                if ($_POST['type'] == 'delete_post') {
                	$posts   = new posts();
					$delete  = $posts->setPostId($report->post_id)->deletePost();
                }
                elseif ($_POST['type'] == 'mark_safe') {
					$cpanel  = new cpanel();
					$delete  = $cpanel::$db->where('id',$value)->delete(T_POST_REPORTS);
                }
            }
        }
        $data = ['status' => 200];
    }
}

elseif ($action == 'update-profile_reports') {
    if (!empty($_POST['ids']) && !empty($_POST['type']) && in_array($_POST['type'], array('mark_safe','deactivate'))) {
        foreach ($_POST['ids'] as $key => $value) {
            if (is_numeric($value) && $value > 0) {
            	$value = $user::secure($value);
            	$report = $cpanel::$db->where('id', $value)->getOne(T_USER_REPORTS);
                if ($_POST['type'] == 'deactivate') {
					$db->where('user_id', $report->profile_id);
                    $update_data = array('active' => 2,'email_code' => '');
                    $update = $db->update(T_USERS, $update_data);
					$cpanel   = new cpanel();
					$delete  = $cpanel::$db->where('id',$value)->delete(T_USER_REPORTS);
                }elseif ($_POST['type'] == 'mark_safe') {
					$cpanel   = new cpanel();
					$delete  = $cpanel::$db->where('id',$value)->delete(T_USER_REPORTS);
                }
            }
        }
        $data = ['status' => 200];
    }
}

elseif ($action == 'rep-off-account' && !empty($_POST['id']) && is_numeric($_POST['id'])) {

	$cpanel    = new cpanel();	
	$user_id = $user::secure($_POST['id']);
	$rep_id  = $user::secure($_POST['rep_id']);

	$cpanel::$db->where('user_id',$user_id);
	$update = $cpanel::$db->update(T_USERS,array('active' => '2','email_code' => ''));
	$delete  = $cpanel::$db->where('id',$rep_id)->delete(T_USER_REPORTS);

	if ($update) {
		$data['status'] = 200;
		$data['message'] = "Account has been Deactivated!";
	} else {
		$data['status'] = 400;
		$data['error'] = "Failed to deactivate this user. Try later!";
	}
}

elseif ($action == 'delete-user' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	$user_id = $user::secure($_POST['id']);
	$delete  = $user->setUserById($user_id)->delete();
	$data    = array('status' => 304);
	
	if ($delete) {
		$data['status'] = 200;
	} else {
		$data['status'] = 400;
	}
}
elseif ($action == 'delete-post' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	$post_id = $user::secure($_POST['id']);
	$posts   = new posts();
	$delete  = $posts->setPostId($post_id)->deletePost();
	$data    = array('status' => 304);

	if ($delete) {
		$data['status'] = 200;
	}
}
elseif ($action == 'delete-multi-post' && !empty($_POST['ids'])) {
	$data    = array('status' => 304);
	foreach ($_POST['ids'] as $key => $id) {
        $post_id = $user::secure($id);
		$posts   = new posts();
		$delete  = $posts->setPostId($post_id)->deletePost();
    }
	if ($delete) {
		$data['status'] = 200;
	}
}
elseif ($action == 'delete-story' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	$data    = array('status' => 304);
	$story_id = $user::secure($_POST['id']);

	$cpanel    = new cpanel();
	$cpanel::$db->where('id',$story_id);
	$cpanel::$db->delete(T_STORY);

	$data['status'] = 200;
}
elseif ($action == 'delete-multi-story' && !empty($_POST['ids'])) {
	$data    = array('status' => 304);
	foreach ($_POST['ids'] as $key => $id) {
        $story_id = $user::secure($id);

		$cpanel    = new cpanel();
		$cpanel::$db->where('id',$story_id);
		$cpanel::$db->delete(T_STORY);
    }
	$data['status'] = 200;
}
elseif ($action == 'delete-ad' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	$ad_id = $user::secure($_POST['id']);
	$data    = array('status' => 304);
	$user = new users();
	$media = new Media();
	
	$ad = $user->GetAdByID($ad_id);
	if (!empty($ad)) {
		$db->where('id',$ad->id)->delete(T_ADS);
		$photo_file = $ad->ad_media;
		if (file_exists($photo_file)) {
            @unlink(trim($photo_file));
        }
        else if($config['amazone_s3'] == 1 || $config['ftp_upload'] == 1 || $config['google_cloud_storage'] == 1){
            $media->deleteFromFTPorS3($photo_file);
        }
		$data['status'] = 200;
		$notif   = new notify();
		$re_data = array(
			'notifier_id' => $me['user_id'],
			'recipient_id' => $ad->user_id,
			'type' => 'ad_deleted',
			'url' => $site_url.'/campaign',
			'time' => time()
		);
		$notif->notify($re_data);
	}
}

elseif ($action == 'remove_multi_ban') {
    if (!empty($_POST['ids'])) {
        foreach ($_POST['ids'] as $key => $value) {
            if (!empty($value) && is_numeric($value) && $value > 0) {
            	$cpanel    = new cpanel();
				$id = $user::secure($value);
				$cpanel::$db->where('id',$id);
				$cpanel::$db->delete(T_BLACKLIST);
            }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'remove_multi_blog_category') {
    if (!empty($_POST['ids'])) {
        foreach ($_POST['ids'] as $key => $value) {
        	if (!empty($value) && in_array($value, array_keys(blog_categories()))) {
		        $db->where('lang_key',aura::secure($value))->delete(T_LANGS);
		    }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'remove_multi_ads') {
    if (!empty($_POST['ids'])) {
        foreach ($_POST['ids'] as $key => $value) {
            if (!empty($value) && is_numeric($value) && $value > 0) {
            	$ad_id = $user::secure($value);
				$user = new users();
				$media = new Media();
				
				$ad = $user->GetAdByID($ad_id);
				if (!empty($ad)) {
					$db->where('id',$ad->id)->delete(T_ADS);
					$photo_file = $ad->ad_media;
					if (file_exists($photo_file)) {
			            @unlink(trim($photo_file));
			        }
			        else if($config['amazone_s3'] == 1 || $config['ftp_upload'] == 1 || $config['google_cloud_storage'] == 1){
			            $media->deleteFromFTPorS3($photo_file);
			        }
				}
            }
        }
        $data = ['status' => 200];
		$notif   = new notify();
		$re_data = array(
			'notifier_id' => $me['user_id'],
			'recipient_id' => $ad->user_id,
			'type' => 'ads_deleted',
			'url' => $site_url.'/campaign',
			'time' => time()
		);
		$notif->notify($re_data);
    }
}
elseif ($action == 'delete-comments' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	$posts   = new posts();
	$c_id    = $user::secure($_POST['id']);
	$co_id   =  $db->where('id',$c_id)->getOne(T_POST_COMMENTS);
	$post_id =  $db->where('id',$c_id)->getValue(T_POST_COMMENTS,'post_id');

	$data['status'] = 304;
	$posts->setUserById($me['user_id']);
	if ($posts->isCommentOwner($id)) {
		$delete = $posts->deletePostComment($c_id);
		$data['status'] = 200;

		$notif   = new notify();
		$re_data = array(
			'notifier_id' => $me['user_id'],
			'recipient_id' => $co_id->user_id,
			'type' => 'comment_deleted',
			'url' =>  $site_url.'/post'.'/'.$post_id,
			'time' => time()
		);
		$notif->notify($re_data);
	}
}
elseif ($action == 'delete-multi-comments' && !empty($_POST['ids'])) {
	$data    = array('status' => 304);
	foreach ($_POST['ids'] as $key => $id) {
        $c_id = $user::secure($id);
		$posts   = new posts();

		$co_id   =  $db->where('id',$c_id)->getOne(T_POST_COMMENTS);
	    $post_id =  $db->where('id',$c_id)->getValue(T_POST_COMMENTS,'post_id');
		$delete = $posts->deletePostComment($c_id);
		$notif   = new notify();
		$re_data = array(
			'notifier_id' => $me['user_id'],
			'recipient_id' => $co_id->user_id,
			'type' => 'comment_deleted',
			'url' =>  $site_url.'/post'.'/'.$post_id,
			'time' => time()
		);
		$notif->notify($re_data);
    }
	if ($delete) {
		$data['status'] = 200;
	}
}
elseif ($action == 'activate-theme' && !empty($_POST['theme'])) {
	$theme   = $user::secure($_POST['theme']);
	$cpanel  = new cpanel();
	$data    = array('status' => 304);
	$update  = $cpanel->updateSettings(array('theme' => $theme));
	if ($update) {
		$data['status'] = 200;
	}
}
elseif ($action == 'delete-report' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	if (!empty($_POST['t']) && is_numeric($_POST['t'])) {
		$rid     = $user::secure($_POST['id']);
		$type    = $user::secure($_POST['t']);
		$cpanel  = new cpanel();
		$table   = ($type == 2) ? T_POST_REPORTS : T_USER_REPORTS;
		$data    = array('status' => 304);
		$delete  = $cpanel::$db->where('id',$rid)->delete($table);
		if ($delete) {
			$data['status'] = 200;
		}
	}
}

elseif ($action == 'system-backup') {
	$error  = false;
	$cpanel  = new cpanel();
	$zip_ex = class_exists('ZipArchive');

	if (empty($zip_ex)) {
		$error = 'ERROR: ZipArchive is not installed on your server';
	}
	else if(empty(is_writable(ROOT))){
		$error = 'ERROR: Permission denied in ' . ROOT . '/backups';
	}
	if (empty($error)) {
		try {
			$backup = $cpanel->createBackup();
			if ($backup == true) {
				$data['status']  = 200;
				$data['message'] = "New site backup has been successfully created";
				$data['time']    = date('Y-m-d h:i:s');
			}
		} 
		catch (Exception $e) {
			$data['status']  = 500;
			$data['message'] = "Something went wrong Please try again later!";
		}
	}else{
		$data['status']  = 500;
		$data['message'] = $error;
	}
}
elseif ($action == 'edit-lang-key') {
	$cpanel  = new cpanel();
	$vl1    = (!empty($_POST['id']) && is_numeric($_POST['id']));
	$vl2    = (!empty($_POST['val']) && is_string($_POST['val']));
	$vl3    = (!empty($_POST['lang']) && in_array($_POST['lang'], array_keys($langs)));
	$vl4    = ($vl1 && $vl2 && $vl3);
	$data   = array(
		'status' => 400,
		'message' => "Something went wrong Please try again later!"
	);

	if ($vl4) {
		$key_id = $cpanel::secure($_POST['id']);
		$key_vl = $cpanel::secure($_POST['val']);
		$lang   = $cpanel::secure($_POST['lang']);

		$cpanel::$db->where('id',$key_id)->update(T_LANGS,array($lang => $key_vl));
		$data['status']  = 200;
		$data['message'] = "Language changes has been updated!";
	}
}
elseif ($action == 'delete-lang') {
	$cpanel  = new cpanel();
	$t_lang = T_LANGS;
	$data   = array(
		'status' => 400,
	);

	if (!empty($_POST['id']) && in_array($_POST['id'], array_keys($langs)) && len(array_keys($langs)) >= 2) {
		$lang = $_POST['id'];
		try {
			@$cpanel::$db->rawQuery("ALTER TABLE `$t_lang` DROP `$lang`");
			$data   = array(
				'status' => 200,
			);
		} 
		catch (Exception $e) {}
	}
}
elseif ($action == 'remove_multi_lang') {
	$cpanel  = new cpanel();
	$t_lang = T_LANGS;
	$data   = array('status' => 400);
	if (!empty($_POST['ids'])) {
        foreach ($_POST['ids'] as $key => $value) {
        	if (!empty($value) && in_array($value, array_keys($langs)) && len(array_keys($langs)) >= 2) {
				$lang = $cpanel::secure($value);
				try {
					@$cpanel::$db->rawQuery("ALTER TABLE `$t_lang` DROP `$lang`");
				} 
				catch (Exception $e) {
					
				}
			}
        }
        $data   = array('status' => 200);
    }			
}


elseif ($action == 'new-lang' && !empty($_POST['lang']) && is_string($_POST['lang'])) {
	$cpanel    = new cpanel();
	$newlang  = strtolower($_POST['lang']);
	$stat     = 400;

	if (len($newlang) > 20) {
		$stat = 401;
	}
	elseif (in_array($newlang, array_keys($langs))) {
		$stat = 402;
	}
	else{
		try {
			$sql      = "ALTER TABLE `langs` ADD `$newlang` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
			$add_lang =  mysqli_query($mysqli,$sql);
		} 

		catch (Exception $e) {}

		if (!empty($add_lang)) {
			$def_items = $cpanel->fetchLanguage();
			$stat      = 200;
			if (!empty($def_items)) {
				foreach ($def_items as $lang_key => $lang_val) {
					$cpanel::$db->where('lang_key',$lang_key);
					$cpanel::$db->update(T_LANGS,array($newlang => $def_items[$lang_key]));
				}
			}
		}
	}

	$data['status'] = $stat;
}
elseif ($action == 'new-key' && !empty($_POST['lang_key']) && is_string($_POST['lang_key'])) {
	$cpanel    = new cpanel();
	$lang_key = strtolower($_POST['lang_key']);
	$stat     = 400;

	if (preg_match('/[^a-z0-9_]/', $lang_key)) {
		$stat = 401;
	}
	else if(len($lang_key) > 100){
		$stat = 402;
	}
	else if(in_array($lang_key, array_keys($lang))){
		$stat = 403;
	}
	else{
		$stat = 200;
		$cpanel::$db->insert(T_LANGS,array('lang_key' => $lang_key));
	}

	$data['status'] = $stat;
}

elseif ($action == 'test_s3_2'){
	try {
		require_once('core/zipy/s3/vendor/autoload.php');
		$s3Client = S3Client::factory(array(
			'version' => 'latest',
			'region' => $config['region_2'],
			'credentials' => array(
				'key' => $config['amazone_s3_key_2'],
				'secret' => $config['amazone_s3_s_key_2']
			)
		));
		$buckets  = $s3Client->listBuckets();
		$result = $s3Client->putBucketCors([
			'Bucket' => $config['bucket_name_2'], // REQUIRED
			'CORSConfiguration' => [ // REQUIRED
				'CORSRules' => [ // REQUIRED
					[
						'AllowedHeaders' => ['Authorization'],
						'AllowedMethods' => ['POST', 'GET', 'PUT'], // REQUIRED
						'AllowedOrigins' => ['*'], // REQUIRED
						'ExposeHeaders' => [],
						'MaxAgeSeconds' => 3000
					],
				],
			]
		]);

		if (!empty($buckets)) {
			if ($s3Client->doesBucketExist($config['bucket_name_2'])) {
				$stat = 200;
				$array          = array(
					'media/img/d-avatar.jpg',
					'media/img/story-bg.jpg',
					'media/img/user-m.png'
				);
				$media = new Media();
				foreach ($array as $key => $value) {
					$upload = $media->uploadToS3($value, false);
				}
			} else {
				$stat = 300;
			}
		} else {
			$stat = 500;
		}
	}catch (Exception $e) {
		$stat  = 400;
		$data['message'] = $e->getMessage();
	}
	$data['status'] = $stat;
}

elseif ($action == 'test_spaces') {
	try {
            $key        = $config['digital_ocean_key'];
            $secret     = $config['digital_ocean_s_key'];
            $space_name = $config['digital_ocean_space_name'];
            $region     = $config['digital_ocean_region'];
            $space      = new SpacesConnect($key, $secret, $space_name, $region);
            $buckets    = $space->ListSpaces();
            $result     = $space->PutCORS(array(
                'AllowedHeaders' => array(
                    'Authorization'
                ),
                'AllowedMethods' => array(
                    'POST',
                    'GET',
                    'PUT'
                ), // REQUIRED
                'AllowedOrigins' => array(
                    '*'
                ), // REQUIRED
                'ExposeHeaders' => array(),
                'MaxAgeSeconds' => 3000
            ));
            if (!empty($buckets)) {
                if (!empty($space->GetSpaceName())) {
                    $data['status'] = 200;
                    $array          = array(
                        'media/img/d-avatar.jpg',
						'media/img/story-bg.jpg',
						'media/img/user-m.png'
                    );
                    $media = new Media();
					foreach ($array as $key => $value) {
						$upload = $media->uploadToS3($value, false);
					}
                } else {
                    $data['status'] = 300;
                }
            } else {
                $data['status'] = 500;
            }
        }
        catch (Exception $e) {
            $data['status']  = 400;
            $data['message'] = $e->getMessage();
        }
}
elseif ($action == 'test_cloud') {
	if ($config['google_cloud_storage'] == 0 || empty($config['google_cloud_storage_service_account']) || empty($config['google_cloud_storage_bucket_name'])) {
        $data['message'] = 'Please enable Google Cloud Storage and fill all the required fields!';
    }
    elseif (!file_exists($config['google_cloud_storage_service_account'])) {
        $data['message'] = 'Google Cloud File not found on the fumigram server. Please upload it to the server!';
    }
    else{
        try {
			require_once('core/zipy/google-storage/autoload.php');
            $storage = new StorageClient(['keyFile' => json_decode($config['google_cloud_storage_service_account'], true)]);
            // set which bucket to work in
            $bucket = $storage->bucket($config['google_cloud_storage_bucket_name']);
            if ($bucket) {
                $array          = array('media/img/d-avatar.jpg','media/img/story-bg.jpg','media/img/user-m.png');
                foreach ($array as $key => $value) {
                    $fileContent = file_get_contents($value);

                    // upload/replace file 
                    $storageObject = $bucket->upload($fileContent,['name' => $value]);
                }

                $data['status'] = 200;
            }
            else{
                $data['message'] = 'Error in connection';
            }
        } catch (Exception $e) {
            $data['message'] = "".$e;
        }
    }
}
elseif ($action == 'test_s3'){
	try {
		require_once('core/zipy/s3/vendor/autoload.php');
		$s3Client = S3Client::factory(array(
			'version' => 'latest',
			'region' => $config['region'],
			'credentials' => array('key' => $config['amazone_s3_key'],'secret' => $config['amazone_s3_s_key'])
		));
		$buckets  = $s3Client->listBuckets();
		$result = $s3Client->putBucketCors([
			'Bucket' => $config['bucket_name'], // REQUIRED
			'CORSConfiguration' => [ // REQUIRED
				'CORSRules' => [ // REQUIRED
					[
						'AllowedHeaders' => ['Authorization'],
						'AllowedMethods' => ['POST', 'GET', 'PUT'], // REQUIRED
						'AllowedOrigins' => ['*'], // REQUIRED
						'ExposeHeaders' => [],
						'MaxAgeSeconds' => 3000
					],
				],
			]
		]);

		if (!empty($buckets)) {
			if ($s3Client->doesBucketExist($config['bucket_name'])) {
				$stat = 200;
				$array          = array(
					'media/img/d-avatar.jpg',
					'media/img/story-bg.jpg',
					'media/img/user-female.png'
				);
				$media = new Media();
				foreach ($array as $key => $value) {
					$upload = $media->uploadToS3($value, false);
				}
			} else {
				$stat = 300;
			}
		} else {
			$stat = 500;
		}
	}
	catch (Exception $e) {
		$stat  = 400;
		$data['message'] = $e->getMessage();
	}
	$data['status'] = $stat;
	
} elseif ($action == 'test_ftp') {
	try {
		require_once('core/zipy/ftp/vendor/autoload.php');
		$ftp = new \FtpClient\FtpClient();
		$ftp->connect($config['ftp_host'], false, $config['ftp_port']);
		$login = $ftp->login($config['ftp_username'], $config['ftp_password']);
	    $array = array('media/img/d-cover.jpg','media/img/d-avatar.jpg','media/img/story-bg.jpg','media/img/user-male.png','media/img/user-female.png');
        $media = new Media();
        foreach ($array as $key => $value) {
            $upload = $media->uploadToFtp($value,false);
        }
		$stat  = 200;
	} catch (Exception $e) {
		$stat  = 400;
		$data['message'] = $e->getMessage();
	}
	$data['status'] = $stat;
}
elseif ($action == 'reset_server_key') {
	$app_key    = sha1(rand(111111111, 999999999)) . '-' . md5(microtime()) . '-' . rand(11111111, 99999999);
    $data_array = array(
        'server_key' => $app_key
    );
    $cpanel  = new cpanel();
	$query  = $cpanel->updateSettings($data_array);
	$data['status']  = 200;
    $data['app_key'] = $app_key;
}
elseif ($action == 'multi_verification') {
    if (!empty($_POST['ids']) && !empty($_POST['type']) && in_array($_POST['type'], array('Approve','Decline'))) {
        foreach ($_POST['ids'] as $key => $value) {
            if (is_numeric($value) && $value > 0) {
            	$id = $user::secure($value);
                $request = $db->where('id', $id)->getOne(T_VERIFY);
                if ($_POST['type'] == 'Decline') {
					$cpanel  = new cpanel();
                	$cpanel::$db->where('id',$id);
					$cpanel::$db->delete(T_VERIFY);
					#Notify the user
					$notif   = new notify();
					$re_data = array(
						'notifier_id' => $me['user_id'],
						'recipient_id' => $request->user_id,
						'type' => 'verification_decline',
						'url'  =>  $site_url.'/settings/verification/'.$user_data->username,
						'time' => time(),
						'ajax_url' => 'load.php?app=settings&apph=settings&user='.$user_data->username.'&page=verification'
					);
					$notif->notify($re_data);
                }
                elseif ($_POST['type'] == 'Approve') {
					$cpanel  = new cpanel();
                	$cpanel::$db->where('user_id',$request->user_id);
					$cpanel::$db->update(T_USERS,array('verified' => 1));
					$cpanel::$db->where('id',$id);
					$cpanel::$db->delete(T_VERIFY);
					#Notify the user
					$user = new users();
					$user_data = $user->getUserDataById($request->user_id);
					$notif   = new notify();
					$re_data = array(
						'notifier_id' => $me['user_id'],
						'recipient_id' => $request->user_id,
						'type' => 'verification_approved',
						'url'  =>  $site_url.'/settings/verification/'.$user_data->username,
						'time' => time(),
						'ajax_url' => 'load.php?app=settings&apph=settings&user='.$user_data->username.'&page=verification'
						);
					$notif->notify($re_data);
					
					#Reward points to the user
					RecordAction('profile_verified', array('user_id' => $request->user_id));
                }
            }
        }
        $data = ['status' => 200];
	}
}
elseif ($action == 'delete_v_request' && !empty($_POST['id'])) {
	$stat = 200;
	$cpanel    = new cpanel();
	$id = $user::secure($_POST['id']);
	$cpanel::$db->where('id',$id);
	$request = $cpanel::$db->getOne(T_VERIFY);
	if (!empty($request)) {
		$cpanel::$db->where('id',$id);
		$cpanel::$db->delete(T_VERIFY);
		$user = new users();
		$user_data = $user->getUserDataById($request->user_id);
		#Notify the user
		$notif   = new notify();
        $re_data = array(
			'notifier_id' => $me['user_id'],
			'recipient_id' => $request->user_id,
			'type' => 'verification_decline',
			'url'  =>  $site_url.'/settings/verification/'.$user_data->username,
			'time' => time(),
			'ajax_url' => 'load.php?app=settings&apph=settings&user='.$user_data->username.'&page=verification'
		);
        $notif->notify($re_data);
	}
	$data['status'] = $stat;
	$data['message'] = "Verification request was declined";
}
elseif ($action == 'accept_v_request' && !empty($_POST['id'])) {
	$stat = 200;
	$cpanel    = new cpanel();
	$id = $user::secure($_POST['id']);
	$cpanel::$db->where('id',$id);
	$request = $cpanel::$db->getOne(T_VERIFY);
	if (!empty($request)) {
		$cpanel::$db->where('user_id',$request->user_id);
		$cpanel::$db->update(T_USERS,array('verified' => 1));

		$cpanel::$db->where('id',$id);
		$cpanel::$db->delete(T_VERIFY);
		
		$user = new users();
		$user_data = $user->getUserDataById($request->user_id);

		#Notify the user
		$notif   = new notify();
		$re_data = array(
			'notifier_id' => $me['user_id'],
			'recipient_id' => $request->user_id,
			'type' => 'verification_approved',
			'url'  =>  $site_url.'/settings/verification/'.$user_data->username,
			'time' => time(),
			'ajax_url' => 'load.php?app=settings&apph=settings&user='.$user_data->username.'&page=verification'
			);
		$notif->notify($re_data);
		
		#Reward points to the user
	    RecordAction('profile_verified', array('user_id' => $user_data->user_id));
	}
	$data['status'] = $stat;
	$data['message'] = "Verification request was approved";
}

elseif ($action == 'add_ban' && !empty($_POST['value'])) {
	$cpanel    = new cpanel();
	$value = $user::secure($_POST['value']);
	$cpanel::$db->insert(T_BLACKLIST,array('value' => $value, 'time'  => time()));
	$data['status'] = 200;
}
elseif ($action == 'delete-ban' && !empty($_POST['id'])) {
	$cpanel    = new cpanel();
	$id = $user::secure($_POST['id']);
	$cpanel::$db->where('id',$id);
	$cpanel::$db->delete(T_BLACKLIST);
	$data['status'] = 200;
}

elseif ($action == 'update-design') {
	$data['status'] = 200;
	$cpanel    = new cpanel();
	if (isset($_FILES['logo']['name'])) {
        $fileInfo = array(
            'file' => $_FILES["logo"]["tmp_name"],
            'name' => $_FILES['logo']['name'],
            'size' => $_FILES["logo"]["size"]
        );
        $media    = $cpanel->UploadLogo($fileInfo);
    }
    if (isset($_FILES['favicon']['name'])) {
        $fileInfo = array(
            'file' => $_FILES["favicon"]["tmp_name"],
            'name' => $_FILES['favicon']['name'],
            'size' => $_FILES["favicon"]["size"]
        );
        $media    = $cpanel->UploadLogo($fileInfo,'fav');
    }
    if (isset($_FILES['light-logo']['name'])) {
        $fileInfo = array(
            'file' => $_FILES["light-logo"]["tmp_name"],
            'name' => $_FILES['light-logo']['name'],
            'size' => $_FILES["light-logo"]["size"]
        );
        $media    = $cpanel->UploadLogo($fileInfo, 'logo-light');
	}
	if(isset($_POST['site_display_mode'])){
		$update = array();
		$update['site_display_mode'] = $_POST['site_display_mode'];
		$query  = $cpanel->updateSettings($update);
	}

}

elseif ($action == 'cpanel-design') {
	$data['status'] = 200;
	$cpanel    = new cpanel();
	if (isset($_FILES['logo-day']['name'])) {
        $fileInfo = array(
            'file' => $_FILES["logo-day"]["tmp_name"],
            'name' => $_FILES['logo-day']['name'],
            'size' => $_FILES["logo-day"]["size"]
        );
        $media    = $cpanel->CpanelLogo($fileInfo, 'logo-day');
    }
    if (isset($_FILES['logo-night']['name'])) {
        $fileInfo = array(
            'file' => $_FILES["logo-night"]["tmp_name"],
            'name' => $_FILES['logo-night']['name'],
            'size' => $_FILES["logo-night"]["size"]
        );
        $media    = $cpanel->CpanelLogo($fileInfo, 'logo-night');
	}
}

elseif ($action == 'add_new_category') {
    $insert_data = array();
    $insert_data['ref'] = 'blog_categories';
    $add = false;
    foreach (LangsNamesFromDB() as $key_) {
        if (!empty($_POST[$key_])) {
            $insert_data[$key_] = aura::secure($_POST[$key_]);
            $add = true;
        }
    }
    if ($add == true) {
        $id = $db->insert(T_LANGS, $insert_data);
        $db->where('id', $id)->update(T_LANGS, array('lang_key' => $id));
        $data['status'] = 200;
		$data['message'] = 'Category added successfully';
    } else {
        $data['status'] = 400;
        $data['message'] = 'please check details';
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'get_lang_key') {
	$cpanel  = new cpanel();
    $html  = '';
    $langs = GetLangDetails($_GET['id']);
    if (count($langs) > 0) {
        foreach ($langs as $key => $langs) {
            foreach ($langs as $key_ => $lang_vlaue) {
                $context['lang'] = array();
                $is_editale = 0;
                if ($_GET['lang_name'] == $key_) {
                    $is_editale = 1;
                }
                $context['lang'] = array('key_' => $key_, 'is_editale' => $is_editale, 'lang_vlaue' => $lang_vlaue);
                $html .= $cpanel->Senction('edit-language/form-list');
            }
        }
    } else {
        $html = "<h4>Keyword not found</h4>";
    }
    $data['status'] = 200;
    $data['html']   = $html;
}
elseif ($action == 'update_lang_key') {
    $array_langs = array();
    $lang_key    = aura::secure($_POST['id_of_key']);
    $langs       = LangsNamesFromDB();
    foreach ($_POST as $key => $value) {
        if (in_array($key, $langs)) {
            $key   = aura::secure($key);
            $value = aura::secure($value);
            $query = mysqli_query($sqlConnect, "UPDATE `".T_LANGS."` SET `{$key}` = '{$value}' WHERE `lang_key` = '{$lang_key}'");
            if ($query) {
                $data['status'] = 200;
                $_SESSION['language_changed'] = true;
            }
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'delete_category') {
    header("Content-type: application/json");
    if (!empty($_GET['key']) && in_array($_GET['key'], array_keys(blog_categories()))) {
        $db->where('lang_key',aura::secure($_GET['key']))->delete(T_LANGS);
        $data['status'] = 200;
    }
    echo json_encode($data);
    exit();
}

elseif ($action == 'exchange'){
	if ($config['exchange_update'] < time()) {
        $exchange= $cpanel->curlConnect("https://api.exchangerate.host/latest?base=".$config['currency']."&symbols=".implode(",", array_values($config['currency_array'])));
        if (!empty($exchange) && $exchange['success'] == true && !empty($exchange['rates'])) {
        	$cpanel->updateSettings(array('exchange' => json_encode($exchange['rates']),'exchange_update' => (time() + (60 * 60 * 12))));
        }
    }
    $data = array('status' => 200);
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'add_new_curreny'){
	if (!empty($_POST['currency']) && !empty($_POST['currency_symbol'])) {
        $config['currency_array'][] = aura::secure($_POST['currency']);
        $config['currency_symbol_array'][aura::secure($_POST['currency'])] = aura::secure($_POST['currency_symbol']);
        $cpanel->updateSettings(array('currency_array' => json_encode($config['currency_array']),'currency_symbol_array' => json_encode($config['currency_symbol_array'])));
        $exchange= $cpanel->curlConnect("https://api.exchangerate.host/latest?base=".$config['currency']."&symbols=".implode(",", array_values($config['currency_array'])));
        if (!empty($exchange) && $exchange['success'] == true && !empty($exchange['rates'])) {
            $cpanel->updateSettings(array('exchange' => json_encode($exchange['rates']),'exchange_update' => (time() + (60 * 60 * 12))));
        }
    }
    $data = array('status' => 200);
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'select_currency'){
	if (!empty($_POST['currency']) && in_array($_POST['currency'], $config['currency_array'])) {
        $currency = aura::secure($_POST['currency']);
        $update_array = array('currency' => $currency);
        if (in_array($_POST['currency'], $config['stripe_currency_array'])) {
        	$update_array['stripe_currency'] = $currency;
        } if (in_array($_POST['currency'], $config['paypal_currency_array'])) {
        	$update_array['paypal_currency'] = $currency;
        } if (in_array($_POST['currency'], $config['2checkout_currency_array'])) {
        	$update_array['checkout_currency'] = $currency;
        } if (in_array($_POST['currency'], $config['paystack_currency_array'])) {
        	$update_array['paystack_currency'] = $currency;
        } if (in_array($_POST['currency'], $config['iyzipay_currency_array'])) {
        	$update_array['iyzipay_currency'] = $currency;
        }
        $cpanel->updateSettings($update_array);
        $exchange= $cpanel->curlConnect("https://api.exchangerate.host/latest?base=".$currency."&symbols=".implode(",", array_values($config['currency_array'])));
        if (!empty($exchange) && $exchange['success'] == true && !empty($exchange['rates'])) {
            $cpanel->updateSettings(array('exchange' => json_encode($exchange['rates']),'exchange_update' => (time() + (60 * 60 * 12))));
        }
    }
    $data = array('status' => 200);
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'edit_curreny'){
	if (!empty($_POST['currency']) && !empty($_POST['currency_symbol']) && in_array($_POST['currency_id'], array_keys($config['currency_array']))) {
        $config['currency_array'][$_POST['currency_id']] = aura::secure($_POST['currency']);
        $config['currency_symbol_array'][aura::secure($_POST['currency'])] = aura::secure($_POST['currency_symbol']);
        $cpanel->updateSettings(array('currency_array' => json_encode($config['currency_array']),'currency_symbol_array' => json_encode($config['currency_symbol_array'])));
        $exchange= $cpanel->curlConnect("https://api.exchangerate.host/latest?base=".$config['currency']."&symbols=".implode(",", array_values($config['currency_array'])));
        if (!empty($exchange) && $exchange['success'] == true && !empty($exchange['rates'])) {
            $cpanel->updateSettings(array('exchange' => json_encode($exchange['rates']),'exchange_update' => (time() + (60 * 60 * 12))));
        }
    }
    $data = array('status' => 200);
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}

elseif ($action == 'remove__curreny'){
	if (!empty($_POST['currency'])) {
        if (in_array($_POST['currency'], $config['currency_array'])) {
            foreach ($config['currency_array'] as $key => $currency) {
                if ($currency == $_POST['currency']) {
                    if (in_array($currency,array_keys($config['currency_symbol_array']))) {
                        unset($config['currency_symbol_array'][$currency]);
                    }
                    unset($config['currency_array'][$key]);
                }
            }
            if ($config['currency'] == $_POST['currency']) {
                if (!empty($config['currency_array'])) {
                    $cpanel->updateSettings(array('currency' => reset($config['currency_array'])));
                }
            }
            $cpanel->updateSettings(array('currency_array' => json_encode($config['currency_array']),'currency_symbol_array' => json_encode($config['currency_symbol_array'])));
        }
    }
    $data = array('status' => 200);
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}

elseif ($action == 'details' && !empty($_POST)) {
	$error   = false;
	$update  = array();	
	$userid  = $me['user_id'];
	$udata   = $db->where('user_id',$userid)->getOne(T_USERS);
	$me      = $udata;
	
	if (empty($udata)) {
		$data['error'] = "This account does not exist on the system";
	}
	if (!empty($_POST['username'])) {
		if ($me->username != $_POST['username']) {
			if (users::userNameExists($_POST['username'])) {
				$error = "That username is already taken";
			}	
		}
		if(strlen($_POST['username']) < 4 || strlen($_POST['username']) > 32){
			$error = "Username must be between 4 and 16 characters in length";
		}
		if(preg_match('/[^\w]+/', $_POST['username'])){
			$error = "Username contains invalid characters";
		}
	}
	if (!empty($_POST['email'])) {
		if($me->email != $_POST['email']){
			if (users::userEmailExists($_POST['email'])) {
				$error = "That email already exists";
			}
		}
		if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
			$error = "E-mail contains invalid characters";
		}
	}
	
	if (empty($error)) {
		if (!empty($_POST['fname'])) {
			$up_data = array('fname' => aura::secure($_POST['fname']));
		}
		if (!empty($_POST['lname'])) {
			$up_data = array('lname' => aura::secure($_POST['lname']));
		}
		if (!empty($_POST['b_phone'])) {
			$up_data = array('lname' => aura::secure($_POST['b_phone']));
		}
		if (!empty($_POST['about'])) {
			$up_data = array('about' => aura::secure($_POST['about']));
		}
		if (!empty($_POST['b_phone'])) {
			$up_data = array('b_phone' => aura::secure($_POST['b_phone']));
		}
		if (!empty($_POST['profile'])) {
			$up_data = array('profile' => aura::secure($_POST['profile']));
		}
		$update  = $user->updateStatic($me->user_id,$up_data);
		if ($update == true) {
			$data['status'] = 200;
			$data['message'] = "Your changes has been successfully saved!";	
		}
	} else {
		$data['status'] = 400;
		$data['message'] = $error;
	}
}

elseif ($action == 'notifications' && !empty($_POST)) {
	$user_id   = $me['user_id'];
	$update    = array();	
	$data      = array('status' => 400);
	if (!empty($_POST['on_like_post'])) {
		$up_data = array('n_on_like' => $_POST['on_like_post']);
	}
	if (!empty($_POST['on_comment_like'])) {
		$up_data = array('n_on_comment_like' => $_POST['on_comment_like']);
	}
	if (!empty($_POST['on_commnet_post'])) {
		$up_data = array('n_on_comment' => $_POST['on_commnet_post']);
	}
	if (!empty($_POST['on_follow'])) {
		$up_data = array('n_on_follow' => $_POST['on_follow']);
	}
	if (!empty($_POST['on_mention'])) {
		$up_data = array('n_on_mention' => $_POST['on_mention']);
	}
	if (!empty($_POST['on_comment_reply'])) {
		$up_data = array('n_on_comment_reply' => $_POST['on_comment_reply']);
	}
	if (!empty($_POST['on_rejected_request'])) {
		$up_data = array('n_on_rejected_request' => $_POST['on_rejected_request']);
	}
	if (!empty($_POST['on_follow_request'])) {
		$up_data = array('n_on_follow_request' => ((!empty($_POST['on_follow_request'])) ? '1' : '0'));
	}
	$update = $user->updateStatic($user_id,$up_data);
	if ($update == true) {
		$data['status']  = 200;
		$data['message'] = "Notification updated successfully!";
	}
}

elseif ($action == 'sitemap') {
	try {
		$sitemap = new Sitemap($site_url);
		$cpanel   = new cpanel();
		$sitemap->setPath('./');
		$sitemap->setFilename('sitemap');

		{ 
			$sitemap->addItem('/home','0.8', 'yearly', 'Never');
			$sitemap->addItem('/login','0.8', 'yearly', 'Never');
			$sitemap->addItem('/signup','0.8', 'yearly', 'Never');
			$sitemap->addItem('/tiles','0.8', 'yearly', 'Never');
			$sitemap->addItem('/explore','0.8', 'yearly', 'Never');
			$sitemap->addItem('/hashtag','0.8', 'yearly', 'Never');
			$sitemap->addItem('/boosts','0.8', 'yearly', 'Never');
			$sitemap->addItem('/points','0.8', 'yearly', 'Never');
			$sitemap->addItem('/tokens','0.8', 'yearly', 'Never');
			$sitemap->addItem('/contact_us','0.8', 'yearly', 'Never');
			$sitemap->addItem('/loose_points','0.8', 'yearly', 'Never');
			$sitemap->addItem('/help/about-us', '0.8', 'yearly', 'Never');
			$sitemap->addItem('/help/terms-of-use', '0.8', 'yearly', 'Never');
			$sitemap->addItem('/help/privacy-policy', '0.8', 'yearly', 'Never');
			$sitemap->addItem('/help/account-delection', '0.8', 'yearly', 'Never');
		}
		
		{   
			$posts = $cpanel::$db->get(T_POSTS,null,array('post_id','time'));
			foreach ($posts as $post) {
				$pid = $post->post_id;
				$sitemap->addItem("/post/$pid", '0.8', 'daily', $post->time);
			}
		}

		$sitemap->createSitemapIndex("$site_url/sitemap/");
		$data['status']  = 200;
		$data['message'] = "New sitemap has been successfully Generated";
		$data['time']    = date('Y-m-d h:i:s');
	} 
	catch (Exception $e) {
		$data['status']  = 500;
		$data['message'] = "ERROR: Permission denied in " . ROOT . '/sitemap/';
	}
}