<?php
namespace passport;

require_once(__DIR__.'/../../passport.conn.php');
require_once(__DIR__.'/../includes/util.php');
require_once(__DIR__.'/models/passport.php');

function create_token($uid, $desc=null){
	//Gives a user access to their account for this device. Should only be called if the password has already been verified.
	$session = new Session(null, $uid, $desc);
	$session->mysql_insert();
	$session->cookie_store(true);
	
	return $session;
}

function verify_user_token($tokenhash){
	$session = new Session($tokenhash);
	$user = $session->fetch();
	
	//As a bonus for verifying the token, extend its lifetime as well.
	if($user){
		$user->session->renew();
		return $user;
	}
	return false;
}

function verify_app_token($tokenhash){
	$appsession = new AppSession($tokenhash);
	return $appsession->fetch();
}

function autologin(){
	if(key_exists('passportToken', $_COOKIE) && isset($_COOKIE['passportToken']) && !is_null($_COOKIE['passportToken'])){
		return verify_user_token($_COOKIE['passportToken']);
	}
	return false;
}

function autoauthapp(){
	$headers = apache_request_headers();
	if(isset($headers['Authorization'])){
		$regresult = [];
		if(preg_match('/Token token="(.*)"/', $headers['Authorization'], $regresult)){
			$token = $regresult[1];
			return verify_app_token($token);
		}
	}
	return false;
}
?>