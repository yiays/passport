<?php
require_once('../passport.conn.php');
require_once('forms/registerform.php');
if(isset($_POST['username'])){
	$result = $registerform->validate($_POST);
	if($result->passed){
		header("Location: /account");
		die();
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