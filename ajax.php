<?php
require_once('./core/init.php');
$app      = (!empty($_GET['app'])) ? $_GET['app'] : '';
$action   = (!empty($_GET['a'])) ? $_GET['a'] : '';
$handle   = "core/rita/$app.php";
$data     = array();
$root     = __DIR__;
$hash     = (!empty($_GET['hash'])) ? $_GET['hash'] : '';
if (empty($hash)) {
    $hash = (!empty($_POST['hash'])) ? $_POST['hash'] : '';
}
define('ROOT', $root);
header("Content-type: application/json");
if (empty(IS_ADMIN) && empty(IS_DEVEL) && (empty($hash) || empty(verifcsrf_token($hash))) && $action != 'contact_us' && $app != 'wallet' && $app != 'credits' && $app != 'paystack') {
	$data = array('status'   => '400','message'  => 'ERROR: Invalid or missing CSRF token');
    echo json_encode($data, JSON_PRETTY_PRINT);
	exit();
} else if (!file_exists($handle)) {
    $data = array('status' => '404','message' => 'Not Found');
    echo json_encode($data, JSON_PRETTY_PRINT);
	exit();
} else {	
	require_once($handle);
	echo json_encode($data, JSON_PRETTY_PRINT);
	$db->disconnect();
	unset($context);
	exit();
}