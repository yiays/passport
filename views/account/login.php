<?php
require_once('api/auth.php');

$user = passport\autologin();
if($user){
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
		$title = 'Login';
		$miniheader = true;
		require_once('includes/header.php');
		echo "<p style=\"color:red;\">$result->message</p>";
		echo $loginform;
		require_once('includes/footer.php');
		die();
	}
}

$title = 'Login';
$miniheader = true;
require_once('includes/header.php');
echo $loginform;
require_once('includes/footer.php');
$conn->close();
?>