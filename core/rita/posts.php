<?php
if ($action == 'new-image' && IS_LOGGED && ($config['upload_images'] == 'on')) {
	$data['status'] = 200;
	$data['html']    = $ui->intel('home/templates/home/includes/upload-image');
}
else if ($action == 'new-embed' && IS_LOGGED && ($config['import_videos'] == 'on')) {
	$data['status'] = 200;
	$data['html']    = $ui->intel('home/templates/home/includes/embed-video');
}
else if ($action == 'new-gif' && IS_LOGGED && ($config['import_images'] == 'on')) {
	$data['status'] = 200;
	$data['html']    = $ui->intel('home/templates/home/includes/import-gifs');
}

else if ($action == 'upload-post-images' && IS_LOGGED && ($config['upload_images'] == 'on')) {
	$ext = '';
	$error = '';
	if (!empty($_FILES)) {
		if (!empty($_FILES['images'])) {
			$images  = multiple($_FILES['images']);
		}else{
			$images  = multiple($_FILES['images']);
		}
		if ($config['private_photos'] == 'on' && !empty($_POST['photo_price']) && is_numeric($_POST['photo_price']) && $_POST['photo_price'] > 0) {
			$context['ftp_upload'] = $config['ftp_upload'];
			$context['amazone_s3'] = $config['amazone_s3'];
			$context['google_cloud_storage'] = $config['google_cloud_storage'];
			$context['digital_ocean'] = $config['digital_ocean'];
			aura::$config['ftp_upload'] = 0;
			aura::$config['amazone_s3'] = 0;
			aura::$config['google_cloud_storage'] = 0;
			aura::$config['digital_ocean'] = 0;
		}
		$media   = new Media();
		$posts   = new posts();
		$notif   = new notify();
		$uploads = array();
		$attach  = explode(',', $_POST['attach']);
		$up_size = 0;
		$mx_size = $config['max_upload'];
		foreach ($images as $image){
			$up_size += $image['size'];
		}
		if ($up_size <= $mx_size) {
			if (count($images) <= 10) {
				foreach ($images as $key => $image) {
					if($attach[$key] !== ''){
						if($image['type'] == 'image/webp'){
							$ext = 'image/webp';
						}
						if ($media->isImage($image['tmp_name'])) {
							$file_info = array(
								'file' => $image['tmp_name'],
								'name' => $image['name'],
								'size' => $image['size'],
								'type' => $image['type'],
								'allowed' => 'jpeg,jpg,png,gif',
							);
							$media->setFile($file_info);
							if ($config['private_photos'] == 'on' && !empty($_POST['photo_price']) && is_numeric($_POST['photo_price']) && $_POST['photo_price'] > 0) {
								aura::$config['ftp_upload'] = 0;
								aura::$config['amazone_s3'] = 0;
								aura::$config['digital_ocean'] = 0;
								aura::$config['google_cloud_storage'] = 0;
							}
							$upload = $media->uploadFile();
							if (!empty($upload['filename'])) {
								if ($config['private_photos'] == 'on' && !empty($_POST['photo_price']) && is_numeric($_POST['photo_price']) && $_POST['photo_price'] > 0) {
									$blured_file = BlurUploadedImage($upload['filename']);
									if (!empty($blured_file)) {
										$upload['blured_file'] = $blured_file;
									}
								}
								$uploads[] = $upload; 
							}else{
								$error = $upload['error'];
							}
						}
					}
				}
			}
			if (!empty($uploads)) {
				$re_data = array('user_id' => $me['user_id'],'username' => $me['username'],'time' => time(),'type' => 'image');
				if (!empty($_POST['caption'])) {
					$text = aura::cropText($_POST['caption'],$config['caption_len']);
					$re_data['description'] = $text;
				}
				if ($config['private_photos'] == 'on' && !empty($_POST['photo_price']) && is_numeric($_POST['photo_price']) && $_POST['photo_price'] > 0) {
					$re_data['price'] = aura::secure($_POST['photo_price']);
				}
				$post_id = $posts->insertPost($re_data);
				if (is_numeric($post_id)) {
					foreach ($uploads as $key => $file) {
						$re_data = array('post_id' => $post_id,'file' => $file['filename'],'extra' => $file['name']);
						if (!empty($file['blured_file'])) {
							$re_data['blured_file'] = $file['blured_file'];
						}
						$posts->insertMedia($re_data);
					}
					$posts->setPostId($post_id);
					$post_data = o2array($posts->postData());
					$data['html']    = $ui->intel('home/templates/home/includes/post-image');
					$data['status']  = 200;
					$data['message'] = lang('post_published');
					#Reward points to the user
					RecordAction('upload_image', array('user_id' => $me['user_id']));

					#Notify mentioned users
					$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id),$post_id);
				}
			}else{
				$data['status']  = 400;
				if( !empty($error)){
					$data['message'] = $error;
				} else if(empty($ext)){
					$data['message'] = lang('unknown_error');
				}else{
					$data['message'] = lang('file_is_not_support');
				}
			}
		}
		else{
			$mx_size         = $mx_size;
			$data['status']  = 400;
			$data['message'] = str_replace('{{size}}', $mx_size, lang('max_upload_limit'));
		}
	}
}
else if($action == 'embed-post-video' && IS_LOGGED && ($config['import_videos'] == 'on')) {

	if (!empty($_POST['embed']) && !empty($_POST['video_id']) && !empty($_POST['url'])) {
		$posts    = new posts();
		$embed    = new embed();
		$notif    = new notify();
		$emsrc    = (in_array($_POST['embed'], array('youtube','vimeo','dailymotion','mp4')) === true);
		$id_val   = false;
 
		if ($_POST['embed'] == 'youtube' && preg_match('/^([a-zA-Z0-9_-]{4,15})$/', $_POST['video_id'])) {
			$id_val = true;
		}
		else if($_POST['embed'] == 'vimeo' && preg_match('/^([0-9]+)$/', $_POST['video_id'])){
			$id_val = true;
		}
		else if($_POST['embed'] == 'dailymotion' && preg_match('/^([a-zA-Z0-9_]{4,15})$/', $_POST['video_id'])){
			$id_val = true;
		}
		else if($_POST['embed'] == 'mp4' && !empty($_FILES['thumb']) && $config['mp4_links'] == 'on'){
			$id_val = true;
		}

		if ($emsrc && $id_val) {
			$media  = new Media();
            $url     = ((aura::isUrl($_POST['url'])) ? $_POST['url'] : '');
            $emsrc   = $_POST['embed'];

            $re_data = array(
            	"user_id" => $me['user_id'],
				'username' => $me['username'],
            	"link" => $url,
            	"time" => time(),
            	"type" => $_POST['embed'],
            	"$emsrc" => $_POST['video_id'],
            );

            if (!empty($_POST['caption'])) {
				$text = aura::cropText($_POST['caption'],$config['caption_len']);
				$re_data['description'] = $text;
			}

			if($_POST['embed'] == 'mp4' && !empty($_FILES['thumb'])){
				$media->setFile(array(
					'file' => $_FILES['thumb']['tmp_name'],
					'name' => $_FILES['thumb']['name'],
					'size' => $_FILES['thumb']['size'],
					'type' => $_FILES['thumb']['type'],
					'allowed' => 'jpeg,jpg,png',
					'crop' => array(
						'width' => '600',
						'height' => '400',
					)
				));
				$image = $media->uploadFile();
				if (empty($image['filename'])) {
					$data['status']  = 500;
					$data['message'] = lang('unknown_error');
					goto xhr_exit;
				}
			}

			$post_id = $posts->insertPost($re_data);
			
			if($_POST['embed'] == 'mp4' && !empty($image['filename'])){
				$re_data = array(
					'post_id' => $post_id,
					'file' => $image['filename'],
					'extra' => $image['filename']
				);

				$posts->setPostId($post_id);
				$posts->insertMedia($re_data);
			}
			
			if (is_numeric($post_id)) {
				$em_data = array();
				$re_data = array(
					'post_id' => $post_id,
				);

				try {
	            	$em_data = $embed->fetchVideo($url);
	            } 
	            catch (Exception $e) {
	            	$data['status']  = 500;
					$data['message'] = lang('unknown_error');
					goto xhr_exit;
	            }

            	if (!empty($em_data['images'])) {
            		$re_data['file'] = $em_data['images']['filename'];
            		$re_data['extra'] = $em_data['images']['extra'];
	            	$posts->insertMedia($re_data);
            	}
	            	
				$posts->setPostId($post_id);
				$post_data = o2array($posts->postData());

				$data['html']    = $ui->intel('home/templates/home/includes/post-'.$emsrc);	
				$data['status']  = 200;
				$data['message'] = lang('post_published');

				#Reward points to the user
				RecordAction('embed_videos', array('user_id' => $me['user_id']));

				#Notify mentioned users
				$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id),$post_id);
			}
			else{
				$data['status']  = 500;
				$data['message'] = lang('unknown_error');
			}
		}

		else{
			$data['status']  = 400;
		}
	}
	elseif (!empty($_POST['thumb_url']) && aura::isUrl($_POST['thumb_url'])) {
		$media    = new Media();
		$posts    = new posts();
		$embed    = new embed();
		$notif    = new notify();

		$img = $media->ImportImageAndCrop($_POST['thumb_url']);
		if (!empty($img['filename'])) {
			$re_data = array(
				'user_id' => $me['user_id'],
				'username' => $me['username'],
				'time' => time(),
				'type' => 'fetched',
				'link' => aura::secure($_POST['url'])
			);

			if (!empty($_POST['caption'])) {
				$text = aura::cropText($_POST['caption'],$config['caption_len']);
				$re_data['description'] = $text;
			}
			$post_id = $posts->insertPost($re_data);

			$posts->setPostId($post_id);
			$_data['file'] = $img['filename'];
			$_data['extra'] = $img['extra'];
	        $posts->insertMedia($_data);

			$post_data = o2array($posts->postData());
			$data['html']    = $ui->intel('home/templates/home/includes/post-fetched');
			$data['status']  = 200;
			$data['message'] = lang('post_published');

			#Reward points to the user
			RecordAction('embed_videos', array('user_id' => $me['user_id']));

			#Notify mentioned users
			$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id),$post_id);
		}
		
	}
}

else if($action == 'import-post-gifs' && IS_LOGGED && ($config['import_images'] == 'on')) {
	$media    = new Media();
	if (!empty($_POST['gif_url'])){
		$posts = new posts();
		$notif = new notify();

		if (aura::isUrl($_POST['gif_url'])) {
            $gif_url = urlencode($_POST['gif_url']);
            $img = $media->ImportImageAndCrop($_POST['gif_url'],'gif');
            if (!empty($img['extra'])) {
            	$re_data = array(
	            	"user_id" => $me['user_id'],
					'username' => $me['username'],
	            	"time" => time(),
	            	"type" => 'gif',
	            );

	            if (!empty($_POST['caption'])) {
					$text = aura::cropText($_POST['caption'],$config['caption_len']);
					$re_data['description'] = $text;
				}

				$post_id = $posts->insertPost($re_data);

				if (is_numeric($post_id)) {
					$re_data = array(
						'post_id' => $post_id,
						'file' => $gif_url,
						'extra' => $img['extra']
					);

					$posts->setPostId($post_id);
					$posts->insertMedia($re_data);

					
					$post_data = o2array($posts->postData());

					$data['html']    = $ui->intel('home/templates/home/includes/post-image');	
					$data['status']  = 200;
					$data['message'] = lang('post_published');

					#Reward points to the user
				    RecordAction('import_gifs', array('user_id' => $me['user_id']));

					#Notify mentioned users
					$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id),$post_id);
	            }
	            else{
					$data['status']  = 500;
					$data['message'] = lang('unknown_error');
				}

			}
			else{
				$data['status']  = 500;
				$data['message'] = lang('unknown_error');
			}

		}

		else{
			$data['status']  = 400;
			$data['message'] = lang('unknown_error');
		}
	}
}

else if($action == 'delete-post' && IS_LOGGED) {	
	if (!empty($_POST['post_id']) && is_numeric($_POST['post_id'])) {
		$posts   = new posts();
		$post_id = $_POST['post_id'];
		$data['status']  = 304;
		$data['message'] = lang('unknown_error');

		$posts->setPostId($post_id);
		$posts->setUserById($me['user_id']);

		if ($posts->isPostOwner() || IS_ADMIN) {
			$del = $posts->deletePost();
			if ($del) {
				$data['status']  = 200;
				$data['message'] = lang('post_deleted_success');
				#Remove points from the user
				RecordAction('delete_post', array('user_id' => $me['user_id']));
			}
		}
	}
}

else if($action == 'add-comment' && IS_LOGGED) {
	if (!empty($_POST['post_id']) && is_numeric($_POST['post_id']) && !empty($_POST['text'])) {
		$posts   = new posts();
		$notif   = new notify();
		$post_id = $_POST['post_id'];
		$text    = aura::cropText($_POST['text'],$config['comment_len']);
		$text    = aura::secure($text);
		$data['status'] = 304;

		$posts->setPostId($post_id);
		$posts->setUserById($me['user_id']);

		$post_data = o2array($posts->postData());
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

		$link_regex = '/(http\:\/\/|https\:\/\/|www\.)([^\ ]+)/i';
        $i          = 0;
        preg_match_all($link_regex, $text, $matches);
        foreach ($matches[0] as $match) {
            $match_url = strip_tags($match);
            $syntax    = '[a]' . urlencode($match_url) . '[/a]';
            $text      = str_replace($match, $syntax, $text);
        }

		$re_data = array(
			'text' => $text,
			'time' => time(),
		);

		$insert = $posts->addPostComment($re_data);
		if (!empty($insert)) {
			$comment = $posts->postCommentData($insert);
			if (!empty($comment)) {
				$comment = o2array($comment);
				$data['html']    = $ui->intel('home/templates/home/includes/comments');
				$data['status'] = 200;

				#Notify post owner
				if (!$posts->isPostOwner()) {
					try {
						$posts->setPostId($post_id);
						$post_owner = $posts->getPostOwnerData();
						if (!empty($post_owner)) {
							$notif_conf = $notif->notifSettings($post_owner->user_id,'on_comment');
							if ($notif_conf) {
								if($type == 'tile') {
									$re_data = array(
										'notifier_id' => $me['user_id'],
									    'recipient_id' => $post_owner->user_id,
										'type' => 'commented_ur_post',
										'url' => pid3url($post_id),
										'time' => time(),
										'post_id' => $post_id,
										'ftype' => $type,
										'thumb' => $thumb,
										'file' => $fullpath,
										'comment_text' => $text,
									);
								} else {
									$re_data = array(
										'notifier_id' => $me['user_id'],
									    'recipient_id' => $post_owner->user_id,
										'type' => 'commented_ur_post',
										'url' => pid2url($post_id),
										'time' => time(),
										'post_id' => $post_id,
										'ftype' => $type,
										'thumb' => $thumb,
										'file' => $fullpath,
										'comment_text' => $text,
									);	
								}
								$notif->notify($re_data);
							}
							#Reward points to the user
				            RecordAction('add_comment', array('user_id' => $post_owner->user_id));
						}
					} 
					catch (Exception $e) {}
				}
				#Notify mentioned users
				$notif->notifyMentionedUsers($_POST['text'],pid2url($post_id),$post_id);
			}
		}
	}
}

else if($action == 'delete-comment' && IS_LOGGED) {
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$posts   = new posts();
		$id      = $_POST['id'];
		$data['status'] = 304;
		$posts->setUserById($me['user_id']);
		if ($posts->isCommentOwner($id)) {
			$delete = $posts->deletePostComment($id);
			$data['status'] = 200;
			#Reward points to the user
			RecordAction('delete_comment', array('user_id' => $me['user_id']));
		}
	}
}

else if($action == 'on_mute' && !empty($_POST['post_id'])) {
	$data    = array('status' => 400);
	$post_id = aura::secure($_POST['post_id']);
	$posts   = new posts();
	$stats = $posts->MuteTile($post_id);
	if ($stats == 1) {
		$data = array('status' => 200,'code' => 1);
		$data['turl']    = pid3url($post_id);
		$data['t_ajax']    = "load.php?app=tile&apph=view_tile&pid=$post_id";
		
	}
	elseif($stats == 2){
		$data = array('status' => 200,'code' => 0);
		$data['turl']    = pid3url($post_id);
		$data['t_ajax']    = "load.php?app=tile&apph=view_tile&pid=$post_id";
	}
}

else if($action == 'explore-posts' && IS_LOGGED) {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		$last_id      = aura::secure($_GET['offset']);
		$posts        = new posts();
		$latest_posts = $posts->explorePosts($last_id);
		$context['posts'] = array();
		$data['status'] = 404;
		$data['html']   = "";
		$context['app_name'] = 'explore';

		if (!empty($latest_posts)) {
			$context['posts'] = o2array($latest_posts);
			foreach ($context['posts'] as $key => $post_data) {
				$context['page'] ='explore';
				$data['html']    .= $ui->intel('explore/templates/explore/includes/list');
			}
			$data['status'] = 200;
		}
	}
}

else if($action == 'explore-tiles' && IS_LOGGED) {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		$last_id      = aura::secure($_GET['offset']);
		$posts        = new posts();
		$latest_posts = $posts->exploreTiles($last_id);
		$context['posts'] = array();
		$data['status'] = 404;
		$data['html']   = "";
		$context['app_name'] = 'tiles';
		if (!empty($latest_posts)) {
			$context['posts'] = o2array($latest_posts);
			foreach ($context['posts'] as $key => $post_data) {
				$context['page'] ='tiles';
				$data['html']    .= $ui->intel('explore/templates/explore/includes/list');
			}
			$data['status'] = 200;
		}
	}
}

else if($action == 'explore-tags') {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset']) && !empty($_SESSION['tag_id'])) {
		$last_id = aura::secure($_GET['offset']);
		$htag    = aura::secure($_SESSION['tag_id']);
		$posts   = new posts();
		$latest_posts     = $posts->exploreTags($htag,$last_id);
		$context['posts'] = array();	
		$data['status']   = 404;
		$html             = "";
		$context['app_name'] = 'explore';

		if (!empty($latest_posts)) {
			$context['posts'] = o2array($latest_posts);
			foreach ($context['posts'] as $key => $post_data) {
				$context['page'] ='tags';
				$html   .= $ui->intel('explore/templates/explore/includes/list');
			}
			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}

else if($action == 'load-user-posts') {
	$vl1 = (!empty($_GET['user_id']) && is_numeric($_GET['user_id']));
	$vl2 = (!empty($_GET['offset']) && is_numeric($_GET['offset']));
	if ($vl1 && $vl2) {
		$last_id = aura::secure($_GET['offset']);
		$user_id = aura::secure($_GET['user_id']);
		$posts   = new posts();
		try {
			$ami_blocked = $user->isBlocked($user_id,true);
			if ($user->profilePrivacy($user_id) && empty($ami_blocked)) {
				$posts->setUserById($user_id);
				$user_posts = $posts->getUserPosts($last_id);
			}
			elseif (empty(IS_LOGGED)) {
				$posts->setUserById($user_id);
				$user_posts = $posts->getUserPosts($last_id);
			}
		} 
		catch (Exception $e) {
			goto xhr_exit;
		}
		$context['posts'] = array();
		$data['status'] = 404;
		$data['html']   = "";
		if (!empty($user_posts)) {
			$context['posts'] = o2array($user_posts);
			foreach ($context['posts'] as $key => $post_data) {
				$context['page'] ='posts';
				$data['html']    .= $ui->intel('profile/templates/profile/includes/list');
			}
			$data['status'] = 200;
		}
	}
}
else if($action == 'load-user-tiles') {
	$vl1 = (!empty($_GET['user_id']) && is_numeric($_GET['user_id']));
	$vl2 = (!empty($_GET['offset']) && is_numeric($_GET['offset']));
	if ($vl1 && $vl2) {
		$last_id = aura::secure($_GET['offset']);
		$user_id = aura::secure($_GET['user_id']);
		$posts   = new posts();
		try {
			$ami_blocked = $user->isBlocked($user_id,true);
			if ($user->profilePrivacy($user_id) && empty($ami_blocked)) {
				$posts->setUserById($user_id);
				$user_posts = $posts->getUserTiles($last_id);
			}
			elseif (empty(IS_LOGGED)) {
				$posts->setUserById($user_id);
				$user_posts = $posts->getUserTiles($last_id);
			}
		} catch (Exception $e) {
			goto xhr_exit;
		}
		$context['posts'] = array();
		$data['status'] = 404;
		$data['html']   = "";
		if (!empty($user_posts)) {
			$context['posts'] = o2array($user_posts);
			foreach ($context['posts'] as $key => $post_data) {
				$context['page'] ='posts';
				$data['html']    .= $ui->intel('profile/templates/profile/includes/tiles_list');
			}
			$data['status'] = 200;
		}
	}
}

else if($action == 'load-saved-posts'  && IS_LOGGED) {
	$request = (!empty($_GET['offset']) && is_numeric($_GET['offset']));
	if ($request == true) {
		$last_id = aura::secure($_GET['offset']);
		$posts   = new posts();
		$data['status']   = 404;
		$user_posts       = $posts->getSavedPosts($last_id);
		$html             = "";
		if (!empty($user_posts)) {
			$context['posts'] = o2array($user_posts);
			foreach ($context['posts'] as $key => $post_data) {
				$context['page'] = 'favourites';
				$data['html']    .= $ui->intel('profile/templates/profile/includes/fav');
			}
			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}

else if($action == 'lightbox') {
	if ((!empty($_GET['post_id']) && is_numeric($_GET['post_id']))) {
		$post_id = $_GET['post_id'];
		$page    = (!empty($_GET['page'])) ? $_GET['page'] : false;
		$posts   = new posts();
		$posts->setPostId($post_id);
		$posts->setPostId($post_id)->add_play();
		$post_data = $posts->postData();
		$data['status'] = 404;
		$data['html']   = "";
		if (!empty($post_data) && !empty($page)) {
			$posts->setPostId($post_id);
			if ($page == 'tags' && !empty($_SESSION['tag_id'])) {
				$posts->tag_id = $_SESSION['tag_id'];
			}

			$post_data      = o2array($post_data);
			$thumb          = "";
			$is_following   = false;
			$has_next       = $posts->hasNext($page);
			$has_prev       = $posts->hasPrev($page);
			if (IS_LOGGED) {
				$is_following = $user->isFollowing($post_data['user_id']);
			}
			if (in_array($post_data['type'], array('youtube','dailymotion','vimeo'))) {
				if (!empty($post_data['thumb'])) {
					$thumb = $post_data['thumb'];
				}
			}
            $context['thumb'] = $thumb;
            $context['post_data'] = $post_data;
            $context['is_following'] = $is_following;
            $context['prev'] = $has_prev;
            $context['next'] = $has_next;
            $context['page'] = $page;
            $data['html']    = $ui->intel('main/templates/includes/lightbox');
			$data['status'] = 200;
		}
	}
}

else if($action == 'like' && IS_LOGGED) {
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$post_id = $_POST['id'];
		$posts   = new posts();
		$data    = array('status' => 304);
		$posts->setPostId($post_id);
		$post_data = o2array($posts->postData());
		$code      = $posts->likePost();
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

		if ($code == 1 || $code == -1) {
			$data['code'] = $code;
			$data['status'] = 200;
			$post_owner = $posts->getPostOwnerData();
			if ($posts->isPostOwner(false) == false && $code == 1) {
				if (!empty($post_owner)) {
					$notif      = new notify();
					$notif_conf = $notif->notifSettings($post_owner->user_id,'on_like');
					if ($notif_conf) {
						if($type == 'tile') {
							$re_data = array(
								'notifier_id' => $me['user_id'],
								'recipient_id' => $post_owner->user_id,
								'type' => 'liked_ur_post',
								'url' => pid3url($post_id),
								'time' => time(),
								'ftype' => $type,
								'thumb' => $thumb,
								'post_id' => $post_id,
								'file' => $fullpath
							);
						} else {
							$re_data = array(
								'notifier_id' => $me['user_id'],
								'recipient_id' => $post_owner->user_id,
								'type' => 'liked_ur_post',
								'url' => pid2url($post_id),
								'time' => time(),
								'ftype' => $type,
								'thumb' => $thumb,
								'post_id' => $post_id,
								'file' => $fullpath
							);	
						}
						$notif->notify($re_data);
					}
					#Rewards points to the user
					RecordAction('liked_post', array('user_id' => $post_owner->user_id));
				}
			} else {
				#Remove the points
				RecordAction('unlike_post', array('user_id' => $post_owner->user_id));
			}
		}
	}
}

else if($action == 'star' && IS_LOGGED) {
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$post_id = $_POST['id'];
		$posts   = new posts();
		$data    = array('status' => 304);

		$posts->setPostId($post_id);
		$code            = $posts->staredPost();

		$code            = ($code == -1) ? 0 : 1;
		$data['code']    = $code;
		$data['status']  = 200;
		$data['message'] = lang('post_added2fav');

		$post_data = o2array($posts->postData());
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
		$post_owner = $posts->getPostOwnerData();
		if ($posts->isPostOwner(false) == false && $code == 1) {
			if (!empty($post_owner)) {
				$notif      = new notify();
				$notif_conf = $notif->notifSettings($post_owner->user_id,'on_post_saved');
				if ($notif_conf) {
					if($type == 'tile') {
						$re_data = array(
							'notifier_id' => $me['user_id'],
							'recipient_id' => $post_owner->user_id,
							'type' => 'saved_your_post',
							'url' => pid3url($post_id),
							'time' => time(),
							'ftype' => $type,
							'thumb' => $thumb,
							'post_id' => $post_id,
							'file' => $fullpath
						);
					} else {
						$re_data = array(
							'notifier_id' => $me['user_id'],
							'recipient_id' => $post_owner->user_id,
							'type' => 'saved_your_post',
							'url' => pid2url($post_id),
							'time' => time(),
							'ftype' => $type,
							'thumb' => $thumb,
							'post_id' => $post_id,
							'file' => $fullpath
						);	
					}
					$notif->notify($re_data);
				}
				#Rewards points to the user
				RecordAction('star_post', array('user_id' => $post_owner->user_id));
				//$db->insert(T_ACTIVITIES, array('user_id' => $me['user_id'], 'post_id' => $post_id, 'type' => 'saved_post', 'time' => time()));
			}
		}
		if ($code == 0) {
			$data['message'] = lang('post_rem_from_fav');
			RecordAction('remove_star', array('user_id' => $post_owner->user_id));
		}
	}
}

elseif ($action == 'update' && IS_LOGGED) {
	$vl1 = (!empty($_POST['id']) && is_numeric($_POST['id']));
	$vl2 = (isset($_POST['text']));
	if ($vl1 && $vl2) {
		$post_id  = $_POST['id'];
		$text     = aura::secure($_POST['text']);
		$posts    = new posts();
		$notif    = new notify();
		$caption  = trim($text);
		$is_owner = $posts->setPostId($post_id)->isPostOwner();
		$data     = array('status' => 304,'message' => lang('unknown_error'));
		if ($is_owner === true|| IS_ADMIN) {
			$update = $posts->updatePost(array('description' => $caption));
			$data['status']  = 200;
			$data['message'] = lang('changes_saved');

			#Notify mentioned users
			$notif->notifyMentionedUsers($caption,pid2url($post_id),$post_id);
		}
	}
}

elseif ($action == 'report' && IS_LOGGED) {
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$post_id  = $_POST['id'];
		$type     = $_POST['t'];
		$text     = $_POST['m'];

		$posts    = new posts();
		$report   = $posts->setPostId($post_id)->reportPost($type,$text);
		$data     = array('status' => 304);

		if ($report == 1) {
			$data['status']  = 200;
			$data['code']    = 1;
			$data['purl']    = pid2url($post_id);
			$data['turl']    = pid3url($post_id);
			$data['ajax']    = "load.php?app=posts&apph=view_post&pid=$post_id";
			$data['t_ajax']    = "load.php?app=tile&apph=view_tile&pid=$post_id";
			$data['message'] = lang('report_sent');
		}
	}
}

elseif ($action == 'cancel_report' && IS_LOGGED) {
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$post_id  = $_POST['id'];
		$posts    = new posts();
		$report   = $posts->setPostId($post_id)->cancelReportedPost();
		$data     = array('status' => 304);
		if($report == -1){
			$data['status']  = 200;
			$data['code']    = 0;
			$data['purl']    = pid2url($post_id);
			$data['turl']    = pid3url($post_id);
			$data['ajax']    = "load.php?app=posts&apph=view_post&pid=$post_id";
			$data['t_ajax']    = "load.php?app=tile&apph=view_tile&pid=$post_id";
			$data['message'] = lang('report_canceled');
		}
	}
}

elseif ($action == 'load-feed-posts') {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		$last_id  = $_GET['offset'];
		$posts    = new posts();
		$data     = array('status' => 404);
		$qset     = $posts->FeedPosts($last_id);
		$qset     = (!empty($qset)) ? o2array($qset) : 0;
		$html     = "";
		if (len($qset) > 0) {
			foreach ($qset as $post_data) {
				if ($post_data['type'] == 'image' || $post_data['type'] == 'gif') {
					$html  .= $ui->intel('home/templates/home/includes/post-image');
				} elseif ($post_data['type'] == 'video') {
					$html  .= $ui->intel('home/templates/home/includes/post-video');
				} elseif ($post_data['type'] == 'youtube') {
					$html  .= $ui->intel('home/templates/home/includes/post-youtube');
				} elseif ($post_data['type'] == 'vimeo') {
					$html  .= $ui->intel('home/templates/home/includes/post-vimeo');
				} elseif ($post_data['type'] == 'dailymotion') {
					$html  .= $ui->intel('home/templates/home/includes/post-dailymotion');
				} elseif ($post_data['type'] == 'fetched') {
					$html  .= $ui->intel('home/templates/home/includes/post-fetched');
				} 
				// elseif ($post_data['type'] == 'ad') {
				// 	$html  .= $ui->intel('home/templates/home/includes/post-ad');
				// }
			}
			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}

elseif ($action == 'load-tl-posts' && IS_LOGGED) {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		$last_id  = $_GET['offset'];
		$posts    = new posts();
		$data     = array('status' => 404);
		$qset     = $posts->TimelinePosts($last_id);
		$qset     = (!empty($qset)) ? o2array($qset) : 0;
		$html     = "";

		if (len($qset) > 0) {
			foreach ($qset as $post_data) {
				if ($post_data['type'] == 'image' || $post_data['type'] == 'gif') {
					$html  .= $ui->intel('home/templates/home/includes/post-image');
				} elseif ($post_data['type'] == 'video') {
					$html  .= $ui->intel('home/templates/home/includes/post-video');
				} elseif ($post_data['type'] == 'youtube') {
					$html  .= $ui->intel('home/templates/home/includes/post-youtube');
				} elseif ($post_data['type'] == 'vimeo') {
					$html  .= $ui->intel('home/templates/home/includes/post-vimeo');
				} elseif ($post_data['type'] == 'dailymotion') {
					$html  .= $ui->intel('home/templates/home/includes/post-dailymotion');
				} elseif ($post_data['type'] == 'fetched') {
					$html  .= $ui->intel('home/templates/home/includes/post-fetched');
				} 
				// elseif ($post_data['type'] == 'ad') {
				// 	$html  .= $ui->intel('home/templates/home/includes/post-ad');
				// }
			}
			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}

elseif ($action == 'load-tag-posts' && IS_LOGGED) {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		$tag_id   = $_GET['tag_id'];
		$last_id  = $_GET['offset'];
		$posts    = new posts();
		$data     = array('status' => 404);
		$qset     = $posts->HashtagPosts($tag_id,$last_id);
		$qset     = (!empty($qset)) ? o2array($qset) : 0;
		$html     = "";

		if (len($qset) > 0) {
			foreach ($qset as $post_data) {
				if ($post_data['type'] == 'image' || $post_data['type'] == 'gif') {
					$html  .= $ui->intel('home/templates/home/includes/post-image');
				} elseif ($post_data['type'] == 'video') {
					$html  .= $ui->intel('home/templates/home/includes/post-video');
				} elseif ($post_data['type'] == 'youtube') {
					$html  .= $ui->intel('home/templates/home/includes/post-youtube');
				} elseif ($post_data['type'] == 'vimeo') {
					$html  .= $ui->intel('home/templates/home/includes/post-vimeo');
				} elseif ($post_data['type'] == 'dailymotion') {
					$html  .= $ui->intel('home/templates/home/includes/post-dailymotion');
				} elseif ($post_data['type'] == 'fetched') {
					$html  .= $ui->intel('home/templates/home/includes/post-fetched');
				}
			}
			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}

elseif ($action == 'view-likes' && !empty($_GET['post_id'])) {
	if (is_numeric($_GET['post_id'])) {
		$posts   = new posts();
		$post_id = $posts::secure($_GET['post_id']);
		$query   = $posts->setPostId($post_id)->LikedUsers();
		$query   = (!empty($query)) ? o2array($query) : array();
		$data    = array('status' => 404);
		if (!empty($query)) {
			$context['users'] = $query;
			$data['status']  = 200;
			$data['html']    = $ui->intel('main/templates/modals/view-post-likes');
		}else{
			$data['message'] = lang('unknown_error');
		}
	}
}

else if($action == 'load-tlp-comments' && IS_LOGGED && !empty($_POST['post_id'])) {
	if (is_numeric($_POST['post_id']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) {
		$data    = array('status' => 404);
		$posts   = new posts();
		$posts->comm_limit = $config['load_more_comments'];
		$post_id = $_POST['post_id'];
		$offset  = $_POST['offset'];
		$query   = $posts->setPostId($post_id)->getPostComments($offset);
		$html    = '';
		if (!empty($query)) {
			$comments = o2array($query);
			foreach ($comments as $comment) {
				$html  .= $ui->intel('home/templates/home/includes/comments');
			}
			$data['status']  = 200;
			$data['html']    = $html;
		} else {
			$data['message'] = lang('no_more_comments');
		}
	}
}


else if($action == 'add_plays' && !empty($_POST['post_id'])) {
	if (is_numeric($_POST['post_id'])) {
		$data    = array('status' => 400);
		$posts   = new posts();
		$post_id = $_POST['post_id'];
		$count   = $posts->setPostId($post_id)->add_play();
		if (!empty($count)) {
			$data['status']  = 200;
			$data['count']    = $count;
		}
	}
}

else if($action == 'url_fetch' && !empty($_POST['url'])) {
	$data['status'] = 400;
	$page_title = '';
	$page_body = '';
    $image_urls = array();
    if (aura::isUrl($_POST['url'])) {
    	include 'core/zipy/simple_html_dom.inc.php';
		$get_content = file_get_html($_POST['url']);
	    foreach ($get_content->find('title') as $element) {
	        @$page_title = $element->plaintext;
	    }
	    if (empty($page_title)) {
	        $page_title = '';
	    }
	    @$page_body = $get_content->find("meta[name='description']", 0)->content;
	    $page_body = mb_substr($page_body, 0, 250, "utf-8");
	    if ($page_body === false) {
	        $page_body = '';
	    }
	    if (empty($page_body)) {
	        @$page_body = $get_content->find("meta[property='og:description']", 0)->content;
	        $page_body = mb_substr($page_body, 0, 250, "utf-8");
	        if ($page_body === false) {
	            $page_body = '';
	        }
	    }
	    $image_urls = array();
	    @$page_image = $get_content->find("meta[property='og:image']", 0)->content;
	    if (!empty($page_image)) {
	        if (preg_match('/[\w\-]+\.(jpg|png|gif|jpeg)/', $page_image)) {
	            $image_urls[] = $page_image;
	        }
	    } else {
	        foreach ($get_content->find('img') as $element) {
	            if (!preg_match('/blank.(.*)/i', $element->src)) {
	                if (preg_match('/[\w\-]+\.(jpg|png|gif|jpeg)/', $element->src)) {
	                    $image_urls[] = $element->src;
	                }
	            }
	        }
	    }
	    if (!empty($image_urls)) {
	    	$data = array(
		        'title' => $page_title,
		        'images' => $image_urls,
		        'content' => $page_body,
		        'url' => $_POST["url"],
		        'status' => 200
		    );
	    }
    }
}
else if($action == 'boost' && !empty($_POST['post_id'])) {
	$data    = array('status' => 400);
	$post_id = aura::secure($_POST['post_id']);
	$posts   = new posts();
	$boost = $posts->BoostPost($post_id);
	if ($boost == 1) {
		$data = array('status' => 200,'code' => 1, 'message' => lang('unboost_post'),'subtitle' => lang('cancel_boosted_post'),'success' => lang('post_boosted_success'));
	}elseif($boost == 2){
		$data = array('status' => 200,'code' => 2,'message' => lang('boost_post'), 'subtitle' => lang('boost_this_post'), 'success' => lang('boosted_post_canceled'));
	}elseif($boost == 3){
		$data = array('status' => 200,'code' => 3,'message' => lang('boost_post'), 'subtitle' => lang('boost_this_post'), 'success' => lang('you_do_not_have_a_boost'));
	}elseif($boost == 4){
		$data = array('status' => 200,'code' => 4,'message' => lang('boost_post'), 'subtitle' => lang('boost_this_post'), 'success' => lang('you_have_to_be_premium'));
	}
}

else if($action == 'on_comments' && !empty($_POST['post_id'])) {
	$data    = array('status' => 400);
	$post_id = aura::secure($_POST['post_id']);
	$posts   = new posts();
	$stats = $posts->CommentStatus($post_id);
	if ($stats == 1) {
		$data = array('status' => 200,'code' => 1,'message' => lang('enable_comments'),'success' => lang('post_no_comments'),'subtitle' => lang('emable_commenting_for_post'));
	}elseif($stats == 2){
		$data = array('status' => 200,'code' => 0,'message' => lang('disable_comments'),'success' => lang('post_comments_on'),'subtitle' => lang('disable_commenting_for_post'));
	}
}

// else if($action == 'next_tile') {
// 	$post_id = NextTile();
// 	$data['status']  = 200;
// 	$data['turl']    = pid3url($post_id);
// 	$data['t_ajax']    = "load.php?app=tile&apph=view_tile&pid=$post_id";
// 	$data['pid'] = $post_id;
// }


xhr_exit: