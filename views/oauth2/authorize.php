<?php
require_once('api/auth.php');

$user = passport\autologin();
if(!$user){
	header('Location: /account/login?redirect='.urlencode($_SERVER['REQUEST_URI']));
	die();
}

$app =  passport\getApplicationFromData($_GET);

$title = "Authorize an application";
$miniheader = true;
require_once('includes/header.php');
echo $app->authwindow($user);
require_once('includes/footer.php');
?>