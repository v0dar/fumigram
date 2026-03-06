<?php
require_once('core/init.php');
$root     = __DIR__;
define('ROOT', $root);
$app      = (!empty($_GET['app'])) ? $_GET['app'] : 'home';
$apph     = (!empty($_GET['apph'])) ? $_GET['apph'] : 'home';
$tens     = "apps/$theme/$app/handlers/$apph.php";
if (file_exists($tens)) {
	require_once($tens);
}
if (!empty($_GET)) {
    foreach ($_GET as $key => $value) {
        if (!is_array($value)) {
            $value = preg_replace('/on[^<>=]+=[^<>]*/m', '', $value);
            $value = preg_replace('/\((.*?)\)/m', '', $value);
            $_GET[$key] = strip_tags($value);
        }
    }
}
if (!empty($_REQUEST)) {
    foreach ($_REQUEST as $key => $value) {
        if (!is_array($value)) {
            $value = preg_replace('/on[^<>=]+=[^<>]*/m', '', $value);
            $_REQUEST[$key] = strip_tags($value);
        }
    }
}
if (!empty($_POST)) {
    foreach ($_POST as $key => $value) {
        if (!is_array($value)) {
            $value = preg_replace('/on[^<>=]+=[^<>]*/m', '', $value);
            $_POST[$key] = strip_tags($value);
        }
    }
}
if(isset($context['user'])) {
	if($context['user']["active"] == 0){
		$app      = 'activate';
		$apph     = 'activate';
		$tens     = "apps/$theme/activate/handlers/activate.php";
		require_once($tens);
	}
    if($context['user']["active"] == 2){
		$app      = 'deactivated';
		$apph     = 'deactivated';
		$tens     = "apps/$theme/deactivated/handlers/deactivated.php";
		require_once($tens);
	}
	if (($context['user']['startup_avatar'] == 0 || $context['user']['startup_info'] == 0 || $context['user']['startup_follow'] == 0) && $app != 'startup' && $context['user']['active']) {
		$app      = 'startup';
		$apph     = 'startup';
		header("Location: startup");
		exit;
	}
}

if (empty($context['content'])) {
	header("Location: $site_url/404");
	exit;
}

$context['header'] = '';
if ($config['header']) {
	$context['header'] = $ui->intel('main/templates/header/header');
}
$context['footer'] = $ui->intel('main/templates/footer/footer');
$context['page_title'] = strip_tags($context['page_title']);
if(isset($context['post_data']['description'])){
	$context['post_data']['description'] = strip_tags($context['post_data']['description']);
}
echo $ui->intel('main/templates/container', array('CONTENT'=> $context['content'], 'THEME_URL' => $context['theme_url'], 'HEADER' => $context['header'], 'FOOTER' => $context['footer'], 'TITLE' => $context['page_title']));
$db->disconnect();
unset($context);