<?php
$httpOrHttps = '';
if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])=="on") {
	$httpOrHttps = 'https';
} else {
	$httpOrHttps = 'http';
}


$root_dir = str_ireplace($httpOrHttps."://".$_SERVER['HTTP_HOST'], "",  $httpOrHttps."://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']));
$root_dir = str_replace('\\','',$root_dir); 
if($root_dir=="/"){
	$root_dir = "";
}else{
	$lastChar = substr($root_dir, -1);
	if($lastChar!="/"){
		$root_dir = $root_dir."/";
	}
	if (substr($root_dir, 0, 1) === '/') { 
		$root_dir = substr($root_dir, 1);
	}
}

if (!defined('ROOT_DIR_NAME')) {
	define('ROOT_DIR_NAME', $root_dir);
}

include 'public/index.php';