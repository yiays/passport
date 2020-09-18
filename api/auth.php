<?php
namespace passport;

require_once($_SERVER['DOCUMENT_ROOT'].'/../passport.conn.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/util.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/models/passport.php');

class Authenticator{
	// public User $user = null;
	public $user = null;
	
	function __construct($session, $uid=null)
	{
		$this->user = new User($uid);
		$this->user->session = $session;
	}
	
	//Functions for fetching tokens
	function fetch(){
		global $conn;
		
		$result = $conn->query("SELECT Description,Expiry,user.* FROM session LEFT JOIN user ON session.UserId = user.Id WHERE SessionId = \"{$this->user->session->token}\" AND Expiry > CURRENT_TIMESTAMP()");
		
		if(!$result){
			throw new \Exception("Failed to restore login data from token; $conn->error");
			return false;
		}
		if($result->num_rows != 1) return false;
		
		$row = $result->fetch_assoc();
		$this->user->session->uid = intval($row['Id']);
		$this->user->session->desc = $row['Description'];
		unset($row['Description']);
		$this->user->session->expiry = strtotime($row['Expiry']);
		unset($row['Expiry']);
		$this->user->setup($row);
		
		return true;
	}
}

function create_token($uid, $desc=null){
	//Gives a user access to their account for this device. Should only be called if the password has already been verified.
	$session = new Session(rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '='), $uid, $desc);
	$authenticator = new Authenticator($session, $uid);
	$authenticator->user->session->mysql_insert();
	$authenticator->user->session->cookie_store(true);
	
	return $authenticator;
}

function verify_token($tokenhash){
	$authenticator = new Authenticator(new Session($tokenhash));
	$result = $authenticator->fetch();
	
	//As a bonus for verifying the token, extend its lifetime as well.
	if($result){
		$authenticator->user->session->renew();
		return $authenticator;
	}
	return false;
}

function dologin($authenticator){
	if(is_null($authenticator->user)) $authenticator->fetch();
	
	if(!is_null($authenticator->user)){
		return $authenticator->user;
	}
	return false;
}

function autologin(){
	if(key_exists('passportToken', $_COOKIE) && isset($_COOKIE['passportToken']) && !is_null($_COOKIE['passportToken'])){
		$authenticator = verify_token($_COOKIE['passportToken']);
		if($authenticator !== false){
			return dologin($authenticator);
		}
	}
	return false;
}
?>