<?php
session_start();
require_once('../passport.conn.php'); // $conn: DB Connection Object
require_once('../passport.discord.php'); // DISCORD_CLIENT_ID: string, DISCORD_CLIENT_SECRET: string
require_once('../passport.google.php'); // GOOGLE_CLIENT_ID: string, GOOGLE_CLIENT_SECRET: string

$services = [];
$services['discord'] = new Service("Discord", "/img/icons/discord.svg", "#7289DA", "https://discordapp.com/api", "../passport.discord.php");
$services['google'] = new Service("Google", "/img/icons/google.svg", "#e0e0e0", "https://www.googleapis.com", "../passport.google.php");

$me = new User();
if(isset($_SESSION['userid'])){
	$me->get($_SESSION['userid']);
}

class User {
	/*
	public int $id;
	public string $username;
	public DiscordUser $discord;
	public GoogleUser $google;
	public string $pfp;
	public Email $email;
	public bool $admin;
	public bool $banned;
	*/
	public $id;
	public $username;
	public $discord;
	public $google;
	public $pfp;
	public $email;
	public $admin;
	public $banned;
	
	//public bool $exists = false;
	public $exists = false;
	
	function get(int $id){
		$result = $conn->query("SELECT * FROM user WHERE Id = $id");
		if($result){
			if($result->num_rows > 0){
				$this->id = $id;
				$row = $result->fetch_assoc();
				$this->username = $row['Username'];
				$this->pfp = $row['ProfileUrl'];
				$this->email = new Email($row['Email'], $row['EmailVerificationToken'], $row['EmailVerified']);
				$this->admin = $row['Admin'];
				$this->banned = $row['Banned'];
				
				if(!is_null($row['DiscordToken'])){
					$this->discord = new DiscordUser($id, $row['DiscordToken']);
					if(!$this->discord->online){
						if(strlen($row['DiscordUsername']) > 5){
							$discordusername = substr($row['DiscordUsername'], 0, strlen($row['DiscordUsername'])-5);
							$discorddiscrim  = intval(substr($row['DiscordUsername'], strlen($row['DiscordUsername'])-4));
							$this->discord->offlinesetup($discordusername, $discorddiscrim);
						}else{
							$this->discord->offlinesetup('Unknown', '0000');
						}
					}
				}
				
				if(!is_null($row['GoogleToken'])){
					$this->google = new GoogleUser($row['GoogleToken']);
				}
			}
		}
	}
}

class OauthUser {
	/*
	public int $id;
	public string $token;
	public Service $service;
	*/
	public $id;
	public $token;
	public $service;
	
	//public bool $online = false;
	public $online = false;
}

class DiscordUser extends OauthUser {
	/*
	public string $username;
	public string $discriminator;
	public string $email;
	public string $pfp;
	*/
	public $username;
	public $discriminator;
	public $email;
	public $pfp;
	
	function __construct($passportid, $token){
		global $services;
		$this->service = $services['discord'];
		
		//$online = true;
	}
	
	function offlinesetup($username, $discriminator){
		$this->username = $username;
		$this->discriminator = $discriminator;
	}
}

class GoogleUser extends OauthUser {
	/*
	public string $name;
	public string $email;
	public string $pfp;
	*/
	public $name;
	public $email;
	public $pfp;
	
	function __construct($token){
		global $services;
		$this->service = $services['google'];
		
		//$online = true;
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
	
	function __construct($name, $icon, $colour, $api_url, $secret_file){
		$this->name = $name;
		$this->icon = $icon;
		$this->colour = $colour;
		$this->api_url = $api_url;
		$this->secret_file = $secret_file;
	}
}

?>