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
require_once('api/forms/registerform.php');
if(isset($_POST['username'])){
	$result = $registerform->validate($_POST);
	if($result->passed){
		$result = $registerform->submit($_POST);
		if($result !== true){
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

$title = 'Register';
$miniheader = true;
require_once('includes/header.php');
if($redirect !== '/account') echo "<p><i>Login or register an account to continue.</i></p>\n	";
echo "$registerform
	<h3>Other options</h3>
	<button type=\"register\" data-href=\"/account/login".($redirect!=='/account'?'?redirect='.urlencode($redirect):'')."\">Login</button>
	";
foreach($services as $servicename => $service){
	$textcol = (lightness($service->theme_color) >= 0.7? '#000': '#fff');
	echo "<button data-href=\"#\" style=\"background: $service->theme_color; color: $textcol;\">Register with ".ucfirst($servicename)."</button>
	";
}
require_once('includes/footer.php');
$passportconn->close();
?>