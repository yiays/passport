<?php
session_start();
if(key_exists('user', $_SESSION) && isset($_SESSION['user']) && !is_null($_SESSION['user'])){
	header("Location: /account");
	die();
}
require_once('../passport.conn.php');
require_once('api/forms/registerform.php');
if(isset($_POST['username'])){
	$result = $registerform->validate($_POST);
	if($result->passed){
		$result = $registerform->submit($_POST);
		if($result !== true){
			header("Location: /account");
			die();
		}else{
			die($result);
		}
	}else{
		require_once('includes/header.php');
		echo "<p style=\"color:red;\">$result->message</p>";
		echo $registerform;
		require_once('includes/footer.php');
		die();
	}
}

require_once('includes/header.php');
echo $registerform;
require_once('includes/footer.php');
?>