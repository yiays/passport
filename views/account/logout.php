<?php
session_start();
require_once('api/auth.php');
if(passport\logout()){
	header('Location: /');
	die();
}else{
	die('Failed to log you out. Please try again later.');
}
?>