<?php
use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Details;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\InputFields;
use PayPal\Api\WebProfile;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

use SecurionPay\SecurionPayGateway;
use SecurionPay\Exception\SecurionPayException;
use SecurionPay\Request\CheckoutRequestCharge;
use SecurionPay\Request\CheckoutRequest;

if ($action == 'pay' && IS_LOGGED){
    
    $data = array('status' => 400);

    $bal       = $me['credits'];
    $mode      =  aura::secure($_POST['mode']);
    $price     =  aura::secure($_POST['price']);
    $product   =  aura::secure($_POST['description']);

    if (empty($price)) {
        $data = array('status' => 400, 'message' =>  lang("no_price_available"));
    }
    if (!is_numeric($price)) {
        $data = array('status' => 400, 'message' =>  lang("price_must_ba_a_number"));
    }
    if (empty($product)) {
        $data = array('status' => 400, 'message' => lang("no_description_passed"));
    }
    if (empty($mode)) {
        $data = array('status' => 400, 'message' => lang("no_mode_set_for_this_payment"));
    }

    if ($mode == 'credits') {
        if($bal >= $price){
            
            $process = $bal - $price;
            $user->updateStatic($me['user_id'], array('credits' => $process));

            if($product === 'sack_of_points') {

                $points = $config['sack_of_points'];
                $update = $db->where('user_id', $me['user_id'])->update(T_USERS, array('points' => $db->inc($points)));

                if($update){
                    $data['status']    = 200;
                    $data['message']   = lang('payment_successful');

                    $notif   = new notify();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $me['user_id'],
                        'type' => 'ur_ccount_has_been_credited',
                        'url' => $config['site_url'] . '/points',
                        'time' => time(),
                        'points' => $points
                     ); $notif->notify($re_data);
                }

            }
            if($product === 'rain_of_tokens') {
                
                $tokens = $config['rain_of_tokens'];
                $update = $db->where('user_id', $me['user_id'])->update(T_USERS, array('tokens' => $db->inc($tokens)));

                if($update){
                    $data['status']    = 200;
                    $data['message']   = lang('payment_successful');

                    $notif   = new notify();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $me['user_id'],
                        'type' => 'ur_ccount_has_been_credited_with_tokens',
                        'url' => $config['site_url'] . '/tokens',
                        'time' => time(),
                        'tokens' => $tokens
                     ); $notif->notify($re_data);
                }

            }
            if($product === 'box_of_boosts') {

                $boosts = $config['box_of_boosts'];
                $update = $db->where('user_id', $me['user_id'])->update(T_USERS, array('boosts' => $db->inc($boosts)));
                if($update){
                    $data['status']    = 200;
                    $data['message']   = lang('payment_successful');

                    $notif   = new notify();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $me['user_id'],
                        'type' => 'ur_ccount_has_been_credited_with_boosts',
                        'url' => $config['site_url'] . '/boosts',
                        'time' => time(),
                        'boosts' => $boosts
                     ); $notif->notify($re_data);
                }
            }
            if($product === 'single_boost') {

                $boosts = $config['single_boost'];
                $update = $db->where('user_id', $me['user_id'])->update(T_USERS, array('boosts' => $db->inc($boosts)));
                if($update){
                    $data['status']    = 200;
                    $data['message']   = lang('payment_successful');

                    $notif   = new notify();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $me['user_id'],
                        'type' => 'ur_ccount_has_been_credited_with_boosts',
                        'url' => $config['site_url'] . '/boosts',
                        'time' => time(),
                        'boosts' => $boosts
                     ); $notif->notify($re_data);
                }
            }
            if($product === 'boost_timer') {
                $timer = $config['boost_timer_count'];
                $update = $db->where('user_id', $me['user_id'])->update(T_USERS, array('boost_timer' => $db->inc($timer), 'has_bought' => 1));
                if($update){
                    $data['status']    = 200;
                    $data['message']   = lang('payment_successful');

                    $notif   = new notify();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $me['user_id'],
                        'type' => 'ur_boost_timer_has_been_updated',
                        'url' => $config['site_url'] . '/boosts',
                        'time' => time(),
                        'boosts' => $timer
                     ); $notif->notify($re_data);
                }
            }
            if($product === 'crate_of_boost') {

                $boosts = $config['crate_of_boost'];
                $update = $db->where('user_id', $me['user_id'])->update(T_USERS, array('boosts' => $db->inc($boosts)));
                if($update){
                    $data['status']    = 200;
                    $data['message']   = lang('payment_successful');

                    $notif   = new notify();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $me['user_id'],
                        'type' => 'you_have_been_credited_with_boosts',
                        'url' => $config['site_url'] . '/boosts',
                        'time' => time(),
                        'boosts' => $boosts
                     ); $notif->notify($re_data);
                }
            }
            if($product === 'rain_of_tokens_main') {
                
                $tokens = $config['rain_of_tokens_main'];
                $update = $db->where('user_id', $me['user_id'])->update(T_USERS, array('tokens' => $db->inc($tokens)));

                if($update){
                    $data['status']    = 200;
                    $data['message']   = lang('payment_successful');

                    $notif   = new notify();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $me['user_id'],
                        'type' => 'ur_ccount_has_been_credited_with_tokens',
                        'url' => $config['site_url'] . '/tokens',
                        'time' => time(),
                        'tokens' => $tokens
                     ); $notif->notify($re_data);
                }
            }
            if($product === 'bag_of_tokens') {
                $tokens = $config['bag_of_tokens'];
                $update = $db->where('user_id', $me['user_id'])->update(T_USERS, array('tokens' => $db->inc($tokens)));
                if($update){
                    $data['status']    = 200;
                    $data['message']   = lang('payment_successful');

                    $notif   = new notify();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $me['user_id'],
                        'type' => 'ur_ccount_has_been_credited_with_tokens',
                        'url' => $config['site_url'] . '/tokens',
                        'time' => time(),
                        'tokens' => $tokens
                     ); $notif->notify($re_data);
                }
            }
            if($product === 'chest_of_tokens') {
                $tokens = $config['chest_of_tokens'];
                $update = $db->where('user_id', $me['user_id'])->update(T_USERS, array('tokens' => $db->inc($tokens)));

                if($update){
                    $data['status']    = 200;
                    $data['message']   = lang('payment_successful');

                    $notif   = new notify();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $me['user_id'],
                        'type' => 'you_have_been_credited_with_chest_tokens',
                        'url' => $config['site_url'] . '/tokens',
                        'time' => time(),
                        'tokens' => $tokens
                     ); $notif->notify($re_data);
                }
            }
        } else {
            $data = array('status' => 400, 'message' => lang("not_enough_credits"));
        }
    }

}


if ($action == 'convert' && IS_LOGGED){

    $data              = array('status' => 400);
    $data['message']   = lang('not_enough_points');

    $points      =  $me['points'];
    $to_convert  =  $config['points_to_credits'];

    if($points >= $to_convert){
        $str = 1;
        $process = $points - $to_convert;
        $user->updateStatic($me['user_id'], array('points' => $process));
        $update = $db->where('user_id', $me['user_id'])->update(T_USERS, array('credits' => $db->inc($str)));
        if($update){
            $data['status']    = 200;
            $data['message']   = lang('converted_successful');

            $notif   = new notify();
            $re_data = array(
                'notifier_id' => $me['user_id'],
                'recipient_id' => $me['user_id'],
                'type' => 'ur_ak_has_been_credited_with_credits',
                'url'  =>  $config['site_url'].'/settings/credits/'.$me['username'],
                'time' => time(),
                'credits' => $str,
                'ajax_url' => 'load.php?app=settings&apph=settings&user='.$me['username'].'&page=credits'
            ); $notif->notify($re_data);
        }
    }
}

if ($action == 'redeem' && IS_LOGGED){

    $data              = array('status' => 400);
    $data['message']   = lang('not_enough_tokens');

    $tokens      =  $me['tokens'];
    $to_convert  =  $config['rain_of_tokens'];

    if($tokens >= $to_convert){
        $str     = $me['lost_points'];
        $process = $tokens - $to_convert;
        $user->updateStatic($me['user_id'], array('tokens' => $process));
        $user->updateStatic($me['user_id'], array('lost_points' => 0));
        $update = $db->where('user_id', $me['user_id'])->update(T_USERS, array('points' => $db->inc($str)));
        if($update){
            $data['status']    = 200;
            $data['message']   = lang('points_redeemed_successful');

            $notif   = new notify();
            $re_data = array(
                'notifier_id' => $me['user_id'],
                'recipient_id' => $me['user_id'],
                'type' => 'ur_ccount_has_been_credited',
                'url' => $config['site_url'] . '/points',
                'time' => time(),
                'points' => $str,
            ); $notif->notify($re_data);
        }
    }
}

if ($action == 'exchange' && IS_LOGGED){

    $data              = array('status' => 400);
    $data['message']   = lang('not_enough_tokens_to_exchange');

    $tokens      =  $me['tokens'];
    $to_convert  =  $config['chest_of_tokens'];

    if($tokens >= $to_convert){
        $str     = 64;
        $process = $tokens - $to_convert;
        $user->updateStatic($me['user_id'], array('tokens' => $process));
        $update = $db->where('user_id', $me['user_id'])->update(T_USERS, array('credits' => $db->inc($str)));
        if($update){
            $data['status']    = 200;
            $data['message']   = lang('tokens_exchanged_successful');

            $notif   = new notify();
            $re_data = array(
                'notifier_id' => $me['user_id'],
                'recipient_id' => $me['user_id'],
                'type' => 'ur_ak_has_been_credited_with_credits',
                'url'  =>  $config['site_url'].'/settings/credits/'.$me['username'],
                'time' => time(),
                'credits' => $str,
                'ajax_url' => 'load.php?app=settings&apph=settings&user='.$me['username'].'&page=credits'
            ); $notif->notify($re_data);
        }
    }
}

if ($action == 'subscribe' && IS_LOGGED){
    $data = array('status' => 400);
    $price = 0;
    if (!empty($_GET['type']) && in_array($_GET['type'], array('subscribe'))) {
        if ($_GET['type'] == 'subscribe' && !empty($_GET['user_id']) && is_numeric($_GET['user_id']) && $_GET['user_id'] > 0 && ($config['private_videos'] == 'on' || $config['private_photos'] == 'on')) {
            $user_id = aura::secure($_GET['user_id']);
            $user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
            if (!empty($user_data) && $user_data->user_id != $me['user_id'] && !empty($user_data->subscribe_price)) {
                $month = 60 * 60 * 24 * 30;
                $is_subscribed = $db->where('user_id',$user_data->user_id)->where('subscriber_id',$me['user_id'])->where('time',(time() - $month),'>=')->getValue(T_SUBSCRIBERS,'COUNT(*)');
                if ($is_subscribed < 1) {
                    $amount = $user_data->subscribe_price;
                    $admin_com = 0;
                    if ($config['monthly_subscribers_commission'] > 0) {
                        $admin_com = ($config['monthly_subscribers_commission'] * $amount) / 100;
                        $amount = $amount - $admin_com;
                    }
                    $credits = $me['credits'] - $user_data->subscribe_price;
                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'], 'amount' => $amount, 'subscription_id' => $user_data->user_id, 'type' => 'subscribe', 'time' => time(), 'admin_com' => $admin_com));
                    $db->insert(T_SUBSCRIBERS,array('user_id' => $user_data->user_id, 'subscriber_id' => $me['user_id'], 'time' => time()));
                    $update = $user->updateStatic($me['user_id'],array('credits' => $credits));
                    $db->where('user_id',$user_data->user_id)->update(T_USERS,array('credits'=>$db->inc($amount)));
                    $notif   = new notify();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $user_data->user_id,
                        'type' => 'have_new_subscriber',
                        'url' => $config['site_url'] . "/".$me['username'],
                        'time' => time(),
                        'credits' => $amount,
                        'ajax_url' => 'load.php?app=profile&apph=profile&uname='.$me['username'],
                    ); $notif->notify($re_data);

                    $ne_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $user_data->user_id,
                        'type' => 'new_subscribe_earn',
                        'url'  =>  $config['site_url'].'/settings/credits/'.$user_data->username,
                        'time' => time(),
                        'credits' => $amount,
                        'ajax_url' => 'load.php?app=settings&apph=settings&user='.$user_data->username.'&page=credits'
                    ); $notif->notify($ne_data);

                    $ra_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $me['user_id'],
                        'type' => 'your_ak_has_been_debited',
                        'url'  =>  $config['site_url'].'/settings/credits/'.$me['username'],
                        'time' => time(),
                        'credits' => $amount,
                        'ajax_url' => 'load.php?app=settings&apph=settings&user='.$me['username'].'&page=credits'
                    ); $notif->notify($ra_data);

                    $data['status']     = 200;
                    $data['uname']      = $user_data->username;
                    $data['url']        = $config['site_url'].'/'.$user_data->username;

                    #Reward points to the user
			        RecordAction('point_subscribe_cost', array('user_id' => $me['user_id']));
                }
                else{
                    $data['status']     = 400;
                    $data['uname']      = $user_data->username;
                    $data['message']    = lang('you_already_subscribed');
                    $data['url']        = $config['site_url'].'/'.$user_data->username;
                }
            }
            else{
                $data = array('status' => 400,'message' => lang('user_dont_have_subscribe'));
            }
        }
    }
}

if ($action == 'premium' && IS_LOGGED){
    $data = array('status' => 400);

    $bal       = $me['credits'];
    $type      =  aura::secure($_POST['type']);
    $mode      =  aura::secure($_POST['mode']);
    $price     =  aura::secure($_POST['price']);
    $product   =  aura::secure($_POST['description']);

    if (empty($price)) {
        $data = array('status' => 400, 'message' =>  lang("no_price_available"));
    }
    if (!is_numeric($price)) {
        $data = array('status' => 400, 'message' =>  lang("price_must_ba_a_number"));
    }
    if (empty($product)) {
        $data = array('status' => 400, 'message' => lang("no_description_passed"));
    }
    if (empty($mode)) {
        $data = array('status' => 400, 'message' => lang("no_mode_set_for_this_payment"));
    }

    if ($mode == 'premium') {
        if($bal >= $price){
            $boosts = 3;
            $points = 5000;
            $time   = time();
            $process = $bal - $price;
            $user->updateStatic($me['user_id'], array('credits' => $process));

            $db->insert(T_PAYMENTS,array('user_id' => $me['user_id'],'amount' => $price,'type' => 'premium','date' => $time));
            $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],'amount' => $price,'type' => 'premium','time' => $time));

            $update = $user->updateStatic($me['user_id'],array(
                'is_pro' => 1,
                'pro_type' => $type,
                'pro_time' => $time,
                'business_account' => 1,
                'update_username' => 1,
                'username_timer' => null,
                'banner_key' => 1,
                'switch' => 1,
                'can_verify' => 1,
                'boosts' => $db->inc($boosts),
                'points' => $db->inc($points)
            ));

            if ($update) {
                $notif        = new notify();
                $re_data = array(
                    'notifier_id' => $me['user_id'],
                    'recipient_id' => $me['user_id'],
                    'type' => 'membership_plan_activated',
                    'url' => un2url($me['username']),
                    'time' => time(),
                    'ajax_url' => 'load.php?app=profile&apph=profile&uname='.$me['username']
                ); $notif->notify($re_data);

                $data['status']    = 200;
                $data['message']   = lang('premium_activated');
                $data['upgraded']  = $config['site_url'] . "/upgraded";

                #Reward points to the user
			    RecordAction('buy_premium', array('user_id' => $me['user_id']));
            }
        } else {
            $data = array('status' => 400, 'message' => lang("not_enough_credits"));
        }
    }

}

if ($action == 'unlock' && IS_LOGGED){
    $data = array('status' => 400);
    $price = 0;
    if (!empty($_GET['type']) && in_array($_GET['type'], array('unlock_image','unlock_video'))) {
        if ($_GET['type'] == 'unlock_image' && !empty($_GET['post_id']) && is_numeric($_GET['post_id']) && $_GET['post_id'] > 0 && $config['private_photos'] == 'on') {
            $post_id = aura::secure($_GET['post_id']);
            $post = $db->where('post_id',$post_id)->getOne(T_POSTS);
            if (!empty($post) && $post->user_id != $me['user_id'] && !empty($post->price)) {
                $is_bought = $db->where('post_id',$post_id)->where('type','unlock image')->getValue(T_TRANSACTIONS,'COUNT(*)');
                if ($is_bought < 1) {
                    $amount = $post->price;
                    $admin_com = 0;
                    if ($config['private_photos_commission'] > 0) {
                        $admin_com = ($config['private_photos_commission'] * $amount) / 100;
                        $amount = $amount - $admin_com;
                    }
                    $process = $me['credits'] - $post->price;
                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],'amount' => $amount,'post_id' => $post_id,'type' => 'unlock image','time' => time(),'admin_com' => $admin_com));
                    $update = $user->updateStatic($me['user_id'],array('credits' => $process));
                    $db->where('user_id',$post->user_id)->update(T_USERS,array('credits'=>$db->inc($amount)));
                    $posts   = new Posts();
                    $posts->setPostId($post_id);
                    $post_data = o2array($posts->postData());
                    foreach ($post_data['media_set'] as $id => $file) {
                        $thumb = '';
                        $type = $post_data['type'];
                        $extra = media($file['extra']);
                        $fullpath = media($file['file']);
                        if (in_array($type, array('video'))) {
                            if(!empty($extra)){
                                $thumb = $extra;
                            }
                        } else {
                            $thumb = media($file['file']);
                        }
                    }
                    $notif   = new notify();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $post->user_id,
                        'type' => 'unlock_user_image',
                        'url' => $config['site_url'] . "/post/".$post_id,
                        'time' => time(),
                        'ftype' => $type,
						'thumb' => $thumb,
						'post_id' => $post_id,
						'file' => $fullpath,
                        'credits' => $amount
                    );
                    $ra_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $post->user_id,
                        'type' => 'ur_ak_has_been_credited_with_credits',
                        'url'  =>  $config['site_url'].'/settings/credits/'.$post->username,
                        'time' => time(),
                        'credits' => $amount,
                        'ajax_url' => 'load.php?app=settings&apph=settings&user='.$post->username.'&page=credits'
                    );
                    try {
                        $notif->notify($re_data);
                        $notif->notify($ra_data);
                    } catch (Exception $e) {}
                    $notif   = new notify();
                    $rx_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $me['user_id'],
                        'type' => 'your_ak_has_been_debited',
                        'url'  =>  $config['site_url'].'/settings/credits/'.$me['username'],
                        'time' => time(),
                        'credits' => $amount,
                        'ajax_url' => 'load.php?app=settings&apph=settings&user='.$me['username'].'&page=credits'
                    ); $notif->notify($rx_data);
                    $data['purl']    = pid2url($post_id);
                    $data['ajax']    = "load.php?app=posts&apph=view_post&pid=$post_id";
                    $data['status']     = 200;
                }else{
                    $data = array('status' => 400,'message' => lang('you_already_bought_this_post'));
                }
            }else{
                $data = array('status' => 400,'message' => lang('post_not_for_sell'));
            }
        } elseif ($_GET['type'] == 'unlock_video' && !empty($_GET['post_id']) && is_numeric($_GET['post_id']) && $_GET['post_id'] > 0 && $config['private_videos'] == 'on') {
            $post_id = aura::secure($_GET['post_id']);
            $post = $db->where('post_id',$post_id)->getOne(T_POSTS);
            if (!empty($post) && $post->user_id != $me['user_id'] && !empty($post->price)) {
                $is_bought = $db->where('post_id',$post_id)->where('type','unlock video')->getValue(T_TRANSACTIONS,'COUNT(*)');
                if ($is_bought < 1) {
                    $amount = $post->price;
                    $admin_com = 0;
                    if ($config['private_videos_commission'] > 0) {
                        $admin_com = ($config['private_videos_commission'] * $amount) / 100;
                        $amount = $amount - $admin_com;
                    }
                    $process = $me['credits'] - $post->price;
                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],'amount' => $amount,'post_id' => $post_id,'type' => 'unlock video','time' => time(),'admin_com' => $admin_com));
                    $update = $user->updateStatic($me['user_id'],array('credits' => $process));
                    $db->where('user_id',$post->user_id)->update(T_USERS,array('credits'=>$db->inc($amount)));
                    $posts   = new Posts();
                    $posts->setPostId($post_id);
                    $post_data = o2array($posts->postData());
                    foreach ($post_data['media_set'] as $id => $file) {
                        $thumb = '';
                        $type = $post_data['type'];
                        $extra = media($file['extra']);
                        $fullpath = media($file['file']);
                        if (in_array($type, array('video'))) {
                            if(!empty($extra)){
                                $thumb = $extra;
                            }
                        } else {
                            $thumb = media($file['file']);
                        }
                    }
                    $notif   = new notify();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $post->user_id,
                        'type' => 'unlock_user_video',
                        'url' => $config['site_url'] . "/post/".$post_id,
                        'time' => time(),
                        'ftype' => $type,
						'thumb' => $thumb,
						'post_id' => $post_id,
						'file' => $fullpath,
                        'credits' => $amount
                    );
                    $ra_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $post->user_id,
                        'type' => 'ur_ak_has_been_credited_with_credits',
                        'url'  =>  $config['site_url'].'/settings/credits/'.$post->username,
                        'time' => time(),
                        'credits' => $amount,
                        'ajax_url' => 'load.php?app=settings&apph=settings&user='.$post->username.'&page=credits'
                    );
                    try {
                        $notif->notify($re_data);
                        $notif->notify($ra_data);
                    } catch (Exception $e) {}
                    $notif   = new notify();
                    $rx_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $me['user_id'],
                        'type' => 'your_ak_has_been_debited',
                        'url'  =>  $config['site_url'].'/settings/credits/'.$me['username'],
                        'time' => time(),
                        'credits' => $amount,
                        'ajax_url' => 'load.php?app=settings&apph=settings&user='.$me['username'].'&page=credits'
                    ); $notif->notify($rx_data);
                    $data['purl']    = pid2url($post_id);
                    $data['ajax']    = "load.php?app=posts&apph=view_post&pid=$post_id";
                    $data['status']  = 200;
                }
                else{
                    $data = array('status' => 400,'message' => lang('you_already_bought_this_post'));
                }
            }
            else{
                $data = array('status' => 400,'message' => lang('post_not_for_sell'));
            }
        }
    }
}


if ($action == 'paypal' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret'])) {
    $data = array('status' => 400);
    require_once('core/sync/paypal.php');
    
    $mode    =  aura::secure($_POST['mode']);
    $price   =  aura::secure($_POST['price']);
    $value   =  aura::secure($_POST['value']);
    if (empty($mode)) {
        $data = array('status' => 400, 'message' => lang("no_mode_set_for_this_payment"));
    }
    $sum  = $price;  
    $dec  = "Buy Credits";
    if (!empty($_POST['mode']) && $_POST['mode'] == 'add_money' && !empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
        $sum = aura::secure($_POST['amount']);
        $type = 'add_money';
        $dec = "Wallet top up";
    }
    $total = $sum;
    if (!empty($config['currency_array']) && in_array($config['paypal_currency'], $config['currency_array']) && $config['paypal_currency'] != $config['currency'] && !empty($config['exchange']) && !empty($config['exchange'][$config['paypal_currency']])) {
        $sum = (($sum * $config['exchange'][$config['paypal_currency']]));
    }
    $sum = (int)$sum;
    $inputFields = new InputFields();
    $inputFields->setAllowNote(true)->setNoShipping(1)->setAddressOverride(0);
    $webProfile = new WebProfile();
    $webProfile->setName($dec." ". uniqid())->setInputFields($inputFields);
    try {
        $createdProfile = $webProfile->create($paypal);
        $createdProfileID = json_decode($createdProfile);
        $profileid = $createdProfileID->id;
    } catch(PayPal\Exception\PayPalConnectionException $pce) {
        $data = array('type' => 'ERROR','details' => json_decode($pce->getData()));
        return $data;
    }
    $payer = new Payer();
    $payer->setPaymentMethod('paypal');
    $item = new Item();
    $item->setName($dec)->setQuantity(1)->setPrice($sum)->setCurrency($config['paypal_currency']);
    $itemList = new ItemList();
    $itemList->setItems(array($item));
    $details = new Details();
    $details->setSubtotal($sum);
    $amount = new Amount();
    $amount->setCurrency($config['paypal_currency'])->setTotal($sum)->setDetails($details);
    $transaction = new Transaction();
    $transaction->setAmount($amount)->setItemList($itemList)->setDescription($dec)->setInvoiceNumber(uniqid());
    $redirectUrls = new RedirectUrls();
    if ($mode == 'add_credits') {
        $redirectUrls->setReturnUrl($config['site_url'] . "/aj/payment/added&success=1&amount=".$total."&type=".$value)->setCancelUrl($config['site_url']);
    } elseif ($mode == 'add_money') {
        $redirectUrls->setReturnUrl($config['site_url'] . "/aj/payment/top_up&success=1&amount=".$total)->setCancelUrl($config['site_url']);
    }
    $payment = new Payment();
    $payment->setExperienceProfileId($profileid)->setIntent('sale')->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions(array($transaction));
    try {
        $payment->create($paypal);
    }catch (PayPal\Exception\PayPalConnectionException $e) {
        $data = array('status' => 400,'message' => json_decode($e->getData()));
        if (empty($data['message'])) {
            $data['message'] = json_decode($e->getCode());
        }
        return $data;
    }
    $data = array('status' => 200,'url' => $payment->getApprovalLink());
}

// Get Paid
if ($action == 'added' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret']) && $_GET['success'] == 1 && !empty($_GET['paymentId']) && !empty($_GET['PayerID']) && !empty($_GET['amount'])) {
    require_once('core/sync/paypal.php');
    $paymentId  = $_GET['paymentId'];
    $PayerID    = $_GET['PayerID'];
    $value      = aura::secure($_GET['type']);
    $payment   = Payment::get($paymentId, $paypal);
    $execute   = new PaymentExecution();
    $execute->setPayerId($PayerID);
    $error = '';
    try {
        $result = $payment->execute($execute, $paypal);
    }
    catch (PayPal\Exception\PayPalConnectionException $e) {
        $error = json_decode($e->getData(), true);
    }
    if (empty($error)) {
        if($value === 'sack_of_credits') {
            $sack    = $config['sack_of_credits'];
            $payed   = aura::secure($_GET['amount']);
            $process = $me['credits'] + $sack;
            $update = $user->updateStatic($me['user_id'],array('credits' => $process));
            $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],'amount' => $payed,'type' => 'Bought a sack of credits','time' => time()));
            $notif   = new notify();
            if($update) {
                $re_data = array(
                    'notifier_id' => $me['user_id'],
                    'recipient_id' => $me['user_id'],
                    'type' => 'ur_ak_has_been_credited_with_credits',
                    'url' => $config['site_url'] . "/settings/credits/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''),
                    'time' => time(),
                    'credits' => $payed
                );
                $notif->notify($ra_data);
            }
            if (!empty($_COOKIE['redirect_page'])) {
                $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
                $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
                header("Location: " . $redirect_page);
            }else{
                header("Location: " . $config['site_url'] . "/settings/credits/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''));
            }
            exit();
        }
        if($type === 'pot_of_credits') {

        }
        if($type === 'cup_of_credits') {

        }
        if($type === 'chest_of_credits') {

        }
    }else{
        header("Location: " . $config['site_url'] . "/oops");
        exit();
    }  
}

// To Up
if ($action == 'top_up' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret']) && $_GET['success'] == 1 && !empty($_GET['paymentId']) && !empty($_GET['PayerID']) && !empty($_GET['amount'])) {
    require_once('core/sync/paypal.php');
    $PayerID = $_GET['PayerID'];
    $paymentId = $_GET['paymentId'];
    $amount = aura::secure($_GET['amount']);
    $payment = Payment::get($paymentId, $paypal);
    $execute = new PaymentExecution();
    $execute->setPayerId($PayerID);
    $error = '';
    try {
        $result = $payment->execute($execute, $paypal);
    }
    catch (PayPal\Exception\PayPalConnectionException $e) {
        $error = json_decode($e->getData(), true);
    }
    if (empty($error)) {
        $top_up = $me['balance'] + $_GET['amount'];
        $update = $user->updateStatic($me['user_id'],array('balance' => $top_up));
        $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],'amount' => $amount,'type' => 'Added_money_to_wallet','text' => 'Added money to wallet using Paypal','time' => time()));
        $notif   = new notify();
        if($update) {
            $re_data = array(
                'notifier_id' => $me['user_id'],
                'recipient_id' => $me['user_id'],
                'type' => 'top_up_success',
                'url' => $config['site_url'] . "/campaign/wallet",
                'time' => time(),
                'money' => $amount
            );
            $notif->notify($ra_data);
        }
        if (!empty($_COOKIE['redirect_page'])) {
            $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
            $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
            header("Location: " . $redirect_page);
        }else{
            header("Location: " . $config['site_url'] . "/campaign/wallet");
        }
        exit();
    }else{
        header("Location: " . $config['site_url'] . "/oops");
        exit();
    }
}

if($action == 'paysera' && !empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0){
    $url = '';
    $amount = intval($_POST['amount']);
    try {
        $self_url = $config['site_url'];
        $payment_url = WebToPay::getPaymentUrl();
        $request = WebToPay::buildRequest(array(
            'projectid'     => $config['paysera_project_id'],
            'sign_password' => $config['paysera_password'],
            'orderid'       => rand(1111,4444),
            'amount'        => $amount,
            'currency'      => $config['currency'],
            'country'       => 'TR',
            'accepturl'     => $self_url.'/aj/payment/paysera_success?amount='.$amount,
            'cancelurl'     => $self_url.'/aj/payment/paysera_cancel',
            'callbackurl'   => $self_url.'/aj/payment/paysera_callback',
            'test'          => ($config['paysera_test_mode'] == 'test') ? 1 : 0,
        ));
        $url = $payment_url . '?data='. $request['data'] . '&sign=' . $request['sign'];
        $data = array('status' => 200,'url' => $url); 
    }
    catch (WebToPayException $e) {
        echo $e->getMessage();
    }
}

if(($action == 'paysera_success' || $action == 'paysera_callback') && !empty($_GET['amount']) && is_numeric($_GET['amount']) && $_GET['amount'] > 0){
    $response = WebToPay::checkResponse($_GET, array('projectid' => $config['paysera_project_id'],'sign_password' => $config['paysera_password']));
    if ($response['type'] !== 'macro') {
        die('Only macro payment callbacks are accepted');
    }
    $amount = aura::secure($_GET['amount']);
    $wallet = $me['balance'] + $amount;
    $update = $user->updateStatic($me['user_id'],array('balance' => $wallet));
    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'], 'amount' => $amount,'type' => 'Added_money_to_wallet','text' => 'Added money to wallet using Paysera','time' => time()));
    $notif   = new notify();
    if($update) {
        $re_data = array(
            'notifier_id' => $me['user_id'],
            'recipient_id' => $me['user_id'],
            'type' => 'top_up_success',
            'url' => $config['site_url'] . "/campaign/wallet",
            'time' => time(),
            'money' => $amount
        );
        $notif->notify($ra_data);
    }
    if (!empty($_COOKIE['redirect_page'])) {
        $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
        $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
        header("Location: " . $redirect_page);
    }else{
        header("Location: " . $config['site_url'] . "/campaign/wallet");
    }
    exit();
}

if ($action == 'stripe_session' && IS_LOGGED) {
    $data = array('status' => 400);
    if (!empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
        require_once('core/sync/stripe.php');
        $amount = aura::secure($_POST['amount']);
        $main_total = $amount;
        if (!empty($config['currency_array']) && in_array($config['stripe_currency'], $config['currency_array']) && $config['stripe_currency'] != $config['currency'] && !empty($config['exchange']) && !empty($config['exchange'][$config['stripe_currency']])) {
            $amount= (($amount * $config['exchange'][$config['stripe_currency']]));
        }
        $amount = round($amount, 2) * 100;
        $payment_method_types = array('card');
        try {
            $checkout_session = \Stripe\Checkout\Session::create([
                'payment_method_types' => [implode(',', $payment_method_types)],
                'line_items' => [[
                  'price_data' => [
                    'currency' => $config['stripe_currency'],
                    'product_data' => [
                      'name' => 'Top Up Wallet',
                    ],
                    'unit_amount' => $amount,
                  ],
                  'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $config['site_url'] . "/aj/payment/stripe_success&amount=".$main_total,
                'cancel_url' =>  $config['site_url'] . "/aj/payment/stripe_cancel&amount=".$main_total,
            ]);
            if (!empty($checkout_session) && !empty($checkout_session['id'])) {
                $db->where('user_id',$me['user_id'])->update(T_USERS,array('StripeSessionId' => $checkout_session['id']));
                $data = array('status' => 200,'sessionId' => $checkout_session['id']);
            }else{
                $data = array('status' => 400,'message' => lang("something_went_wrong_please_try_again_later_"));
            }
        }catch (Exception $e) {
            $data = array('status' => 400,'message' => $e->getMessage());
        }
    }
}

if ($action == 'stripe_success' && IS_LOGGED) {
    if (!empty($me['StripeSessionId']) && !empty($_GET['amount']) && is_numeric($_GET['amount']) && $_GET['amount'] > 0) {
        require_once('core/sync/stripe.php');
        try {
            $checkout_session = \Stripe\Checkout\Session::retrieve($me['StripeSessionId']);
            if ($checkout_session->payment_status == 'paid') {
                $db->where('user_id',$me['user_id'])->update(T_USERS,array('StripeSessionId' => ''));
                $amount = aura::secure($_GET['amount']);
                $wallet = $me['balance'] + $amount;
                $update = $user->updateStatic($me['user_id'],array('balance' => $wallet));
                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],'amount' => $amount,'type' => 'Added_money_to_wallet','text' => 'Added money to wallet using Stripe','time' => time()));
                $notif   = new notify();
                if($update) {
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $me['user_id'],
                        'type' => 'top_up_success',
                        'url' => $config['site_url'] . "/campaign/wallet",
                        'time' => time(),
                        'money' => $amount
                    );
                    $notif->notify($ra_data);
                }
                if (!empty($_COOKIE['redirect_page'])) {
                    $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
                    $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
                    header("Location: " . $redirect_page);
                }else{
                    header("Location: " . $config['site_url'] . "/campaign/wallet");
                }
                exit();
            }else{
                header("Location: " . $config['site_url'] . "/campaign/wallet");
                exit();
            }
        } catch (Exception $e) {
            header("Location: " . $config['site_url'] . "/campaign/wallet");
            exit();
        }
    }
    header("Location: " . $config['site_url'] . "/campaign/wallet");
    exit();
}

if ($action == 'cashfree' && $config['cashfree_payment'] == 'yes') {
	if (!empty($_POST['name']) && !empty($_POST['phone']) && !empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) && !empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
		$result = array();
	    $order_id = uniqid();
	    $name = aura::secure($_POST['name']);
	    $email = aura::secure($_POST['email']);
	    $phone = aura::secure($_POST['phone']);
	    $price = $main_total = aura::secure($_POST['amount']);
        if (!empty($config['currency_array']) && in_array($config['cashfree_currency'], $config['currency_array']) && $config['cashfree_currency'] != $config['currency'] && !empty($config['exchange']) && !empty($config['exchange'][$config['cashfree_currency']])) {
            $price= (($price * $config['exchange'][$config['cashfree_currency']]));
            $price = round($price, 2);
        }
	    $callback_url = $config['site_url'] . "/aj/payment/cashfree_paid?amount=".$main_total;
	    $secretKey = $config['cashfree_secret_key'];
		$postData = array( 
		  "appId" => $config['cashfree_client_key'], 
		  "orderId" => "order".$order_id, 
		  "orderAmount" => $price, 
		  "orderCurrency" => $config['cashfree_currency'], 
		  "orderNote" => "", 
		  "customerName" => $name, 
		  "customerPhone" => $phone, 
		  "customerEmail" => $email,
		  "returnUrl" => $callback_url, 
		  "notifyUrl" => $callback_url,
		);
		 // get secret key from your config
		 ksort($postData);
		 $signatureData = "";
		 foreach ($postData as $key => $value){
		      $signatureData .= $key.$value;
		 }
		 $signature = hash_hmac('sha256', $signatureData, $secretKey,true);
		 $signature = base64_encode($signature);
		 $cashfree_link = 'https://test.cashfree.com/billpay/checkout/post/submit';
		 if ($config['cashfree_mode'] == 'live') {
		 	$cashfree_link = 'https://www.cashfree.com/checkout/post/submit';
		 }
		$form = '<form id="redirectForm" method="post" action="'.$cashfree_link.'"><input type="hidden" name="appId" value="'.$config['cashfree_client_key'].'"/><input type="hidden" name="orderId" value="order'.$order_id.'"/><input type="hidden" name="orderAmount" value="'.$price.'"/><input type="hidden" name="orderCurrency" value="INR"/><input type="hidden" name="orderNote" value=""/><input type="hidden" name="customerName" value="'.$name.'"/><input type="hidden" name="customerEmail" value="'.$email.'"/><input type="hidden" name="customerPhone" value="'.$phone.'"/><input type="hidden" name="returnUrl" value="'.$callback_url.'"/><input type="hidden" name="notifyUrl" value="'.$callback_url.'"/><input type="hidden" name="signature" value="'.$signature.'"/></form>';
		$data['status'] = 200;
		$data['html'] = $form;
	}
	else{
		$data['message'] = lang('unknown_error');
	}
}

if ($action == 'cashfree_paid' && $config['cashfree_payment'] == 'yes' && IS_LOGGED ) {
	if (empty($_POST['txStatus']) || $_POST['txStatus'] != 'SUCCESS') {
		header('Location: ' . $config['site_url'] . '/payment');
        exit();
	}
    $orderId = $_POST["orderId"];
    $amount = aura::secure($_GET["amount"]);
	$orderAmount = $_POST["orderAmount"];
	$referenceId = $_POST["referenceId"];
	$txStatus = $_POST["txStatus"];
	$paymentMode = $_POST["paymentMode"];
	$txMsg = $_POST["txMsg"];
	$txTime = $_POST["txTime"];
	$signature = $_POST["signature"];
	$data = $orderId.$orderAmount.$referenceId.$txStatus.$paymentMode.$txMsg.$txTime;
	$hash_hmac = hash_hmac('sha256', $data, $config['cashfree_secret_key'], true) ;
	$computedSignature = base64_encode($hash_hmac);
	if ($signature == $computedSignature) {
        $wallet = $me['balance'] + $amount;
        $update = $user->updateStatic($me['user_id'],array('balance' => $wallet));
        $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],'amount' => $amount,'type' => 'Added_money_to_wallet','text' => 'Added money to wallet using Cashfree','time' => time()));
        $notif   = new notify();
        if($update) {
            $re_data = array(
                'notifier_id' => $me['user_id'],
                'recipient_id' => $me['user_id'],
                'type' => 'top_up_success',
                'url' => $config['site_url'] . "/campaign/wallet",
                'time' => time(),
                'money' => $amount
            );
            $notif->notify($ra_data);
        }
        if (!empty($_COOKIE['redirect_page'])) {
            $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
            $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
            header("Location: " . $redirect_page);
        }else{
            header("Location: " . $config['site_url'] . "/campaign/wallet");
        }
        exit();
    } else {
        header("Location: " . $config['site_url'] . "/campaign/wallet");
        exit();
    }
}

if ($action == 'iyzipay' && ($config['iyzipay_payment'] == "yes" && !empty($config['iyzipay_key']) && !empty($config['iyzipay_secret_key'])) && !empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
    require_once('core/zipy/iyzipay/samples/config.php');
	$amount = $main_total = aura::secure($_POST['amount']);
    if (!empty($config['currency_array']) && in_array($config['iyzipay_currency'], $config['currency_array']) && $config['iyzipay_currency'] != $config['currency'] && !empty($config['exchange']) && !empty($config['exchange'][$config['iyzipay_currency']])) {
        $amount= (($amount * $config['exchange'][$config['iyzipay_currency']]));
    }
	$callback_url = $config['site_url'] . "aj/payment/iyzipay_paid?amount=".$main_total;
	$request->setPrice($amount);
	$request->setPaidPrice($amount);
	$request->setCallbackUrl($callback_url);
	$basketItems = array();
	$firstBasketItem = new \Iyzipay\Model\BasketItem();
	$firstBasketItem->setId("BI".rand(11111111,99999999));
	$firstBasketItem->setName("Top Up Wallet");
	$firstBasketItem->setCategory1("Top Up Wallet");
	$firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
	$firstBasketItem->setPrice($amount);
	$basketItems[0] = $firstBasketItem;
	$request->setBasketItems($basketItems);
	$checkoutFormInitialize = \Iyzipay\Model\CheckoutFormInitialize::create($request, Config::options());
    $content = $checkoutFormInitialize->getCheckoutFormContent();
	if (!empty($content)) {
		$db->where('user_id',$me['user_id'])->update(T_USERS,array('conversation_id' => $ConversationId));
		$data['html'] = $content;
		$data['status'] = 200;
	}
	else{
		$data['message'] = lang('unknown_error');
	}
}

if ($action == 'iyzipay_paid' && $config['iyzipay_payment'] == "yes"){
	if (!empty($_POST['token']) && !empty($me['conversation_id']) && !empty($_GET['amount']) && is_numeric($_GET['amount']) && $_GET['amount'] > 0) {
        require_once('core/zipy/iyzipay/samples/config.php');

		# create request class
		$request = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
		$request->setLocale(\Iyzipay\Model\Locale::TR);
		$request->setConversationId($me['conversation_id']);
		$request->setToken($_POST['token']);

		# make request
		$checkoutForm = \Iyzipay\Model\CheckoutForm::retrieve($request, Config::options());

		# print result
		if ($checkoutForm->getPaymentStatus() == 'SUCCESS') {
            $amount = aura::secure($_GET['amount']);
            $wallet = $me['balance'] + $amount;
            $update = $user->updateStatic($me['user_id'],array('balance' => $wallet));
            $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],'amount' => $amount,'type' => 'Added_money_to_wallet','text' => 'Added money to wallet using Iyzipay','time' => time()));
            $notif   = new notify();
            if($update) {
                $re_data = array(
                    'notifier_id' => $me['user_id'],
                    'recipient_id' => $me['user_id'],
                    'type' => 'top_up_success',
                    'url' => $config['site_url'] . "/campaign/wallet",
                    'time' => time(),
                    'money' => $amount
                );
                $notif->notify($ra_data);
            }
            if (!empty($_COOKIE['redirect_page'])) {
                $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
                $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
                header("Location: " . $redirect_page);
            }else{
                header("Location: " . $config['site_url'] . "/campaign/wallet");
            }
            exit();
		}else{
			header("Location: " . $config['site_url'] . "/campaign/wallet");
	        exit();
		}
	}else{
        header("Location: " . $config['site_url'] . "/campaign/wallet");
	    exit();
	}
}

if ($action == 'checkout') {
	if (empty($_POST['card_number']) || empty($_POST['card_cvc']) || empty($_POST['card_month']) || empty($_POST['card_year']) || empty($_POST['token']) || empty($_POST['card_name']) || empty($_POST['card_address']) || empty($_POST['card_city']) || empty($_POST['card_state']) || empty($_POST['card_zip']) || empty($_POST['card_country']) || empty($_POST['card_email']) || empty($_POST['card_phone']) || empty($_POST['amount']) || !is_numeric($_POST['amount'])) {
		$data = array('status' => 400,'error' => lang('unknown_error'));
	} else {
        require_once('core/zipy/2checkout/Twocheckout.php');
		Twocheckout::privateKey($config['checkout_private_key']);
		Twocheckout::sellerId($config['checkout_seller_id']);
		if ($config['checkout_mode'] == 'sandbox') {
			Twocheckout::sandbox(true);
		} else {
			Twocheckout::sandbox(false);
		}
		try {
			$amount = $main_total = aura::secure(intval($_POST['amount']));
			if (!empty($config['currency_array']) && in_array($config['checkout_currency'], $config['currency_array']) && $config['checkout_currency'] != $config['currency'] && !empty($config['exchange']) && !empty($config['exchange'][$config['checkout_currency']])) {
				$amount = (($amount * $config['exchange'][$config['checkout_currency']]));
			}
			$charge  = Twocheckout_Charge::auth(array(
				"merchantOrderId" => "123",
				"token" => $_POST['token'],
				"currency" => $config['checkout_currency'],
				"total" => $amount,
				"billingAddr" => array(
					"name" => $_POST['card_name'],
					"addrLine1" => $_POST['card_address'],
					"city" => $_POST['card_city'],
					"state" => $_POST['card_state'],
					"zipCode" => $_POST['card_zip'],
					"country" => $cnames[$_POST['card_country']],
					"email" => $_POST['card_email'],
					"phoneNumber" => $_POST['card_phone']
				)
			));

			if ($charge['response']['responseCode'] == 'APPROVED') {
				$wallet = $me['balance'] + $main_total;
				$update = $user->updateStatic($me['user_id'], array('balance' => $wallet));
                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],'amount' => $main_total,'type' => 'Added_money_to_wallet','text' => 'Added money to wallet using 2checkout','time' => time()));
                $notif   = new notify();
                if($update) {
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $me['user_id'],
                        'type' => 'top_up_success',
                        'url' => $config['site_url'] . "/campaign/wallet",
                        'time' => time(),
                        'money' => $main_total
                    );
                    $notif->notify($ra_data);
                }
				if (!empty($_COOKIE['redirect_page'])) {
					$redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
					$redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
					$url = $redirect_page;
				} else {
					$url = $config['site_url'] . "/campaign/wallet";
				}
				if ($me['address'] != $_POST['card_address'] || $me['city'] != $_POST['card_city'] || $me['state'] != $_POST['card_state'] || $me['zip'] != $_POST['card_zip'] || $me['country_id'] != $_POST['card_country'] || $me['phone_number'] != $_POST['card_phone']) {
					$update_data = array(
						'address' => aura::secure($_POST['card_address']),
						'city' => aura::secure($_POST['card_city']),
						'state' => aura::secure($_POST['card_state']),
						'zip' => aura::secure($_POST['card_zip']),
						'country_id' => aura::secure($_POST['card_country']),
						'phone_number' => aura::secure($_POST['card_phone'])
					);
					$user->updateStatic($me['user_id'], $update_data);
				}
				$data = array('status' => 200,'url' => $url);
			} else {
				$data = array('status' => 400,'error' => lang('checkout_declined'));
			}
		} catch (Twocheckout_Error $e) {
			$data = array('status' => 400,'error' => $e->getMessage());
		}
	}
}

if ($action == 'authorize') {
    if (!empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
        require_once('core/zipy/authorize/vendor/autoload.php');
        $amount = aura::secure($_POST['amount']);
        $APILoginId = $config['authorize_login_id'];
        $APIKey = $config['authorize_transaction_key'];
        $refId = 'ref' . time();
        define("AUTHORIZE_MODE", $config['authorize_test_mode']);
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($APILoginId);
        $merchantAuthentication->setTransactionKey($APIKey);

        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($_POST['card_number']);
        $creditCard->setExpirationDate($_POST['card_year'] . "-" . $_POST['card_month']);
        $creditCard->setCardCode($_POST['card_cvc']);

        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setCreditCard($creditCard);

        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setPayment($paymentType);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        $controller = new AnetController\CreateTransactionController($request);
        if ($config['authorize_test_mode'] == 'SANDBOX') {
            $Aresponse = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        }else{
            $Aresponse = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        } 
        if ($Aresponse != null) {
            // if ($Aresponse->getMessages()->getResultCode() == 'Ok') {
            //     $trans = $Aresponse->TransactionResponse();
            //     if ($trans != null && $trans->getMessages() != null) {
            //         $wallet = $me['balance'] + $amount;
            //         $update = $user->updateStatic($me['user_id'],array('balance' => $wallet));
            //         $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],'amount' => $amount,'type' => 'Added_money_to_wallet','text' => 'Added money to wallet using Authorize','time' => time()));
            //         $notif   = new notify();
            //         if($update) {
            //             $re_data = array(
            //                 'notifier_id' => $me['user_id'],
            //                 'recipient_id' => $me['user_id'],
            //                 'type' => 'top_up_success',
            //                 'url' => $config['site_url'] . "/campaign/wallet",
            //                 'time' => time(),
            //                 'money' => $amount
            //             );
            //             $notif->notify($ra_data);
            //         }
            //         if (!empty($_COOKIE['redirect_page'])) {
            //             $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
            //             $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
            //             $data['url'] = $redirect_page;
            //         }else{
            //             $data['url'] = $config['site_url'] . "/campaign/wallet";
            //         }
            //         $data['status'] = 200;
            //     }else{
            //         $error = lang("something_went_wrong_please_try_again_later_");
            //         if ($trans->getErrors() != null) {
            //             $error = $trans->getErrors()[0]->getErrorText();
            //         }
            //         $data['status'] = 400;
            //         $data['error'] = $error;
            //     }
            // }else{
            //     $trans = $Aresponse->getTransactionResponse();
            //     $error = lang("something_went_wrong_please_try_again_later_");
            //     if (!empty($trans) && $trans->getErrors() != null) {
            //         $error = $trans->getErrors()[0]->getErrorText();
            //     }
            //     $data['status'] = 400;
            //     $data['error'] = $error;
            // }
        }else{
            $data['status'] = 400;
            $data['error'] = lang("kindly_fill_details");
        }
    }else{
        $data['status'] = 400;
        $data['error'] = lang('amount_empty');
    }
}

if ($action == 'securionpay') {
    if (!empty($_POST) && !empty($_POST['charge']) && !empty($_POST['charge']['id'])) {
        $url = "https://api.securionpay.com/charges?limit=10";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERPWD, $config['securionpay_secret_key'].":password");
        $resp = curl_exec($curl);
        curl_close($curl);
        $resp = json_decode($resp,true);
        if (!empty($resp) && !empty($resp['list'])) {
            foreach ($resp['list'] as $key => $value) {
                if ($value['id'] == $_POST['charge']['id']) {
                    if (!empty($value['metadata']) && !empty($value['metadata']['user_key']) && !empty($value['amount'])) {
                        if ($me['securionpay_key'] == $value['metadata']['user_key']) {
                            $db->where('user_id',$me['user_id'])->update(T_USERS,array('securionpay_key' => 0));
                            $amount = intval(aura::secure($value['amount'])) / 100;
                            $wallet = $me['balance'] + $amount;
                            $update = $user->updateStatic($me['user_id'],array('balance' => $wallet));
                            $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],'amount' => $amount,'type' => 'Added_money_to_wallet','text' => 'Added money to wallet using Securionpay','time' => time()));
                            $notif   = new notify();
                            if($update) {
                                $re_data = array(
                                    'notifier_id' => $me['user_id'],
                                    'recipient_id' => $me['user_id'],
                                    'type' => 'top_up_success',
                                    'url' => $config['site_url'] . "/campaign/wallet",
                                    'time' => time(),
                                    'money' => $amount
                                );
                                $notif->notify($ra_data);
                            }
                            if (!empty($_COOKIE['redirect_page'])) {
                                $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
                                $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
                                $data['url'] = $redirect_page;
                            }else{
                                $data['url'] = $config['site_url'] . "/campaign/wallet";
                            }
                            $data['status'] = 200;
                        }else{
                            $data['status'] = 400;
                            $data['error'] = lang("something_went_wrong_please_try_again_later_");
                        }
                    }else{
                        $data['status'] = 400;
                        $data['error'] = lang("something_went_wrong_please_try_again_later_");
                    }
                }
            }
        }else{
            $data['status'] = 400;
            $data['error'] = lang("something_went_wrong_please_try_again_later_");
        }
    }else{
        $data['status'] = 400;
        $data['error'] = lang("please_check_the_details");
    }
}

if ($action == 'securionpay_token') {
    if (!empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
        $amount = aura::secure($_POST['amount']);
        require_once('core/zipy/securionpay/vendor/autoload.php');
        $securionPay = new SecurionPayGateway($config['securionpay_secret_key']);
        $user_key = rand(1111,9999).rand(11111,99999);
        $checkoutCharge = new CheckoutRequestCharge();
        $checkoutCharge->amount(($amount * 100))->currency('USD')->metadata(array('user_key' => $user_key));
        $checkoutRequest = new CheckoutRequest();
        $checkoutRequest->charge($checkoutCharge);
        $signedCheckoutRequest = $securionPay->signCheckoutRequest($checkoutRequest);
        if (!empty($signedCheckoutRequest)) {
            $db->where('user_id',$me['user_id'])->update(T_USERS,array('securionpay_key' => $user_key));
            $data['status'] = 200;
            $data['token'] = $signedCheckoutRequest;
        }else{
            $data['status'] = 400;
            $data['error'] = lang("please_check_the_details");
        }
    }else{
        $data['status'] = 400;
        $data['error'] = lang('amount_empty');
    }
}

if($action == 'paysera_cancel'){
    header('Location: ' . $config['site_url']);
    exit();
}

if ($action == 'stripe_cancel') {
    $db->where('user_id',$me['user_id'])->update(T_USERS,array('StripeSessionId' => ''));
    header("Location: " . $config['site_url'] . "/campaign/wallet");
    exit();
}