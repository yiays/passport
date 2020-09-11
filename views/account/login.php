<?php
session_start();
require_once('api/auth.php');
if((key_exists('user', $_SESSION) && isset($_SESSION['user']) && !is_null($_SESSION['user'])) || passport\autologin()){
	header("Location: /account");
	die();
}
require_once('../passport.conn.php');
require_once('api/forms/loginform.php');
if(isset($_POST['username'])){
	$result = $loginform->validate($_POST);
	if($result->passed){
		$result = $loginform->submit($_POST);
		if($result){
			header("Location: /account");
			die();
		}else{
			die($result);
		}
	}else{
		require_once('includes/header.php');
		echo "<p style=\"color:red;\">$result->message</p>";
		echo $loginform;
		require_once('includes/footer.php');
		die();
	}
}

require_once('includes/header.php');
echo $loginform;
require_once('includes/footer.php');
?>