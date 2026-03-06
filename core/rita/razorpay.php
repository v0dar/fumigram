<?php
if ($action == 'generate_order' && IS_LOGGED && $config['razorpay'] == "on" && !empty($config['razorpay_key']) && !empty($config['razorpay_secret'])) {
    $url = 'https://api.razorpay.com/v1/orders';
    $key_id = $config['razorpay_key'];
    $key_secret = $config['razorpay_secret'];
    //cURL Request
    $ch = curl_init();
    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['amount' => aura::secure($_POST['amount'])*100,'currency' => 'INR']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $request = curl_exec ($ch);
    curl_close ($ch);
    $tranx = json_decode($request);
    $err = curl_error($ch);
    if($err){
        $data = array('status' => 400,'message' => $tranx->error->description); 
    }else{
        $data = array('status' => 200,'message' => 'success','order_id' => $tranx->id); 
    }
}
else if ($action == 'proccess_payment' && IS_LOGGED && $config['razorpay'] == "on" && !empty($config['razorpay_key']) && !empty($config['razorpay_secret'])) {
    $payment_id = aura::secure($_POST['payment_id']);
    $data = array('amount' => aura::secure($_POST['amount'])*100,'currency' => $config['currency']);
    $url = 'https://api.razorpay.com/v1/payments/' . $payment_id . '/capture';
    $key_id = $config['razorpay_key'];
    $key_secret = $config['razorpay_secret'];
    $params = http_build_query($data);
    //cURL Request
    $ch = curl_init();
    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $request = curl_exec ($ch);
    curl_close ($ch);
    $tranx = json_decode($request);
    $err = curl_error($ch);

    if($err){
        $data = array('status' => 400,'message' => $tranx->error->description); 
    }else{
        if( $tranx->status == 'captured'){
            $type = aura::secure($_POST['type']);
            $url = '';
            if ($type == 'add_money') {
                $amount = (int)$tranx->amount / 100;
                $wallet = $me['balance'] + $amount;
                $update = $user->updateStatic($me['user_id'],array('balance' => $wallet));
                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],'amount' => $amount,'type' => 'Added money to wallet using Razorpay','time' => time()));
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
                    $url = $redirect_page;
                }else{
                    $data['url'] = $config['site_url'] . "/campaign/wallet";
                }
            }
            $data = array('status' => 200,'message' => 'success','url' => $url); 
        }else{
            $data = array('status' => 400,'message' => 'error while proccess payment'); 
        }
    }
}