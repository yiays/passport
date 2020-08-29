<?php
session_start();

require_once('../passport.conn.php'); // $conn: DB Connection Object
require_once('../passport.discord.php'); // DISCORD_CLIENT_ID: string, DISCORD_CLIENT_SECRET: string
require_once('../passport.google.php'); // GOOGLE_CLIENT_ID: string, GOOGLE_CLIENT_SECRET: string

require_once('includes/util.php');

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
	public array $linked_services;
	public string $pfp;
	public Email $email;
	public bool $admin;
	public bool $banned;
	*/
	public $id;
	public $username;
	public $linked_services;
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
				
				$result = $conn->query("SELECT * FROM platform WHERE UserId = $id");
				if($result){
					while($row = $result->fetch_assoc()){
						// Create specialized object depending on platform
						switch($row['Platform']){
							case 'discord':
								$link = new DiscordUser();
							break;
							case 'google':
								$link = new GoogleUser();
							break;
							default:
								$link = new OauthUser();
						}
						
						$link->id = $row['Id'];
						$link->name = $row['Name'];
						$link->profileurl = $row['ProfileUrl'];
						$link->email = $row['Email'];
						$link->token = $row['Token'];
						
						// Use token to fetch additional information
						$link->get();
						
						$this->linked_services[$row['Platform']] = $link;
					}
				}else{
					//error
				}
			}
		}else{
			//error
		}
	}
}

class OauthUser {
	/*
	public int $id;
	public string $name;
	public string $profileurl;
	public string $email;
	public string $token;
	*/
	public $id;
	public $name;
	public $profileurl;
	public $email;
	public $token;
	
	//public bool $online = false;
	public $online = false;
	
	function get(){
		$online = true;
	}
}

class DiscordUser extends OauthUser {
	/*
	public string $username;
	public string $discriminator;
	*/
	public $username;
	public $discriminator;
	
	function get(){
		//TODO
	}
	
	function offlinesetup(){
		$this->username = substr($this->name, 0, strpos($this->name, '#'));
		$this->discriminator = substr($this->name, strlen($this->name) - 4);
	}
}

class GoogleUser extends OauthUser {
	function get(){
		//TODO
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