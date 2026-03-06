<?php

function IsBanned($value = '') {
    global $mysqli;
    $query_one    = mysqli_query($mysqli, "SELECT COUNT(`id`) as count FROM " . T_BLACKLIST . " WHERE `value` = '{$value}'");
    $fetched_data = mysqli_fetch_assoc($query_one);
    if ($fetched_data['count'] > 0) {
        return true;
    }
    return false;
}

function IsSharedPost($post_id) {
    global $mysqli;
    $query_one    = mysqli_query($mysqli, "SELECT COUNT(`id`) as count FROM " . T_NOTIF . " WHERE `type` = 'shared_your_post' AND `url` LIKE '%/post/{$post_id}'");
    $fetched_data = mysqli_fetch_assoc($query_one);
    if ($fetched_data['count'] > 0) {
        return true;
    }
    return false;
}

function GetSharedPostOwner($post_id) {
    global $mysqli, $db;
    $query_one    = mysqli_query($mysqli, "SELECT `recipient_id` FROM " . T_NOTIF . " WHERE `type` = 'shared_your_post' AND `url` LIKE '%/post/{$post_id}'");
    $fetched_data = mysqli_fetch_assoc($query_one);
    if (!empty($fetched_data) && $fetched_data['recipient_id'] > 0) {
        $user = $db->arrayBuilder()->where('user_id',$fetched_data['recipient_id'])->get(T_USERS,null,array('*'));
        return (isset($user[0])) ? $user[0] : array();
    }
    return '';
}

function xl_StripSlashes($value) {
    if (version_compare(PHP_VERSION, '7.4.0', '<=')) {
        if (function_exists("get_magic_quotes_gpc") && !get_magic_quotes_gpc()) return $value;
    }
    if (is_array($value)) {
        return array_map('px_StripSlashes', $value);
    } else {
        return stripslashes($value);
    }
}

function RemoveXSS($val) {
    $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        $val = preg_replace('/(&#[x|X]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val);
        $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val);
    }
    $ra =  array('javascript', 'vbscript', 'expression', '<applet', '<meta', '<xml', '<blink', '<link', '<style', '<script', '<embed', '<object', '<iframe', '<frame', '<frameset', '<ilayer', '<layer', '<bgsound', '<title', '<base', 'onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $found = true;
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
                    $pattern .= '|(&#0{0,8}([9][10][13]);?)?';
                    $pattern .= ')?';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2);
            $val = preg_replace($pattern, $replacement, $val);
            if ($val_before == $val) {
                $found = false;
            }
        }
    }
    return $val;
}

function Generate($minlength = 20, $maxlength = 20, $uselower = true, $useupper = true, $usenumbers = true, $usespecial = false) {
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

function isVideo($file) {
    return is_file($file) && (0 === strpos(mime_content_type($file), 'video/'));
}

function blog_categories(){
    global $db,$context;
    $lang = $context['language'];//'english';
    $blog_categories = $db->arrayBuilder()->where('ref','blog_categories')->get(T_LANGS,null,array('lang_key',$lang));
    $data = array();
    foreach ($blog_categories as $key => $value) {
        if(isset($value[$lang])) {
            $data[$value['lang_key']] = $value[$lang];
        }
    }
    return $data;
}

function anime_categories(){
    global $db,$context;
    $lang = $context['language'];//'english';
    
    $anime_categories = $db->arrayBuilder()->where('ref','anime_categories')->get(T_LANGS,null,array('lang_key' ,$lang));
    $data = array();
    foreach ($anime_categories as $key => $value) {
        if(isset($value[$lang])) {
            $data[$value['lang_key']] = $value[$lang];
        }
        
    }
    return $data;
}

function LangsNamesFromDB($lang = 'english') {
    global $sqlConnect;
    $data  = array();
    $query = mysqli_query($sqlConnect, "SHOW COLUMNS FROM `".T_LANGS."`");
    while ($fetched_data = mysqli_fetch_assoc($query)) {
        $data[] = $fetched_data['Field'];
    }
    unset($data[0]);
    unset($data[1]);
    unset($data[2]);
    return $data;
}

function GetLangDetails($lang_key = '') {
    global $sqlConnect;
    if (empty($lang_key)) {
        return false;
    }
    $lang_key = aura::secure($lang_key);
    $data     = array();
    $query    = mysqli_query($sqlConnect, "SELECT * FROM `".T_LANGS."` WHERE `lang_key` = '{$lang_key}'");
    while ($fetched_data = mysqli_fetch_assoc($query)) {
        unset($fetched_data['lang_key']);
        unset($fetched_data['id']);
        unset($fetched_data['ref']);
        $data[] = $fetched_data;
    }
    return $data;
}

function FormattedNumber($num){
    if ($num >= 0 && $num < 1000) {
        $format = floor($num);
        $suffix = '';
    } elseif ($num >= 1000 && $num < 1000000) {
        $format = floor($num / 1000);
        $suffix = 'k';
    } elseif ($num >= 1000000 && $num < 1000000000) {
        $format = floor($num / 1000000);
        $suffix = 'm';
    } elseif ($num >= 1000000000 && $num < 1000000000000) {
        $format = floor($num / 1000000000);
        $suffix = 'b';
    } elseif ($num >= 1000000000000) {
        $format = floor($num / 1000000000000);
        $suffix = 't';
    }
    return !empty($format . $suffix) ? $format . $suffix : 0;
}

function tenkFormat($num){
    if ($num >= 10000 && $num < 1000000) {
        $format = floor($num / 1000);
        $suffix = 'k';
    } elseif ($num >= 1000000 && $num < 1000000000) {
        $format = floor($num / 1000000);
        $suffix = 'm';
    } elseif ($num >= 1000000000 && $num < 1000000000000) {
        $format = floor($num / 1000000000);
        $suffix = 'b';
    } elseif ($num >= 1000000000000) {
        $format = floor($num / 1000000000000);
        $suffix = 't';
    }
    return !empty($format . $suffix) ? $format . $suffix : 0;
}

function Limit($x, $length){
	if(strlen($x)<=$length){
		echo $x;
	}else{
		$y=substr($x,0,$length) . '...';
		echo $y;
	}
}

function RecordAction($activity, $obj = array()){
    global $config,$db,$me;
    $update = false;
    $activities = array(
        'register', // Done
        'subscribe',
        'star_post', //Done
        'liked_post', //Done
        'share_post', // Done
        'create_ads', //Done
        'buy_premium', //Done
        'remove_star', //Done
        'upload_tile', //Done
        'upload_image', //Done
        'upload_video', //Done
        'upload_story', //Done
        'import_gifs', //Done
        'delete_post', //Done
        'embed_videos', //Done
        'unlike_post', //Done
        'like_reply', //Done
        'add_comment', //Done
        'unlike_reply', //Done
        'follow_users', //Done
        'delete_story', //Done
        'delete_reply', //Done
        'reply_comment', //Done
        'liked_comment', //Done
        'unlike_comment', //Done
        'delete_comment', //Done
        'unfollow_users', //Done
        'profile_verified', //Done
        'point_subscribe_cost', //Done
        'update_photo_cover'
    );
    if (!IS_LOGGED) {return false;}
    if(empty($obj)) return false;
    if($config['point_system'] === 'off') return false;
    if(empty($activity)) return false;
    if(!in_array($activity, $activities) || !isset($config['point_' . $activity . '_cost'])) return false;
    $_cost = intval($config['point_' . $activity . '_cost']);

    if($activity === 'liked_post' || $activity === 'star_post' || $activity === 'liked_comment' || $activity === 'like_reply'){
        if($obj['user_id'] === $me['user_id']) return false;
        $_add_points = true;
    }
    if($activity === 'remove_star'){
        if($obj['user_id'] === $me['user_id']) return false;
        $_add_points = false;
    }
    if($activity === 'share_post'){
        if($obj['user_id'] === $me['user_id']) return false;
        $_add_points = true;
    }
    if($activity === 'unlike_post') {
        if($obj['user_id'] === $me['user_id']) return false;
        $_add_points = false;
    }
    if($activity === 'unlike_comment' || $activity === 'unlike_reply') {
        if($obj['user_id'] === $me['user_id']) {
            $_add_points = false;
        }
    }
    if($activity === 'point_subscribe_cost'){
        if($obj['user_id'] === $me['user_id']) {
            $_add_points = true;
        }
    }
    if($activity === 'delete_post' || $activity === 'delete_comment' || $activity === 'delete_reply' || $activity === 'delete_story'){
        if($obj['user_id'] === $me['user_id']) {
            $_add_points = false;
        }
    }
    if($activity === 'add_comment' || $activity === 'reply_comment'){
        if($obj['user_id'] === $me['user_id']) return false;
        $_add_points = true;
    }
    if($activity === 'create_ads' || $activity === 'buy_premium'){
        if($obj['user_id'] === $me['user_id']) {
            $_add_points = true;
        }
    }
    if($activity === 'profile_verified') {
        if($obj['user_id'] === $me['user_id']) return false;
        $_add_points = true;
    }
    if($activity === 'unfollow_users') {
        if($obj['user_id'] === $me['user_id']) {
            $_add_points = false;
        }
    }
    if($activity === 'follow_users' || $activity === 'subscribe'){
        if($obj['user_id'] === $me['user_id']) {
            $_add_points = true;
        }
    }
    if($activity === 'register'){
        if($obj['user_id'] === $me['user_id']) {
            $_add_points = true;
        }
    }
    if($activity === 'upload_image' || $activity === 'upload_video' || $activity === 'upload_tile' || $activity === 'upload_story' || $activity === 'import_gifs' || $activity === 'embed_videos'){
        if($obj['user_id'] === $me['user_id']) {
            $_add_points = true;  
        }
    }
    $point_cost = $_cost;
    if($me['is_pro'] > 0) {
        $point_cost = $_cost * 2;
    }
    if($_add_points == true) {
        $update = $db->where('user_id', $me['user_id'])->update(T_USERS, array('points' => $db->inc($point_cost)));;
        if ($update) {
            $db->insert(T_POINT_SYSTEM, 
            array(
                'user_id' => $me['user_id'],
                'action' => $activity,
                'reward' => $point_cost,
                'is_add' =>  1,
                'obj' => serialize($obj),
                'time' => time()
            ));
            $notif   = new notify();
            $notif_conf = $notif->notifSettings($me['user_id'],'on_points_earn');
            if($notif_conf) {
                $re_data = array(
                    'notifier_id' => $me['user_id'],
                    'recipient_id' => $me['user_id'],
                    'type' => 'earn_new_point',
                    'url' => $config['site_url'] . '/points',
                    'time' => time(),
                    'points' => $point_cost
                ); $notif->notify($re_data);
            }
            return true;
        }
    } 
    #Lost Points
    elseif($_add_points == false) {
        $remove_cost = $_cost;
        $lost_points = $db->where('user_id', $me['user_id'])->update(T_USERS, array('points' => $db->dec($remove_cost)));
        $lost_points = $db->where('user_id', $me['user_id'])->update(T_USERS, array('lost_points' => $db->inc($remove_cost)));
        if ($lost_points) {
            $db->insert(T_POINT_SYSTEM, 
            array(
                'user_id' => $me['user_id'],
                'action' => $activity,
                'reward' => '-'.$remove_cost,
                'is_add' => 0,
                'obj' => serialize($obj),
                'time' => time()
            ));
            $notif   = new notify();
            $notif_conf = $notif->notifSettings($me['user_id'],'on_points_loose');
            if($notif_conf){
                $re_data = array(
                    'notifier_id' => $me['user_id'],
                    'recipient_id' => $me['user_id'],
                    'type' => 'lost_earned_point',
                    'url' => $config['site_url'] . '/loose_points',
                    'time' => time(),
                    'points' => $remove_cost
                ); $notif->notify($re_data);
            }
            return false;
        }
    }
}

function ExpiredPremium() {
    global $sqlConnect,$db,$me,$config;
    $hour                = 3600; // 1 hour in seconds
    $one_day             = 86400; // 1 day in seconds
    $three_days          = 259200; // 3 days in seconds
    $week_duration       = 604800; // 1 week in seconds
    $month_duration      = 2629743; // 1 month in seconds
    $year_duration       = 31556926; // 1 year in seconds
    $query_one     = "SELECT `user_id`, `username`, `is_pro`, `pro_type`, `pro_time`, `auto_renew`, `credits` FROM " . T_USERS . " WHERE `is_pro` = '1' ORDER BY `user_id` ASC";
    $sql = mysqli_query($sqlConnect, $query_one);
    while ($fetched_data = mysqli_fetch_assoc($sql)) {
        $type   = 0;
        $price  = 0; 
        $renew  = false;
        $update = false;
        $low_bal = false;
        switch ($fetched_data['pro_type']) {
            case '1': // 1 hour time
                if ($fetched_data['pro_time'] < (time() - $hour)) {
                    if($fetched_data['auto_renew'] > 0) {
                        $type    = 1;
                        $renew   = true;
                        $update  = false;
                        $price   =  $config['one_hour_premium_price'];
                    } else {
                        $update = true;
                    }
                }
                break;
            case '2': // 1 day time
                if ($fetched_data['pro_time'] < (time() - $one_day)) {
                    if($fetched_data['auto_renew'] > 0) {
                        $type    = 2;
                        $renew   = true;
                        $update  = false;
                        $price   =  $config['daily_premium_price'];
                    } else {
                        $update = true;
                    }
                }
                break;
            case '3': //  3 days time
                if ($fetched_data['pro_time'] < (time() - $three_days)) {
                    if($fetched_data['auto_renew'] > 0) {
                        $type    = 3;
                        $renew   = true;
                        $update  = false;
                        $price   =  $config['three_days_premium_price'];
                    } else {
                        $update = true;
                    }
                }
                break;
            case '4': // 1 week time 
                if ($fetched_data['pro_time'] < (time() - $week_duration)) {
                    if($fetched_data['auto_renew'] > 0) {
                        $type    = 4;
                        $renew   = true;
                        $update  = false;
                        $price   =  $config['weekly_premium_price'];
                    } else {
                        $update = true;
                    }
                }
                break;
            case '5': // 1 month time
                if ($fetched_data['pro_time'] < (time() - $month_duration)) {
                    if($fetched_data['auto_renew'] > 0) {
                        $type    = 5;
                        $renew   = true;
                        $update  = false;
                        $price   =  $config['monthly_premium_price'];
                    } else {
                        $update = true;
                    }
                }
                break;
            case '6': // 1 year time
                if ($fetched_data['pro_time'] < (time() - $year_duration)) {
                    if($fetched_data['auto_renew'] > 0) {
                        $type    = 6;
                        $renew   = true;
                        $update  = false;
                        $price   =  $config['yearly_premium_price'];
                    } else {
                        $update = true;
                    }
                }
                break;
            case '7': // Lifetime 
                $update = false; // Take no action
                break;
        }
        if ($renew == true) {
            $boosts    = 3;
            $points    = 5000;
            $time      = time();
            $balance   = $fetched_data['credits'];
            $price     =  $price;
            if($balance >= $price){
                $process = $balance - $price;
                $data = $db->where('user_id',$fetched_data['user_id'])->update(T_USERS,array(
                    'is_pro' => 1,
                    'pro_type' => $type,
                    'pro_time' => $time,
                    'credits' => $process,
                    'business_account' => 1,
                    'update_username' => 1,
                    'username_timer' => null,
                    'banner_key' => 1,
                    'switch' => 1,
                    'can_verify' => 1,
                    'boosts' => $db->inc($boosts),
                    'points' => $db->inc($points))
                );
                if ($data) {
                    $notif   = new notify();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $fetched_data['user_id'],
                        'type' => 'membership_plan_renewed',
                        'url' => $config['site_url'] . '/upgraded',
                        'time' => time(),
                    ); $notif->notify($re_data);            
                }
            } else {
                $low_bal = true;
                $update  = false;
            }
        }
        if ($update == true) {
            $db->where('user_id',$fetched_data['user_id'])->update(T_USERS,array(
                'is_pro' => 0,
                'pro_type' => 0,
                'pro_time' => 0,
                'banner_key' => 0,
                'switch' => 0,
                'can_verify' => 0,
                'update_username' => 0
            ));
            $notif        = new notify();
            $re_data = array(
                'notifier_id' => $me['user_id'],
                'recipient_id' => $fetched_data['user_id'],
                'type' => 'membership_plan_expired',
                'url' => $config['site_url'] . '/premium',
                'time' => time(),
            ); $notif->notify($re_data);
        }
        if ($low_bal == true) {
            $update    = false;
            $db->where('user_id',$fetched_data['user_id'])->update(T_USERS,array(
                'is_pro' => 0,
                'pro_type' => 0,
                'pro_time' => 0,
                'banner_key' => 0,
                'switch' => 0,
                'can_verify' => 0,
                'update_username' => 0
            ));
            $notif        = new notify();
            $re_data = array(
                'notifier_id' => $me['user_id'],
                'recipient_id' => $fetched_data['user_id'],
                'type' => 'low_balance_to_upgrade',
                'url' => $config['site_url'] . '/premium',
                'time' => time(),
            ); $notif->notify($re_data);
        }
    }
}

function HomeHastags() {
    global $sqlConnect,$config;
    $data = array();
    $limit  = $config['htag_limit'];
    $htype  = $config['home_htag_type'];
    if ($htype == "latest") {
        $query = "SELECT * FROM " . T_HTAGS . " WHERE `expiring` >= CURRENT_DATE() AND `used` > '0' ORDER BY `last_trend` DESC LIMIT {$limit}";
    } elseif ($htype == "popular") {
        $query = "SELECT * FROM " . T_HTAGS . " WHERE `expiring` >= CURRENT_DATE() AND `used` > '0' ORDER BY `used` DESC LIMIT {$limit}";
    }
    $sql = mysqli_query($sqlConnect, $query);
    if (mysqli_num_rows($sql)) {
        $num = mysqli_num_rows($sql);
        if ($num > 0) {
            while ($fetch = mysqli_fetch_assoc($sql)) {
                $fetch["url"]  = $config['site_url'] . '/hashtag/'.$fetch["tag"];
                $data[]        = $fetch;
            }
        }
    }
    return $data;
}

function TimelineHastags() {
    global $sqlConnect,$config;
    $data = array();
    $limit  = $config['htag_limit'];
    $timeline = $config['timeline_htag_type'];
    if ($timeline == "latest") {
        $query = "SELECT * FROM " . T_HTAGS . " WHERE `expiring` >= CURRENT_DATE() AND `used` > '0' ORDER BY `last_trend` DESC LIMIT {$limit}";
    } elseif ($timeline == "popular") {
        $query = "SELECT * FROM " . T_HTAGS . " WHERE `expiring` >= CURRENT_DATE() AND `used` > '0' ORDER BY `used` DESC LIMIT {$limit}";
    }
    $sql = mysqli_query($sqlConnect, $query);
    if (mysqli_num_rows($sql)) {
        $num = mysqli_num_rows($sql);
        if ($num > 0) {
            while ($fetch = mysqli_fetch_assoc($sql)) {
                $fetch["url"]  = $config['site_url'] . '/hashtag/'.$fetch["tag"];
                $data[]        = $fetch;
            }
        }
    }
    return $data;
}

function RandomHastags($type = 'latest') {
    global $sqlConnect,$config;
    $limit = $config['htag_nav_limit'];
    $data  = array();
    if ($type == "latest") {
        $query = "SELECT * FROM " . T_HTAGS . " WHERE `expiring` >= CURRENT_DATE() AND `used` > '0' ORDER BY RAND() LIMIT {$limit}";
    }
    $sql = mysqli_query($sqlConnect, $query);
    if (mysqli_num_rows($sql)) {
        $num = mysqli_num_rows($sql);
        if ($num > 0) {
            while ($fetch = mysqli_fetch_assoc($sql)) {
                $fetch["url"]  = $config['site_url'] . '/hashtag/'.$fetch["tag"];
                $data[]        = $fetch;
            }
        }
    }
    return $data;
}