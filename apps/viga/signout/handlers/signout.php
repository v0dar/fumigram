<?php
$signout = users::signoutUser(); 
header("Location: $site_url");
exit();