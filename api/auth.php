<?php
require_once('../passport.conn.php');
require_once('includes/util.php');

class passportToken{
	public $token;
	public $uid;
	public $desc;
	public $expiry;
	public $user = null;
	
	function __construct($token, $uid=null, $desc=null, $expiry=null)
	{
		$this->token = $token;
		$this->uid = $uid;
		$this->desc = (is_null($desc)?$this->generate_desc():$desc);
		$this->expiry = (is_null($expiry)?$this->generate_expiry():$expiry);
	}
	function generate_desc(){
		ini_set('browscap', dirname(__DIR__).'/includes/lite_php_browscap.ini');
		$browsernfo = getBrowser();
		$ipnfo = json_decode(file_get_contents("http://ipinfo.io/$_SERVER[REMOTE_ADDR]/json"));
		return "Unnamed $browsernfo[platform] device via $browsernfo[name] in $ipnfo->city.";
	}
	function generate_expiry(){
		// Expires in 7 days
		return time() + (7 * 24 * 60 * 60);
	}
	
	// Functions for storing tokens
	function mysql_insert(){
		global $conn;
		
		$desc = $conn->escape_string($this->desc);
		$expiry = date('Y-m-d H:i:s', $this->expiry);
		$result = $conn->query("INSERT INTO session(SessionId, UserId, Description, Expiry) VALUES(\"$this->token\", $this->uid, \"$desc\", \"$expiry\")");
		if(!$result){
			throw new Exception("Failed to store token; $conn->error");
		}else{
			return $this;
		}
	}
	function mysql_update(){
		global $conn;
		
		$desc = $conn->escape_string($this->desc);
		$expiry = date('Y-m-d H:i:s', $this->expiry);
		$result = $conn->query("UPDATE session SET Description = \"$desc\", Expiry = \"$expiry\"");
		if(!$result){
			throw new Exception("Failed to update stored token; $conn->error");
			return null;
		}else{
			return $this;
		}
	}
	function cookie_store(){
		setcookie('passportToken', $this->token, $this->expiry, '/', '.yiays.com', true, false);
	}
	
	//Functions for fetching tokens
	function fetch(){
		global $conn;
		
		$tokenhash = $conn->escape_string($this->token);
		$result = $conn->query("SELECT Description,Expiry,user.* FROM session LEFT JOIN user ON session.UserId = user.Id WHERE SessionId = \"$tokenhash\" AND Expiry > CURRENT_TIMESTAMP()");
		
		if(!$result){
			throw new Exception("Failed to restore login data from token; $conn->error");
			return false;
		}
		if($result->num_rows != 1) return false;
		
		$row = $result->fetch_assoc();
		$this->desc = $row['Description'];
		unset($row['Description']);
		$this->expiry = strtotime($row['Expiry']);
		unset($row['Expiry']);
		$this->user = $row;
		
		return true;
	}
}

function generate_token($uid, $desc=null){
	//Gives a user access to their account for this device. Should only be called if the password has already been verified.
	$token = new passportToken(base64_encode(random_bytes(16)), $uid, $desc);
	$token->mysql_insert();
	$token->cookie_store();
	
	return $token;
}

function verify_token($tokenhash){
	$token = new passportToken($tokenhash);
	$result = $token->fetch();
	
	//As a bonus for verifying the token, extend it's lifetime as well.
	if($result){
		$token->generate_expiry();
		$token->mysql_update();
		$token->cookie_store();
		return $token;
	}
	return false;
}

function dologin($token){
	if(is_null($token->user)) $token->fetch();
	
	if(!is_null($token->user)){
		$_SESSION['user'] = $token->user;
		$_SESSION['uid'] = $token->user['Id'];
		return true;
	}
	return false;
}

function autologin(){
	if(key_exists('passportToken', $_COOKIE) && isset($_COOKIE['passportToken']) && !is_null($_COOKIE['passportToken'])){
		$token = verify_token($_COOKIE['passportToken']);
		if($token !== false){
			dologin($token);
			return true;
		}
	}
	return false;
}
?>