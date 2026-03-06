<?php
require_once('core/init.php');
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
$files = scandir('help/uis');
unset($files[0]);
unset($files[1]);
$ui = 'home';
if (!empty($path['ui']) && in_array($path['ui'], $files) && file_exists('help/uis/'.$path['ui'].'/content.phtml')) {
    $ui = $path['ui'];
}
$data = array();
$help = new help();
$text = $help->Senction($ui.'/content'); ?>
<input type="hidden" id="json-data" value='<?php echo htmlspecialchars(json_encode($data));?>'>
<?php echo $text; ?>