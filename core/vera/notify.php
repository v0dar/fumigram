<?php 
 
class notify extends users{
	public $type = null;
	public $notifier_id = null;
	public $types = array(
		'followed_u' => array(
			'icon' => 'user-plus', 
			'text' => 'followed_u'
		),
		'accept_request' => array(
			'icon' => 'user-plus', 
			'text' => 'accept_request'
		),
		'rejected_request' => array(
			'icon' => 'user-times', 
			'text' => 'rejected_request'
		),
		'liked_ur_post' => array(
			'icon' => 'thumbs-up',
			'text' => 'liked_ur_post'
		),
		'liked_ur_comment' => array(
			'icon' => 'thumbs-up',
			'text' => 'liked_ur_comment'
		),
		'commented_ur_post' => array(
			'icon' => 'comments-o',
			'text' => 'commented_ur_post'
		),'reply_ur_comment' => array(
			'icon' => 'comments-o',
			'text' => 'reply_ur_comment'
		),
		'mentioned_u_in_comment' => array(
			'icon' => 'comments-o',
			'text' => 'mentioned_u_in_comment'
		),
		'mentioned_u_in_post' => array(
			'icon' => 'comments-o',
			'text' => 'mentioned_u_in_post'
		),
		'donated' => array(
			'icon' => 'user-plus', 
			'text' => 'donated'
		),
        'shared_your_post' => array(
            'icon' => 'user-plus',
            'text' => 'shared_your_post'
        ),
        'started_live_video' => array(
            'icon' => 'user-plus',
            'text' => 'started_live_video'
		),
		'post_saved' => array(
            'icon' => 'favourite',
            'text' => 'post_saved'
        ),
		'created_a_new_post' => array(
            'icon' => 'new-post',
            'text' => 'created_a_new_post'
        ),
		'follow_request' => array(
            'icon' => 'user-plus',
            'text' => 'follow_request'
        ),
		'earned_points' => array(
            'icon' => 'user-plus',
            'text' => 'earned_points'
        ),
		'loose_points' => array(
            'icon' => 'user-plus',
            'text' => 'loose_points'
        ),
		'accept_request' => array(
            'icon' => 'user-plus',
            'text' => 'accept_request'
        ),
		'rejected_request' => array(
            'icon' => 'user-plus',
            'text' => 'rejected_request'
        ),
	);

	public function notify($data = array()){
		global $config;
	    if (empty($data) || !is_array($data)) {
	        return false;
	    }

	    $data['text'] = '';
	    $t_notif      = T_NOTIF;
	    $query        = self::$db->insert($t_notif,$data);
	    if ($config['push'] == 1) {
		    $this->NotificationWebPushNotifier();
		}
	    return $query;
	}

	function getNotifications($offset = false){
		if (empty($this->user_id)) {
			return false;
		}
		$user_id = $this->user_id;
		$t_notif = T_NOTIF;
		$t_users = T_USERS;
		$limit   = $this->limit;
		$data    = array();
		$update  = array();
		
	    self::$db->where('recipient_id',$user_id);
	    if ($this->type == 'new') {
	        $data = self::$db->where('seen', 0)->getValue($t_notif,'COUNT(*)');
	    } else{
	    	self::$db->join("{$t_users} u","n.notifier_id = u.user_id ","INNER");
	    	if (!empty($offset)) {
	    		self::$db->where('id',$offset,'<');
	    	}
	    	self::$db->orderBy('id','DESC');
	        $query = self::$db->get("{$t_notif} n",$limit,"n.*,u.username,u.avatar");
	        if (!empty($query)) {
	        	foreach ($query as $notif_data) {
	        		$notif_data->avatar = $notif_data->avatar;
		            $data[] = $notif_data;
		            $update[] = $notif_data->id;
		        }
		        self::$db->where('id',$update,"IN")->update($t_notif,array('seen' => time()));
	        }
	    }
	    return $data;
	}

	function ReadNotifications() {
		if (empty($this->user_id)) {
			return false;
		}
		$user_id = $this->user_id;
		$notify_id = $this->notify_id;
	    $query = self::$db->where('recipient_id',$user_id)->where('id', $notify_id)->update(T_NOTIF,array('click' => 1)); 
	    return $query;
	}

	function MarkReadNotifications() {
		if (empty($this->user_id)) {
			return false;
		}
		$user_id = $this->user_id;
		self::$db->where('recipient_id', $user_id)->where('follow', 0)->update(T_NOTIF,array('follow' => 1));
		$query = self::$db->where('recipient_id',$user_id)->where('click', 0)->update(T_NOTIF,array('click' => 1));
		return $query;
	}

	public function PointsAutoNotifications(){ // DISABLED
		global $config;
	    if (IS_LOGGED == false) {
	        return false;
	    }
		$notif        = new notify();
		$newtime      = strtotime('-10 seconds');
		$user_id      = aura::secure(self::$me->user_id);
		$remind       = self::$db->where('user_id', $user_id)->getValue(T_USERS,'reminder');
		$total_points = self::$db->where('user_id',$user_id)->getValue(T_USERS,'points');
		//self::$db->where('user_id',$user_id)->update(T_USERS,array('reminder' => time()));
		if(empty($remind)){
			return false;
		}

		if($remind >= $newtime){
			$query = self::$db->where('user_id',$user_id)->update(T_USERS,array('reminder' => time()));
			$re_data = array(
				'notifier_id' => $user_id,
				'recipient_id' => $user_id,
				'type' => 'you_have_total_points',
				'url' => $config['site_url'] . '/points',
				'time' => time(),
				'points' => $total_points
			); $notif->notify($re_data);
			return $query;
		}
	}

	public function clearNotifications(){
		if (empty($this->user_id)) {
			return false;
		} if (!empty($this->notifier_id) && is_numeric($this->notifier_id)) {
			self::$db->where('notifier_id',$this->notifier_id);
			self::$db->where('recipient_id',$this->user_id);	   
		} else{
			self::$db->where('recipient_id',$this->user_id);
		    self::$db->where('time',(time() - 8432000));
			self::$db->where('click',1,'>');
		}
		return self::$db->delete(T_NOTIF);
	}

	public function notifyMentionedUsers($text = "", $url = "" ,$post_id = ""){
		if (empty(IS_LOGGED) || empty($text) || empty($url)) {
			return false;
		}
		$mentions = mentions($text);
		$user_id  = self::$me->user_id;
		foreach ($mentions as $username) {
			try {
				$pinged  = $this->setUserByName($username);
				$notif_conf     = null;
				if (is_numeric($pinged)) {
					$notif_conf = $this->notifSettings($pinged,'on_mention');
				}
				if ($pinged && ($pinged != $user_id) && $notif_conf) {
					$posts   = new posts();
					$posts->setPostId($post_id);
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
					if($type == 'tile') {
						$re_data = array('notifier_id' => $user_id,'recipient_id' => $pinged,'type' => 'mentioned_u_in_comment','url' => $url,'time' => time(),'ftype' => $type,'thumb' => $thumb,'post_id' => $post_id,'file' => $fullpath);
					} else {
						$re_data = array('notifier_id' => $user_id,'recipient_id' => $pinged,'type' => 'mentioned_u_in_comment','url' => $url,'time' => time(),'ftype' => $type,'thumb' => $thumb,'post_id' => $post_id,'file' => $fullpath);
					}
					$this->notify($re_data);
				}
			}  catch (Exception $e) {}
		}
	}

	public function notifSettings($user_id = false,$type = ''){
		if (empty($user_id) || empty($type) || !is_numeric($user_id)) {
			return false;
		} elseif (!in_array($type, array(
			'on_like','on_mention','on_comment','on_follow','on_comment_like',
			'on_comment_reply','on_post_saved','on_post_shared','on_new_post',
			'on_follow_request','on_points_earn','on_points_loose','on_accept_request','on_rejected_request'
			))) {
			return false;
		}
		$type  = self::secure($type);
		$query = self::$db->where('user_id',$user_id)->getOne(T_USERS,array("n_$type"));
		$val   = null;
		if (!empty($query)) {
			$type = "n_$type";
			$val  = $query->$type;
		}
		return $val;
	}

	public function NotificationWebPushNotifier() {
	    global $sqlConnect, $me,$db,$config;
	    if (IS_LOGGED == false) {
	        return false;
	    }
	    if ($config['push'] == 0 || empty($config['push_id']) || empty($config['push_key'])) {
	        return false;
	    }
	    $user_id   =  aura::secure(self::$me->user_id);
	    $to_ids    = array();
	    $notifications = $db->where('notifier_id',$user_id)->where('seen',0)->where('sent_push',0)->orderBy('id','DESC')->get(T_NOTIF);
	    if (!empty($notifications)) {
	        foreach ($notifications as $key => $notify) {
	            $notification_id = $notify->id;
	            $to_id           = $notify->recipient_id;
	            $user         = new users();
				$to_data = $user->getUserDataById($notify->recipient_id);
	            $ids             = '';
	            if (!empty($to_data->device_id)) {
	                $ids = array($to_data->device_id);
	            }
	            $send_array = array(
	                'send_to' => $ids,
	                'notification' => array(
	                    'notification_content' => '',
	                    'notification_title' => $me['name'],
	                    'notification_image' => $me['avatar'],
	                    'notification_data' => array('user_id' => $user_id)
	                )
	            );
	            $notify->type_text = '';
	            $notificationText = $notify->text;
	            if (!empty($notify->type)) {
	                $notify->type_text  = lang($notify->type);
	            }
	            $user->setUserById($user_id);
	            $send_array['notification']['notification_content']     = $notify->type_text;
	            $send_array['notification']['notification_data']['url'] = $notify->url;
	            $send_array['notification']['notification_data']['user_data'] = $user->getUserDataById($user_id);
	            $send_array['notification']['notification_data']['notification_info'] = $notify;
	            $send_array['notification']['notification_data']['post_data'] = '';
	            if ($notify->type != 'followed_u' && strpos($notify->url, 'post/')) {
	            	$post_id = substr($notify->url, strpos($notify->url, 'post/') + 5);
	            	$posts   = new posts();
	            	$post = $posts->setPostId($post_id)->postData();
	            	if (!empty($post)) {
						foreach ($post->media_set as $key => $value2) {
							$value2->file = media($value2->file);
			    			$value2->extra = media($value2->extra);
						}
						$post->comments = array();
						$user         = new users();
						$new_user = $user->getUserDataById($post->user_id);
						$post->name = $new_user->name;
						$post->avatar = media($post->avatar);
						$post->description = strip_tags($post->description);
						$post->time_text = time2str($post->time);
						$post->description  = $posts->tagifyHTags($post->description);
					}
					$send_array['notification']['notification_data']['post_data']      = $post;
	            }
	            $send       = SendPushNotification($send_array, 'native'); 
	        }
	        $push = $db->where('notifier_id',$user_id)->where('sent_push',0)->update(T_NOTIF,array('sent_push' => 1)); 
	    }
	    return true;
	}
}