<?php
require_once('core/sync/config.php');

# Connect to MySQL Server
$mysqli  = new mysqli($host, $user, $pass, $name);
require_once('core/zipy/DB/vendor/autoload.php');

# Handling Server Errors
$Errors = array();
if (mysqli_connect_errno()) {
    $Errors[] = "Failed to connect to MySQL: " . mysqli_connect_error();
}
if (!function_exists('curl_init')) {
    $Errors[] = "PHP CURL is NOT installed on your web server !";
}
if (!version_compare(PHP_VERSION, '5.5.0', '>=')) {
    $Errors[] = "Required PHP_VERSION >= 5.5.0 , Your PHP_VERSION is : " . PHP_VERSION . "\n";
}
if (isset($Errors) && !empty($Errors)) {
    foreach ($Errors as $Error) {
        echo "<h2>" . $Error . "</h2>";
    }
    die();
}

// Connect to the database after light verfication
$query      = $mysqli->query("SET NAMES utf8mb4");
$sqlConnect = $mysqli;
$db         = new MysqliDb($mysqli);