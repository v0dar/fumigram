<?php
require_once('core/zipy/stripe/vendor/autoload.php');
$stripe = array("secret_key" => $config['stripe_secret'],"publishable_key" => $config['stripe_id']);
\Stripe\Stripe::setApiKey($stripe['secret_key']);