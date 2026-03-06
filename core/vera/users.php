<?php 

/* The main users class which contains all the users data */
class users extends aura{
	protected $user_id = 0;
	public $udata = array();
	public $limit = 20;

	public static $user;
	protected static $me;

	public function __construct() {}
	public function getAllUsers(){
		$users = self::$db->get(T_USERS,$this->limit);
		$data  = array();
		foreach ($users as $key => $udata) {
			$udata = $this->userData($udata);
			$data[]    = $udata;
		}
		return $data;
	}

	public function offset($whereProp, $whereValue = 'DBNULL', $operator = '=', $cond = 'AND'){
		self::$db->where($whereProp, $whereValue, $operator);
		return $this;
	}

	public function orderBy($col = false,$type = false){
		self::$db->orderBy($col,$type);
		return $this;
	}

	// set user ID to use in the CLass.
	public function setUserById( $user_id = 0) {
		$this->user_id = self::secure($user_id);
		if (empty($this->user_id)) {
			$this->throwError("User doesn't exist");
		}
		return $this;
	}

	public function updateLastSeen(){
		if (empty(self::$me)) {
			return false;
		}
		self::$db->where('user_id',self::$me->user_id);
		return self::$db->update(T_USERS,array('last_seen' => time()));
	}

	public function onlineStatus(){
		if (empty(self::$me)) {
			return false;
		}
		$user_id      = self::$me->user_id;
		self::$db->where('user_id',$user_id);
		$online = self::$db->where('last_seen',time() - 60,'>');
		if ($online) {
			self::$db->where('user_id',$user_id);
			self::$db->where('online', '0')->update(T_USERS, array('online' => '1'));
		}
	}

	public function offlineUsers() {
		$offline_users = self::$db->where('last_seen', time() - 60, '<')->get(T_USERS);
		if (!empty($offline_users)) {
			foreach ($offline_users as $key => $value) {
				$offline = self::$db->where('user_id', $value->user_id)->getOne(T_USERS, array('user_id', 'online'));
				if ($offline) {
					self::$db->where('user_id', $value->user_id)->where('online', '1')->update(T_USERS, array('online' => '0'));
				}
			}
		}
	}

	// set the user in class by email
	public function setUserByEmail($email) {
		$this->user_id = self::$db->where('email', $this->secure($email))->getValue(T_USERS, 'user_id');
		if (empty($this->user_id)) {
			$this->throwError("User doesn't exist");
		}
		return $this;
	}

	// set the user in class by username
	public function setUserByName($username) {
		$this->user_id = self::$db->where('username', $this->secure($username))->getValue(T_USERS, 'user_id');
		if (empty($this->user_id)) {
			$this->throwError("User doesn't exist");
		}
		return $this->user_id;
	}

	// check if a user by username exists
	public static function userNameExists($username) {
		$user_id = self::$db->where('username', self::secure($username))->getValue(T_USERS, 'user_id');
		return (empty($user_id)) ? false : true;
	}

	// check if a user by email exits
	public static function userEmailExists($email) {
		$user_id = self::$db->where('email', self::secure($email))->getValue(T_USERS, 'user_id');
		return (empty($user_id)) ? false : true;
	}

    // return the user data (object)
	public function getUser() {
		return $this->fetchUser();
	}

	// export user data from class
	public static function getStaticUser($user_id = 0) {
		if (!empty($user_id)) {
			$user = new users;
			$user->setUserById($user_id)->getUser();
		}
		return self::$user;
	}

	// export logged in data from class
	public static function getStaticLoggedInUser() {
		return self::$me;
	}

	// update user stactily
	public function updateStatic( $id = 0, $data = array()) {
		return self::$db->where('user_id', $id)->update(T_USERS, $data);
	}

	// check for reset check
	public static function validateCode($code = '') {
		return self::$db->where('email_code', $code)->getValue(T_USERS, 'user_id');
	}

	// get user data from the database
	private function fetchUser() {
	    $this->udata = self::$db->where('user_id', $this->user_id)->getOne(T_USERS);
	    if (empty($this->udata)) {
	    	return false;
	    }
		$this->udata->name  = $this->udata->username;
	    if (!empty($this->udata->fname) && !empty($this->udata->lname)) {
	    	$this->udata->name = sprintf('%s %s',$this->udata->fname,$this->udata->lname);
	    }
	    $this->udata->avatar = media($this->udata->avatar);
	    $this->udata->banner = media($this->udata->banner);
	    $this->udata->uname  = sprintf('%s',$this->udata->username);
	    $this->udata->url    = sprintf('%s/%s',self::$site_url,$this->udata->username);
	    $this->udata->am_i_subscribed = 0;
		if ((self::$config['private_videos'] == 'on' || self::$config['private_photos'] == 'on') && !empty($this->udata->subscribe_price) && IS_LOGGED && !empty(self::$me)) {
			$month = 60 * 60 * 24 * 30;
			$this->udata->am_i_subscribed = self::$db->where('user_id',$this->udata->user_id)->where('subscriber_id',self::$me->user_id)->where('time',(time() - $month),'>=')->getValue(T_SUBSCRIBERS,'COUNT(*)');
		}
	    self::$user = $this->udata;
	    return $this->udata;
	}

	// get user data from the object
	public function userData($udata = null) {
	    $this->udata = $udata;
	    if (empty($this->udata)) {
	    	$this->throwError("Invalid argument: udata must be a instance of " . T_USERS);
	    }
	    $this->udata->name     = $this->udata->username;
	    if (!empty($this->udata->fname) && !empty($this->udata->lname)) {
	    	$this->udata->name = sprintf('%s %s',$this->udata->fname,$this->udata->lname);
	    }
	    $this->udata->avatar   = media($this->udata->avatar);
		$this->udata->banner   = media($this->udata->banner);
	    $this->udata->uname    = sprintf('%s',$this->udata->username);
	    $this->udata->url      = sprintf('%s/%s',self::$site_url,$this->udata->username);
	    $this->udata->edit     = sprintf('%s/settings/general/%s',self::$site_url,$this->udata->username);
	    
	    if (len($this->udata->website)) {
	    	$this->udata->website  = urldecode($this->udata->website);
	    } if (len($this->udata->facebook)) {
	    	$this->udata->facebook  = urldecode($this->udata->facebook);
	    } if (len($this->udata->google)) {
	    	$this->udata->google  = urldecode($this->udata->google);
	    } if (len($this->udata->twitter)) {
	    	$this->udata->twitter  = urldecode($this->udata->twitter);
	    } if (len($this->udata->instagram)) {
	    	$this->udata->instagram  = urldecode($this->udata->instagram);
	    } if (len($this->udata->discord)) {
	    	$this->udata->discord  = urldecode($this->udata->discord);
	    } if (len($this->udata->deviantart)) {
	    	$this->udata->deviantart  = urldecode($this->udata->deviantart);
	    } if (len($this->udata->github)) {
	    	$this->udata->github  = urldecode($this->udata->github);
	    } if (len($this->udata->pinterest)) {
	    	$this->udata->pinterest  = urldecode($this->udata->pinterest);
	    } if (len($this->udata->tiktok)) {
	    	$this->udata->tiktok  = urldecode($this->udata->tiktok);
	    } if (len($this->udata->youtube)) {
	    	$this->udata->youtube  = urldecode($this->udata->youtube);
	    } if (len($this->udata->spotify)) {
	    	$this->udata->spotify  = urldecode($this->udata->spotify);
	    }
	    $this->udata->followers    = $this->countFollowers();
		$this->udata->following    = $this->countFollowing();
		$posts   = new posts();
		$posts->setUserById($this->udata->user_id);
		$this->udata->favourites  = $posts->countSavedPosts();
		$this->udata->posts_count = $posts->countPosts();
		$this->udata->tiles_count = $posts->countTiles();
		$this->udata->am_i_subscribed = 0;
		if ((self::$config['private_videos'] == 'on' || self::$config['private_photos'] == 'on') && !empty($this->udata->subscribe_price) && IS_LOGGED) {
			$month = 60 * 60 * 24 * 30;
			$this->udata->am_i_subscribed = self::$db->where('user_id',$this->udata->user_id)->where('subscriber_id',self::$me->user_id)->where('time',(time() - $month),'>=')->getValue(T_SUBSCRIBERS,'COUNT(*)');
		}
	    self::$user = $this->udata;
	    return $this->udata;
	}

	public function getUserDataById($user_id = false){
		if (empty($user_id)) {
			return false;
		}
		self::$db->where('user_id',$user_id);
		$udata = self::$db->getOne(T_USERS);
		if (!empty($udata)) {
			return $this->userData($udata);
		}
		return false;
	}

    // check if the user is logged in
	public function isLogged() {
		$id = 0;
		// * API *
		if (self::EndPointRequest()) {
			if (isset($_POST['access_token']) && !empty($_POST['access_token'])) {
				$id = self::$db->where('session_id', aura::secure($_POST['access_token']))->getValue(T_SESSIONS, 'user_id');
			}
		}else{
			if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
		        $id = self::$db->where('session_id', $_SESSION['user_id'])->getValue(T_SESSIONS, 'user_id');
		    } else if (!empty($_COOKIE['user_id']) && !empty($_COOKIE['user_id'])) {
		        $id = self::$db->where('session_id', $_COOKIE['user_id'])->getValue(T_SESSIONS, 'user_id');
			}
		}
	    return (is_numeric($id) && !empty($id)) ? true : false;
	}
	
	// logged in user data
	public function LoggedInUser() {
		// * API *
		if (!empty($_SESSION['user_id'])) {
			$session_id = $_SESSION['user_id'];
		}elseif (isset($_POST['access_token']) && !empty($_POST['access_token'])) {
			$session_id = aura::secure($_POST['access_token']);
		}else{
			$session_id = $_COOKIE['user_id'];
		}
        $user_id  = self::$db->where('session_id', $session_id)->getValue(T_SESSIONS, 'user_id');
		return self::$me = $this->setUserById($user_id)->getUser();
	}

	// check if user is authorized for an action
	public function isOwner($user_id = 0) {
		return ($this->udata->user_id == $user_id) ? true : false;
	}

	// Register a new user
	public static function registerUser(){
		$gender = 'male';
		$active = (self::$config['email_validation'] == 'on') ? 0 : 1;
		if ($_POST['gender'] == 'female') {
			$gender = 'female';
		}
		$email_code = rand(100000,999999);
		$data = array(
            'username' => self::secure($_POST['username']),
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'email' => self::secure($_POST['email']),
            'ip_address' => '0.0.0.0',
            'gender' => $gender,
			'ac_code' => $email_code,
            'active' => $active,
            'last_seen' => time(),
            'time' => time(),
			'joined' => date('jS \of F Y'),
            'registered' => date('Y') . '/' . intval(date('m'))
		);
		$idata = new users();
		if (!empty($_SESSION['ref']) && self::$config['affiliate_system'] == 1) {
			$idata->setUserByName($_SESSION['ref']);
			$udata = $idata->userData($idata->getUser());
            $uid   = $udata->user_id;
			$uname = $udata->username;
            if (!empty($uid) && is_numeric($uid)) {
                $data['referrer'] = self::secure($uid);
                $data['src']      = self::secure('Referrer');
				if($udata->is_pro > 0) {
					self::$db->where('user_id',$uid)->update(T_USERS,array('credits' => self::$db->inc(self::$config['xp_amount_ref'])));
					$notif        = new notify();
					$re_data = array(
						'notifier_id' => $uid,
						'recipient_id' => $uid,
						'type' => 'ref_credited_wit_credits',
						'url'  =>  self::$config['site_url'].'/settings/credits/'.$uname,
						'time' => time(),
						'credits' => self::$config['xp_amount_ref'],
						'ajax_url' => 'load.php?app=settings&apph=settings&user='.$uname.'&page=credits'
					); $notif->notify($re_data);
				} else {
					self::$db->where('user_id',$uid)->update(T_USERS,array('credits' => self::$db->inc(self::$config['amount_ref'])));
					$notif        = new notify();
					$re_data = array(
						'notifier_id' => $uid,
						'recipient_id' => $uid,
						'type' => 'ref_credited_wit_credits',
						'url'  =>  self::$config['site_url'].'/settings/credits/'.$uname,
						'time' => time(),
						'credits' => self::$config['amount_ref'],
						'ajax_url' => 'load.php?app=settings&apph=settings&user='.$uname.'&page=credits'
					); $notif->notify($re_data);
				}
            }
        }
		if (!empty($_POST['device_id'])) {
            $data['device_id'] = self::secure($_POST['device_id']);
        }
        if (!empty($_SESSION['lang'])) {
            $data['language'] = self::secure($_SESSION['lang']);
        }
        $user_id     = self::$db->insert(T_USERS, $data);
        $signup      = false;
        if (!empty($user_id)) {
        	$signup      = true;
			if (self::$config['email_validation'] == 'on') {
			    $body = self::$config['block_email_code'];
				$body = str_replace(
					array("{{NAME}}", "{{DESCRIPTION}}", "{{SITE_NAME}}", "{{EMAIL_CODE}}"),
					array(self::secure($_POST['username']), self::$config['email_code_description'], self::$config['site_name'], $email_code),
					$body 
				);
				$email = array(
					'from_email' => self::$config['noreply_email'],
					'from_name' => self::$config['site_name'],
					'to_email' => self::secure($_POST['email']),
					'to_name' => self::secure($_POST['username']),
					'subject' => 'Activate Your Account',
					'charSet' => 'UTF-8',
					'message_body' => $body,
					'is_html' => true
				);
				aura::sendMail($email);
		   	} else {
	        	$session_id  = sha1(rand(11111, 99999)) . time() . md5(microtime());
	        	$platform = $idata->getUserBrowser();
		        $data = array(
		           'user_id' => $user_id,
		           'session_id' => $session_id,
		           'time' => time(),
		           'platform_details'  => json_encode($platform),
		           'platform' => $platform['platform']
		        );
				self::$db->insert(T_SESSIONS, $data);
				$_SESSION['user_id'] = $session_id;
	            setcookie("user_id", $session_id, time() + (10 * 365 * 24 * 60 * 60), "/");
	            // * API *
	            if ($idata->EndPointRequest()) {
					return array('user_id' => $data['user_id'], 'access_token' => $session_id);
				}
		   	}
        }
        return $signup;
	}

	// Login the user
	public static function loginUser(){
		$username        = self::secure($_POST['username']);
        $password        = $_POST['password'];
        $upassword = self::$db->where("(username = ? or email = ?)", array($username,$username))->getValue(T_USERS, 'password');
        $idata = new users();
        $hashed = sha1($password);
        $hashed = self::secure($hashed);
        self::$db->where("(username = ? or email = ?)", array($username,$username));
        if (strlen($upassword) == 40) {
        	self::$db->where("password", $hashed);
        	$login  = self::$db->getOne(T_USERS);
        } else if (strlen($upassword) == 60) {
        	$validate_password = password_verify($password, $upassword);
        	if ($validate_password) {
        		$login = self::$db->where("(username = ? or email = ?)", array($username,$username))->getOne(T_USERS);
        	}
        }
        $signin = false;
        if (!empty($login)) {
        	if (strlen($upassword) == 40) {
        		self::$db->where('user_id', $login->user_id)->update(T_USERS, ['password' => password_hash($password, PASSWORD_DEFAULT)]);
        	}
        	$signin      = true;
            $session_id  = sha1(rand(11111, 99999)) . time() . md5(microtime());
            $platform = $idata->getUserBrowser();
            $data = array(
                'user_id' => $login->user_id,
                'session_id' => $session_id,
                'time' => time(),
	            'platform_details'  => json_encode($platform),
	            'platform' => $platform['platform']
            );
            self::$db->insert(T_SESSIONS, $data);
            $_SESSION['user_id'] = $session_id;
            setcookie("user_id", $session_id, time() + (10 * 365 * 24 * 60 * 60), "/");
            $update_udata = array();
            $update_udata['ip_address'] = get_ip_address();
            if (!empty($_POST['device_id'])) {
                $update_udata['device_id'] = self::secure($_POST['device_id']);
            }
            self::$db->where('user_id',$login->user_id)->update(T_USERS,$update_udata);
            // * API *
            if ($idata->EndPointRequest()) {
				return array('user_id' => $login->user_id, 'access_token' => $session_id);
			}
        }
        return $signin;
	}

	// logout a user 
	public static function signoutUser(){
		if (!empty($_SESSION['user_id'])) {
			self::$db->where('session_id', self::secure($_SESSION['user_id']));
			self::$db->delete(T_SESSIONS);
		}
		if (!empty($_COOKIE['user_id'])) {
			self::$db->where('session_id', self::secure($_COOKIE['user_id']));
			self::$db->delete(T_SESSIONS);
		    unset($_COOKIE['user_id']);
		    setcookie('user_id', null, -1);
		}
		@session_destroy();
	}

	public function delete() {
		self::$db->where('user_id' , $this->user_id)->delete(T_ACTIVITIES);
		$media  = self::$db->where('user_id',$this->user_id)->get(T_MEDIA,null,array('file','extra','blured_file'));
		$story  = self::$db->where('user_id',$this->user_id)->get(T_STORY,null,array('media_file'));
		$media  = (!empty($media)) ? $media : array();
		$story  = (!empty($story)) ? $story : array();
    	$del = new Media();
		foreach ($media as $file_obj) {
		    $del->deleteFromFtpOrS3($file_obj->file);
		    $del->deleteFromFtpOrS3($file_obj->extra);
		    $del->deleteFromFtpOrS3($file_obj->blured_file);
		    
			if (file_exists($file_obj->file)) {
				@unlink($file_obj->file);
			}

			if (file_exists($file_obj->extra)) {
				@unlink($file_obj->extra);
			}

			if (file_exists($file_obj->blured_file)) {
				@unlink($file_obj->blured_file);
			}
		}

		foreach ($story as $file_obj) {
		    $del->deleteFromFtpOrS3($file_obj->media_file);
		    
			if (file_exists($file_obj->media_file)) {
				@unlink($file_obj->media_file);
			}
		}
		
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_USERS);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_POST_COMMENTS);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_POST_LIKES);

		$delete = self::$db->where('user_id', $this->user_id)->delete(T_POSTS);
		$delete = self::$db->where('follower_id', $this->user_id)->delete(T_CONNECTIV);
		$delete = self::$db->where('following_id', $this->user_id)->delete(T_CONNECTIV);

		$delete = self::$db->where('to_id', $this->user_id)->delete(T_MESSAGES);
		$delete = self::$db->where('from_id', $this->user_id)->delete(T_MESSAGES);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_STORY);

		$delete = self::$db->where('user_id', $this->user_id)->delete(T_STORY);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_STORY_VIEWS);

		$delete = self::$db->where('notifier_id', $this->user_id)->delete(T_NOTIF);
		$delete = self::$db->where('recipient_id', $this->user_id)->delete(T_NOTIF);

		$delete = self::$db->where('from_id', $this->user_id)->delete(T_CHATS);
		$delete = self::$db->where('to_id', $this->user_id)->delete(T_CHATS);

		$delete = self::$db->where('user_id', $this->user_id)->delete(T_SAVED_POSTS);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_POST_REPORTS);

		$delete = self::$db->where('user_id', $this->user_id)->delete(T_POST_REPORTS);

		$delete = self::$db->where('profile_id', $this->user_id)->delete(T_USER_REPORTS);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_USER_REPORTS);

		$delete = self::$db->where('profile_id', $this->user_id)->delete(T_PROF_BLOCKS);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_PROF_BLOCKS);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_TRANSACTIONS);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_ADS);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_SUBSCRIBERS);
		$delete = self::$db->where('subscriber_id', $this->user_id)->delete(T_SUBSCRIBERS);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_SESSIONS);
		$verify = self::$db->where('user_id', $this->user_id)->get(T_VERIFY);
		if (!empty($verify)) {
			foreach ($verify as $key => $value) {
				$del->deleteFromFtpOrS3($value->passport);
				if (file_exists($value->passport)) {
					@unlink($value->passport);
				}
				$del->deleteFromFtpOrS3($value->photo);
		    
				if (file_exists($value->photo)) {
					@unlink($value->photo);
				}
			}
		}
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_VERIFY);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_COMMENTS_LIKES);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_COMMENTS_REPLY);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_COMMENTS_REPLY_LIKES);
		$delete = self::$db->where('user_id', $this->user_id)->delete(T_PAYMENTS);
		return $delete;
	}

	public function followSuggestions($limit = 50,$offset = false){
		if(empty(IS_LOGGED)){
			return false;
		}
		$data    = array();
		$user_id = self::$me->user_id;
		$sql     = sql('users/get.suggested.users',array(
			't_users' => T_USERS,
			't_conn' => T_CONNECTIV,
			't_blocks' => T_PROF_BLOCKS,
			'user_id' => $user_id,
			'total_limit' => $limit,
			'offset' => $offset,
		));
		try {
			$users = self::$db->rawQuery($sql);
		} 
		catch (Exception $e) {
			$users = array();
		}
		if (!empty($users)) {
			foreach ($users as $user) {
				$data[] = $this->userData($user);
			}
		}
		return $data;
	}

	public function followNewMembers($limit = 20,$offset = false){
		if(empty(IS_LOGGED)){
			return false;
		}
		$data    = array();
		$user_id = self::$me->user_id;
		$sql     = sql('users/get.new.users',array(
			't_users' => T_USERS,
			't_conn' => T_CONNECTIV,
			't_blocks' => T_PROF_BLOCKS,
			'user_id' => $user_id,
			'total_limit' => $limit,
			'offset' => $offset,
		));
		try {
			$users = self::$db->rawQuery($sql);
		} 
		catch (Exception $e) {
			$users = array();
		}
		if (!empty($users)) {
			foreach ($users as $user) {
				$data[] = $this->userData($user);
			}
		}
		return $data;
	}

	public function followPopularUsers($limit = 50,$offset = false){
		if(empty(IS_LOGGED)){
			return false;
		}
		$data    = array();
		$user_id = self::$me->user_id;
		$sql     = sql('users/get.popular.users',array(
			't_users' => T_USERS,
			't_conn' => T_CONNECTIV,
			't_blocks' => T_PROF_BLOCKS,
			'user_id' => $user_id,
			'total_limit' => $limit,
			'offset' => $offset,
		));
		try {
			$users = self::$db->rawQuery($sql);
		} 
		catch (Exception $e) {
			$users = array();
		}
		if (!empty($users)) {
			foreach ($users as $user) {
				$data[] = $this->userData($user);
			}
		}
		return $data;
	}

	public function followRandomUsers($limit = 50,$offset = false){
		if(empty(IS_LOGGED)){
			return false;
		}
		$data    = array();
		$user_id = self::$me->user_id;
		$sql     = sql('users/get.random.users',array(
			't_users' => T_USERS,
			't_conn' => T_CONNECTIV,
			't_blocks' => T_PROF_BLOCKS,
			'user_id' => $user_id,
			'total_limit' => $limit,
			'offset' => $offset,
		));
		try {
			$users = self::$db->rawQuery($sql);
		} 
		catch (Exception $e) {
			$users = array();
		}
		if (!empty($users)) {
			foreach ($users as $user) {
				$data[] = $this->userData($user);
			}
		}
		return $data;
	}

	public function isFollowing($following_id = null,$rev = false) {
		if (empty($following_id) || !is_numeric($following_id)) {
			return false;
		} else if(empty(IS_LOGGED)){
			return false;
		}
		$res = false;
		if ($rev === true && ($following_id != self::$me->user_id)) {
			self::$db->where('follower_id',$following_id);
			self::$db->where('following_id',self::$me->user_id);
			self::$db->where('type',1);
			$res = (self::$db->getValue(T_CONNECTIV,'COUNT(*)') > 0);
			if ($res == 0) {
				self::$db->where('follower_id',$following_id);
				self::$db->where('following_id',self::$me->user_id);
				self::$db->where('type',2);
				$res2 = (self::$db->getValue(T_CONNECTIV,'COUNT(*)') > 0);
				if ($res2 > 0) {
					$res = 2;
				}
			}
		} elseif ($following_id != self::$me->user_id) {
			self::$db->where('follower_id',self::$me->user_id);
			self::$db->where('following_id',$following_id);
			self::$db->where('type',1);
			$res = (self::$db->getValue(T_CONNECTIV,'COUNT(*)') > 0);
			if ($res == 0) {
				self::$db->where('follower_id',self::$me->user_id);
				self::$db->where('following_id',$following_id);
				self::$db->where('type',2);
				$res2 = (self::$db->getValue(T_CONNECTIV,'COUNT(*)') > 0);
				if ($res2 > 0) {
					$res = 2;
				}
			}
		}
		return $res;
	}

	public function unFollow($following_id = null){
		if (empty($following_id) || !is_numeric($following_id)) {
			return false;
		} else if(empty(IS_LOGGED)){
			return false;
		}
		self::$db->where('follower_id',self::$me->user_id);
		self::$db->where('following_id',$following_id);
		$res = self::$db->delete(T_CONNECTIV);
		return boolval($res);
	}

	public function follow($following_id = null){
		if (empty($following_id) || !is_numeric($following_id)) {
			return false;
		} else if(empty(IS_LOGGED) || (self::$me->user_id == $following_id)) {
			return false;
		}
		$following_data = $this->getUserDataById($following_id);
		if (!empty($following_data)) {
			if ($following_data->p_privacy == 0 || $following_data->p_privacy == 1) {
				if ($this->isFollowing($following_id) === true) {
					self::$db->where('follower_id',self::$me->user_id);
					self::$db->where('following_id',$following_id);
					self::$db->delete(T_CONNECTIV);
					self::$db->where('user_id' , self::$me->user_id)->where('following_id' , $following_id)->where('type' , 'followed_user')->delete(T_ACTIVITIES);
					return -1;
				} elseif ($this->isFollowing($following_id) == 2) {
					self::$db->where('follower_id',self::$me->user_id);
					self::$db->where('following_id',$following_id);
					self::$db->delete(T_CONNECTIV);

					#Delete the Notification
					self::$db->where('notifier_id',self::$me->user_id);
					self::$db->where('recipient_id',$following_id);
					self::$db->where('type','follow_request');
					self::$db->delete(T_NOTIF);
					return -1;
				} else{
					$re_data = array(
						'follower_id' => self::$me->user_id,
						'following_id' => $following_id,
						'active' => 0,
						'type' => 2,
						'time' => time()
					);

					self::$db->insert(T_CONNECTIV,$re_data);

					#Notify the user
					$notif        = new notify();
					$notif_conf = $notif->notifSettings($following_id,'on_follow_request');
					if ($notif_conf) {
						$re_data = array(
							'notifier_id' => self::$me->user_id,
							'recipient_id' => $following_id,
							'type' => 'follow_request',
							'url' => un2url(self::$me->username),
							'time' => time(),
						); $notif->notify($re_data);
					}
					return 2;
				}
			} else{
				if ($this->isFollowing($following_id) === true) {
					self::$db->where('follower_id',self::$me->user_id);
					self::$db->where('following_id',$following_id);
					self::$db->delete(T_CONNECTIV);
					self::$db->where('user_id' , self::$me->user_id)->where('following_id' , $following_id)->where('type' , 'followed_user')->delete(T_ACTIVITIES);
					return -1;
				} else{
					$re_data = array(
						'follower_id' => self::$me->user_id,
						'following_id' => $following_id,
						'active' => 1,
						'time' => time()
					);
					self::$db->insert(T_CONNECTIV,$re_data);
					self::$db->insert(T_ACTIVITIES,array('user_id' => self::$me->user_id, 'following_id' => $following_id, 'type' => 'followed_user', 'time' => time()));
					return 1;
				}
			}
		}
		return false;
	}

	public function getFollowers($offset = false,$limit = null){
		if (empty($this->user_id) || !is_numeric($this->user_id)) {
			return false;
		} else if (!empty($limit) && !is_numeric($limit)) {
			return false;
		}

		$user_id = $this->user_id;
		$t_users = T_USERS;
		$t_conn  = T_CONNECTIV;

		self::$db->join("{$t_conn} c","c.follower_id = u.user_id AND c.type = 1","INNER");
		self::$db->where("c.following_id",$user_id);
		self::$db->orderBy("u.user_id","DESC");

		if (!empty($offset) && is_numeric($offset)) {
			self::$db->where("u.user_id",$offset,'<');
		}
		$users = self::$db->get("{$t_users} u",$limit);
		$data  = array();

		foreach ($users as $key => $udata) {
			$udata = $this->userData($udata);
			$udata->is_following = false;
			if (IS_LOGGED) {
				$this->user_id = self::$me->user_id;
				$udata->is_following = $this->isFollowing($udata->user_id);
			}
			$data[]    = $udata;
		}
		return $data;
	}
	public function getUserRequests($offset = false,$limit = null){
		if (empty($this->user_id) || !is_numeric($this->user_id)) {
			return false;
		} else if (!empty($limit) && !is_numeric($limit)) {
			return false;
		}
		$user_id = $this->user_id;
		$t_users = T_USERS;
		$t_conn  = T_CONNECTIV;

		self::$db->join("{$t_conn} c","c.follower_id = u.user_id AND c.type = 2","INNER");
		self::$db->where("c.following_id",$user_id);
		self::$db->orderBy("c.id","DESC");

		if (!empty($offset) && is_numeric($offset)) {
			self::$db->where("u.user_id",$offset,'<');
		}
		$users = self::$db->get("{$t_users} u",$limit);
		$data  = array();
		foreach ($users as $key => $udata) {
			$udata = $this->userData($udata);
			$udata->is_following = false;

			$data[]    = $udata;
		}
		return $data;
	}
	
	public function getFollowing($offset = false,$limit = null){
			if (empty($this->user_id) || !is_numeric($this->user_id)) {
				return false;
			} else if (!empty($limit) && !is_numeric($limit)) {
				return false;
			}
			$user_id = $this->user_id;
			$t_users = T_USERS;
			$t_conn  = T_CONNECTIV;
			self::$db->join("{$t_conn} c","c.following_id = u.user_id AND c.type = 1","LEFT");
			self::$db->where("c.follower_id",$user_id);
			self::$db->orderBy("u.user_id","DESC");
			if (!empty($offset) && is_numeric($offset)) {
				self::$db->where("u.user_id",$offset,'<');
			}
			$users = self::$db->get("{$t_users} u",$limit);
			$data  = array();
			foreach ($users as $key => $udata) {
				$udata = $this->userData($udata);
				if (IS_LOGGED) {
					$this->user_id = self::$me->user_id;
					$udata->is_following = $this->isFollowing($udata->user_id);
				}
				$data[]    = $udata;
			}
			return $data;
	}
	
	public function countFollowers(){
		if (empty($this->user_id) || !is_numeric($this->user_id)) {
			return false;
		}
		$user_id = $this->user_id;
		$t_conn  = T_CONNECTIV;
		self::$db->where('following_id',$user_id)->where('type',1);
		return self::$db->getValue($t_conn,"COUNT(`id`)");
	}

	public function countFollowing(){
		if (empty($this->user_id) || !is_numeric($this->user_id)) {
			return false;
		}
		$user_id = $this->user_id;
		$t_conn  = T_CONNECTIV;
		self::$db->where('follower_id',$user_id)->where('type',1);
		return self::$db->getValue($t_conn,"COUNT(`id`)");
	}

	public function getUserId( $username){
		if (empty($username) || !is_string($username)) {
			return false;
		}
		$user_id  = false;
		$username = self::secure($username);
		self::$db->where('username',$username);
		$query = self::$db->getValue(T_USERS,'user_id');
		if (!empty($query)) {
			$user_id = $query;
		}
		return $user_id;
	}

	public function explorePeople($offset = false){
		$data    = array();
		$user_id = self::$me->user_id;
		$sql     = sql('users/explore.people',array(
			't_users' => T_USERS,
			't_conn' => T_CONNECTIV,
			't_posts' => T_POSTS,
			't_blocks' => T_PROF_BLOCKS,
			'total_limit' => $this->limit,
			'user_id' => $user_id,
			'offset' => $offset,
		));
		try {
			$users = self::$db->rawQuery($sql);
		} catch (Exception $e) {
			$users = array();
		} if (!empty($users)) {
			$data = $users;
		}
		return $data;
	}

	public function PremiumUsers($offset = false){
		$data    = array();
		$user_id = self::$me->user_id;
		$sql     = sql('users/get.pro.users',array(
			't_users' => T_USERS,
			't_conn' => T_CONNECTIV,
			'total_limit' => $this->limit,
			'user_id' => $user_id,
			'offset' => $offset,
		));
		try {
			$users = self::$db->rawQuery($sql);
		} 
		catch (Exception $e) {
			$users = array();
		} if (!empty($users)) {
			$data = $users;
		}
		return $data;
	}
	
	public function everyBody($offset = false){
		$data    = array();
		$user_id = self::$me->user_id;
		$sql     = sql('users/get.every.user',array(
			't_users' => T_USERS,
			't_conn' => T_CONNECTIV,
			'total_limit' => $this->limit,
			'user_id' => $user_id,
			'offset' => $offset,
		));
		try {
			$users = self::$db->rawQuery($sql);
		} catch (Exception $e) {
			$users = array();
		} if (!empty($users)) {
			$data = $users;
		}
		return $data;
	}

	public function profilePrivacy($user_id = false){
		if (empty($user_id) || !is_numeric($user_id) || empty(IS_LOGGED)) {
			return false;
		}
		self::$db->where('user_id',$user_id);
		$udata = self::$db->getOne(T_USERS,'p_privacy');
		$privacy   = false;
		if (!empty($udata)) {
			if ($user_id == self::$me->user_id) {
				$privacy = true;
			} else if ($udata->p_privacy == '2') {
				$privacy = true;
			} elseif ($udata->p_privacy == '1' && $this->isFollowing($user_id)) {
				$privacy = true;
			}
		}
		return $privacy;
	}

	public function chatPrivacy($user_id = false){
		if (empty($user_id) || !is_numeric($user_id) || empty(IS_LOGGED)) {
			return false;
		}
		self::$db->where('user_id',$user_id);
		$udata = self::$db->getOne(T_USERS,'c_privacy');
		$privacy   = false;
		if (!empty($udata)) {
			if ($udata->c_privacy == '1' && self::$me->c_privacy == '1') {
				$privacy = true;
			} elseif ($udata->c_privacy == '2' && self::$me->c_privacy == '1' && $this->isFollowing($user_id,true)) {
				$privacy = true;
			} elseif (self::$me->c_privacy == '2' && $udata->c_privacy == '1' && $this->isFollowing($user_id)) {
				$privacy = true;
			} elseif (($udata->c_privacy == '2' && $this->isFollowing($user_id,true)) &&  (self::$me->c_privacy == '2' && $this->isFollowing($user_id))) {
				$privacy = true;
			}
		}
		return $privacy;
	}

	public function isUserRepoted($user_id = false){
		if (empty(IS_LOGGED) || empty($user_id)) {
			return false;
		}
		self::$db->where('user_id',self::$me->user_id);
		self::$db->where('profile_id',$user_id);
		return (self::$db->getValue(T_USER_REPORTS,'COUNT(`id`)') > 0);
	}

	public function reportUser($user_id = false,$type = false,$report = false){
		if (empty(IS_LOGGED) || empty($user_id) || empty($type)) {
			return false;
		}
		$code = null;
		self::$db->insert(T_USER_REPORTS,array(
			'user_id' => self::$me->user_id,
			'profile_id' => $user_id,
			'type' => $type,
			'message' => $report,
			'time' => time()
		));
		$code = 1;
		return $code;
	}

	public function isBlocked($user_id = false,$rev = false){
		if (empty(IS_LOGGED) || empty($user_id)) {
			return false;
		}
		$blcoked = false;
		if ($rev === true && ($user_id != self::$me->user_id)) {
			self::$db->where('user_id',$user_id);
			self::$db->where('profile_id',self::$me->user_id);
			$blcoked = (self::$db->getValue(T_PROF_BLOCKS,'COUNT(`id`)') > 0);
		} else if($user_id != self::$me->user_id){
			self::$db->where('user_id',self::$me->user_id);
			self::$db->where('profile_id',$user_id);
			$blcoked = (self::$db->getValue(T_PROF_BLOCKS,'COUNT(`id`)') > 0);
		}
		return $blcoked;
	}

	public function unBlockUser($user_id = false){
		if (empty(IS_LOGGED) || empty($user_id)) {
			return false;
		}
		self::$db->where('user_id',self::$me->user_id);
		self::$db->where('profile_id',$user_id);
		return self::$db->delete(T_PROF_BLOCKS);
	}

	public function blockUser($user_id = false){
		if (empty(IS_LOGGED) || empty($user_id)) {
			return false;
		}
		self::$db->where('user_id',self::$me->user_id);
		self::$db->where('profile_id',$user_id);

		$code   = null;
		$bloked = self::$db->getValue(T_PROF_BLOCKS,'COUNT(`id`)');
		if (!empty($bloked)) {
			$this->unBlockUser($user_id);
			$code = -1;
		} else {
			self::$db->insert(T_PROF_BLOCKS,array(
				'user_id' => self::$me->user_id,
				'profile_id' => $user_id,
				'time' => time()
			));

			$code = 1;
			$this->unFollow($user_id);
			self::$db->where('following_id',self::$me->user_id);
			self::$db->where('follower_id',$user_id);
			self::$db->delete(T_CONNECTIV);
		}
		return $code;
	}

	public function getBlockedUsers(){
		if (empty(IS_LOGGED)) {
			return false;
		}
		$data  = array();
		$sql   = sql('users/get.blocked.users',array(
			't_users' => T_USERS,
			't_blocks' => T_PROF_BLOCKS,
			'user_id' => self::$me->user_id,
		));	
		try {
			$users = self::$db->rawQuery($sql);
		} catch (Exception $e) {
			$users = array();
		}
		if (!empty($users)) {
			foreach ($users as $user) {
				$user->name = $user->username;
				if (!empty($user->fname) && !empty($user->lname)) {
					$user->name = sprintf('%s %s',$user->fname,$user->lname);
				}
				$data[] = $user;
			}
		}
		return $data;
	}

	public function seachUsers($keyword = '',$limit = 100,$offset = 0,$order = 'DESC'){
		if (empty($keyword)) { return false; }
		$string = '';
		if (!empty($offset) && is_numeric($offset) && $offset > 0) {
			if ($order == 'DESC') {
				$string = " AND user_id < ".(int)$offset;
			}else{
				$string = " AND user_id > ".(int)$offset;
			}
		}
		$users = self::$db->rawQuery("SELECT * FROM ".T_USERS." WHERE (username LIKE '%$keyword%' OR email LIKE '%$keyword%' OR fname LIKE '%$keyword%' OR lname LIKE '%$keyword%') ".$string." ORDER BY user_id ".$order." LIMIT ".$limit);
		
		return $users;
	}

	public function sendVerificationRequest($data = array()){
		if (empty(IS_LOGGED) || empty($data)) {
			return false;
		}
		if (self::isVerificationRequested() > 0) {
			return false;
		}
		return self::$db->insert(T_VERIFY,$data);
	}

	public function isVerificationRequested(){
		global $me;
		if (empty(IS_LOGGED)) {
			return false;
		}
		self::$db->where('user_id',self::$me->user_id);
		return self::$db->getValue(T_VERIFY,'COUNT(*)');
	}

	public function is_verified($user_id){
		if (empty(IS_LOGGED) || empty($user_id)) {
			return false;
		}
		$user_id = self::secure($user_id);
		self::$db->where('user_id',$user_id);
		$is_verified = self::$db->get(T_USERS,1,'verified');
		$is_verified = $is_verified[0]->verified;
		return $is_verified;
	}

	public function getUserSessions(){
		global $me;
		if (empty(IS_LOGGED)) {
			return false;
		}
		self::$db->where('user_id',self::$me->user_id);
		self::$db->orderBy('id','DESC');
		return self::$db->get(T_SESSIONS);
	}

	public function delete_session($id){
		if (!empty($id)) {
			self::$db->where('id', $id);
			self::$db->delete(T_SESSIONS);
		}
	}

	public function getUserBrowser() {
	     $u_agent = $_SERVER['HTTP_USER_AGENT'];
	     $bname = 'Unknown';
	     $platform = 'Unknown';
	     $version= "";
	     // First get the platform?
	     if (preg_match('/linux/i', $u_agent)) {
	       $platform = 'linux';
	     } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
	       $platform = 'Mac';
	     } elseif (preg_match('/windows|win32/i', $u_agent)) {
	       $platform = 'windows';
	     } elseif (preg_match('/iphone|IPhone/i', $u_agent)) {
	       $platform = 'IPhone';
	     } elseif (preg_match('/android|Android/i', $u_agent)) {
	       $platform = 'Android';
	     } else if (preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $u_agent)) {
	       $platform = 'Mobile';
	     }
	     // Next get the name of the useragent yes seperately and for good reason
	     if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
	       $bname = 'Internet Explorer';
	       $ub = "MSIE";
	     } elseif(preg_match('/Firefox/i',$u_agent)) {
	       $bname = 'Mozilla Firefox';
	       $ub = "Firefox";
	     } elseif(preg_match('/Chrome/i',$u_agent)) {
	       $bname = 'Google Chrome';
	       $ub = "Chrome";
	     } elseif(preg_match('/Safari/i',$u_agent)) {
	       $bname = 'Apple Safari';
	       $ub = "Safari";
	     } elseif(preg_match('/Opera/i',$u_agent)) {
	       $bname = 'Opera';
	       $ub = "Opera";
	     } elseif(preg_match('/Netscape/i',$u_agent)) {
	       $bname = 'Netscape';
	       $ub = "Netscape";
	     }
	     // finally get the correct version number
	     $known = array('Version', $ub, 'other');
	     $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	     if (!preg_match_all($pattern, $u_agent, $matches)) {
	       // we have no matching number just continue
	     }
	     // see how many we have
	     $i = count($matches['browser']);
	     if ($i != 1) {
	       //we will have two since we are not using 'other' argument yet
	       //see if version is before or after the name
	       if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
	         $version= $matches['version'][0];
	       } else {
	         $version= $matches['version'][1];
	       }
	     } else {
	       $version= $matches['version'][0];
	     }
	     // check if we have a number
	     if ($version==null || $version=="") {$version="?";}
	     return array(
	         'userAgent' => $u_agent,
	         'name'      => $bname,
	         'version'   => $version,
	         'platform'  => $platform,
	         'pattern'    => $pattern,
	         'ip_address' => get_ip_address()
	     );
	}

	public function isInBlackList($username = '',$email = ''){
		$ip = get_ip_address();
		self::$db->where('value',$ip);
		$count = self::$db->getValue(T_BLACKLIST,'COUNT(*)');
		if ($count > 0) {
			return array('count' => $count , 'type' => 'ip');
		} if (!empty($username) && empty($email)) {
			$username = self::secure($username);
			self::$db->where('value',$username);
			$count = self::$db->getValue(T_BLACKLIST,'COUNT(*)');
			return array('count' => $count , 'type' => 'username');
		} if (empty($username) && !empty($email)) {
			$email = self::secure($email);
			self::$db->where('value',$email);
			$count = self::$db->getValue(T_BLACKLIST,'COUNT(*)');
			return array('count' => $count , 'type' => 'email');
		} if (!empty($username) && !empty($email)) {
			$username = self::secure($username);
			$email = self::secure($email);
			self::$db->where('value',array($email,$username),'IN');
			$count = self::$db->getValue(T_BLACKLIST,'COUNT(*)');
			return array('count' => $count , 'type' => 'email_username');
		}
	}

	public function GetProUsers($limit = 9){
		$data = array();
		$users = self::$db->where('is_pro' , 1)->orderBy('RAND()')->get(T_USERS,$limit);
		if (!empty($users)) {
			foreach ($users as $key => $user) {
				$data[] = self::getUserDataById($user->user_id);
			}
		}
		return $data;
	}

	public function GetUserAds(){
		$ads = self::$db->where('user_id', self::$me->user_id)->orderBy('id','DESC')->get(T_ADS);
		$data = array();
		if (!empty($ads)) {
			foreach ($ads as $key => $ad) {
				$new_ad = $ad;
				$new_ad->edit_url = self::$config['site_url'].'/edit_ad/'.$ad->id;
				$data[] = $new_ad;
			}
		}
		return $data;
	}

	public function GetAdByID($id){
		if (empty($id) || !is_numeric($id) || $id < 1) {
			return false;
		}
		$id = self::secure($id);
		$ad = self::$db->where('id' , $id)->getOne(T_ADS);
		return $ad;
	}

	public function GetRandomAd($type = 'post'){
		$ads_array = array();
		$type = self::secure($type);
		if (!empty($_SESSION['ads'])) {
			$ads_array = explode(',', $_SESSION['ads']);
			self::$db->where('id', $ads_array, 'NOT IN');
		}
		$sql   = "(`audience` LIKE '%".self::$me->country_id."%')";
		$ad = self::$db->where('status',1)->where('appears',$type)->where($sql)->orderBy('RAND()')->getOne(T_ADS);
		if (!empty($ad) && $ad->bidding == 'views' && !in_array($ad->id, $ads_array)) {
			self::$db->where('id', $ad->id)->update(T_ADS,array('views' => self::$db->inc(1)));
			self::$db->where('user_id', $ad->user_id)->update(T_USERS,array('balance' => self::$db->dec(self::$config['ad_v_price'])));
			$ad_user = self::$db->where('user_id', $ad->user_id)->getOne(T_USERS);
			$user_wallet = $ad_user->balance - self::$config['ad_v_price'];
			if ($user_wallet < self::$config['ad_v_price']) {
				self::$db->where('id', $ad->id)->update(T_ADS,array('status' => 0));
			}
			$ads_array[] = $ad->id;
			$_SESSION['ads'] = implode(',', $ads_array);
		}
		return $ad;
	}

	public function getUserAffiliates(){
		$users = self::$db->where('referrer', self::$me->user_id)->orderBy('user_id','DESC')->get(T_USERS);
		$data = array();
		if (!empty($users)) {
			foreach ($users as $key => $user) {
				$data[] = $this->getUserDataById($user->user_id);
			}
		}
		return $data;
	}

	public function CountUserAffiliates(){
		return self::$db->where('referrer', self::$me->user_id)->getValue(T_USERS,"COUNT(*)");
	}

	public function add_visit(){
		if (empty($this->user_id)) {
			return false;
		}
		$user_id = $this->user_id;
		$view = 1;
		$count    = 0;
		$hash = sha1($user_id);
		if (isset($_COOKIE[$hash])) {
			self::$db->where('user_id',$user_id);
			$count = self::$db->getValue(T_USERS,'visits');
		}
		else{
			setcookie($hash,$hash,time()+(60*60*2));
			self::$db->where('user_id',$user_id)->update(T_USERS,array('visits' => self::$db->inc($view)));
			self::$db->where('user_id',$user_id);
			$count = self::$db->getValue(T_USERS,'visits');
		}
		return $count;
	}
}

// function TwoFactor() {
//     global $config, $db;
//     if($config['two_factor'] === 0) {
//         return true;
//     }
// 	$user_object = new User();
// 	$udata = $user_object->userData($user_object->getUser());
// 	$user_id = $udata->user_id;
//     if ($udata->two_factor == 0 || $udata->two_factor_verified == 0) {
//         return true;
//     }
//     $code = rand(111111, 999999);
//     $hash_code = md5($code);
//     $update_code =  $db->where('user_id', $user_id)->update(T_USERS, array('email_code' => $hash_code));
//     $message = "Your confirmation code is: $code";
//     if (!empty($udata->phone_number) && ($config['two_factor'] === 'both' || $config['two_factor'] === 'phone')) {
//        // $send_message = SendSMS($udata->phone_number, $message);
//     }
//     if ($config['two_factor'] === 'both' || $config['two_factor'] === 'email') {
//         //$send = SendEmail($udata->email,'Please verify that it\'s you',$message,false);
//     }
//     return false;
// }