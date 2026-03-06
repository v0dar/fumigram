<?php
function UploadToDigitalOcean($filename, $delete = true) {
    global $config; 
    // Create a new cURL resource
    $curl = curl_init();

    if (!$curl) {
        die("Couldn't initialize a cURL handle");
    }

    // Set the file URL to fetch through cURL
    curl_setopt($curl, CURLOPT_URL, $config['site_url'] . "/command.php");

    // Set a different user agent string (Googlebot)
    curl_setopt($curl, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)');

    // Follow redirects, if any
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

    // Fail the cURL request if response code = 400 (like 404 errors)
    curl_setopt($curl, CURLOPT_FAILONERROR, true);

    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS,
            "file=".$filename."&is_delete=".$delete."&token=".$_COOKIE['user_id']);

    // Return the actual result of the curl result instead of success code
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Wait for 10 seconds to connect, set 0 to wait indefinitely
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);

    // Execute the cURL request for a maximum of 50 seconds
    curl_setopt($curl, CURLOPT_TIMEOUT, 50);

    // Do not check the SSL certificates
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    // Fetch the URL and save the content in $html variable
    $html = curl_exec($curl);
    // Check if any error has occurred
    if (curl_errno($curl))
    {
        //echo 'cURL error: ' . curl_error($curl);
    }
    else
    {
        // cURL executed successfully
        //print_r(curl_getinfo($curl));
    }
    // close cURL resource to free up system resources
    curl_close($curl);
}
function HashPassword($password = '', $hashed_password = '') {
    if (empty($password)) {
        return '';
    }
    $hash = 'sha1';
    if (strlen($hashed_password) == 60) {
        $hash = 'password_hash';
    }
    if ($hash == 'password_hash') {
        if (password_verify($password, $hashed_password)) {
            return true;
        }
    } else {
        $password = $hash($password);
    }
    if ($password == $hashed_password) {
        return true;
    }
    return false;
}

function media($path = ""){
    global $site_url, $config;
    if ($config['amazone_s3'] == 1) {
        return 'https://'.$config['bucket_name'].'.s3.amazonaws.com/'.$path;
    } elseif ($config['digital_ocean'] == 1) {
        return 'https://'.$config['digital_ocean_space_name'].'.'.$config['digital_ocean_region'].'.digitaloceanspaces.com/'.$path;
    } else if ($config['google_cloud_storage'] == 1) {
        return 'https://storage.googleapis.com/'.$config['google_cloud_storage_bucket_name'].'/'.$path; 
    } else if ($config['ftp_upload'] == 1) {
        return $config['ftp_endpoint'].'/'.$path;
    } else{
        if (strpos($path, "http") === 0) {
            return $path;
        } else {
            return $site_url.'/'.$path;
        }
    }
    return $path;
}

function un2url($username = ""){
    global $site_url;
    $url = sprintf('%s/%s',$site_url,$username);
    return $url;
}

function br2nl($st) {
    $breaks   = array("\r\n","\r","\n");
    $st       = str_replace($breaks, "", $st);
    $lb       = preg_replace("/\r|\n/", "", $st);
    return preg_replace('/<br(\s+)?\/?>/i', "\r", $lb);
}

function pid2url($post_id = 0){
    global $site_url;
    $url = sprintf('%s/post/%u',$site_url,$post_id);
    return $url;
}

function pid3url($post_id = 0){
    global $site_url;
    $url = sprintf('%s/tile/%u',$site_url,$post_id);
    return $url;
}

function croptxt($text = "", $len = 100,$ellip = '..') {
    if (empty($text) || !is_string($text) || !is_numeric($len) || $len < 1) {
        return '';
    }
    if (strlen($text) > $len) {
        $text = mb_substr($text, 0, $len, "UTF-8") . $ellip;
    }
    return $text;
}

function o2array($obj) {
    if (is_object($obj))
        $obj = (array) $obj;
    if (is_array($obj)) {
        $new = array();
        foreach ($obj as $key => $val) {
            $new[$key] = o2array($val);
        }
    } else {
        $new = $obj;
    }
    return $new;
}

function get_ip_address() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP))
                    return $ip;
            }
        } else {
            if (filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED']) && filter_var($_SERVER['HTTP_X_FORWARDED'], FILTER_VALIDATE_IP))
        return $_SERVER['HTTP_X_FORWARDED'];
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && filter_var($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'], FILTER_VALIDATE_IP))
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP))
        return $_SERVER['HTTP_FORWARDED_FOR'];
    if (!empty($_SERVER['HTTP_FORWARDED']) && filter_var($_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP))
        return $_SERVER['HTTP_FORWARDED'];
    return $_SERVER['REMOTE_ADDR'];
}

function multiple($file = array()) {
    if (!is_array($file)) {
        return array();
    }
    $mt = array();
    $ct = count($file['name']);
    $keys  = array_keys($file);
    for ($i=0; $i < $ct; $i++) {
        foreach ($keys as $key) {
            $mt[$i][$key] = $file[$key][$i];
        }
    }
    return $mt;
}

function covtime($youtube_time) {
    preg_match_all('/(\d+)/', $youtube_time, $parts);
    if (count($parts[0]) == 1) {
        array_unshift($parts[0], "0", "0");
    } elseif (count($parts[0]) == 2) {
        array_unshift($parts[0], "0");
    }
    $sec_init         = $parts[0][2];
    $seconds          = $sec_init % 60;
    $oseconds         = floor($sec_init / 60);
    $min_init         = $parts[0][1] + $oseconds;
    $minutes          = ($min_init) % 60;
    $ominutes = floor(($min_init) / 60);
    $hours            = $parts[0][0] + $ominutes;
    if ($hours != 0) {
        return $hours . ':' . $minutes . ':' . $seconds;
    } else {
        return $minutes . ':' . $seconds;
    }
}

function sql($path = '',$data = array()){
  $face  = false;
  $ipath = ROOTPATH . "/core/sqli/$path.sql";
  if (file_exists($ipath)) {
    $if   = '/(\{\%\s{0,1}if\s{1}(?P<key>[\w]+)\s{0,1}\%\}(?P<sq>.+?)\{\%\s{0,1}endif\s{0,1}\%\})/is';
  	$ifeq = '/(\{\%\s{0,1}if\s{1}[\'\"]?(?P<key>[^\s]+?)[\'\"]?\s==\s[\'\"]?(?P<val>[^\s]+?)[\'\"]?\s{0,1}\%\}(?P<sq>.+?)\{\%\s{0,1}endif\s{0,1}\%\})/is';
    $face = file_get_contents($ipath);
    foreach ($data as $key => $value) {
        $face = preg_replace_callback($ifeq, function($i) use($data) {
            if ($i && !empty($i['key']) && !empty($i['val']) && !empty($data[$i['key']]) && ($data[$i['key']] == $i['val'])) {
                return (!empty($i['sq'])) ? $i['sq'] : '';
            }else{
                return '';
            }
        },$face);
    	$face = preg_replace_callback($if, function($i) use($data) {
            if ($i && !empty($i['key']) && !empty($data[$i['key']])) {
                return (!empty($i['sq'])) ? $i['sq'] : '';
            }else{
                return '';
            }
        },$face);
        $face = preg_replace("/\{\%\s{0,1}$key\s{0,1}\%\}/i",$value, $face);
    	$face = preg_replace("/\{\@(.*?)\@\}/is",'', $face);
    }
  }
  return $face;
}

function pxp_link($path = "") {
    global $site_url;
    return sprintf('%s/%s',$site_url,$path);
}

function url($url = '',$path = ''){
    return sprintf('%s/%s',$url,$path);
}

function ToDate($time = '') {
    return date('c', $time);
}

function time2str($ptime) {
    $etime = time() - $ptime;
    if ($etime < 1) {
        return sprintf('%d %s',0,lang('seconds'));
    }
    $a = array(
        365 * 24 * 60 * 60 => 'year',
        30 * 24 * 60 * 60 => 'month',
        24 * 60 * 60 => 'day',
        60 * 60 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    $a_plural = array(
        'year' => 'years',
        'month' => 'months',
        'day' => 'days',
        'hour' => 'hours',
        'minute' => 'minutes',
        'second' => 'seconds'
    );
    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . ($r > 1 ? lang($a_plural[$str]) : lang($str)) . ' ' . lang('time_ago');
        }
    }
}

function time1str($ptime) {
    $etime = time() - $ptime;
    if ($etime < 1) {
        return sprintf('%d %s',0,lang('sec'));
    }
    $a = array(
        365 * 24 * 60 * 60 => 'y',
        30 * 24 * 60 * 60 => 'm',
        24 * 60 * 60 => 'd',
        60 * 60 => 'h',
        60 => 'min',
        1 => 's'
    );
    $a_plural = array(
        'y' => 'yrs',
        'm' => 'm',
        'd' => 'd',
        'h' => 'hrs',
        'min' => 'mins',
        's' => 's'
    );
    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . ($r > 1 ? lang($a_plural[$str]) : lang($str));
        }
    }
}

function substitute($stringOrFunction, $number) {
    return $number . ' ' . $stringOrFunction;
}

function time3str($ptime) {
    $etime = (time()) - $ptime;
    if ($etime < 1) {
        return 'Now';
    }
    $seconds = abs($etime);
    $minutes = $seconds / 60;
    $hours   = $minutes / 60;
    $days    = $hours / 24;
    $weeks   = $days / 7;
    $years   = $days / 365;
    if ($seconds < 45) {
        return substitute(lang('now'), '');
    } elseif ($seconds < 90) {
        return substitute(lang("_time_m"), 1);
    } elseif ($minutes < 45) {
        return substitute(lang("_time_ms"), round($minutes));
    } elseif ($minutes < 90) {
        return substitute(lang("_time_h"), 1);
    } elseif ($hours < 24) {
        return substitute(lang("_time_hrs"), round($hours));
    } elseif ($hours < 42) {
        return substitute(lang("_time_d"), 1);
    } elseif ($days < 7) {
        return substitute(lang("_time_d"), round($days));
    } elseif ($weeks < 2) {
        return substitute(lang("_time_w"), 1);
    } elseif ($weeks < 52) {
        return substitute(lang("_time_w"), round($weeks));
    } elseif ($years < 1.5) {
        return substitute(lang("_time_y"), 1);
    } else {
        return substitute(lang("_time_yrs"), round($years));
    }
}

function pre($val = null){
    echo "<pre>";
    print_r($val);
    echo "</pre>";
    exit();
}

function mentions($text = ""){
    $regex = '/@([A-Za-z0-9_]+)/i';
    preg_match_all($regex, $text, $mentions);
    $users = array();
    if (is_array($mentions) && !empty($mentions[1])) {
        $users = $mentions[1];
    }
    return $users;
}

function hashtags($text = ""){
    preg_match_all('/#([^`~!@$%^&*\#()\-+=\\|\/\.,<>?\'\":;{}\[\]* ]{4,120})/is', $text, $hashtags);
    $tags = array();
    if (is_array($hashtags) && !empty($hashtags[1])) {
        $tags = $hashtags[1];
    }
    return $tags;
}

function is($boolval  = null){
    return ($boolval == true);
}

function not($boolval  = null){
    return ($boolval != true);
}

function date4mat($time = 0,$format = ''){
    return date($format,$time);
}

function icon($icon_name = '',$icon_type = 'svg'){
    global $site_url;
    $ipath = sprintf('%s/media/icons/%s.%s',$site_url,$icon_name,$icon_type);
    return $ipath;
}

function len($val = ""){
    if (is_string($val)) {
        $val = strlen($val);
    }elseif (is_array($val)) {
        $val = count($val);
    }
    return $val;
}

function minify_js($code = ''){
    $code = preg_replace('/(\r\n|\n|\t|\s{2,})/is', '', $code);
    return $code;
}

function help($path = ''){
    global $site_url;
    return sprintf('%s/help/%s',$site_url,$path);
}

function cpanel($path = ''){
    global $site_url;
    return sprintf('%s/cpanel/%s',$site_url,$path);
}

function config(){
    global $db;
    $data    = array();
    $configs = $db->get(T_CONFIG,null,array('name','value'));
    foreach ($configs as $key => $config) {
        $data[$config->name] = $config->value;
    }
    return $data;
}

function encode($html = ""){
    return htmlspecialchars($html);
}

function decode($html = ""){
    return htmlspecialchars_decode($html);
}

function toArray($obj) {
    if (is_object($obj))
        $obj = (array) $obj;
    if (is_array($obj)) {
        $new = array();
        foreach ($obj as $key => $val) {
            $new[$key] = toArray($val);
        }
    } else {
        $new = $obj;
    }
    return $new;
}

function lang($key = ""){
    global $lang,$config, $db;
    if ($key == "") {
        return '';
    }
    $repl = array('site_name' => $config['site_name']);
    $text = "";
    if(array_key_exists($key, $lang) == true){
        $text = $lang[$key] ;
    }else{
        $keyd = trim(strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/','_', $key)));
        $exist = $db->where('lang_key', $keyd )->getValue(T_LANGS,'count(*)');
        if($exist === 0 ){}
        $text = $key;
    }
    foreach ($repl as $key => $value) {
        $text = preg_replace("/\{{2}$key\}{2}/", $value, $text);
    }
    $text = str_replace('% d', '%d', $text);
    $text = str_replace('٪ d', '%d', $text);
    $text = str_replace('٪d', '%d', $text);
    $text = str_replace('% ', '%d', $text);
    $text = str_replace('%d', ' %d ', $text);
    return $text;
}

function csrf_token() {
    if (!empty($_SESSION['csrf'])) {
        return $_SESSION['csrf'];
    }
    $hash = substr(sha1(rand(1111, 9999)), 0, 70);
    $slat = time();
    $hash = sprintf('%d:%s',$slat,$hash);
    $_SESSION['csrf'] = $hash;
    return $hash;
}

function verifcsrf_token($hash = '') {
    if (empty($_SESSION['csrf']) || empty($hash)) {
        return false;
    }
    return ($hash == $_SESSION['csrf']) ? true : false;
}

function ip_in_range($ip, $range) {
    if (!is_numeric($ip)) {
        return false;
    }
    if (strpos($range, '/') == false) {
        $range .= '/32';
    }
    list($range, $netmask) = explode('/', $range, 2);
    $range_decimal    = ip2long($range);
    $ip_decimal       = ip2long($ip);
    $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
    $netmask_decimal  = ~$wildcard_decimal;
    return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
}

function Currency($currency) {
    global $lang,$config, $db;
    if (empty($currency)) {
        return false;
    }
    if (!in_array($currency,array_keys($config['currency_symbol_array']))) {
        return '$';
    }
    return $config['currency_symbol_array'][$currency];
}

function UiPath($path = '') {
    if (empty($path)) {
        return false;
    }
    $path = explode("//", $path);
    $data = array();
    $data['options'] = array();
    if (!empty($path[0])) {
        $data['ui'] = $path[0];
    }
    if (!empty($path[1])) {
        unset($path[0]);
        $data['options'] = $path;
        foreach ($path as $key => $value) {
            preg_match_all('/(.*)=(.*)/m', $value, $matches);
            if (!empty($matches) && !empty($matches[1]) && !empty($matches[1][0]) && !empty($matches[2]) && !empty($matches[2][0])) {
                $_GET[$matches[1][0]] = $matches[2][0];
            }
            
        }
    }
    return $data;
}

function CheckPaystackPayment($ref){
    global $me,$config, $db;
    if (empty($ref) || IS_LOGGED == false) {
        return false;
    }
    $ref = aura::secure($ref);
    $user = $db->where('user_id',$me['user_id'])->where('paystack_ref',$ref)->getValue(T_USERS,"COUNT(*)");
    if ($user < 1) {
        return false;
    }
    $result = array();
    //The parameter after verify/ is the transaction reference to be verified
    $url = 'https://api.paystack.co/transaction/verify/'.$ref;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$config['paystack_secret_key']]);
    $request = curl_exec($ch);
    curl_close($ch);
    if ($request) {
        $result = json_decode($request, true);
        if($result){
          if($result['data']){
            if($result['data']['status'] == 'success'){
                $db->where('user_id',$me['user_id'])->where('paystack_ref',$ref)->update(T_USERS,array('paystack_ref' => ''));
                return true;
            }else{
              die("Transaction was not successful: Last gateway response was: ".$result['data']['gateway_response']);
            }
          }else{
            die($result['message']);
          }
        }else{
          die("Something went wrong while trying to convert the request variable to json. Uncomment the print_r command to see what is in the result variable.");
        }
      }else{
        die("Something went wrong while executing curl. Uncomment the var_dump line above this line to see what the issue is. Please check your CURL command to make sure everything is ok");
      }
}

function BlurUploadedImage($file){
    global $me, $sqlConnect,$db,$config,$ui,$context;
    $string            = pathinfo($file, PATHINFO_FILENAME) . '.' . strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $extension         = pathinfo($string, PATHINFO_EXTENSION);
    if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'gif') {
        $folder   = 'photos';
        $fileType = 'image';
    } else if ($extension == 'mp4' || $extension == 'webm' || $extension == 'flv') {
        $folder   = 'videos';
        $fileType = 'video';
    } else {
        $folder   = 'files';
        $fileType = 'file';
    }
    $dir         = "media/upload";
    $generate    = date('Y') . '/' . date('m') . '/' . $ui->generateKey(50,50) . '_' . date('d') . '_' . md5(time());
    $path        = "{$folder}/" . $generate . "_{$fileType}.{$extension}";
    $filename    = $dir . '/' . $path;

    $image = imagecreatefromjpeg($file);
    $imgsize = list($w, $h) = @getimagesize($file);
    $finfof  = $imgsize['mime'];
    $image_c = 'imagejpeg';
    if ($finfof == 'image/jpeg') {
        $image   = @imagecreatefromjpeg($file);
        $image_c = 'imagejpeg';
    } else if ($finfof == 'image/gif') {
        $image   = @imagecreatefromgif($file);
        $image_c = 'imagegif';
    } else if ($finfof == 'image/png') {
        $image   = @imagecreatefrompng($file);
        $image_c = 'imagepng';
    } else {
        $image = @imagecreatefromjpeg($file);
    }
    $size = array('sm'=>array('w'=>intval($w), 'h'=>intval($h)));  
    if ($config['downsize_blurred_photo'] > 1) {
        $size = array('sm'=>array('w'=>intval($w/$config['downsize_blurred_photo']), 'h'=>intval($h/$config['downsize_blurred_photo'])));  
    }         
    $sm = imagecreatetruecolor($size['sm']['w'],$size['sm']['h']);
    imagecopyresampled($sm, $image, 0, 0, 0, 0, $size['sm']['w'], $size['sm']['h'], $w, $h);
    if ($config['photo_blurred_number'] > 0) {
        for ($x=1; $x <= $config['photo_blurred_number']; $x++){
            imagefilter($sm, IMG_FILTER_GAUSSIAN_BLUR, 999);
        }
    }
    imagecopyresampled($image, $sm, 0, 0, 0, 0, $w, $h, $size['sm']['w'], $size['sm']['h']);
    @imagejpeg($image, $filename);
    imagedestroy($sm);
    imagedestroy($image);

    if ($context['ftp_upload'] == 1 || $context['amazone_s3'] == 1 || $context['google_cloud_storage'] == 1 || $context['digital_ocean'] == 1) {
        aura::$config['ftp_upload'] = $context['ftp_upload'];
        aura::$config['amazone_s3'] = $context['amazone_s3'];
        aura::$config['google_cloud_storage'] = $context['google_cloud_storage'];
        aura::$config['digital_ocean'] = $context['digital_ocean'];
        $media = new Media();
        $c_path = str_replace('_'.$fileType, '_'.$fileType.'_c', $file);
        $media->cropImage(350, 350, $filename, $c_path, 90);
        if ($context['ftp_upload'] == 1) {
            $upload_     = $media->uploadToFtp($file, true);
            $upload_     = $media->uploadToFtp($filename, true);
            if($c_path !== ''){
                $upload_     = $media->uploadToFtp($c_path, true);
            }
        } else if ($context['amazone_s3'] == 1) {
            $upload_     = $media->uploadToS3($file, true);
            $upload_     = $media->uploadToS3($filename, true);
            if($c_path !== ''){
                $upload_     = $media->uploadToS3($c_path, true);
            }
        } else if ($context['google_cloud_storage'] == 1) {
            $upload_     = $media->uploadToGoogleCloud($file, true);
            $upload_     = $media->uploadToGoogleCloud($filename, true);
            if($c_path !== ''){
                $upload_     = $media->uploadToGoogleCloud($c_path, true);
            }
        } else if ($context['digital_ocean'] == 1) {
            $upload_     = $media->UploadToDigitalOcean($file, true);
            $upload_     = $media->UploadToDigitalOcean($filename, true);
            if($c_path !== ''){
                $upload_     = $media->UploadToDigitalOcean($c_path, true);
            }
        }
    }
    return $filename;
}

use Google\Cloud\Storage\StorageClient;
function uploadFiletoGoogleCloud($fileContent, $cloudPath) {
    global $config;
    if ($config['google_cloud_storage'] == 0 || empty($config['google_cloud_storage_service_account']) || empty($config['google_cloud_storage_bucket_name'])) {
        return false;
    }
    require_once('core/zipy/google-storage/autoload.php');
    $bucketName = $config['google_cloud_storage_bucket_name'];
    $privateKeyFileContent = $config['google_cloud_storage_service_account'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode($privateKeyFileContent, true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
 
    // set which bucket to work in
    $bucket = $storage->bucket($bucketName);
 
    // upload/replace file 
    $storageObject = $bucket->upload(
            $fileContent,
            ['name' => $cloudPath]
            // if $cloudPath is existed then will be overwrite without confirmation
            // NOTE: 
            // a. do not put prefix '/', '/' is a separate folder name  !!
            // b. private key MUST have 'storage.objects.delete' permission if want to replace file !
    );
 
    // is it succeed ?
    return $storageObject != null;
}

function deleteFiletoGoogleCloud($filename) {
    global $config;
    if ($config['google_cloud_storage'] == 0 || empty($config['google_cloud_storage_service_account']) || empty($config['google_cloud_storage_bucket_name'])) {
        return false;
    }
    require_once('core/zipy/google-storage/autoload.php');
    $bucketName = $config['google_cloud_storage_bucket_name'];
    $privateKeyFileContent = $config['google_cloud_storage_service_account'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient(['keyFile' => json_decode($privateKeyFileContent, true)]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
 
    // set which bucket to work in
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($filename);
    $deleted = $object->delete();

    // is it succeed ?
    return $deleted != null;
}

function rx_size_format($bytes) {
    $size = array('1' => '0MB',
                  '2000000' => '2MB',
                  '6000000' => '6MB',
                  '12000000' => '12MB',
                  '24000000' => '24MB',
                  '48000000' => '48MB',
                  '96000000' => '96MB',
                  '256000000' => '256MB',
                  '512000000' => '512MB',
                  '1000000000' => '1GB',
                  '10000000000' => '10GB',
                  '0'           => 'unlimited');
    return $size[$bytes];
}

function frame_duration($filename = false){
    global $config;
    $time     = 30;
    $ffmpeg   = $config['ffmpeg_binary'];
    $output   = shell_exec("$ffmpeg -i {$filename} 2>&1");
    $ptrn     = '/Duration: ([0-9]{2}):([0-9]{2}):([^ ,])+/';
    if (preg_match($ptrn, $output, $matches)) {
        $time  = str_replace("Duration: ", "", $matches[0]);
        $break = explode(":", $time);
        $time  = round(($break[0]*60*60) + ($break[1]*60) + $break[2]);
    }
    return $time;
}

// function date2str($data = null) {
//     $format = 'Y-m-d H:i:s';
//     $time_format = "24_format";
//     $dateformat = "mdy_format";
//     $result = $timeformat = $date = null;
//     if (isset($data['format']) && !empty($data['format'])) {
//         $format = $data['format'];
//     }
//     if (isset($data['auto_format']) && $data['auto_format']) {
//         if ($dateformat === 'mdy_format') {
//             $format = "M-d-Y";
//         } elseif ($dateformat === 'ymd_format') {
//             $format = "Y-M-d";
//         } else {
//             $format = "d-M-Y";
//         }

//         if (isset($data['include_time']) && $data['include_time']) {
//             if ($time_format === '24_format') {
//                 $timeformat = 'H:i';
//             } else {
//                 $timeformat = 'h:i a';
//             }
//         } elseif (isset($data['time_alone']) && $data['time_alone']) {
//             if ($time_format === '24_format') {
//                 $format = 'H:i';
//             } else {
//                 $format = 'h:i a';
//             }
//         }
//     }

//     if (isset($data['date']) && !empty($data['date'])) {
//         $date = $data['date'];
//     }
//     if (isset($data['timezone']) && !empty($data['timezone'])) {
//         $datetime = new DateTime($date);
//         $timezone = new DateTimeZone($data['timezone']);
//         $datetime->setTimezone($timezone);

//         $result = $datetime->format($format);
//         if (!empty($timeformat)) {
//             $previous_result = $result;

//             $result = array();
//             $result['date'] = $previous_result;
//             $result['time'] = $datetime->format($timeformat);

//             if ($timeformat === 'h:i a') {
//                 $find_am_pm = ['am', 'pm'];
//                 $replace_am_pm = array();
//                 $replace_am_pm[] = "AM";
//                 $replace_am_pm[] = "PM";
//                 $result['time'] = str_replace($find_am_pm, $replace_am_pm, $result['time']);
//             }

//             if (isset($data['compare_with_today']) && !empty($data['compare_with_today'])) {
//                 $today = new DateTime();
//                 $today->setTimezone($timezone);
//                 $yesterday = date($format, strtotime($today->format('Y-m-d H:i:s')) - (24 * 60 * 60));
//                 $today = $today->format($format);
//                 if ($result['date'] == $today) {
//                     $result['date'] = 'today';
//                 } elseif ($result['date'] == $yesterday) {
//                     $result['date'] = 'yesterday';
//                 }
//             }
//         }
//     } else {
//         if (isset($data['date']) && !empty($data['date'])) {
//             $result = date($format, strtotime($date));
//             if (!empty($timeformat)) {
//                 $previous_result = $result;
//                 $result = array();
//                 $result['date'] = $previous_result;
//                 $result['time'] = date($timeformat, strtotime($date));
//             }
//         } else {
//             $result = date($format);
//             if (!empty($timeformat)) {
//                 $previous_result = $result;
//                 $result = array();
//                 $result['date'] = $previous_result;
//                 $result['time'] = date($timeformat);
//             }
//         }
//     }
//     return $result;
// }