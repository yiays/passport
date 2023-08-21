<?php
require('views/503.php');
/*
$dir = $_SERVER['REQUEST_URI'];
if(strpos($dir, '?')) $dir = substr($dir, 0, strpos($dir, '?'));
$dir = rtrim($dir, '/');

if(strpos($dir, '.')===0){
	http_response_code(403);
	die('<h1>Forbidden</h3><p>You don\'t have permission to access this resource.</p>');
}

if(file_exists('views'.$dir.'.php')){
	require('views'.$dir.'.php');
}
elseif(file_exists('views'.$dir.'/index.php')){
	require('views'.$dir.'/index.php');
}else{
	http_response_code(404);
	die('<h1>Not Found</h1><p>The requested URL was not found on this server.</p>');
}*/
?>