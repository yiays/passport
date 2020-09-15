<?php
namespace passport;

require_once($_SERVER['DOCUMENT_ROOT'].'/../passport.conn.php'); // $conn: DB Connection Object
require_once($_SERVER['DOCUMENT_ROOT'].'/../passport.discord.php'); // DISCORD_CLIENT_ID: string, DISCORD_CLIENT_SECRET: string
require_once($_SERVER['DOCUMENT_ROOT'].'/../passport.google.php'); // GOOGLE_CLIENT_ID: string, GOOGLE_CLIENT_SECRET: string

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/util.php');

$services = [];
$services['discord'] = new Service("Discord", "/img/icons/discord.svg", "#7289DA", "https://discordapp.com/api", "../passport.discord.php");
$services['google'] = new Service("Google", "/img/icons/google.svg", "#e0e0e0", "https://www.googleapis.com", "../passport.google.php");

class User {
	/*
	public int $id;
	public string $username;
	public Session $session;
	public array $sessions;
	public array $services;
	public string $pfp;
	public Email $email;
	public bool $admin;
	public bool $banned;
	*/
	public $id;
	public $username;
	public $session;
	public $sessions;
	public $services;
	public $pfp;
	public $email;
	public $admin;
	public $banned;
	
	function __construct($id)
	{
		$this->id = (is_null($id)?null:intval($id));
	}
	
	function fetch(){
		global $conn;
		
		$result = $conn->query("SELECT * FROM user WHERE Id = $this->id");
		if($result){
			if($result->num_rows == 1){
				$this->setup($result->fetch_assoc());
			}else{
				return false;
			}
		}else{
			throw new \Exception("Failed to fetch user; $conn->error");
			return false;
		}
	}
	
	function setup($row){
		$this->id = intval($row['Id']);
		$this->username = $row['Username'];
		$this->pfp = $row['ProfileUrl'];
		$this->email = new Email($row['Email'], $row['EmailVerificationToken'], $row['EmailVerified']);
		$this->admin = boolval($row['Admin']);
		$this->banned = boolval($row['Banned']);
	}
	
	function getservices(){
		global $conn;
		
		$this->services = [];
		$result = $conn->query("SELECT * FROM linked_service WHERE UserId = $this->id");
		if($result){
			while($row = $result->fetch_assoc()){
				// Create specialized object depending on platform
				switch($row['Platform']){
					case 'discord':
						$service = new DiscordUser();
					break;
					case 'google':
						$service = new GoogleUser();
					break;
					default:
						$service = new ServiceUser();
						trigger_error("Unrecognized passport service: '$row[Platform]'", E_USER_WARNING);
				}
				
				$service->id = $row['UserId'];
				$service->name = $row['Name'];
				$service->pfp = $row['ProfileUrl'];
				$service->email = $row['Email'];
				$service->token = $row['Token'];
				$service->parseData($row['AdditionalData']);
				
				// Use token to fetch additional information
				$service->get();
				
				$this->services[$row['Platform']] = $service;
			}
		}else{
			throw new \Exception("Failed to fetch user services; $conn->error");
			return false;
		}
		return $this->services;
	}
	
	function getsessions(){
		global $conn;
		
		$this->sessions = [];
		$result = $conn->query("SELECT * FROM session WHERE UserId = $this->id ORDER BY Expiry DESC");
		if(!$result){
			throw new \Exception("Failed to fetch user sessions; $conn->error");
			return false;
		}
		while($row = $result->fetch_assoc()){
			$this->sessions[$row['SessionId']] = new Session($row['SessionId'], $row['UserId'], $row['Description'], $row['Expiry']);
		}
		return $this->sessions;
	}
}

class Session {
	public $token;
	public $uid;
	public $desc;
	public $expiry;
	
	function __construct($token, $uid=null, $desc=null, $expiry=null)
	{
		global $conn;
		
		$this->token = $conn->escape_string($token);
		$this->uid = (is_null($uid)?null:intval($uid));
		$this->desc = (is_null($desc)?$this->generate_desc():$conn->escape_string($desc));
		$this->expiry = (is_null($expiry)?$this->generate_expiry():intval($expiry));
	}
	
	function generate_desc(){
		ini_set('browscap', $_SERVER['DOCUMENT_ROOT'].'/includes/lite_php_browscap.ini');
		$browsernfo = getBrowser();
		$ipnfo = json_decode(file_get_contents("http://ipinfo.io/$_SERVER[REMOTE_ADDR]/json"));
		return "Unnamed $browsernfo[platform] device via $browsernfo[name] in $ipnfo->city.";
	}
	function generate_expiry(){
		// Expires in 7 days
		return time() + (7 * 24 * 60 * 60);
	}
	
	// High level functions
	function revoke(){
		$this->expiry = 0;
		$this->cookie_store();
		$this->mysql_delete();
	}
	
	// Functions for storing tokens
	function mysql_insert(){
		global $conn;
		
		$expiry = date('Y-m-d H:i:s', $this->expiry);
		$result = $conn->query("INSERT INTO session(SessionId, UserId, Description, Expiry) VALUES(\"$this->token\", $this->uid, \"$this->desc\", \"$expiry\")");
		if(!$result){
			throw new \Exception("Failed to store token; $conn->error");
		}else{
			return $this;
		}
	}
	function mysql_update(){
		global $conn;
		
		$expiry = date('Y-m-d H:i:s', $this->expiry);
		$result = $conn->query("UPDATE session SET Description = \"$this->desc\", Expiry = \"$expiry\" WHERE SessionId = \"$this->token\"");
		if(!$result){
			throw new \Exception("Failed to update stored token; $conn->error");
			return null;
		}else{
			return $this;
		}
	}
	function mysql_delete(){
		global $conn;
		
		$result = $conn->query("DELETE FROM session WHERE SessionId = \"$this->token\"");
		if(!$result){
			throw new \Exception("Failed to remove stored token; $conn->error");
			return false;
		}
		return true;
	}
	function cookie_store(){
		setcookie('passportToken', $this->token, $this->expiry, '/', '.yiays.com', true, false);
	}
}

class ServiceUser {
	/*
	public int $id;
	public string $name;
	public string $pfp;
	public string $email;
	public string $token;
	*/
	public $id;
	public $name;
	public $pfp;
	public $email;
	public $token;
	
	//public bool $activelink = false;
	public $activelink = false;
	
	function get(){
		$this->activelink = true;
	}
	
	function parseData($data){
		return;
	}
}

class DiscordUser extends ServiceUser {
	/*
	public string $username;
	public string $discriminator;
	*/
	public $username;
	public $discriminator;
	
	function get(){
		//TODO
	}
	
	function parseData($data){
		return;
	}
}

class GoogleUser extends ServiceUser {
	function get(){
		//TODO
	}
	
	function parseData($data){
		return;
	}
}

class Email {
	/*
	public string $address;
	public string $token;
	public bool $verified;
	*/
	public $address;
	public $token;
	public $verified;
	
	function __construct($address, $token, $verified)
	{
		$this->address = $address;
		$this->token = $token;
		$this->verified = $verified;
	}
}

class Service {
	/*
	public string $name;
	public string $icon;
	public string $api_url;
	public string $secret_file;
	*/
	
	public $name;
	public $icon;
	public $theme_color;
	public $api_url;
	public $secret_file;
	
	function __construct($name, $icon, $theme_color, $api_url, $secret_file){
		$this->name = $name;
		$this->icon = $icon;
		$this->theme_color = $theme_color;
		$this->api_url = $api_url;
		$this->secret_file = $secret_file;
	}
}

?>