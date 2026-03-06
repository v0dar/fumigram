<?php
require_once('core/zipy/PayPal/vendor/autoload.php');
$paypal = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential($config['paypal_id'],$config['paypal_secret']));
$paypal->setConfig(array('mode' => $config['paypal_mode']));