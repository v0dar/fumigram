<?php 
require_once('./core/init.php');
$root     = __DIR__;
define('ROOT', $root);
$app      = (!empty($_GET['app'])) ? $_GET['app'] : 'home';
$apph     = (!empty($_GET['apph'])) ? $_GET['apph'] : 'home';
$tens     = "apps/$theme/$app/handlers/$apph.php";

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

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        exit("Restrcited Area");
    }
} else {
    exit("Restrcited Area");
}

if (file_exists($tens)) {
    require_once($tens);
} else{
    require_once("apps/$theme/404/handlers/404.php");
}

if(ISSET( $context['user'])){
    if($context['user']["active"] == 0){
        $app      = 'activate';
        $apph     = 'activate';
        $tens     = "apps/$theme/activate/handlers/activate.php";
        require_once($tens);
    } if($context['user']["active"] == 2){
        $app      = 'deactivated';
        $apph     = 'deactivated';
        $tens     = "apps/$theme/deactivated/handlers/deactivated.php";
        require_once($tens);
    } if (($context['user']['startup_avatar'] == 0 || $context['user']['startup_info'] == 0 || $context['user']['startup_follow'] == 0) && $app != 'startup' && $context['user']['active']) {
        $app      = 'startup';
        $apph     = 'startup';
        $tens     = "apps/$theme/$app/handlers/$apph.php";
        require_once($tens);
    }
}

if (empty($context['content'])) {
    require_once("apps/$theme/404/handlers/404.php");
}

$data['url'] = $context['page_link'];
$context['header'] = '';
if ($config['header']) {
    $context['header'] = $ui->intel('main/templates/header/header');
}
$context['footer'] = '';
if ($config['footer']) {
    $context['footer'] = $ui->intel('main/templates/footer/footer');
}
if (!empty($context['settings_page'])) {
    $data['settings_page'] = $context['settings_page'];
}

$data['header'] = $config['header'];
$data['footer'] = $config['footer'];
$data['page_title'] = $context['page_title'];
$data['app_name'] = $context['app_name'];
?>
<input type="hidden" id="json-data" value='<?php echo htmlspecialchars(json_encode($data));?>'>
<?php
echo $context['content'];
$db->disconnect();
unset($context);
exit();