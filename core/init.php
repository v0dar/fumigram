<?php
error_reporting(1);
define("ROOTPATH", dirname(dirname(__FILE__)) );
session_start();

require_once('core/db.php');
require_once('core/utils.php');
require_once('core/sync/tables.php');
require_once('core/sync/settings.php');
require_once('core/sync/autoload.php');
require_once('core/zipy/getID3-1.9.14/getid3/getid3.php');
require_once('core/zipy/SimpleImage/src/claviska/SimpleImage.php');
$init  = array('db' => $db,'site_url' => $site_url,'config' => $config,'mysqli' => $mysqli);
$ui    = new aura($init);
$user  = new users();
$me    = array();
$langs = $user->Langs();

$context['theme_url']     = $site_url.'/apps/'.$config['theme'];
$context['loggedin']      = $user->isLogged();
$context['is_admin']      = false;
$context['is_devel']      = false;
$context['langs']         = $langs;
$context['site_url']      = $site_url;
$context['config']        = $config;
$context['direct']        = dirname(__dir__).'/apps/'.$config['theme'];
$context['images']        = sprintf('%s/media/img',$config['site_url']);

$config['currency_array']        = (Array) json_decode($config['currency_array']);
$config['currency_symbol_array'] = (Array) json_decode($config['currency_symbol_array']);
if (!empty($config['exchange'])) {
    $config['exchange'] = (Array) json_decode($config['exchange']);
}
$config['paystack_currency_array']   = array('USD','GHS','NGN','ZAR');
$config['iyzipay_currency_array']    = array('USD','EUR','GBP','IRR','TL');
$config['stripe_currency_array']     = array('USD','EUR','AUD','BRL','CAD','CZK','DKK','HKD','HUF','ILS','JPY','MYR','MXN','TWD','NZD','NOK','PHP','PLN','RUB','SGD','SEK','CHF','THB','GBP');
$config['paypal_currency_array']     = array('USD','EUR','AUD','BRL','CAD','CZK','DKK','HKD','HUF','INR','ILS','JPY','MYR','MXN','TWD','NZD','NOK','PHP','PLN','GBP','RUB','SGD','SEK','CHF','THB');
$config['cashfree_currency_array']   = array('INR','USD','BDT','GBP','AED','AUD','BHD','CAD','CHF','DKK','EUR','HKD','JPY','KES','KWD','LKR','MUR','MYR','NOK','NPR','NZD','OMR','QAR','SAR','SEK','SGD','THB','ZAR');
$config['2checkout_currency_array']  = array('USD','EUR','AED','AFN','ALL','ARS','AUD','AZN','BBD','BDT','BGN','BMD','BND','BOB','BRL','BSD','BWP','BYN','BZD','CAD','CHF','CLP','CNY','COP','CRC','CZK','DKK','DOP','DZD','EGP','FJD','GBP','GTQ','HKD','HNL','HRK','HUF','IDR','ILS','INR','JMD','JOD','JPY','KES','KRW','KWD','KZT','LAK','LBP','LKR','LRD','MAD','MDL','MMK','MOP','MRO','MUR','MVR','MXN','MYR','NAD','NGN','NIO','NOK','NPR','NZD','OMR','PEN','PGK','PHP','PKR','PLN','PYG','QAR','RON','RSD','RUB','SAR','SBD','SCR','SEK','SGD','SYP','THB','TND','TOP','TRY','TTD','TWD','UAH','UYU','VND','VUV','WST','XCD','XOF','YER','ZAR');

if ($context['loggedin'] === true) {
    define('IS_LOGGED', $context['loggedin']);
	$context['user'] = $user->LoggedInUser();
	$context['user'] = aura::toArray($context['user']);
	$me              = $context['me'] = $context['user'];
	$ulang           = $context['user']['language'];
	$countries       = "lang/countries/english.php";
	if (file_exists($countries)) {
		$countries = "lang/countries/$ulang.php";
	}
    $user->onlineStatus();
    $user->offlineUsers();
	$user->updateLastSeen();
	require_once($countries);
	$context['cnames']         = $cnames;
	$context['is_admin']       = (($me['admin'] == 1) ? true : false) || (($me['manager'] == 1) ? true : false) || (($me['moderator'] == 1) ? true : false);
    $context['is_devel']       = (($me['admin'] == 1) ? true : false);
    define('IS_ADMIN', $context['is_admin']);
    define('IS_DEVEL', $context['is_devel']);
	$_SESSION['lang']          = $me['language'];
}

if (!empty($_GET['lang']) && in_array($_GET['lang'], array_keys($langs))) {
    $lname = $user::secure(strtolower($_GET['lang']));
    $_SESSION['lang'] = $lname;
    if ($context['loggedin'] === true) {
        $db->where('user_id', $me['user_id'])->update(T_USERS, array('language' => $lname));
    }
}

if (empty($_SESSION['lang'])) {
    $_SESSION['lang'] = $config['language'];
}
$context['language']         = $_SESSION['lang'];
$lang                        = $user->fetchLanguage($context['language']);
$context['lang']             = $lang;
$context['csrf_token']       = csrf_token();
$context['currency_symbol']  = Currency($config['currency']);
if (!defined('IS_LOGGED')) {
    define('IS_LOGGED', $context['loggedin']);
}
if (!defined('IS_ADMIN')) {
    define('IS_ADMIN', $context['is_admin']);
}
if (!defined('IS_DEVEL')) {
    define('IS_DEVEL', $context['is_devel']);
}

if (!empty($_GET['ref']) && $context['loggedin'] == false && !isset($_COOKIE['src']) && $config['affiliate_system'] == 1) {
    $get_ip = get_ip_address();
    if (!isset($_SESSION['ref']) && !empty($get_ip)) {
        $_GET['ref'] = $user::secure($_GET['ref']);
        $user->setUserByName($_GET['ref']);
		$udata = $user->userData($user->getUser());
		$udata = o2array($udata);
        if (!empty($udata)) {
            $_SESSION['ref'] = $udata['username'];
        }
    }
}

//Ability to set light or dark mode by default
if($config['site_display_mode'] === 'day' && !isset($_COOKIE['mode'])){
    setcookie("mode", 'day', time() + (10 * 365 * 24 * 60 * 60), "/");
}else if($config['site_display_mode'] === 'night' && !isset($_COOKIE['mode'])){
    setcookie("mode", 'night', time() + (10 * 365 * 24 * 60 * 60), "/");
}

$context['call_action'] = array(
    '1' => 'read_more',
    '2' => 'shop_now',
    '3' => 'view_now',
    '4' => 'visit_now',
    '5' => 'book_now',
    '6' => 'learn_more',
    '7' => 'play_now',
    '8' => 'bet_now',
    '9' => 'donate',
    '10' => 'apply_here',
    '11' => 'quote_here',
    '12' => 'order_now',
    '13' => 'book_tickets',
    '14' => 'enroll_now',
    '15' => 'find_card',
    '16' => 'get_quote',
    '17' => 'get_tickets',
    '18' => 'locate_dealer',
    '19' => 'order_online',
    '20' => 'preorder_now',
    '21' => 'schedule_now',
    '22' => 'sign_up_now',
    '23' => 'subscribe',
    '24' => 'register_now',
    '25' => 'go_to'
);

$context['anime_categories'] = array(
    '1' => 'action',
    '2' => 'adventure',
    '3' => 'comedy',
    '4' => 'drama',
    '5' => 'slice_of_life',
    '6' => 'fantasy',
    '7' => 'magic',
    '8' => 'supernatural',
    '9' => 'horror',
    '10' => 'mystery',
    '11' => 'psychological',
    '12' => 'romance',
    '13' => 'sci_fi',
    '14' => 'cyberpunk',
    '15' => 'game',
    '16' => 'ecchi',
    '17' => 'demons',
    '18' => 'harem',
    '19' => 'josei',
    '20' => 'martial_arts',
    '21' => 'kids',
    '22' => 'historical',
    '23' => 'hentai',
    '24' => 'isekai',
    '25' => 'military',
    '26' => 'mecha',
    '27' => 'music',
    '28' => 'parody',
    '29' => 'police',
    '30' => 'post_apocalyptic',
    '31' => 'reverse_harem',
    '32' => 'school',
    '33' => 'seinen',
    '34' => 'shoujo',
    '35' => 'shoujo_ai',
    '36' => 'shounen',
    '37' => 'shounen_ai',
    '38' => 'space',
    '39' => 'sports',
    '40' => 'super_power',
    '41' => 'tragedy',
    '42' => 'vampire',
    '43' => 'yuri',
    '44' => 'yaoi',
);

require_once('core/cron.php');
require_once('core/vera/media.php');
require_once('core/zipy/webtopay.php');
require_once('core/sync/onesignal.php');
require_once('core/sync/functions/function.php');