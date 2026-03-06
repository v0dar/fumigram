<?php
if ($action == 'process-video' && IS_LOGGED) {
	if (!empty($_FILES['video']) && !empty($_POST['type'])){
        if ($config['ffmpeg_sys'] == 'on' && !empty($_FILES['video']) && file_exists($_FILES['video']['tmp_name'])){
			require_once('core/zipy/ffmpeg-php/vendor/autoload.php');
            if(!file_exists($config['ffmpeg_binary'])){
	            header("Content-type: application/json");
	            $data['status']  = 100;
	            $data['message'] = 'FFMPEG Executable file not found';
	            $data['ffmpeg_binary']  = $config['ffmpeg_binary'];
	            $data['exist']  = file_exists($config['ffmpeg_binary']);
	            echo json_encode($data, JSON_PRETTY_PRINT);
	            exit();
	        } else {
				$media   = new Media();
				$ffmpeg  = new FFmpeg($config['ffmpeg_binary']);
				$up_size = (!empty($_FILES['video']['size'])) ? $_FILES['video']['size'] : 0;
				$mx_size = $config['max_upload'];
				if ($up_size <= $mx_size) {
					$media->setFile(array(
						'file' => $_FILES['video']['tmp_name'],
						'name' => $_FILES['video']['name'],
						'size' => $_FILES['video']['size'],
						'type' => $_FILES['video']['type'],
						'allowed' => 'mp4,mov,3gp,webm',
					));
					$upload = $media->uploadFile(0, false);
					if (!empty($upload)) {
						try{
							$images   = array();
							$tvideo   = aura::secure($_POST['type']);
							$filepath = explode('.', $upload['filename'])[0];
							$filetext = explode('.', $upload['filename'])[1];
	
							$filedata  = $upload['filename'];
							$_ffmpeg   = $config['ffmpeg_binary'];
							if($config['video_frames'] === 'on') {
								$seconds = frame_duration($filedata);
								$farme1  = (int) ($seconds > 10) ? 11 : 1;
								$farme2  = (int) ($seconds > 24) ? 25 : 15;
								$farme3  = (int) $seconds - 1;
								$farme4  = (int) $seconds / 2;
								$farme5  = (int) ($seconds / 2) * 1.3;
								$farme6  = (int) $seconds / 3;
								$ircode  = rand(1111,9999);
								$iready  = array($farme1, $farme2, $farme3, $farme4, $farme5, $farme6);
		
								$media->initDir('photos');
								$dir      = "media/upload/photos/" . date('Y') . '/' . date('m');
								foreach ($iready as $i) {
									$hash    = sha1(time() + time() - rand(9999,9999)) . Generate();
									$fname   = "$hash.video_thumb_$ircode" . "_$i.jpeg";
									$thumb   = "$dir/$fname";
		
									$input  = $filedata;
									$output = $thumb;
		
									shell_exec("$_ffmpeg -ss \"$i\" -i $input -vframes 1 -f mjpeg $output 2<&1");
									if (file_exists($thumb) && !empty(@getimagesize($thumb))) {
										if ($tvideo === 'tile') {
											$media->cropImage(604, 1076, $thumb, $thumb, 80);
										}elseif ($tvideo === 'normal') {
											$media->cropImage(1076, 604, $thumb, $thumb, 80);
										} 
										$images[] = Media::getMedia($thumb);
									} else {
										@unlink($thumb);
									}
								}
							} else {
								$media->initDir('photos');
								
								$dir    = "media/upload/photos/" . date('Y') . '/' . date('m');
								$hash   = sha1(time() + time() - rand(9999,9999)) . Generate();
								$fname  = "$hash.video_thumb.jpeg";
								$thumb   = "$dir/$fname";
		
								$input  = $filedata;
								$output = $thumb;

								#Generete video thumb
								$ffmpeg->input($input);
								$ffmpeg->set('-ss','2');
								$ffmpeg->set('-vframes','1');
								$ffmpeg->set('-f','mjpeg');
								$ffmpeg->output("$output")->ready();
								if (file_exists($thumb) && !empty(@getimagesize($thumb))) {
									if ($tvideo === 'tile') {
										$media->cropImage(604, 1076, $thumb, $thumb, 80);
									}elseif ($tvideo === 'normal') {
										$media->cropImage(1076, 604, $thumb, $thumb, 80);
									}
								} else {
									@unlink($thumb);
								}
							}
							
							$explode3  = @explode('.', $upload['name']);
							$file_upload['name'] = $explode3[0];
	
							$ffmpeg->input($filedata);
							$ffmpeg->set('-ss', '0');
							if((int)$config['max_video_duration'] > 0) {
								$ffmpeg->set('-t', (int)$config['max_video_duration']);
							}
							$ffmpeg->set('-vcodec', 'h264');
							$ffmpeg->set('-level', '3.0');
							$ffmpeg->set('-preset', $config['convert_speed']);
							$ffmpeg->set('-crf', '26');
							$ffmpeg->forceFormat('mp4');
							$video = $ffmpeg->output("$filepath.ready.mp4")->ready();
							$video = "$filepath.ready.mp4";
	
							$media = new Media;
							$extra = Media::getMedia($thumb);
							if ($config['ftp_upload'] == 1) {
								$media->uploadToFtp($video);
								$media->uploadToFtp($thumb);
							} else if ($config['amazone_s3'] == 1) {
								$media->uploadToS3($video);
								$media->uploadToS3($thumb);
							} else if ($config['google_cloud_storage'] == 1) {
								$media->uploadToGoogleCloud($video);
								$media->uploadToGoogleCloud($thumb);
							} else if ($config['digital_ocean'] == 1) {
								$media->UploadToDigitalOcean($video);
								$media->UploadToDigitalOcean($thumb);
							}
	
							$data['status']   = 200;
							$data['video']    = $video;
							$data['extra']    = $extra;
							if($config['video_frames'] === 'on') {
							$data['images']   = $images; }
							$data['flname']   = $upload['name'];
							$data['message']  = lang('post_published');
	
							@unlink($upload['filename']);
							$media->deleteFromFTPorS3($upload['filename']);
						} catch (Exception $error) {
							$data['status']  =  400;
							$data['message'] = lang('unknown_error');
						}
					} else {
						$data['status']  = 400;
						$data['message'] = lang('unknown_error');
					}
				} else {
					$mx_size         = rx_size_format($mx_size);
					$data['status']  = 400;
					$data['message'] = str_replace('{{size}}', $mx_size, lang('max_upload_limit'));
				}
			}
        } else {
			$data['status']  = 500;
			$data['message'] = lang('ffmpeg_system_required');
        }
    }
}

if ($action == 'upload-tile' && IS_LOGGED) {
	if (!empty($_POST['video']) && !empty($_POST['extra'])){		
		$posts   = new posts();
		$media   = new Media();
		$notif   = new notify();
		
		$video   = aura::secure($_POST['video']);
		$extra   = aura::secure($_POST['extra']);
		$priv    = aura::secure($_POST['privacy']);
		
		$time  = time();
        $re_data = array(
		    'user_id' => $me['user_id'],
			'username' => $me['username'],
		    'time' => time(),
		    'type' => 'tile',
			'privacy' => $priv,
			'post_comments' => ((!empty($_POST['comment'])) ? '0' : '1'),
		);
		if (!empty($_POST['caption'])) {
			$text = aura::cropText($_POST['caption'],$config['caption_len']);
			$re_data['description'] = $text;
		}
		$post_id = $posts->insertPost($re_data);
		if (is_numeric($post_id)) {
			$re_data = array(
				'post_id' => $post_id,
				'file' => $video,
				'extra' => $extra,
			);

            $posts->setPostId($post_id);
			$posts->insertMedia($re_data);
			$post_data = o2array($posts->postData());

			#Reward points to the user
			RecordAction('upload_tile', array('user_id' => $me['user_id']));

			#Notify mentioned users
			$notif->notifyMentionedUsers($_POST['caption'],pid3url($post_id),$post_id);

			$data['status']  = 200;
			$data['pidurl']  = pid3url($post_id);
			$data['message'] = lang('post_published');
		} else {
			$data['status']  = 500;
			$data['message'] = lang('unknown_error');
		}
	} else {
		$data['status']  = 400;
		$data['message'] = lang('video_data_not_found');
	}
}

if ($action == 'upload-video' && IS_LOGGED) {
	if (!empty($_POST['video']) && !empty($_POST['extra'])){		
		$posts   = new posts();
		$media   = new Media();
		$notif   = new notify();

		$video   = aura::secure($_POST['video']);
		$extra   = aura::secure($_POST['extra']);
		
		$time  = time();
        $re_data = array(
		    'user_id' => $me['user_id'],
			'username' => $me['username'],
		    'time' => time(),
		    'type' => 'video',
			'post_comments' => ((!empty($_POST['comment'])) ? '0' : '1'),
		);
		if (!empty($_POST['caption'])) {
			$text = aura::cropText($_POST['caption'],$config['caption_len']);
			$re_data['description'] = $text;
		}
		if ($config['private_videos'] == 'on' && !empty($_POST['video_price']) && is_numeric($_POST['video_price']) && $_POST['video_price'] > 0) {
			$re_data['price'] = aura::secure($_POST['video_price']);
		}
		$post_id = $posts->insertPost($re_data);
		if (is_numeric($post_id)) {
			$re_data = array(
				'post_id' => $post_id,
				'file' => $video,
				'extra' => $extra,
			);

            $posts->setPostId($post_id);
			$posts->insertMedia($re_data);
			$post_data = o2array($posts->postData());

			#Reward points to the user
			RecordAction('upload_video', array('user_id' => $me['user_id']));

			#Notify mentioned users
			$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id),$post_id);

			$data['status']  = 200;
			$data['pidurl']  = pid2url($post_id);
			$data['message'] = lang('post_published');
		} else {
			$data['status']  = 500;
			$data['message'] = lang('unknown_error');
		}
	} else {
		$data['status']  = 400;
		$data['message'] = lang('video_data_not_found');
	}
}

if ($action == 'upload-thumb' && IS_LOGGED) {
	if (!empty($_FILES['thumb']) && file_exists($_FILES['thumb']['tmp_name'])) {
		$media = new Media();
		$media->setFile(array(
			'file' => $_FILES['thumb']['tmp_name'],
			'name' => $_FILES['thumb']['name'],
			'size' => $_FILES['thumb']['size'],
			'type' => $_FILES['thumb']['type'],
			'allowed' => 'jpeg,jpg,png',
			'crop' => array()	
		));
		$upload = $media->uploadFile();
		if (!empty($upload)) { 
			if(isset($upload['error'])){
				$data['status']  = 400;
				$data['message'] = $upload['error'];
			}else{
				$data['status']  = 200;
				$data['message'] = lang('post_published');
				$data['video_thumb']  = Media::getMedia($upload['filename']);
			}
		}
	} else {
		$data['status']  = 400;
		$data['message'] = lang('upload_thumbnail');
	}
}