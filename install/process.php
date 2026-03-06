<?php 
error_reporting(0);
@ini_set('max_execution_time', 0);
header ("Access-Control-Allow-Origin: *");

$error = '';
$noerror = true;
$data  = array();
$data['status']  = 400;

if (empty($_POST["sql_host"])) {
    $error = 'Database Hostname Cannot be Empty';
} elseif (empty($_POST["sql_name"])) {
    $error = 'Invalid Database Name';
} elseif (empty($_POST["sql_user"])) {
    $error = 'Invalid Database Username';
} elseif (empty($_POST["sql_pass"])) {
    $error = 'Your Database Password is Required';
} elseif (empty($_POST["site_title"])) {
    $error = 'Your Website Title is Required';
} elseif (empty($_POST["site_email"])) {
    $error = 'Your Website Email is Required. EG: info@fumigram.com';
} elseif (empty($_POST["site_url"])) {
    $error = 'Your Website URL is Required';
} elseif (empty($_POST["email_address"])) {
    $error = 'Type in Your Account Email Address';
} elseif (!filter_var($_POST["email_address"], FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid Email Address';
} elseif (empty($_POST["username"])) {
    $error = 'Type in Your Account Username';
} elseif (empty($_POST["password"])) {
    $error = 'Type in Your Account Password';
}

if (empty($error)) {
    if (!empty($_POST['install'])) {
        $con = mysqli_connect($_POST['sql_host'], $_POST['sql_user'], $_POST['sql_pass'], $_POST['sql_name']);
        if (mysqli_connect_errno()) {
            $noerror = false;
            $error = "Failed to connect to MySQL: " . mysqli_connect_error() . "";
        }
        $data_content = 
'<?php
// +------------------------------------------------------------------------+
// | @author Vidar (https://github.com/v0dar)
// | @author_url 1: http://fumigram.free.nf/
// | @author_url 2: https://github.com/v0dar
// | @author_email: fumigram.app@gmail.com   
// +------------------------------------------------------------------------+
// | Fumigram Pixel Media Script
// | Copyright (c) 2019 fumigram. All rights reserved.
// +------------------------------------------------------------------------+
// MySQL Hostname
$host = "'  . $_POST["sql_host"] . '";

// MySQL Database User
$user = "'  . $_POST["sql_user"] . '";

// MySQL Database Password
$pass = "'  . $_POST["sql_pass"] . '";

// MySQL Database Name
$name = "'  . $_POST["sql_name"] . '";

// Site URL
$site_url = "' . $_POST['site_url'] . '"; // e.g (http://example.com)

// Discovered Fumigram
// '. $_POST['username'] .' Discord fumigram through ' . $_POST['discover'] . '
';

        if ($noerror) {
            $file_name = '../core/sync/config.php';
            $config_file = file_put_contents($file_name, $data_content);
        }
        if ($config_file) {
            $filename = '../fumigram.sql';
            $templine = '';
            $lines = file($filename);
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '')
                    continue;
                $templine .= $line;
                $query = false;
                if (substr(trim($line), -1, 1) == ';') {
                    $query = mysqli_query($con, $templine);
                    $templine = ''; 
                }
            } 
        }
        if ($query) {
            mysqli_close($con);
            $con2 = mysqli_connect($_POST['sql_host'], $_POST['sql_user'], $_POST['sql_pass'], $_POST['sql_name']);
            $query_one  = mysqli_query($con2, "UPDATE `config` SET `value` = '" . mysqli_real_escape_string($con2, $_POST['site_url']). "' WHERE `name` = 'site_url'");
            $query_one .= mysqli_query($con2, "UPDATE `config` SET `value` = '" . mysqli_real_escape_string($con2, $_POST['site_title']). "' WHERE `name` = 'site_name'");
            $query_one .= mysqli_query($con2, "UPDATE `config` SET `value` = '" . mysqli_real_escape_string($con2, $_POST['site_email']). "' WHERE `name` = 'site_email'");
            $query_one .= mysqli_query($con2, "UPDATE `config` SET `value` = '" . mysqli_real_escape_string($con2, md5(microtime())). "' WHERE `name` = 'app_api_id'");
            $query_one .= mysqli_query($con2, "UPDATE `config` SET `value` = '" . mysqli_real_escape_string($con2, md5(time())). "' WHERE `name` = 'app_api_key'");
            $query_one .= mysqli_query($con2, "INSERT INTO `users` (`user_id`, `username`, `email`, `ip_address`, `password`, `gender`, `language`, `avatar`, `banner`, `active`, `admin`, `verified`, `online`, `last_seen`, `registered`, `joined`, `time`, `hexagon`, `allow_ads`) VALUES (1,  '" . mysqli_real_escape_string($con2, $_POST['username']). "',  '" . mysqli_real_escape_string($con2, $_POST['email_address']). "',  '::1',  '" . mysqli_real_escape_string($con2, sha1($_POST['password'])) . "', '" . mysqli_real_escape_string($con2, $_POST['gender']). "',  'english', 'media/img/d-avatar.jpg',  'media/img/d-cover.jpg', 1, 1, 1,  1, '" . time() . "', '00/0000', '" . date('jS \of F Y') . "', '" . time() . "', '" . mysqli_real_escape_string($con2, $_POST['hexagon']). "', 1);");
            mysqli_close($con2);
            if ($noerror) {
                $data['status'] = 200;
                $data['siteurl'] = $_POST['site_url'];
                $data['message'] = $_POST['site_title'] .' '. "Pixel Media Script Successfully Installed...";
            }
        }
    }
} else {
    $data['status'] = 400;
    $data['message'] = $error;
}

header("Content-type: application/json");
$data = json_encode($data);
echo $data;
