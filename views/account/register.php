<?php
require_once('api/auth.php');

$user = passport\autologin();
if($user){
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
		$title = 'Login';
		$miniheader = true;
		require_once('includes/header.php');
		echo "<p style=\"color:red;\">$result->message</p>";
		echo $registerform;
		require_once('includes/footer.php');
		die();
	}
}

$title = 'Register';
$miniheader = true;
require_once('includes/header.php');
echo $registerform;
require_once('includes/footer.php');
$conn->close();
?>