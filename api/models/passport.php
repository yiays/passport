<?php
namespace passport;

require_once(__DIR__.'/../../../passport.conn.php'); // $passportconn: DB Connection Object
//require_once(__DIR__.'/../../../passport.discord.php'); // DISCORD_CLIENT_ID: string, DISCORD_CLIENT_SECRET: string
//require_once(__DIR__.'/../../../passport.google.php'); // GOOGLE_CLIENT_ID: string, GOOGLE_CLIENT_SECRET: string

require_once(__DIR__.'/../../includes/util.php');

$services = [];
$services['discord'] = new Service("Discord", "/img/icons/discord.svg", "#7289DA", "https://discordapp.com/api", "../passport.discord.php");
$services['google'] = new Service("Google", "/img/icons/google.svg", "#e0e0e0", "https://www.googleapis.com", "../passport.google.php");

class User {
	public int $id;
	public string $username;
	public Session $session;
	public array $sessions;
	public array $services;
	public array $authapps;
	public string $pfp;
	public Email $email;
	public bool $admin;
	public bool $banned;
	
	function __construct($id)
	{
		$this->id = (is_null($id)?null:intval($id));
	}
	
	function fetch(){
		global $passportconn;
		
		$result = $passportconn->query("SELECT * FROM user WHERE Id = $this->id");
		if($result){
			if($result->num_rows == 1){
				$this->setup($result->fetch_assoc());
			}else{
				return false;
			}
		}else{
			throw new \Exception("Failed to fetch user; $passportconn->error");
			return false;
		}
	}
	
	function setup($row){
		$this->id = intval($row['Id']);
		$this->username = $row['Username'];
		$this->pfp = is_null($row['ProfileUrl'])?'https://passport.yiays.com/img/icons/user.svg':$row['ProfileUrl'];
		$this->email = new Email($row['Email'], $row['EmailVerificationToken'], $row['EmailVerified']);
		$this->admin = boolval($row['Admin']);
		$this->banned = boolval($row['Banned']);
	}
	
	function userpreviewbox(){
		return "
		<div class=\"userpreviewbox\">
			<b>$this->username</b><br>
			<span class=\"sub\">Not you? <a href=\"/account/logout\">Logout</a></span>
		</div>";
	}
	
	function getservices(){
		global $passportconn;
		
		$this->services = [];
		$result = $passportconn->query("SELECT * FROM linked_service WHERE UserId = $this->id");
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
			throw new \Exception("Failed to fetch user services; $passportconn->error");
			return false;
		}
		return $this->services;
	}
	
	function getsessions(){
		global $passportconn;
		
		$this->sessions = [];
		$result = $passportconn->query("SELECT * FROM session WHERE UserId = $this->id ORDER BY Expiry DESC");
		if(!$result){
			throw new \Exception("Failed to fetch user sessions; $passportconn->error");
			return false;
		}
		while($row = $result->fetch_assoc()){
			$this->sessions[$row['SessionId']] = new Session($row['SessionId'], $row['UserId'], $row['Description'], $row['Expiry']);
		}
		return $this->sessions;
	}
	
	function getauthapps(){
		global $passportconn;
		
		$this->authapps = [];
		$result = $passportconn->query("SELECT * FROM auth_app WHERE UserId = $this->id ORDER BY Expiry ASC");
		if(!$result){
			throw new \Exception("Failed to fetch user's authorized apps; $passportconn->error");
			return false;
		}
		while($row = $result->fetch_assoc()){
			$this->authapps[$row['SessionId']] = new AppSession($row['SessionId'], $row['UserId'], $row['AppId'], $row['AuthCode'], $row['Expiry']);
		}
		return $this->authapps;
	}
}

class Session {
	public $token;
	public $TOKEN_LEN = 22;
	public $uid;
	public $desc;
	public $expiry;
	public $EXPIRY_LEN = (7 * 24 * 60 * 60); // Expires in 7 days
	public $verified = false;
	
	function __construct($token, $uid=null, $desc=null, $expiry=null)
	{
		global $passportconn;
		
		$this->token = is_null($token)?$this->generate_token($this->TOKEN_LEN):$passportconn->escape_string($token);
		$this->uid = is_null($uid)?null:intval($uid);
		$this->desc = is_null($desc)?$this->generate_desc():$passportconn->escape_string($desc);
		$this->expiry = is_null($expiry)?$this->generate_expiry($this->EXPIRY_LEN):strtotime($expiry);
	}
	
	function generate_token($len){
		$this->token = rtrim(strtr(base64_encode(random_bytes(floor($len / 1.33))), '+/', '-_'), '=');
		return $this->token;
	}
	function generate_desc(){
		ini_set('browscap', $_SERVER['DOCUMENT_ROOT'].'/includes/lite_php_browscap.ini');
		$browsernfo = getBrowser();
		$ipnfo = json_decode(file_get_contents("http://ipinfo.io/$_SERVER[REMOTE_ADDR]/json"));
		$this->desc = "Unnamed $browsernfo[platform] device via $browsernfo[name] in $ipnfo->city.";
		return $this->desc;
	}
	function generate_expiry($len){
		$this->expiry = time() + $len;
		return $this->expiry;
	}
	
	// High level functions
	function fetch(){
		// Gets extra session information and the associated user
		global $passportconn;
		
		$result = $passportconn->query("SELECT Description,Expiry,user.* FROM session LEFT JOIN user ON session.UserId = user.Id WHERE SessionId = \"{$this->token}\" AND Expiry > CURRENT_TIMESTAMP()");
		
		if(!$result){
			throw new \Exception("Failed to restore login data from token; $passportconn->error");
			return false;
		}
		if($result->num_rows != 1) return false;
		$this->verified = true;
		
		$row = $result->fetch_assoc();
		$user = new User(intval($row['Id']));
		$this->desc = $row['Description'];
		unset($row['Description']);
		$this->expiry = strtotime($row['Expiry']);
		unset($row['Expiry']);
		$user->setup($row);
		$user->session = $this;
		
		return $user;
	}
	function revoke(){
		$this->expiry = 0;
		$this->mysql_delete();
		$this->cookie_store();
	}
	function rename($name){
		global $passportconn;
		
		$this->desc = $passportconn->escape_string($name);
		$this->mysql_update();
	}
	function renew(){
		$this->expiry = $this->generate_expiry($this->EXPIRY_LEN);
		$this->mysql_update();
		$this->cookie_store();
	}
	
	// Functions for storing tokens
	function mysql_insert(){
		global $passportconn;
		
		$expiry = date('Y-m-d H:i:s', $this->expiry);
		$result = $passportconn->query("INSERT INTO session(SessionId, UserId, Description, Expiry) VALUES(\"$this->token\", $this->uid, \"$this->desc\", \"$expiry\")");
		if(!$result){
			throw new \Exception("Failed to store token; $passportconn->error");
		}else{
			return $this;
		}
	}
	function mysql_update(){
		global $passportconn;
		
		$expiry = date('Y-m-d H:i:s', $this->expiry);
		$result = $passportconn->query("UPDATE session SET Description = \"$this->desc\", Expiry = \"$expiry\" WHERE SessionId = \"$this->token\"");
		if(!$result){
			throw new \Exception("Failed to update stored token; $passportconn->error");
			return null;
		}else{
			return $this;
		}
	}
	function mysql_delete(){
		global $passportconn;
		
		$result = $passportconn->query("DELETE FROM session WHERE SessionId = \"$this->token\"");
		if(!$result){
			throw new \Exception("Failed to remove stored token; $passportconn->error");
			return false;
		}
		return true;
	}
	function cookie_store($force=false){
		if((!isset($_COOKIE['passportToken']) || $_COOKIE['passportToken'] == $this->token) || $force){
			setcookie('passportToken', $this->token, $this->expiry, '/', '.yiays.com', true, false);
			return true;
		}
		return false;
	}
}

class AppSession extends Session {
	public $TOKEN_LEN = 32;
	public $AUTHCODE_LEN = 10;
	public $appid;
	public $desc = null;
	public $authcode;
	public $user = null;
	public $EXPIRY_LEN = (3 * 30 * 24 * 60 * 60); // Expires in 3 months
	public $on_db = false;
	
	function __construct($token=null, $uid=null, $appid=null, $authcode=null, $expiry=null)
	{
		global $passportconn;
		
		$this->on_db = is_null($token)?false:true;
		$this->token = is_null($token)?$this->generate_token($this->TOKEN_LEN):$passportconn->escape_string($token);
		$this->uid = is_null($uid)?null:intval($uid);
		$this->appid = is_null($appid)?null:intval($appid);
		$this->authcode = is_null($authcode)?null:intval($authcode);
		$this->expiry = is_null($expiry)?$this->generate_expiry($this->EXPIRY_LEN):strtotime($expiry);
	}
	
	function generate_authcode(){
		$this->authcode = abs(unpack('L', random_bytes(floor($this->AUTHCODE_LEN * 4)))[1]); // Should be using 'Q' mode and no abs, requires 64 bit php...
		return $this->authcode;
	}
	
	// High level functions
	function fetch(){
		// Gets extra app session information and the associated app
		global $passportconn;
		
		if($this->on_db){
			$verificationmethod = "SessionId = \"{$this->token}\"";
		}else{
			$verificationmethod = "AuthCode = \"{$this->authcode}\"";
		}
		
		$result = $passportconn->query("SELECT Expiry,AppId,UserId,application.* FROM auth_app LEFT JOIN application ON auth_app.AppId = application.Id WHERE $verificationmethod AND Expiry > CURRENT_TIMESTAMP()");
		
		if(!$result){
			throw new \Exception("Failed to restore login data from token; $passportconn->error");
			return false;
		}
		if($result->num_rows != 1) return false;
		$this->verified = true;
		
		$row = $result->fetch_assoc();
		$user = new User(intval($row['UserId']));
		$app = new Application(intval($row['AppId']));
		$this->expiry = strtotime($row['Expiry']);
		unset($row['Expiry']);
		$app->setup($row);
		$this->user = $user;
		$app->session = $this;
		
		return $app;
	}
	function rename($desc){
		return false;
	}
	function renew(){
		$this->expiry = $this->generate_expiry($this->EXPIRY_LEN);
		$this->mysql_update();
	}
	
	// Functions for storing tokens
	function mysql_insert(){
		global $passportconn;
		
		$expiry = date('Y-m-d H:i:s', $this->expiry);
		$result = $passportconn->query("INSERT INTO auth_app(SessionId, AppId, UserId, AuthCode, Expiry) VALUES(\"$this->token\", $this->appid, $this->uid, $this->authcode, \"$expiry\")");
		if(!$result){
			throw new \Exception("Failed to store token; $passportconn->error");
		}else{
			return $this;
		}
	}
	function mysql_update(){
		global $passportconn;
		
		$expiry = date('Y-m-d H:i:s', $this->expiry);
		$result = $passportconn->query("UPDATE auth_app SET Expiry = \"$expiry\" WHERE SessionId = \"$this->token\"");
		if(!$result){
			throw new \Exception("Failed to update stored token; $passportconn->error");
			return null;
		}else{
			return $this;
		}
	}
	function mysql_delete(){
		global $passportconn;
		
		$result = $passportconn->query("DELETE FROM auth_app WHERE SessionId = \"$this->token\"");
		if(!$result){
			throw new \Exception("Failed to remove stored token; $passportconn->error");
			return false;
		}
		return true;
	}
	function cookie_store($force=false){
		return false;
	}
}

class ServiceUser {
	public int $id;
	public string $name;
	public string $pfp;
	public string $email;
	public string $token;
	
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
	public string $username;
	public string $discriminator;
	
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
	public string $address;
	public ?string $token;
	public bool $verified;
	
	function __construct($address, $token, $verified)
	{
		$this->address = $address;
		$this->token = $token;
		$this->verified = $verified;
	}
	function __toString()
	{
		return $this->address;
	}
}

class Service {
	public string $name;
	public string $icon;
	public string $theme_color;
	public string $api_url;
	public string $secret_file;
	
	function __construct($name, $icon, $theme_color, $api_url, $secret_file){
		$this->name = $name;
		$this->icon = $icon;
		$this->theme_color = $theme_color;
		$this->api_url = $api_url;
		$this->secret_file = $secret_file;
	}
}

class Application {
	public $id;
	public $secret;
	public $name;
	public $url;
	public $SECRET_LEN = 44;
	public $desc;
	public $icon;
	public $returnurls = [];
	public $session;
	public $hidden;
	
	function __construct($id=null)
	{
		$this->id = $id;
	}
	
	function setup($row){
		$this->id = intval($row['Id']);
		$this->secret = $row['Secret'];
		$this->name = $row['Name'];
		$this->url = $row['Url'];
		$this->desc = $row['Description'];
		$this->icon = $row['Icon'];
		$this->returnurls = explode(',', $row['ReturnUrls'], 8);
		$this->hidden = boolval($row['Hidden']);
	}
	
	function generate_secret()
	{
		$this->secret = rtrim(strtr(base64_encode(random_bytes(floor($this->SECRET_LEN / 1.33))), '+/', '-_'), '=');
	}
	function authorize(User $user){
		if($this->id !== null){
			$session = null;
			
			foreach($user->getauthapps() as $authapp){
				if($authapp->appid == $this->id){
					if($authapp->expiry > time()){
						$session = $authapp;
						$session->generate_authcode();
						$session->expiry = $session->generate_expiry(5 * 60);
						$session->mysql_update();
					}else{
						$authapp->mysql_delete();
					}
				}
			}
			
			if(is_null($session)){
				$session = new AppSession(null, $user->id, $this->id, null);
				$session->generate_authcode();
				$session->expiry = $session->generate_expiry(5 * 60); // AppSessions expire in 5 minutes if unredeemed.
				$session->mysql_insert();
			}
			return $session;
		}
		return false;
	}
	
	function authwindow($user){
		return "
		<div class=\"card\">
			<div class=\"card-header\">
				<h3>Authorize with $this->name</h3>
			</div>
			<div class=\"card-featurette featurette-2panel\">
				<img src=\"$user->pfp\" width=\"256\" height=\"256\" alt=\"$user->username's Profile Picture\" title=\"$user->username's Profile Picture\">
				<img src=\"$this->icon\" width=\"256\" height=\"256\" alt=\"$this->name Icon\" title=\"$this->name Icon\">
				".$user->userpreviewbox()."
				<div>
					<b>$this->name</b><br>
					<a class=\"sub\" href=\"$this->url\">$this->url</a>
				</div>
			</div>
			<div class=\"card-body\">
				<p>$this->desc</p>
				<p>
					$this->name will have access to;
					<ul>
						<li>Your username<br><i>$user->username</i></li>
						<li>Your email address<br><i>$user->email</i></li>
						<li>Your profile picture</li>
					</ul>
				</p>
			</div>
			<div class=\"card-footer\">
				<form target=\"\" method=\"POST\">
					<button type=\"cancel\" data-cancel>Cancel</button>
					<input type=\"submit\" name=\"action\" value=\"Authorize\">
				</form>
			</div>
		</div>";
	}
}
class InvalidApplication extends Application {
	public $id = null;
	public $name = "Invalid Application";
	public $desc = "This application is invalid, the developer that created this authorization link didn't provide all the required information.";
	public $icon = "/img/icons/invalid.svg";
	
	function __construct(){
		
	}
	function authwindow($user)
	{
		return "
		<div class=\"card\">
			<div class=\"card-header\">
				<h3>$this->name</h3>
			</div>
			<div class=\"card-featurette\">
				<img src=\"$this->icon\" width=\"256\" height=\"256\" alt=\"$this->name Icon\" title=\"$this->name Icon\">
			</div>
			<div class=\"card-body\">
				<p>$this->desc</p>
			</div>
			<div class=\"card-footer\">
				<button type=\"cancel\" data-cancel>Cancel</button>
			</div>
		</div>";
	}
}

function getApplications($hidden=false){
	global $passportconn;
	$result = $passportconn->query('SELECT * FROM application' . ($hidden?'':' WHERE Hidden = FALSE'));
	if(!$result){
		throw new \Exception("Failed to get list of applications; $passportconn->error");
		return [];
	}
	$applications = [];
	while($row = $result->fetch_assoc()){
		$app = new Application();
		$app->setup($row);
		$applications []= $app;
	}
	return $applications;
}
function getApplication($id){
	global $passportconn;
	$result = $passportconn->query('SELECT * FROM application WHERE Id = ' . $passportconn->escape_string($id));
	if(!$result){
		throw new \Exception("Failed to get list of applications; $passportconn->error");
		return null;
	}
	$row = $result->fetch_assoc();
	
	$app = new Application();
	$app->setup($row);
	return $app;
}
function getApplicationFromData($data){
	global $passportconn;
	if(!isset($data['id']) || !isset($data['redirect']) || !is_numeric($data['id'])){
		return new InvalidApplication();
	}
	
	$result = $passportconn->query("SELECT * FROM application WHERE Id = $data[id]");
	if(!$result || $result->num_rows == 0){
		return new InvalidApplication();
	}
	
	$row = $result->fetch_assoc();
	if(!in_array(urldecode($data['redirect']), explode(',', $row['ReturnUrls']))){
		return new InvalidApplication();
	}
	
	$app = new Application();
	$app->setup($row);
	return $app;
}
?>