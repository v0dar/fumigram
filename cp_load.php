<?php
require_once('./core/init.php');
if (IS_LOGGED == false || IS_ADMIN == false && IS_DEVEL == false) {
    header("Location: $site_url");
    exit();
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
$path = (!empty($_GET['path'])) ? UiPath($_GET['path']) : null;
$files = scandir('cpanel/uis');
unset($files[0]);
unset($files[1]);
$ui = 'overview';
if (!empty($path['ui']) && in_array($path['ui'], $files) && file_exists('cpanel/uis/'.$path['ui'].'/content.phtml')) {
    $ui = $path['ui'];
}
$data = array();
$cpanel = new cpanel();
$text = $cpanel->Senction($ui.'/content'); ?>
<input type="hidden" id="json-data" value='<?php echo htmlspecialchars(json_encode($data));?>'>
<?php echo $text; ?>