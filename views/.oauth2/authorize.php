<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/api/auth.php');

$user = passport\autologin();
if(!$user){
	header('Location: /account/login?redirect='.urlencode($_SERVER['REQUEST_URI']));
	die();
}

$app =  passport\getApplicationFromData($_GET);

if(isset($_POST['action'])){
	switch(strtolower($_POST['action'])){
		case 'authorize':
			if($_SERVER['HTTP_REFERER'] !== "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"){
				http_response_code(403);
				die("This request failed a security check. Please make sure your browser sends referer information.");
			}
			$appsession = $app->authorize($user);
			header("Location: $_GET[redirect]".(strpos($_GET['redirect'], '?') !== false? '&' : '?')."code=$appsession->authcode");
			die();
		break;
		default:
			http_response_code(404);
			die("Unrecognized command.");
	}
}

$title = "Authorize an application";
$miniheader = true;
$wrapperclass = 'authenticator';
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/header.php');
echo $app->authwindow($user);
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/footer.php');
?>