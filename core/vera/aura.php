<?php 
/* Aura Controller class which contains the main functions and modifers */
class aura {
	public static $db,$site_url, $config,$theme,$mysqli,$user,$loggedin,$langs;
	public function __construct($data = array()){
		self::$db         = $data['db'];
		self::$site_url   = $data['site_url'];
		self::$config     = $data['config'];
		self::$theme      = self::$config['theme'];
		self::$mysqli     = $data['mysqli'];
		self::$user       = self::LoggedInUser();
		self::$loggedin   = self::isLogged_();
		self::$langs      = self::Langs();
	}

	public static function intel($page = '', $data = array(), $set_lang = true) {
		global $context,$me,$ui,$post_data,$comment,$msg_data,$udata,$site_url;
		$larr   = $context['lang'];
		$config = self::$config;
	    $app    = dirname(dirname(__dir__)).DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR . self::$theme . DIRECTORY_SEPARATOR . $page . '.html';
	    if (!file_exists($app)) {
	        die("File not Exists : $app");
	    }
	    $intel = '';
	    ob_start();
	    require($app);
	    $intel = ob_get_contents();
	    ob_end_clean();
	    if ($set_lang == true) {
	        $intel = preg_replace_callback("/{{LANG (.*?)}}/", function($m) use ($larr) {
				$lang = '';
				if(isset($larr[$m[1]])){
					$lang = $larr[$m[1]];
				}else{
					$lang = '';
				}
	            return $lang;
	        }, $intel);
	    }
	    if (!empty($data) && is_array($data)) {
	        foreach ($data as $key => $replace) {
	            if ($key == 'USER_DATA') {
	                $replace = ToArray($replace);
	                $intel = preg_replace_callback("/{{USER (.*?)}}/", function($m) use ($replace) {
	                    return (isset($replace[$m[1]])) ? $replace[$m[1]] : '';
	                }, $intel);
	            } else {
	                $re_rp = "{{" . $key . "}}";
	                $intel      = str_replace($re_rp, $replace, $intel);
	            }
	        }
	    }

	    if (self::$loggedin == true) {
	        $replace = o2array(self::$user);
	        $intel = preg_replace_callback("/{{ME (.*?)}}/", function($m) use ($replace) {
	            return (isset($replace[$m[1]])) ? $replace[$m[1]] : '';
	        }, $intel);
	    }

	    $intel = preg_replace("/{{LINK (.*?)}}/", self::UI_Link("$1"), $intel);
	    $intel = preg_replace_callback("/{{CONFIG (.*?)}}/", function($m) use ($config) {
	        return (isset(self::$config[$m[1]])) ? self::$config[$m[1]] : '';
	    }, $intel);
	    return $intel;
	}
	
	public static function UI_Link($string) {
	    global $site_url;
	    return $site_url . '/' . $string;
	}

    // Convert object to array
	public static function toObject($array) {
	    $object = new \stdClass();
	    foreach ($array as $key => $value) {
	        if (is_array($value)) {
	            $value = self::toObject($value);
	        }
	        if (isset($value)) {
	            $object->$key = $value;
	        }
	    }
	    return $object;
	}

	// Convert object to array
	public static function toArray($obj) {
	    if (is_object($obj))
	        $obj = (array) $obj;
	    if (is_array($obj)) {
	        $new = array();
	        foreach ($obj as $key => $val) {
	            $new[$key] = self::toArray($val);
	        }
	    } else {
	        $new = $obj;
	    }
	    return $new;
	}

	// Secure strings 
	public static function secure($string) {
	    $string = trim($string);
	    $string = mysqli_real_escape_string(self::$mysqli,$string);
	    $string = htmlspecialchars($string, ENT_QUOTES);
	    $string = str_replace('\r\n', " <br>", $string);
	    $string = str_replace('\n\r', " <br>", $string);
	    $string = str_replace('\r', " <br>", $string);
	    $string = str_replace('\n', " <br>", $string);
	    $string = str_replace('&amp;#', '&#', $string);
	    $string = stripslashes($string);
	    return $string;
	}

	// Add media string to site link
    public static function getMedia($media) {
	    return sprintf('%s/%s',self::$site_url,$media);
    }

    // Geenrate random string
	public function generateKey($minlength = 20, $maxlength = 20, $uselower = true, $useupper = true, $usenumbers = true, $usespecial = false) {
	    $charset = '';
	    if ($uselower) {
	        $charset .= "abcdefghijklmnopqrstuvwxyz";
	    } if ($useupper) {
	        $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	    } if ($usenumbers) {
	        $charset .= "123456789";
	    } if ($usespecial) {
	        $charset .= "~@#$%^*()_+-={}|][";
	    } if ($minlength > $maxlength) {
	        $length = mt_rand($maxlength, $minlength);
	    } else {
	        $length = mt_rand($minlength, $maxlength);
	    }
	    $key = '';
	    for ($i = 0; $i < $length; $i++) {
	        $key .= $charset[(mt_rand(0, strlen($charset) - 1))];
	    }
	    return $key;
	}

	// Raise Exceptions
	public function throwError($message) {
		throw new \Exception($message);
		return;
	}

	// Crop strings
	public static function cropText($text = "", $len = 100) {
	    if (empty($text) || !is_string($text) || !is_numeric($len) || $len < 1) {
	        return "****";
	    }
	    if (strlen($text) > $len) {
	        $text = mb_substr($text, 0, $len, "UTF-8") . "...";
	    }
	    return $text;
	}

	// Check if url is valid
	public static function isUrl($url = "") {
	    if (empty($url)) {
	        return false;
	    }
	    if (filter_var($url, FILTER_VALIDATE_URL)) {
	        return true;
	    }
	    return false;
	}

	// Decode links
	public function decodeMarkup($text, $link = true) {
	    if ($link == true) {
	        $search = '/\[a\](.*?)\[\/a\]/i';
	        if (preg_match_all($search, $text, $matches)) {
	            foreach ($matches[1] as $match) {
	                $match_decode     = urldecode($match);
	                $decode_url       = $match_decode;
	                $count_url        = mb_strlen($match_decode);
	                $match_url        = $match_decode;
	                if (!preg_match("/http(|s)\:\/\//", $match_decode)) {
	                    $match_url = 'http://' . $match_url;
	                }
	                $text = str_replace('[a]' . $match . '[/a]', $decode_url, $text);
	            }
	        }
	    }
	    return $text;
	}

	// Encode markup, and create links inside strings
	public function encodeMarkup($text, $link = true) {
	    if ($link == true) {
	        $link_search = '/\[a\](.*?)\[\/a\]/i';
	        if (preg_match_all($link_search, $text, $matches)) {
	            foreach ($matches[1] as $match) {
	                $match_decode     = urldecode($match);
	                $decode_url       = $match_decode;
	                $count_url        = mb_strlen($match_decode);
	                if ($count_url > 50) {
	                    $decode_url = mb_substr($decode_url, 0, 30) . '....' . mb_substr($decode_url, 30, 20);
	                }
	                $match_url = $match_decode;
	                if (!preg_match("/http(|s)\:\/\//", $match_decode)) {
	                    $match_url = 'http://' . $match_url;
	                }
	                $text = str_replace('[a]' . $match . '[/a]', '<a href="' . strip_tags($match_url) . '" target="_blank" class="hash" rel="nofollow">' . $decode_url . '</a>', $text);
	            }
	        }
	    }
	    return $text;
	}

	// check if string is json
	public function isJson($string) {
	 	json_decode($string);
	 	return (json_last_error() == JSON_ERROR_NONE);
	}

	// CURL connection, GET data
	public function curlConnect($url = '', $config = []) {
	    if (empty($url)) {
	        return false;
	    }
	    $curl = curl_init($url);
	    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true );
	    curl_setopt($curl, CURLOPT_HEADER, false );
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7");
	    if (!empty($config['POST'])) {
	    	curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $config['POST']);
	    }
	    if (!empty($config['bearer'])) {
	    	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		    	'Authorization: Bearer ' . $config['bearer']
		    ));
	    }
	    $curl_response = curl_exec($curl);
	    curl_close($curl);
	    if ($this->isJson($curl_response)) {
	    	return json_decode($curl_response, true);
	    }
	    return $curl_response;
	}

	public function lastQuery(){
		return self::$db->getLastQuery();
	}

	public static function createHtmlEl($name = "html",$attrs = array(),$cont = ""){
		$tag_attrs = "";
		if (!empty($attrs) && is_array($attrs)) {
			foreach ($attrs as $attr => $value) {
				$tag_attrs .= " $attr=\"$value\"";
			}
		}
		return "<$name$tag_attrs>$cont</$name>";
	}

	public function Langs(){
		$t_langs = T_LANGS;
		$query   = self::$db->rawQuery("SHOW COLUMNS FROM `$t_langs`");
		$langs   = array();
		if (!empty($query)) {
			foreach ($query as $lang) {
				$langs[$lang->Field] = ucfirst($lang->Field);
			}try {
				unset($langs['id']);
                unset($langs['ref']);
				unset($langs['lang_key']);
			} catch (Exception $e) {}
		}
		return $langs;
	}

	public function fetchLanguage($lname = 'english'){
		try {
			$data = array();
			$lang = self::$db->get(T_LANGS,null,array('id','lang_key' ,$lname));
			if (!empty($lang) && is_array($lang)) {
				foreach ($lang as $val) {
					$data[$val->lang_key] = $val->{"$lname"};
				}
			}
			return $data;
		} 
		catch (Exception $e) {
			return array();
		}
	}

	static function decode($html = ''){
		return decode($html);
	}

	static function encode($html = ''){
		return encode($html);	
	}

	static function sendMail($data = array()) {
		if (empty($data)) {return false;}
		require_once('core/zipy/PHPMailer/PHPMailerAutoload.php');
		$mail            = new PHPMailer();
	    $data['charSet'] = self::secure($data['charSet']);
	    if (self::$config['smtp_or_mail'] == 'mail') {
	        $mail->IsMail();
	    } else if (self::$config['smtp_or_mail'] == 'smtp') {
	        $mail->isSMTP();
	        $mail->Host        = self::$config['smtp_host'];
	        $mail->SMTPAuth    = true;
	        $mail->Username    = self::$config['smtp_username'];
	        $mail->Password    = openssl_decrypt(self::$config['smtp_password'], "AES-128-ECB", 'mysecretkey1234');
	        $mail->SMTPSecure  = self::$config['smtp_encryption'];
	        $mail->Port        = self::$config['smtp_port'];
	        $mail->SMTPOptions = array('ssl' => array('verify_peer' => false,'verify_peer_name' => false,'allow_self_signed' => true));
	    } else {
	        return false;
	    }
	    $mail->IsHTML($data['is_html']);
	    $mail->setFrom($data['from_email'], $data['from_name']);
	    $mail->addAddress($data['to_email'], $data['to_name']);
	    $mail->Subject = $data['subject'];
	    $mail->CharSet = $data['charSet'];
	    $mail->MsgHTML($data['message_body']);
	    if ($mail->send()) {
	        $mail->ClearAddresses();
	        return true;
	    }

	}

	static function Themes() {
	    $themes = glob('apps/*', GLOB_ONLYDIR);
	    $data   = array();
	    if (!empty($themes) && is_array($themes)) {
	    	foreach ($themes as $key => $theme) {
	    		$i = array('folder' => str_replace('apps/', '', $theme), 'author' => '', 'name' => '', 'version' => '', 'email' => '', 'cover' => '');
	    		if (file_exists("$theme/info.php")) {
	    			require_once "$theme/info.php";
	    			$theme_folder = $i['folder'];
	    			$i['author']  = (isset($themeAuthor)) ? $themeAuthor : '';
	    			$i['name']    = (isset($themeName)) ? $themeName : '';
	    			$i['version'] = (isset($themeVersion)) ? $themeVersion : '';
	    			$i['cover']   = (isset($themeCover)) ? sprintf('%s/apps/%s/%s',self::$site_url,$theme_folder,$themeCover) : '';
	    			$i['email']   = (isset($themeEmail)) ? $themeEmail : '';
	    		}
	    		$data[] = $i;
	    	}
	    }
	    return $data;
	}

	public function upsertHtags($text = ""){
		if (!empty($text)) {
		    $reg = '/#([^`~!@$%^&*\#()\-+=\\|\/\.,<>?\'\":;{}\[\]* ]+)/i';
			preg_match_all($reg, $text, $htags);
			if (!empty($htags[1]) && is_array($htags[1])) {
				$htags = $htags[1];
				foreach ($htags as $key => $htag) {
				    if($htag == 'http' || $htag == 'https'){
				        $htag = $text;
                    }
					$htid   = 0;
					$hash   = md5($htag);
					$week   = date('Y-m-d', strtotime(date('Y-m-d') . " +1week"));
					$data   = self::$db->where('tag',self::secure($htag))->getOne(T_HTAGS,array('id'));
					if (!empty($data)) {
						$htid = $data->id;
						self::$db->where('id',$htid)->update(T_HTAGS,array('last_trend' => time(), 'used' => self::$db->inc(1)));
					}else{
						$htid = self::$db->insert(T_HTAGS,array('hash' => $hash, 'tag' => $htag, 'last_trend' => time(), 'expiring' => $week, 'used' => self::$db->inc(1)));
					}
					$text = str_replace("#$htag", "#[$htid]", $text);
				}
			}
		}
		return $text;
	}

	public function linkifyHTags($text = ""){
		$surl = self::$site_url;
		$text = str_replace('&#039;', "'", $text);
        $reg = '/#([^`~!@$%^&*\#* ]+)/i';
        $reg_ = '/#([^`~!@$%^&*\#()\-+=\\|\/\.,<>?\'\":;{}\[\]* ]+)/i';
	    $text = preg_replace_callback($reg, function($m) use($surl) {
			$tag = $m[1];
			$create = self::createHtmlEl('a',array('href' => sprintf("%s/explore/tags/%s",$surl,$tag), 'class' => 'hashtag', 'data-ajax' => 'load.php?app=explore&apph=tags&tag='.$tag),"#$tag");
	        return $create;
	    }, $text);
	    return $text;
	}

	function link_Markup($text, $link = true) {
	    if ($link == true) {
	        $link_search = '/\[a\](.*?)\[\/a\]/i';
	        if (preg_match_all($link_search, $text, $matches)) {
	            foreach ($matches[1] as $match) {
	                $match_decode     = urldecode($match);
	                $decode_url       = $match_decode;
	                $count_url        = mb_strlen($match_decode);
	                if ($count_url > 50) {
	                    $decode_url = mb_substr($decode_url, 0, 30) . '....' . mb_substr($decode_url, 30, 20);
	                }
	                $match_url = $match_decode;
	                if (!preg_match("/http(|s)\:\/\//", $match_decode)) {
	                    $match_url = 'http://' . $match_url;
	                }
	                $text = str_replace('[a]' . $match . '[/a]', '<a href="' . strip_tags($match_url) . '" target="_blank" class="hash" rel="nofollow">' . $decode_url . '</a>', $text);
	            }
	        }
	    }
	    return $text;
	}

	public function linkifyText($text =""){
        if (!empty($text) && is_string($text)) {
            preg_match_all('/(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/im', $text, $matches, PREG_SET_ORDER, 0);
            foreach ($matches as $match) {
                if( $match[0] !== 'http://' && $match[0] !== 'https://' ) {
                    if (preg_match("/http(|s)\:\/\//", $match[0])) {
                        $text = str_replace( $match[0] , '<a href="' . strip_tags($match[0]) . '" target="_blank" class="hash" rel="nofollow">' . $match[0] . '</a>', $text);
                    }
                }
            }
        }
        return $text;
    }

    // convert array to json for API
	public static function json($response_data) {
	    if (!empty($response_data)) {
	    	header("HTTP/1.1 ".$response_data['code']." ".$response_data['status']);
	        header("Content-Type:application/json");
	        if(!empty($response_data)){
	           echo json_encode($response_data, JSON_UNESCAPED_UNICODE);
	        }
	    }
	    exit();
	}

	public static function is_valid_access_token() {
		$access_token = self::secure($_POST['access_token']);
	    $id = self::$db->where('session_id', $access_token)->getValue(T_SESSIONS, 'user_id');
	    return (is_numeric($id) && !empty($id)) ? true : false;
	}

	public function EndPointRequest(){
	//    if (strstr( $_SERVER['SCRIPT_NAME'], '/endpoints.php' ) !== '/endpoints.php' ) {
	//        return false;
	//    }else{
	//        return true;
	//    }
	}

	public function change_site_mode($up_data){
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

	public function LoggedInUser() {
		// * API *
		if (!empty($_SESSION['user_id'])) {
			$session_id = $_SESSION['user_id'];
		}
		elseif (isset($_POST['access_token']) && !empty($_POST['access_token'])) {
			$session_id = aura::secure($_POST['access_token']);
		}
		else{
			$session_id = !empty($_COOKIE['user_id']) ? $_COOKIE['user_id'] : '';
		}
		//$session_id = (!empty($_SESSION['user_id'])) ? $_SESSION['user_id'] : $_COOKIE['user_id'];
        $user_id  = self::$db->where('session_id', $session_id)->getValue(T_SESSIONS, 'user_id');
		return $this->fetchLoggedUser($user_id);
	}

	public function isLogged_() {
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
	
	public function fetchLoggedUser($id) {
		$user_id = self::secure($id);
	    $udata = self::$db->where('user_id', $user_id)->getOne(T_USERS);
	    if (empty($udata)) {
	    	return false;
	    }
		$udata->name  = $udata->username;
	    if (!empty($udata->fname) && !empty($udata->lname)) {
	    	$udata->name = sprintf('%s %s',$udata->fname,$udata->lname);
	    }
	    $udata->avatar = media($udata->avatar);
	    $udata->banner = media($udata->banner);
	    $udata->uname = sprintf('%s',$udata->username);
	    $udata->url = sprintf('%s/%s',self::$site_url,$udata->username);
	    return $udata;
	}

	public function createBackup() {
		$time    = time();
		$date    = date('d-m-Y');
		$mysqld  = new MySQLDump(self::$mysqli);
	    if (!file_exists("backups/$date")) {
	        @mkdir("backups/$date", 0777, true);
	    }
	    if (!file_exists("backups/$date/$time")) {
	        mkdir("backups/$date/$time", 0777, true);
	    }
	    if (!file_exists("backups/$date/$time/index.html")) {
	        $f = @fopen("backups/$date/$time/index.html", "a+");
	        @fclose($f);
	    }
	    if (!file_exists('backups/.htaccess')) {
	        $f = @fopen("backups/.htaccess", "a+");
	        @fwrite($f, "deny from all\nOptions -Indexes");
	        @fclose($f);
	    }
	    if (!file_exists("backups/$date/index.html")) {
	        $f = @fopen("backups/$date/index.html", "a+");
	        @fclose($f);
	    }
	    if (!file_exists('backups/index.html')) {
	        $f = @fopen("backups/index.html", "a+");
	        @fwrite($f, "");
	        @fclose($f);
	    }

	    $folder_name = "backups/$date/$time";
	    $put         = $mysqld->save("$folder_name/SQL-$time-$date.sql");
	    try {
	    	$rootPath = ROOT;
	        $zip      = new ZipArchive();
	        $act      = (ZipArchive::CREATE | ZipArchive::OVERWRITE);
	        $open     = $zip->open("$folder_name/Files-$time-$date.zip",$act);
	        if ($open !== true) {
	            return false;
	        }
	        $riiter = RecursiveIteratorIterator::LEAVES_ONLY;
	        $rditer = new RecursiveDirectoryIterator($rootPath);
	        $files  = new RecursiveIteratorIterator($rditer,$riiter);
	        foreach ($files as $name => $file) {
	            if (!preg_match('/\bbackups\b/', $file)) {
	                if (!$file->isDir()) {
	                    $filePath     = $file->getRealPath();
	                    $relativePath = substr($filePath, strlen($rootPath) + 1);
	                    $zip->addFile($filePath, $relativePath);
	                }
	            }
	        }
	        $zip->close();
	        self::$db->where('name','last_backup')->update(T_CONFIG,array('value' => date('Y-m-d h:i:s')));
	        return true;	
	    } catch (Exception $e) {
	    	return false;
	    }
	}
}
