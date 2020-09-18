<?php
require_once('api/auth.php');

$redirect = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : '/account';
$message = "";

$user = passport\autologin();
if($user){
	header("Location: $redirect");
	die();
}

require_once('../passport.conn.php');
require_once('api/forms/loginform.php');
if(isset($_POST['username'])){
	$result = $loginform->validate($_POST);
	if($result->passed){
		$result = $loginform->submit($_POST);
		if($result){
			header("Location: $redirect");
			die();
		}else{
			die($result);
		}
	}else{
		$message = $result->message;
	}
}

require_once('api/models/passport.php');
require_once('includes/util.php');

$title = 'Login';
$miniheader = true;
require_once('includes/header.php');
if($redirect !== '/account') echo "<p><i>Login or register an account to continue.</i></p>\n	";
if($message) echo "<p style=\"color:red;\">$result->message</p>";
echo "$loginform
	<h3>Other options</h3>
	<button type=\"register\" data-href=\"/account/register".($redirect!=='/account'?'?redirect='.urlencode($redirect):'')."\">Register</button>
	";
foreach($services as $servicename => $service){
	$textcol = (lightness($service->theme_color) >= 0.7? '#000': '#fff');
	echo "<button data-href=\"#\" style=\"background: $service->theme_color; color: $textcol;\">Login with ".ucfirst($servicename)."</button>
	";
}
require_once('includes/footer.php');
$conn->close();
?>