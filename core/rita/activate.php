<?php
if ($action == 'activate' && IS_LOGGED) {
    $me = $user->userData($user->getUser());
    $me = o2array($me);
    if (empty($_POST['ac_code'])) {
        $data['status']  = 400;
        $data['message'] = lang('enter_ur_code');
    } else {
        $user_id = $db->where('user_id',$context['me']['user_id'])->getValue(T_USERS,'user_id');
        $code = $db->where('user_id',$context['me']['user_id'])->getValue(T_USERS,'ac_code');
        if (empty($user_id)) {
            header("Location: $site_url/signup");
            exit();
        } 
        if($_POST['ac_code'] == $code){
           $update_data = array('active' => 1,'ac_code' => $_POST['ac_code']);
            $update = $db->where('user_id', $user_id)->update(T_USERS, $update_data);
            if ($update) {
                $platform_details = $user->getUserBrowser();
                $session_id  = sha1(rand(11111, 99999)) . time() . md5(microtime());
                $insert_data = array(
                    'user_id' => $user_id,
                    'session_id' => $session_id,
                    'time' => time(),
                    'platform_details'  => json_encode($platform_details),
                    'platform' => $platform_details['platform']
                );
                $insert              = $db->insert(T_SESSIONS, $insert_data);
                $_SESSION['user_id'] = $session_id;
                setcookie("user_id", $session_id, time() + (10 * 365 * 24 * 60 * 60), "/");
                $data['status'] = 200;
                $data['message'] = lang('account_code_validated');
                $data['link'] = $site_url;
            }
        } else {
            $data['status'] = 400;
            $data['message'] =  lang('account_code_invalid');
        } 
    }
}