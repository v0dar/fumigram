<?php 
class cpanel extends users{
	public $uis = array();
	public $currp = 'overview';
	public function Senction($path = ""){
		global $cpanel,$context,$config,$me;
		$cpanel = $this;
		$path  = self::secure($path);
		$cd    = $context;
	    $ui  = "cpanel/uis/$path.phtml";
	    if (!file_exists($ui)) {
	        die("File not Exists : $ui");
	    }
	    $echo = '';
	    ob_start();
	    require($ui);
	    $echo = ob_get_contents();
	    ob_end_clean();
	    return $echo;
	}

	public function totalUsers(){
		return self::$db->getValue(T_USERS,'COUNT(`user_id`)');
	}

	public function totalPosts(){
		return self::$db->getValue(T_POSTS,'COUNT(`post_id`)');
	}

	public function totalVideos(){
		self::$db->where('type',array('youtube','vimeo','dailymotion','video'),"IN");
		return self::$db->getValue(T_POSTS,'COUNT(`post_id`)');
	}

	public function totalPhotos(){
		self::$db->where('type',array('image','gif'),"IN");
		return self::$db->getValue(T_POSTS,'COUNT(`post_id`)');
	}

	public function totalGifs(){
		self::$db->where('type',array('gif'),"IN");
		return self::$db->getValue(T_POSTS,'COUNT(`post_id`)');
	}

	public function totalTiles(){
		self::$db->where('type',array('tile'),"IN");
		return self::$db->getValue(T_POSTS,'COUNT(`post_id`)');
	}

	public function totalLikes(){
		return self::$db->getValue(T_POST_LIKES,'COUNT(`id`)');
	}

	public function onlineUsers(){
		self::$db->where('last_seen',(time() - 60),'>');
		return self::$db->getValue(T_USERS,'COUNT(`user_id`)');
	}

	public function totalComments(){
		return self::$db->getValue(T_POST_COMMENTS,'COUNT(`id`)');
	}

	public function totalMessages(){
		return self::$db->getValue(T_MESSAGES,'COUNT(`id`)');
	}

	public function activeUsers(){
		self::$db->where('active', '1');
		return self::$db->getValue(T_USERS,'COUNT(`user_id`)');
	}

	public function verfiedUsers(){
		self::$db->where('verified', '1');
		return self::$db->getValue(T_USERS,'COUNT(`user_id`)');
	}

	public function unverifiedUsers(){
		self::$db->where('verified', '0');
		return self::$db->getValue(T_USERS,'COUNT(`user_id`)');
	}

	public function inactiveUsers(){
		self::$db->where('active', '0');
		return self::$db->getValue(T_USERS,'COUNT(`user_id`)');
	}

	public function deactivatedUsers(){
		self::$db->where('active', '2');
		return self::$db->getValue(T_USERS,'COUNT(`user_id`)');
	}

	public function totalGuys(){
		self::$db->where('gender', 'male');
		return self::$db->getValue(T_USERS,'COUNT(`user_id`)');
	}

	public function totalLadies(){
		self::$db->where('gender', 'female');
		return self::$db->getValue(T_USERS,'COUNT(`user_id`)');
	}

	public function proUsers(){
		self::$db->where('is_pro', '1');
		return self::$db->getValue(T_USERS,'COUNT(`user_id`)');
	}

	public function BusinessUsers(){
		self::$db->where('business_account', '1');
		return self::$db->getValue(T_USERS,'COUNT(`user_id`)');
	}

	public function totalStories(){
		return self::$db->getValue(T_STORY,'COUNT(`id`)');
	}

	public function totalImageStories(){
		self::$db->where('type',array('image'),"IN");
		return self::$db->getValue(T_STORY,'COUNT(`id`)');
	}

	public function totalVideoStories(){
		self::$db->where('type',array('video'),"IN");
		return self::$db->getValue(T_STORY,'COUNT(`id`)');
	}

	public function totalCommentsLikes(){
		return self::$db->getValue(T_COMMENTS_LIKES,'COUNT(`id`)');
	}

	public function totalCommentsReply(){
		return self::$db->getValue(T_COMMENTS_REPLY,'COUNT(`id`)');
	}

	public function totalCommentsReplyLikes(){
		return self::$db->getValue(T_COMMENTS_REPLY_LIKES,'COUNT(`id`)');
	}

	public function activeMenu($ui = 'overview'){
		if ($ui == $this->currp) {
			return 'active';
		}elseif (in_array($ui, array_keys($this->uis)) && in_array($this->currp, $this->uis[$ui])) {
			return 'active';
		}elseif ($ui == $this->currp) {
			return 'active';
		}
	}

	public function updateSettings($up_data = array()){
		if (empty($up_data)) {
			return false;
		}
		foreach ($up_data as $name => $value) {
			try {
				self::$db->where('name', $name)->update(T_CONFIG,array('value' => $value));
			} catch (Exception $e) {
				return false;
			}
		}
		return true;
	}

	function UploadLogo($data = array(),$type = 'logo') {
	    if (isset($data['file']) && !empty($data['file'])) {
	        $data['file'] =  self::secure($data['file']);
	    }
	    if (isset($data['name']) && !empty($data['name'])) {
	        $data['name'] =  self::secure($data['name']);
	    }
	    if (isset($data['name']) && !empty($data['name'])) {
	        $data['name'] =  self::secure($data['name']);
	    }
	    if (empty($data)) {
	        return false;
	    }
	    $allowed           = 'jpg,png,jpeg,gif';
	    $new_string        = pathinfo($data['name'], PATHINFO_FILENAME) . '.' . strtolower(pathinfo($data['name'], PATHINFO_EXTENSION));
	    $extension_allowed = explode(',', $allowed);
	    $file_extension    = pathinfo($new_string, PATHINFO_EXTENSION);
	    if (!in_array($file_extension, $extension_allowed)) {
	        return false;
	    }
	    $dir      = "media/img/";
	    if ($type == 'fav') {
	    	$filename = $dir . "icon.{$file_extension}";
		    if (move_uploaded_file($data['file'], $filename)) {
		        if ($this->updateSettings(array('favicon_extension' => $file_extension))) {
		            return true;
		        }
		    }
	    } else if ($type == 'logo-light') {
            $filename = $dir . "light-logo.{$file_extension}";
            if (move_uploaded_file($data['file'], $filename)) {
                if ($this->updateSettings(array('logo_extension' => $file_extension))) {
                    return true;
                }
            }
        }else{
	    	$filename = $dir . "logo.{$file_extension}";
		    if (move_uploaded_file($data['file'], $filename)) {
		        if ($this->updateSettings(array('logo_extension' => $file_extension))) {
		            return true;
		        }
		    }
	    }
	}

	function CpanelLogo($data = array(),$type = 'logo') {
	    if (isset($data['file']) && !empty($data['file'])) {
	        $data['file'] =  self::secure($data['file']);
	    }
	    if (isset($data['name']) && !empty($data['name'])) {
	        $data['name'] =  self::secure($data['name']);
	    }
	    if (isset($data['name']) && !empty($data['name'])) {
	        $data['name'] =  self::secure($data['name']);
	    }
	    if (empty($data)) {
	        return false;
	    }
	    $allowed           = 'jpg,png,jpeg,gif';
	    $new_string        = pathinfo($data['name'], PATHINFO_FILENAME) . '.' . strtolower(pathinfo($data['name'], PATHINFO_EXTENSION));
	    $extension_allowed = explode(',', $allowed);
	    $file_extension    = pathinfo($new_string, PATHINFO_EXTENSION);
	    if (!in_array($file_extension, $extension_allowed)) {
	        return false;
	    }
	    $dir      = "cpanel/si/img/logo/";
	    if ($type == 'logo-day') {
            $filename = $dir . "logo-day.{$file_extension}";
            if (move_uploaded_file($data['file'], $filename)) {
                if ($this->updateSettings(array('logo_extension' => $file_extension))) {
                    return true;
                }
            }
        }else{
	    	$filename = $dir . "logo-night.{$file_extension}";
		    if (move_uploaded_file($data['file'], $filename)) {
		        if ($this->updateSettings(array('logo_extension' => $file_extension))) {
		            return true;
		        }
		    }
	    }
	}
}