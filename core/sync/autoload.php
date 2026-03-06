<?php 
spl_autoload_register(function($className) {
	$dirs  = array('core/vera','core/zipy/sitemap-php','core/zipy/MySQL-Dump');
	foreach ($dirs as $dir) {
		$path = "$dir/$className.php";
		if (file_exists($path)) {
			require_once($path);
		}
	}
});
