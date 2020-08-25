<?php
session_start();
require_once('../passport.conn.php');
require_once('../passport.discord.php');
require_once('../passport.google.php');

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
	*/
	public $id;
	public $token;
	
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
	
	function __construct($token)
	{
		
		
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

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Passport</title>
	<meta name="description" content="Manage your passport account for Yiays.com projects like the blog, MemeDB and PukekoHost.">
	
	<link rel="stylesheet" href="/css/style.css?v=34">
	<link rel="stylesheet" href="https://cdn.yiays.com/normalize.css">
</head>
<body>
	<header>
		<div class="icons">
			<img src="/img/icons/passport.svg" alt="Logo for Passport" title="Passport">
		</div>
		<h1>Passport</h1>
		<p style="font-size: 1.1em;"><b>Passport gives you one account for all projects on Yiays.com!</b></p>
		<div class="icons icons-mini">
			<img src="/img/icons/yiays.svg" alt="Logo for Yiays.com" title="Yiays.com">
			<img src="/img/icons/blog.svg" alt="Logo for Yiays Blog" title="Yiays Blog">
			<img src="/img/icons/meme.svg" alt="Logo for MemeDB" title="MemeDB">
			<img src="/img/icons/merely.svg" alt="Logo for Merely Services" title="Merely Services">
			<img src="/img/icons/pukeko.svg" alt="Logo for PukekoHost" title="PukekoHost">
			<img src="/img/icons/kahoot.svg" alt="Logo for KahootDiscord" title="KahootDiscord">
		</div>
		<p><i>Get your passport with Discord or email today!</i></p>
		<div class="header-bg">
			<div class="header-bg-pan">
				<img src="/img/previews/yiays.jpg" alt="Yiays.com Preview">
				<img src="/img/previews/blog.jpg" alt="Yiays Blog Preview">
				<img src="/img/previews/meme.jpg" alt="MemeDB Preview">
				<img src="/img/previews/merely.jpg" alt="Merely Services Preview">
				<img src="/img/previews/pukeko.jpg" alt="PukekoHost Preview">
			</div>
		</div>
	</header>
	<div class="wrapper">
		<?php
			if($me->exists){
				echo "
					<div class=\"panel\">
						<div class=\"panel-header\">
							<h2>Welcome, $me->username!</h2>
						</div>
					</div>
				";
			}else{
				echo "
					<div class=\"panel\">
						<div class=\"panel-header\">
							<h2>Create an account</h2>
						</div>
					</div>
				";
			}
		?>
	</div>
	<footer>
		&copy; 2020 Yiays
	</footer>
	
	<script src="https://cdn.yiays.com/jquery-3.5.1.min.js"></script>
</body>
</html>